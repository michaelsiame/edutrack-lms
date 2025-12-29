
import React, { useState, useEffect, createContext, useContext, useCallback } from 'react';
import { db, User, Course, Module, Lesson, Transaction, Settings, Enrollment, Announcement, Category, Certificate } from '../services/db';

// Toast notification type
export type ToastType = 'success' | 'error' | 'info' | 'warning';
export interface Toast {
  id: number;
  message: string;
  type: ToastType;
}

interface AdminContextType {
  data: {
    users: User[];
    courses: Course[];
    transactions: Transaction[];
    settings: Settings;
    logs: any[];
    enrollments: (Enrollment & { user_name: string; course_title: string })[];
    announcements: Announcement[];
    categories: Category[];
    certificates: Certificate[];
    instructors: User[];
  };
  isLoading: boolean;
  toasts: Toast[];
  actions: {
    refresh: () => Promise<void>;
    showToast: (message: string, type?: ToastType) => void;
    dismissToast: (id: number) => void;

    // Users
    addUser: (user: any) => Promise<boolean>;
    editUser: (id: number, user: any) => Promise<boolean>;
    deleteUser: (id: number) => Promise<boolean>;
    updateUserStatus: (id: number, status: string) => Promise<boolean>;

    // Courses
    addCourse: (course: any) => Promise<boolean>;
    editCourse: (id: number, course: any) => Promise<boolean>;
    deleteCourse: (id: number) => Promise<boolean>;

    // Modules & Lessons
    getModules: (courseId: number) => Promise<Module[]>;
    addModule: (module: any) => Promise<boolean>;
    deleteModule: (id: number) => Promise<boolean>;
    getModuleLessons: (moduleId: number) => Promise<Lesson[]>;
    addLesson: (lesson: any) => Promise<boolean>;
    deleteLesson: (id: number) => Promise<boolean>;

    // Enrollments
    addEnrollment: (data: {user_id: number, course_id: number}) => Promise<boolean>;
    updateEnrollmentStatus: (id: number, status: string) => Promise<boolean>;

    // Transactions
    addTransaction: (tx: any) => Promise<boolean>;
    verifyPayment: (id: string) => Promise<boolean>;

    // Settings
    updateSettings: (s: Settings) => Promise<boolean>;

    // Announcements
    addAnnouncement: (ann: any) => Promise<boolean>;
    updateAnnouncement: (id: number, ann: any) => Promise<boolean>;
    deleteAnnouncement: (id: number) => Promise<boolean>;

    // Categories
    addCategory: (cat: any) => Promise<boolean>;
    updateCategory: (id: number, cat: any) => Promise<boolean>;
    deleteCategory: (id: number) => Promise<boolean>;

    // Certificates
    issueCertificate: (enrollmentId: number) => Promise<boolean>;
    downloadCertificate: (certificateId: number) => void;
  };
}

const AdminContext = createContext<AdminContextType | null>(null);

