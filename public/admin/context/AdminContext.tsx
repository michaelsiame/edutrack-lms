
import React, { useState, useEffect, createContext, useContext } from 'react';
import { db, User, Course, Module, Lesson, Transaction, Settings, Enrollment, Announcement, Category, Certificate } from '../services/db';

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
  };
  isLoading: boolean;
  actions: {
    refresh: () => Promise<void>;
    addUser: (user: any) => Promise<void>;
    editUser: (id: number, user: any) => Promise<void>;
    deleteUser: (id: number) => Promise<void>;
    updateUserStatus: (id: number, status: string) => Promise<void>;
    
    addCourse: (course: any) => Promise<void>;
    editCourse: (id: number, course: any) => Promise<void>;
    deleteCourse: (id: number) => Promise<void>;
    
    getModules: (courseId: number) => Promise<Module[]>;
    addModule: (module: any) => Promise<void>;
    deleteModule: (id: number) => Promise<void>;
    
    getModuleLessons: (moduleId: number) => Promise<Lesson[]>;
    addLesson: (lesson: any) => Promise<void>;
    deleteLesson: (id: number) => Promise<void>;
    
    addEnrollment: (data: {user_id: number, course_id: number}) => Promise<void>;
    updateEnrollmentStatus: (id: number, status: string) => Promise<void>;

    addTransaction: (tx: any) => Promise<void>;
    verifyPayment: (id: string) => Promise<void>;
    updateSettings: (s: Settings) => Promise<void>;
    
    addAnnouncement: (ann: any) => Promise<void>;
    addCategory: (cat: any) => Promise<void>;
  };
}

const AdminContext = createContext<AdminContextType | null>(null);

export const AdminProvider = ({ children }: { children?: React.ReactNode }) => {
  const [data, setData] = useState<any>({ 
    users: [], courses: [], transactions: [], settings: {}, logs: [], enrollments: [],
    announcements: [], categories: [], certificates: [] 
  });
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    // We fetch everything in parallel
    const [users, courses, transactions, settings, logs, enrollments, announcements, categories, certificates] = await Promise.all([
      db.getUsers(),
      db.getCourses(),
      db.getTransactions(),
      db.getSettings(),
      db.getLogs(),
      db.getEnrollments(),
      db.getAnnouncements(),
      db.getCategories(),
      db.getCertificates()
    ]);
    setData({ users, courses, transactions, settings, logs, enrollments, announcements, categories, certificates });
    setIsLoading(false);
  };

  useEffect(() => {
    fetchData();
  }, []);

  const actions = {
    refresh: fetchData,
    
    addUser: async (u: any) => { await db.addUser(u); fetchData(); },
    editUser: async (id: number, u: any) => { await db.editUser(id, u); fetchData(); },
    deleteUser: async (id: number) => { await db.deleteUser(id); fetchData(); },
    updateUserStatus: async (id: number, s: string) => { await db.updateUserStatus(id, s); fetchData(); },
    
    addCourse: async (c: any) => { await db.addCourse(c); fetchData(); },
    editCourse: async (id: number, c: any) => { await db.editCourse(id, c); fetchData(); },
    deleteCourse: async (id: number) => { await db.deleteCourse(id); fetchData(); },
    
    getModules: async (courseId: number) => { return await db.getModules(courseId); },
    addModule: async (m: any) => { await db.addModule(m); }, 
    deleteModule: async (id: number) => { await db.deleteModule(id); },
    
    getModuleLessons: async (moduleId: number) => { return await db.getModuleLessons(moduleId); },
    addLesson: async (l: any) => { await db.addLesson(l); },
    deleteLesson: async (id: number) => { await db.deleteLesson(id); },
    
    addEnrollment: async (d: any) => { await db.addEnrollment(d); fetchData(); },
    updateEnrollmentStatus: async (id: number, s: string) => { await db.updateEnrollmentStatus(id, s); fetchData(); },

    addTransaction: async (tx: any) => { await db.addTransaction(tx); fetchData(); },
    verifyPayment: async (id: string) => { await db.verifyTransaction(id); fetchData(); },
    updateSettings: async (s: Settings) => { await db.updateSettings(s); fetchData(); },
    
    addAnnouncement: async (ann: any) => { await db.addAnnouncement(ann); fetchData(); },
    addCategory: async (cat: any) => { await db.addCategory(cat); fetchData(); }
  };

  return (
    <AdminContext.Provider value={{ data, isLoading, actions }}>
      {children}
    </AdminContext.Provider>
  );
};

export const useAdmin = () => {
  const context = useContext(AdminContext);
  if (!context) throw new Error('useAdmin must be used within AdminProvider');
  return context;
};
