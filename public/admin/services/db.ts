
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
};

export type Settings = typeof initialData.settings;

// --- API SERVICE ---
class DBService {
  private API_BASE = '/api'; // This would point to your PHP backend (e.g., http://localhost/edutrack/api)
  private mock = { ...initialData };

  // Helper to simulate network delay for mock fallback
  private delay(ms = 600) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  // Generic Fetch Wrapper
  private async fetch<T>(endpoint: string, options?: RequestInit): Promise<T | null> {
    try {
      const res = await fetch(`${this.API_BASE}${endpoint}`, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest' // Identify as AJAX request for PHP backend
        },
        credentials: 'include', // Include session cookies for authentication
        ...options,
      });

      if (!res.ok) {
        console.error(`[API] ${endpoint} failed with status ${res.status} ${res.statusText}`);

        // Try to get error details
        try {
          const errorData = await res.json();
          console.error(`[API] Error details:`, errorData);
        } catch (e) {
          console.error(`[API] Could not parse error response`);
        }

        throw new Error(`API Error: ${res.status} ${res.statusText}`);
      }

      const json = await res.json();
      return json.data || json; // Handle wrapped responses
    } catch (error) {
      console.warn(`[API] Connection failed for ${endpoint}. Using mock data.`, error);
      return null;
    }
  }

  // --- USERS ---
  async getUsers(): Promise<User[]> {
    const data = await this.fetch<any[]>('/users');
    if (data) {
      // Map DB fields (snake_case) to Frontend (camelCase/custom)
      return data.map(u => ({
        id: u.id,
        name: `${u.first_name} ${u.last_name}`,
        email: u.email,
        role: u.role_name || 'Student', // Assumes a join with roles table
        status: u.status,
        joined: u.created_at.split('T')[0]
      }));
    }
    await this.delay();
    return this.mock.users;
  }

  async addUser(user: Partial<User>) {
    // In a real app, you'd POST to /api/users
    // const res = await this.fetch('/users', { method: 'POST', body: JSON.stringify(user) });
    // if (res) return res;

    await this.delay();
    const newUser = {
      ...user,
      id: Math.max(...this.mock.users.map(u => u.id), 0) + 1,
      joined: new Date().toISOString().split('T')[0]
    } as User;
    this.mock.users.unshift(newUser);
    return newUser;
  }

  async editUser(id: number, updates: Partial<User>) {
    await this.delay();
    this.mock.users = this.mock.users.map(u => u.id === id ? { ...u, ...updates } : u);
    return this.mock.users.find(u => u.id === id);
  }

  async deleteUser(id: number) {
    await this.delay();
    this.mock.users = this.mock.users.filter(u => u.id !== id);
    return true;
  }

  async updateUserStatus(id: number, status: string) {
    return this.editUser(id, { status });
  }

  // --- COURSES ---
  async getCourses(): Promise<Course[]> {
    const data = await this.fetch<any[]>('/courses');
    if (data) {
      return data.map(c => ({
        id: c.id,
        title: c.title,
        instructor: c.instructor_name || 'Unknown', // Requires join in backend
        category: c.category_name || 'General',     // Requires join in backend
        price: parseFloat(c.price),
        status: c.status,
        students: parseInt(c.enrollment_count || '0'),
        level: c.level,
        start_date: c.start_date,
        end_date: c.end_date,
        description: c.description
      }));
    }
    await this.delay();
    return this.mock.courses;
  }

  async addCourse(course: any) {
    await this.delay();
    const newCourse = {
      ...course,
      id: Math.max(...this.mock.courses.map(c => c.id), 0) + 1,
      students: 0
    };
    this.mock.courses.push(newCourse);
    return newCourse;
  }

  async editCourse(id: number, updates: any) {
    await this.delay();
    this.mock.courses = this.mock.courses.map(c => c.id === id ? { ...c, ...updates } : c);
    return this.mock.courses.find(c => c.id === id);
  }

  async deleteCourse(id: number) {
    await this.delay();
    this.mock.courses = this.mock.courses.filter(c => c.id !== id);
    this.mock.modules = this.mock.modules.filter(m => m.course_id !== id);
    return true;
  }

  // --- MODULES ---
  async getModules(courseId: number): Promise<Module[]> {
    const data = await this.fetch<Module[]>(`/courses/${courseId}/modules`);
    if (data) return data;

    await this.delay(300);
    return this.mock.modules.filter(m => m.course_id === courseId);
  }

  async addModule(module: any) {
    await this.delay();
    const newModule = {
      ...module,
      id: Math.max(...this.mock.modules.map(m => m.id), 0) + 1
    };
    this.mock.modules.push(newModule);
    return newModule;
  }

  async deleteModule(id: number) {
    await this.delay();
    this.mock.modules = this.mock.modules.filter(m => m.id !== id);
    this.mock.lessons = this.mock.lessons.filter(l => l.module_id !== id);
    return true;
  }

  // --- LESSONS ---
  async getModuleLessons(moduleId: number): Promise<Lesson[]> {
    const data = await this.fetch<Lesson[]>(`/modules/${moduleId}/lessons`);
    if (data) return data;

    await this.delay(100);
    // Explicitly cast to Lesson[] because initialData type inference widens 'type' field to string
    return this.mock.lessons.filter(l => l.module_id === moduleId) as Lesson[];
  }

  async addLesson(lesson: any) {
    await this.delay(200);
    const newLesson = {
      ...lesson,
      id: Math.max(...this.mock.lessons.map(l => l.id), 0) + 1
    };
    this.mock.lessons.push(newLesson);
    return newLesson;
  }

  async deleteLesson(id: number) {
    await this.delay();
    this.mock.lessons = this.mock.lessons.filter(l => l.id !== id);
    return true;
  }

  // --- ENROLLMENTS ---
  async getEnrollments(): Promise<(Enrollment & { user_name: string; course_title: string })[]> {
    const data = await this.fetch<any[]>('/enrollments');
    if (data) {
        return data.map(e => ({
            id: e.id,
            user_id: e.user_id,
            course_id: e.course_id,
            enrolled_at: e.enrolled_at,
            status: e.enrollment_status, // Map DB enum
            progress: parseFloat(e.progress),
            user_name: e.student_name, // Backend should provide joined name
            course_title: e.course_title
        }));
    }

    await this.delay();
    return this.mock.enrollments.map(e => ({
      ...e,
      user_name: this.mock.users.find(u => u.id === e.user_id)?.name || 'Unknown',
      course_title: this.mock.courses.find(c => c.id === e.course_id)?.title || 'Unknown'
    }));
  }

  async addEnrollment(data: { user_id: number, course_id: number }) {
    await this.delay();
    const newEnrollment = {
      id: Math.max(...this.mock.enrollments.map(e => e.id), 0) + 1,
      ...data,
      enrolled_at: new Date().toISOString().split('T')[0],
      status: 'Enrolled',
      progress: 0
    };
    this.mock.enrollments.push(newEnrollment);
    return newEnrollment;
  }

  async updateEnrollmentStatus(id: number, status: string) {
    await this.delay();
    this.mock.enrollments = this.mock.enrollments.map(e => e.id === id ? { ...e, status } : e);
    return true;
  }

  // --- FINANCIALS ---
  async getTransactions(): Promise<Transaction[]> {
    const data = await this.fetch<any[]>('/transactions');
    if (data) {
        return data.map(t => ({
            id: t.transaction_id, // Map DB ID
            student: t.student_name, // Backend join
            amount: parseFloat(t.amount),
            date: t.processed_at,
            status: t.payment_status, // Map DB status
            method: 'Unknown', // DB has ID, backend should join 'payment_methods'
            type: t.transaction_type
        }));
    }

    await this.delay();
    return this.mock.transactions;
  }

  async addTransaction(transaction: any) {
    await this.delay();
    const newTx = {
      ...transaction,
      id: `TXN-${new Date().getFullYear()}-${Math.floor(Math.random() * 1000000)}`
    };
    this.mock.transactions.unshift(newTx);
    return newTx;
  }

  async verifyTransaction(id: string) {
    await this.delay();
    this.mock.transactions = this.mock.transactions.map(t => 
      t.id === id ? { ...t, status: 'Completed' } : t
    );
    return true;
  }

  // --- ANNOUNCEMENTS ---
  async getAnnouncements(): Promise<Announcement[]> {
    await this.delay();
    return this.mock.announcements;
  }

  async addAnnouncement(announcement: any) {
    await this.delay();
    const newAnn = { ...announcement, id: Math.max(...this.mock.announcements.map(a => a.id), 0) + 1, date: new Date().toISOString().split('T')[0] };
    this.mock.announcements.unshift(newAnn);
    return newAnn;
  }

  // --- CATEGORIES ---
  async getCategories(): Promise<Category[]> {
    await this.delay();
    return this.mock.categories;
  }

  async addCategory(category: any) {
    await this.delay();
    const newCat = { ...category, id: Math.max(...this.mock.categories.map(c => c.id), 0) + 1, count: 0 };
    this.mock.categories.push(newCat);
    return newCat;
  }

  // --- CERTIFICATES ---
  async getCertificates(): Promise<Certificate[]> {
    await this.delay();
    return this.mock.certificates;
  }

  // --- SETTINGS ---
  async getSettings(): Promise<Settings> {
    const data = await this.fetch<Settings>('/settings');
    if (data) return data;

    await this.delay();
    return this.mock.settings;
  }

  async updateSettings(newSettings: Settings) {
    await this.delay();
    this.mock.settings = newSettings;
    return newSettings;
  }

  // --- LOGS ---
  async getLogs() {
    const data = await this.fetch<any[]>('/logs');
    if (data) return data;
    
    return this.mock.logs;
  }
}

export const db = new DBService();
