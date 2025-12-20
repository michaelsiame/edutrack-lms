
import React, { useState } from 'react';
import { AdminProvider } from './context/AdminContext';
import { Layout } from './components/Layout';
import { Dashboard } from './pages/Dashboard';
import { Users } from './pages/Users';
import { Courses } from './pages/Courses';
import { Enrollments } from './pages/Enrollments';
import { Financials } from './pages/Financials';
import { Settings } from './pages/Settings';
import { Announcements } from './pages/Announcements';
import { Categories } from './pages/Categories';
import { Certificates } from './pages/Certificates';
import { Reports } from './pages/Reports';

export const App = () => {
  const [currentView, setView] = useState('dashboard');

  const renderView = () => {
    switch (currentView) {
      case 'dashboard': return <Dashboard />;
      case 'users': return <Users />;
      case 'courses': return <Courses />;
      case 'enrollments': return <Enrollments />;
      case 'financials': return <Financials />;
      case 'announcements': return <Announcements />;
      case 'categories': return <Categories />;
      case 'certificates': return <Certificates />;
      case 'reports': return <Reports />;
      case 'settings': return <Settings />;
      default: return <Dashboard />;
    }
  };

  return (
    <AdminProvider>
      <Layout currentView={currentView} setView={setView}>
        {renderView()}
      </Layout>
    </AdminProvider>
  );
};
