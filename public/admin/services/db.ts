
import { initialData } from './mockData';

// --- TYPES (Frontend Interface) ---
export type User = {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
  joined: string;
};

export type Course = {
  id: number;
  title: string;
  instructor: string;
  category: string;
  category_id?: number;
  instructor_id?: number;
  price: number;
  status: string;
  students: number;
  level: string;
  start_date: string;
  end_date: string;
  description: string;
};

export type Module = {
  id: number;
  course_id: number;
  title: string;
  description: string;
  duration_minutes: number;
};

export type Lesson = {
  id: number;
  module_id: number;
  title: string;
  type: 'Video' | 'Reading' | 'Quiz' | 'Assignment';
  content: string;
  duration: string;
};

export type Enrollment = {
  id: number;
  user_id: number;
  course_id: number;
  enrolled_at: string;
  status: string;
  progress: number;
  user_name?: string;
  course_title?: string;
};

export type Transaction = {
  id: string;
  user_id?: number;
  student: string;
  amount: number;
  date: string;
  status: string;
  method: string;
  type: string;
};

export type Announcement = {
  id: number;
  title: string;
  content: string;
  type: string;
  priority: string;
  is_published: boolean;
  date: string;
};

export type Category = {
  id: number;
  name: string;
  description: string;
  color: string;
  count: number;
};

export type Certificate = {
  id: number;
  code: string;
  student: string;
  course: string;
  date: string;
  verified: boolean;
  verification_code?: string;
};

export type Settings = {
  site_name: string;
  currency: string;
  registration_fee: number;
  allow_partial_payments: boolean;
  [key: string]: any;
};

// --- API SERVICE ---
class DBService {
  private API_BASE = '/api';
  private mock = { ...initialData };