export const AdminProvider = ({ children }: { children?: React.ReactNode }) => {
  const [data, setData] = useState<any>({
    users: [], courses: [], transactions: [], settings: {}, logs: [], enrollments: [],
    announcements: [], categories: [], certificates: [], instructors: []
  });
  const [isLoading, setIsLoading] = useState(true);
  const [toasts, setToasts] = useState<Toast[]>([]);
  const [toastId, setToastId] = useState(0);

  // Toast notification functions
  const showToast = useCallback((message: string, type: ToastType = 'info') => {
    const id = toastId + 1;
    setToastId(id);
    setToasts(prev => [...prev, { id, message, type }]);

    // Auto dismiss after 4 seconds
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 4000);
  }, [toastId]);

  const dismissToast = useCallback((id: number) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  }, []);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const [users, courses, transactions, settings, logs, enrollments, announcements, categories, certificates, instructors] = await Promise.all([
        db.getUsers(),
        db.getCourses(),
        db.getTransactions(),
        db.getSettings(),
        db.getLogs(),
        db.getEnrollments(),
        db.getAnnouncements(),
        db.getCategories(),
        db.getCertificates(),
        db.getInstructors()
      ]);
      setData({ users, courses, transactions, settings, logs, enrollments, announcements, categories, certificates, instructors });
    } catch (error) {
      console.error('Failed to fetch data:', error);
      showToast('Failed to load data. Please refresh the page.', 'error');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const actions = {
    refresh: fetchData,
    showToast,
    dismissToast,

    // Users
    addUser: async (u: any): Promise<boolean> => {
      const result = await db.addUser(u);
      if (result.success) {
        showToast('User created successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to create user', 'error');
        return false;
      }
    },
    editUser: async (id: number, u: any): Promise<boolean> => {
      const result = await db.editUser(id, u);
      if (result.success) {
        showToast('User updated successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to update user', 'error');
        return false;
      }
    },
    deleteUser: async (id: number): Promise<boolean> => {
      const result = await db.deleteUser(id);
      if (result.success) {
        showToast('User deleted successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to delete user', 'error');
        return false;
      }
    },
    updateUserStatus: async (id: number, s: string): Promise<boolean> => {
      const result = await db.updateUserStatus(id, s);
      if (result.success) {
        showToast(`User ${s === 'active' ? 'activated' : 'suspended'} successfully!`, 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to update user status', 'error');
        return false;
      }
    },

    // Courses
    addCourse: async (c: any): Promise<boolean> => {
      const result = await db.addCourse(c);
      if (result.success) {
        showToast('Course created successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to create course', 'error');
        return false;
      }
    },
    editCourse: async (id: number, c: any): Promise<boolean> => {
      const result = await db.editCourse(id, c);
      if (result.success) {
        showToast('Course updated successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to update course', 'error');
        return false;
      }
    },
    deleteCourse: async (id: number): Promise<boolean> => {
      const result = await db.deleteCourse(id);
      if (result.success) {
        showToast('Course deleted successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to delete course', 'error');
        return false;
      }
    },

    // Modules & Lessons
    getModules: async (courseId: number): Promise<Module[]> => {
      return await db.getModules(courseId);
    },
    addModule: async (m: any): Promise<boolean> => {
      const result = await db.addModule(m);
      return result.success;
    },
    deleteModule: async (id: number): Promise<boolean> => {
      const result = await db.deleteModule(id);
      return result.success;
    },
    getModuleLessons: async (moduleId: number): Promise<Lesson[]> => {
      return await db.getModuleLessons(moduleId);
    },
    addLesson: async (l: any): Promise<boolean> => {
      const result = await db.addLesson(l);
      return result.success;
    },
    deleteLesson: async (id: number): Promise<boolean> => {
      const result = await db.deleteLesson(id);
      return result.success;
    },

    // Enrollments
    addEnrollment: async (d: any): Promise<boolean> => {
      const result = await db.addEnrollment(d);
      if (result.success) {
        showToast('Student enrolled successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to enroll student', 'error');
        return false;
      }
    },
    updateEnrollmentStatus: async (id: number, s: string): Promise<boolean> => {
      const result = await db.updateEnrollmentStatus(id, s);
      if (result.success) {
        showToast('Enrollment status updated!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to update enrollment', 'error');
        return false;
      }
    },

    // Transactions
    addTransaction: async (tx: any): Promise<boolean> => {
      const result = await db.addTransaction(tx);
      if (result.success) {
        showToast('Transaction recorded successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to record transaction', 'error');
        return false;
      }
    },
    verifyPayment: async (id: string): Promise<boolean> => {
      const result = await db.verifyTransaction(id);
      if (result.success) {
        showToast('Payment verified successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to verify payment', 'error');
        return false;
      }
    },

    // Settings
    updateSettings: async (s: Settings): Promise<boolean> => {
      const result = await db.updateSettings(s);
      if (result.success) {
        showToast('Settings saved successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to save settings', 'error');
        return false;
      }
    },

    // Announcements
    addAnnouncement: async (ann: any): Promise<boolean> => {
      const result = await db.addAnnouncement(ann);
      if (result.success) {
        showToast('Announcement created successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to create announcement', 'error');
        return false;
      }
    },
    updateAnnouncement: async (id: number, ann: any): Promise<boolean> => {
      const result = await db.updateAnnouncement(id, ann);
      if (result.success) {
        showToast('Announcement updated successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to update announcement', 'error');
        return false;
      }
    },
    deleteAnnouncement: async (id: number): Promise<boolean> => {
      const result = await db.deleteAnnouncement(id);
      if (result.success) {
        showToast('Announcement deleted successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to delete announcement', 'error');
        return false;
      }
    },

    // Categories
    addCategory: async (cat: any): Promise<boolean> => {
      const result = await db.addCategory(cat);
      if (result.success) {
        showToast('Category created successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to create category', 'error');
        return false;
      }
    },
    updateCategory: async (id: number, cat: any): Promise<boolean> => {
      const result = await db.updateCategory(id, cat);
      if (result.success) {
        showToast('Category updated successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to update category', 'error');
        return false;
      }
    },
    deleteCategory: async (id: number): Promise<boolean> => {
      const result = await db.deleteCategory(id);
      if (result.success) {
        showToast('Category deleted successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to delete category', 'error');
        return false;
      }
    },

    // Certificates
    issueCertificate: async (enrollmentId: number): Promise<boolean> => {
      const result = await db.issueCertificate(enrollmentId);
      if (result.success) {
        showToast('Certificate issued successfully!', 'success');
        fetchData();
        return true;
      } else {
        showToast(result.error || 'Failed to issue certificate', 'error');
        return false;
      }
    },
    downloadCertificate: (certificateId: number) => {
      const url = db.getCertificateDownloadUrl(certificateId);
      window.open(url, '_blank');
    }
  };

  return (
    <AdminContext.Provider value={{ data, isLoading, toasts, actions }}>
      {children}
    </AdminContext.Provider>
  );
};

export const useAdmin = () => {
  const context = useContext(AdminContext);
  if (!context) throw new Error('useAdmin must be used within AdminProvider');
  return context;
};
