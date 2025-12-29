
import React, { useState } from 'react';
import { Icons, ToastContainer } from './Shared';
import { useAdmin } from '../context/AdminContext';

interface LayoutProps {
  children?: React.ReactNode;
  currentView: string;
  setView: (view: string) => void;
}

export const Layout = ({ children, currentView, setView }: LayoutProps) => {
  const [isMobileOpen, setMobileOpen] = useState(false);
  const { toasts, actions } = useAdmin();

  const menuItems = [
    { id: 'dashboard', label: 'Dashboard', icon: Icons.Home },
    { id: 'users', label: 'User Management', icon: Icons.Users },
    { id: 'courses', label: 'Courses', icon: Icons.Book },
    { id: 'course-assignments', label: 'Course Assignments', icon: Icons.Users },
    { id: 'categories', label: 'Categories', icon: Icons.Tag },
    { id: 'enrollments', label: 'Enrollments', icon: Icons.AcademicCap },
    { id: 'financials', label: 'Financials', icon: Icons.Cash },
    { id: 'announcements', label: 'Announcements', icon: Icons.Megaphone },
    { id: 'certificates', label: 'Certificates', icon: Icons.Award },
    { id: 'reports', label: 'Reports', icon: Icons.ChartBar },
    { id: 'settings', label: 'Settings', icon: Icons.Settings },
  ];

  return (
    <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
      {/* Toast Notifications */}
      <ToastContainer toasts={toasts} onDismiss={actions.dismissToast} />

      {/* Sidebar */}
      <aside className={`fixed inset-y-0 left-0 z-40 w-64 bg-dark text-white transform transition-transform duration-300 ease-in-out ${isMobileOpen ? 'translate-x-0' : '-translate-x-full'} md:relative md:translate-x-0`}>
        <div className="flex items-center justify-center h-16 border-b border-gray-700 shadow-md">
          <h1 className="text-xl font-bold tracking-wider">EduTrack<span className="text-primary">Admin</span></h1>
        </div>
        <nav className="mt-6 px-4 space-y-2 overflow-y-auto max-h-[calc(100vh-140px)]">
          {menuItems.map(item => (
            <button
              key={item.id}
              onClick={() => { setView(item.id); setMobileOpen(false); }}
              className={`flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-all ${currentView === item.id ? 'bg-primary text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'}`}
            >
              <item.icon />
              <span className="ml-3">{item.label}</span>
            </button>
          ))}
        </nav>
        <div className="absolute bottom-0 w-full p-4 border-t border-gray-700 bg-gray-900">
           <div className="flex items-center">
             <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center font-bold text-xs text-white">AD</div>
             <div className="ml-3">
               <p className="text-sm font-medium">Administrator</p>
               <button
                 onClick={() => window.location.href = '/logout.php'}
                 className="text-xs text-gray-400 hover:text-white transition-colors"
               >
                 Log out
               </button>
             </div>
           </div>
        </div>
      </aside>

      {/* Main Area */}
      <div className="flex-1 flex flex-col min-w-0">
        <header className="bg-white border-b border-gray-200 h-16 px-6 flex items-center justify-between shadow-sm z-10">
           <div className="flex items-center">
              <button className="md:hidden mr-4 p-2 rounded-lg hover:bg-gray-100" onClick={() => setMobileOpen(!isMobileOpen)}>
                <Icons.Menu />
              </button>
              <h2 className="text-lg font-semibold text-gray-700 hidden md:block capitalize">{currentView.replace('-', ' ')}</h2>
           </div>
           <div className="flex items-center space-x-4">
              <button
                onClick={() => actions.refresh()}
                className="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                title="Refresh data"
              >
                 <Icons.Refresh />
              </button>
              <button className="p-2 text-gray-400 hover:text-gray-600 relative rounded-lg hover:bg-gray-100 transition-colors">
                 <Icons.Bell />
                 <span className="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full"></span>
              </button>
           </div>
        </header>

        <main className="flex-1 overflow-y-auto p-4 md:p-8">
           <div className="max-w-7xl mx-auto">
             {children}
           </div>
        </main>
      </div>

      {isMobileOpen && <div className="fixed inset-0 bg-black opacity-50 z-30 md:hidden" onClick={() => setMobileOpen(false)}></div>}
    </div>
  );
};