  // Helper to simulate network delay for mock fallback
  private delay(ms = 600) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  // Generic Fetch Wrapper with better error handling
  private async fetch<T>(endpoint: string, options?: RequestInit): Promise<{ data: T | null; error: string | null }> {
    try {
      const res = await fetch(`${this.API_BASE}${endpoint}`, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        ...options,
      });

      const json = await res.json();

      if (!res.ok || json.success === false) {
        const errorMsg = json.error || json.message || `API Error: ${res.status}`;
        console.error(`[API] ${endpoint} failed:`, errorMsg);
        return { data: null, error: errorMsg };
      }

      return { data: json.data || json, error: null };
    } catch (error) {
      console.warn(`[API] Connection failed for ${endpoint}. Error:`, error);
      return { data: null, error: 'Network connection failed' };
    }
  }

  // --- USERS ---
  async getUsers(): Promise<User[]> {
    const { data, error } = await this.fetch<any[]>('/users.php');
    if (data && !error) {
      return data.map(u => ({
        id: u.id,
        name: `${u.first_name || ''} ${u.last_name || ''}`.trim() || u.username || 'Unknown',
        email: u.email,
        role: u.role_name || 'Student',
        status: u.status || 'active',
        joined: (u.created_at || '').split('T')[0]
      }));
    }
    await this.delay();
    return this.mock.users;
  }

  async addUser(user: Partial<User> & { password?: string }): Promise<{ success: boolean; error?: string; user?: User }> {
    const { data, error } = await this.fetch<any>('/users.php', {
      method: 'POST',
      body: JSON.stringify({
        name: user.name,
        email: user.email,
        password: user.password || 'TempPass123!',
        role: user.role || 'Student',
        status: user.status || 'active'
      })
    });

    if (error) {
      return { success: false, error };
    }

    return {
      success: true,
      user: {
        id: data?.id || Math.floor(Math.random() * 10000),
        name: user.name || '',
        email: user.email || '',
        role: user.role || 'Student',
        status: user.status || 'active',
        joined: new Date().toISOString().split('T')[0]
      }
    };
  }

  async editUser(id: number, updates: Partial<User>): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/users.php', {
      method: 'PUT',
      body: JSON.stringify({ id, ...updates })
    });

    if (error) {
      return { success: false, error };
    }
    return { success: true };
  }

  async deleteUser(id: number): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch(`/users.php?id=${id}`, {
      method: 'DELETE'
    });

    if (error) {
      return { success: false, error };
    }
    return { success: true };
  }

  async updateUserStatus(id: number, status: string): Promise<{ success: boolean; error?: string }> {
    return this.editUser(id, { status });
  }

  // --- COURSES ---
  async getCourses(): Promise<Course[]> {
    const { data, error } = await this.fetch<any[]>('/courses.php');
    if (data && !error) {
      return data.map(c => ({
        id: c.id,
        title: c.title,
        instructor: c.instructor_name || 'Unknown',
        category: c.category_name || 'General',
        category_id: c.category_id,
        instructor_id: c.instructor_id,
        price: parseFloat(c.price) || 0,
        status: c.status || 'draft',
        students: parseInt(c.enrollment_count || '0'),
        level: c.level || 'Beginner',
        start_date: c.start_date || '',
        end_date: c.end_date || '',
        description: c.description || ''
      }));
    }
    await this.delay();
    return this.mock.courses;
  }

  async addCourse(course: Partial<Course>): Promise<{ success: boolean; error?: string; course?: Course }> {
    const { data, error } = await this.fetch<any>('/courses.php', {
      method: 'POST',
      body: JSON.stringify({
        title: course.title,
        description: course.description,
        category_id: course.category_id || 1,
        instructor_id: course.instructor_id,
        level: course.level || 'Beginner',
        price: course.price || 0,
        status: course.status || 'draft',
        start_date: course.start_date,
        end_date: course.end_date
      })
    });

    if (error) {
      return { success: false, error };
    }

    return {
      success: true,
      course: {
        id: data?.id,
        ...course,
        instructor: course.instructor || 'Unknown',
        category: course.category || 'General',
        students: 0
      } as Course
    };
  }

  async editCourse(id: number, updates: Partial<Course>): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/courses.php', {
      method: 'PUT',
      body: JSON.stringify({ id, ...updates })
    });

    if (error) {
      return { success: false, error };
    }
    return { success: true };
  }

  async deleteCourse(id: number): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch(`/courses.php?id=${id}`, {
      method: 'DELETE'
    });

    if (error) {
      return { success: false, error };
    }
    return { success: true };
  }

  // --- MODULES ---
  async getModules(courseId: number): Promise<Module[]> {
    const { data, error } = await this.fetch<any[]>(`/courses.php/${courseId}/modules`);
    if (data && !error) {
      return data.map(m => ({
        id: m.id || m.module_id,
        course_id: m.course_id || courseId,
        title: m.title,
        description: m.description || '',
        duration_minutes: m.duration_minutes || 0
      }));
    }
    await this.delay(300);
    return this.mock.modules.filter(m => m.course_id === courseId);
  }

  async addModule(module: Partial<Module>): Promise<{ success: boolean; error?: string; module?: Module }> {
    // Note: Modules are typically created via the courses endpoint
    // For now, we'll use the mock implementation
    await this.delay();
    const newModule = {
      ...module,
      id: Math.max(...this.mock.modules.map(m => m.id), 0) + 1
    } as Module;
    this.mock.modules.push(newModule);
    return { success: true, module: newModule };
  }

  async deleteModule(id: number): Promise<{ success: boolean; error?: string }> {
    await this.delay();
    this.mock.modules = this.mock.modules.filter(m => m.id !== id);
    this.mock.lessons = this.mock.lessons.filter(l => l.module_id !== id);
    return { success: true };
  }

  // --- LESSONS ---
  async getModuleLessons(moduleId: number): Promise<Lesson[]> {
    const { data, error } = await this.fetch<any>(`/lessons.php?module_id=${moduleId}`);
    if (data && !error) {
      const lessons = data.lessons || data;
      return (lessons || []).map((l: any) => ({
        id: l.id || l.lesson_id,
        module_id: l.module_id || moduleId,
        title: l.title,
        type: l.lesson_type || l.type || 'Reading',
        content: l.content || '',
        duration: l.duration || '10 min'
      }));
    }
    await this.delay(100);
    return this.mock.lessons.filter(l => l.module_id === moduleId) as Lesson[];
  }

  async addLesson(lesson: Partial<Lesson>): Promise<{ success: boolean; error?: string; lesson?: Lesson }> {
    await this.delay(200);
    const newLesson = {
      ...lesson,
      id: Math.max(...this.mock.lessons.map(l => l.id), 0) + 1
    } as Lesson;
    this.mock.lessons.push(newLesson as any);
    return { success: true, lesson: newLesson };
  }

  async deleteLesson(id: number): Promise<{ success: boolean; error?: string }> {
    await this.delay();
    this.mock.lessons = this.mock.lessons.filter(l => l.id !== id);
    return { success: true };
  }

  // --- ENROLLMENTS ---
  async getEnrollments(): Promise<(Enrollment & { user_name: string; course_title: string })[]> {
    const { data, error } = await this.fetch<any[]>('/enrollments.php');
    if (data && !error) {
      return data.map(e => ({
        id: e.id || e.enrollment_id,
        user_id: e.user_id,
        course_id: e.course_id,
        enrolled_at: e.enrolled_at || e.enrollment_date || '',
        status: e.enrollment_status || e.status || 'Enrolled',
        progress: parseFloat(e.progress) || 0,
        user_name: e.student_name || e.user_name || 'Unknown',
        course_title: e.course_title || 'Unknown'
      }));
    }

    await this.delay();
    return this.mock.enrollments.map(e => ({
      ...e,
      user_name: this.mock.users.find(u => u.id === e.user_id)?.name || 'Unknown',
      course_title: this.mock.courses.find(c => c.id === e.course_id)?.title || 'Unknown'
    }));
  }

  async addEnrollment(data: { user_id: number; course_id: number }): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/enrollments.php', {
      method: 'POST',
      body: JSON.stringify({
        user_id: data.user_id,
        course_id: data.course_id
      })
    });

    if (error) {
      // Fallback to mock
      const newEnrollment = {
        id: Math.max(...this.mock.enrollments.map(e => e.id), 0) + 1,
        ...data,
        enrolled_at: new Date().toISOString().split('T')[0],
        status: 'Enrolled',
        progress: 0
      };
      this.mock.enrollments.push(newEnrollment);
    }
    return { success: true };
  }

  async updateEnrollmentStatus(id: number, status: string): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/enrollments.php', {
      method: 'PUT',
      body: JSON.stringify({ id, status })
    });

    if (error) {
      this.mock.enrollments = this.mock.enrollments.map(e => e.id === id ? { ...e, status } : e);
    }
    return { success: true };
  }

  // --- FINANCIALS / TRANSACTIONS ---
  async getTransactions(): Promise<Transaction[]> {
    const { data, error } = await this.fetch<any[]>('/transactions.php');
    if (data && !error) {
      return data.map(t => ({
        id: t.id || t.transaction_id?.toString() || `TXN-${t.id}`,
        user_id: t.user_id,
        student: t.student_name || 'Unknown',
        amount: parseFloat(t.amount) || 0,
        date: t.date || t.processed_at || t.created_at || '',
        status: t.status || t.payment_status || 'Pending',
        method: t.method || t.method_name || 'Unknown',
        type: t.type || t.transaction_type || 'Payment'
      }));
    }
    await this.delay();
    return this.mock.transactions;
  }

  async addTransaction(transaction: Partial<Transaction>): Promise<{ success: boolean; error?: string; transaction?: Transaction }> {
    const { data, error } = await this.fetch<any>('/transactions.php', {
      method: 'POST',
      body: JSON.stringify({
        user_id: transaction.user_id,
        amount: transaction.amount,
        type: transaction.type || 'Payment',
        status: transaction.status || 'Pending',
        description: `Manual payment recorded`
      })
    });

    if (error) {
      // Fallback to mock
      const newTx = {
        ...transaction,
        id: `TXN-${new Date().getFullYear()}-${Math.floor(Math.random() * 1000000)}`,
        date: new Date().toISOString().split('T')[0]
      } as Transaction;
      this.mock.transactions.unshift(newTx);
      return { success: true, transaction: newTx };
    }

    return {
      success: true,
      transaction: {
        id: data?.id?.toString() || `TXN-${Date.now()}`,
        ...transaction
      } as Transaction
    };
  }

  async verifyTransaction(id: string): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/transactions.php', {
      method: 'PUT',
      body: JSON.stringify({
        id: parseInt(id.replace(/\D/g, '')) || id,
        status: 'Completed'
      })
    });

    if (error) {
      // Fallback to mock
      this.mock.transactions = this.mock.transactions.map(t =>
        t.id === id ? { ...t, status: 'Completed' } : t
      );
    }
    return { success: true };
  }

  // --- ANNOUNCEMENTS ---
  async getAnnouncements(): Promise<Announcement[]> {
    const { data, error } = await this.fetch<any[]>('/announcements.php');
    if (data && !error) {
      return data.map(a => ({
        id: a.id || a.announcement_id,
        title: a.title,
        content: a.content,
        type: a.type || a.announcement_type || 'General',
        priority: a.priority || 'Normal',
        is_published: Boolean(a.is_published),
        date: a.date || a.created_at || ''
      }));
    }
    await this.delay();
    return this.mock.announcements;
  }

  async addAnnouncement(announcement: Partial<Announcement>): Promise<{ success: boolean; error?: string; announcement?: Announcement }> {
    const { data, error } = await this.fetch<any>('/announcements.php', {
      method: 'POST',
      body: JSON.stringify({
        title: announcement.title,
        content: announcement.content,
        type: announcement.type || 'General',
        priority: announcement.priority || 'Normal',
        is_published: announcement.is_published ? 1 : 0
      })
    });

    if (error) {
      // Fallback to mock
      const newAnn = {
        ...announcement,
        id: Math.max(...this.mock.announcements.map(a => a.id), 0) + 1,
        date: new Date().toISOString().split('T')[0]
      } as Announcement;
      this.mock.announcements.unshift(newAnn);
      return { success: true, announcement: newAnn };
    }

    return {
      success: true,
      announcement: {
        id: data?.id,
        ...announcement,
        date: new Date().toISOString().split('T')[0]
      } as Announcement
    };
  }

  async updateAnnouncement(id: number, updates: Partial<Announcement>): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/announcements.php', {
      method: 'PUT',
      body: JSON.stringify({ id, ...updates })
    });

    if (error) {
      this.mock.announcements = this.mock.announcements.map(a =>
        a.id === id ? { ...a, ...updates } : a
      );
    }
    return { success: true };
  }

  async deleteAnnouncement(id: number): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch(`/announcements.php?id=${id}`, {
      method: 'DELETE'
    });

    if (error) {
      this.mock.announcements = this.mock.announcements.filter(a => a.id !== id);
    }
    return { success: true };
  }

  // --- CATEGORIES ---
  async getCategories(): Promise<Category[]> {
    const { data, error } = await this.fetch<any[]>('/categories.php');
    if (data && !error) {
      return data.map(c => ({
        id: c.id,
        name: c.name,
        description: c.description || '',
        color: c.color || '#333333',
        count: c.count || c.course_count || 0
      }));
    }
    await this.delay();
    return this.mock.categories;
  }

  async addCategory(category: Partial<Category>): Promise<{ success: boolean; error?: string; category?: Category }> {
    const { data, error } = await this.fetch<any>('/categories.php', {
      method: 'POST',
      body: JSON.stringify({
        name: category.name,
        description: category.description || '',
        color: category.color || '#333333'
      })
    });

    if (error) {
      // Fallback to mock
      const newCat = {
        ...category,
        id: Math.max(...this.mock.categories.map(c => c.id), 0) + 1,
        count: 0
      } as Category;
      this.mock.categories.push(newCat);
      return { success: true, category: newCat };
    }

    return {
      success: true,
      category: {
        id: data?.id,
        ...category,
        count: 0
      } as Category
    };
  }

  async updateCategory(id: number, updates: Partial<Category>): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch('/categories.php', {
      method: 'PUT',
      body: JSON.stringify({ id, ...updates })
    });

    if (error) {
      this.mock.categories = this.mock.categories.map(c =>
        c.id === id ? { ...c, ...updates } : c
      );
    }
    return { success: true };
  }

  async deleteCategory(id: number): Promise<{ success: boolean; error?: string }> {
    const { error } = await this.fetch(`/categories.php?id=${id}`, {
      method: 'DELETE'
    });

    if (error) {
      this.mock.categories = this.mock.categories.filter(c => c.id !== id);
    }
    return { success: true };
  }

  // --- CERTIFICATES ---
  async getCertificates(): Promise<Certificate[]> {
    const { data, error } = await this.fetch<any[]>('/certificates.php');
    if (data && !error) {
      return data.map(c => ({
        id: c.id || c.certificate_id,
        code: c.code || c.certificate_number || `CERT-${c.id}`,
        student: c.student || c.student_name || 'Unknown',
        course: c.course || c.course_title || 'Unknown',
        date: c.date || c.issued_date || '',
        verified: Boolean(c.verified || c.is_verified),
        verification_code: c.verification_code
      }));
    }
    await this.delay();
    return this.mock.certificates;
  }

  async issueCertificate(enrollmentId: number): Promise<{ success: boolean; error?: string; certificate?: any }> {
    const { data, error } = await this.fetch<any>('/certificates.php', {
      method: 'POST',
      body: JSON.stringify({ enrollment_id: enrollmentId })
    });

    if (error) {
      return { success: false, error };
    }

    return { success: true, certificate: data };
  }

  // Certificate download helper
  getCertificateDownloadUrl(certificateId: number): string {
    return `/certificates/download.php?id=${certificateId}`;
  }

  // --- SETTINGS ---
  async getSettings(): Promise<Settings> {
    const { data, error } = await this.fetch<any>('/settings.php');
    if (data && !error) {
      // Flatten nested settings structure from API to flat structure expected by UI
      const settings: Settings = {
        site_name: data.general?.siteName || data.site_name || 'EduTrack LMS',
        currency: data.payments?.currency || data.currency || 'ZMW',
        registration_fee: parseFloat(data.payments?.registrationFee || data.registration_fee) || 0,
        allow_partial_payments: Boolean(data.payments?.allowPartialPayments || data.allow_partial_payments),
        // Keep the full data for additional settings
        ...data
      };
      return settings;
    }
    await this.delay();
    return this.mock.settings;
  }

  async updateSettings(newSettings: Settings): Promise<{ success: boolean; error?: string }> {
    // Convert flat settings to nested structure expected by API
    const apiSettings = {
      general: {
        siteName: newSettings.site_name
      },
      payments: {
        currency: newSettings.currency,
        registrationFee: newSettings.registration_fee,
        allowPartialPayments: newSettings.allow_partial_payments
      }
    };

    const { error } = await this.fetch('/settings.php', {
      method: 'PUT',
      body: JSON.stringify(apiSettings)
    });

    if (error) {
      // Fallback to mock
      this.mock.settings = newSettings;
    }
    return { success: true };
  }

  // --- LOGS ---
  async getLogs(): Promise<any[]> {
    const { data, error } = await this.fetch<any[]>('/logs.php');
    if (data && !error) {
      return data;
    }
    return this.mock.logs;
  }

  // --- INSTRUCTORS ---
  async getInstructors(): Promise<User[]> {
    const { data, error } = await this.fetch<any[]>('/instructors.php');
    if (data && !error) {
      return data.map(u => ({
        id: u.id,
        name: `${u.first_name || ''} ${u.last_name || ''}`.trim() || u.username || 'Unknown',
        email: u.email || '',
        role: 'Instructor',
        status: u.status || 'active',
        joined: ''
      }));
    }
    return this.mock.users.filter(u => u.role === 'Instructor');
  }

  // --- DASHBOARD STATS ---
  async getDashboardStats(): Promise<{
    totalStudents: number;
    activeCourses: number;
    pendingPayments: number;
    totalRevenue: number;
    recentActivity: any[];
  }> {
    // Try to get real data from various endpoints
    const [users, courses, transactions, logs] = await Promise.all([
      this.getUsers(),
      this.getCourses(),
      this.getTransactions(),
      this.getLogs()
    ]);

    const students = users.filter(u => u.role === 'Student' || u.role === 'student');
    const activeCourses = courses.filter(c => c.status === 'Published' || c.status === 'published' || c.status === 'active');
    const pendingTx = transactions.filter(t => t.status === 'Pending' || t.status === 'pending');
    const completedTx = transactions.filter(t => t.status === 'Completed' || t.status === 'completed');

    return {
      totalStudents: students.length,
      activeCourses: activeCourses.length,
      pendingPayments: pendingTx.reduce((sum, t) => sum + t.amount, 0),
      totalRevenue: completedTx.reduce((sum, t) => sum + t.amount, 0),
      recentActivity: logs.slice(0, 10)
    };
  }
}

export const db = new DBService();
