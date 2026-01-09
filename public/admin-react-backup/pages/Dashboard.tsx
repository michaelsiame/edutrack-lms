
import React from 'react';
import { useAdmin } from '../context/AdminContext';
import { Icons, Spinner } from '../components/Shared';

export const Dashboard = () => {
  const { data, isLoading } = useAdmin();

  if (isLoading) return <Spinner />;

  const totalStudents = data.users.filter((u: any) => u.role === 'Student').length;
  const activeCourses = data.courses.filter((c: any) => c.status === 'published').length;
  const pendingMoney = data.transactions
    .filter((t: any) => t.status === 'Pending')
    .reduce((acc: number, t: any) => acc + t.amount, 0);

  return (
    <div className="space-y-6">
      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">Total Students</p>
              <h3 className="text-2xl font-bold text-gray-800 mt-1">{totalStudents}</h3>
            </div>
            <div className="p-2 bg-blue-50 text-blue-600 rounded-lg"><Icons.Users /></div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">Active Courses</p>
              <h3 className="text-2xl font-bold text-gray-800 mt-1">{activeCourses}</h3>
            </div>
            <div className="p-2 bg-purple-50 text-purple-600 rounded-lg"><Icons.Book /></div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">Pending Payments</p>
              <h3 className="text-2xl font-bold text-gray-800 mt-1">
                {data.settings.currency} {pendingMoney.toFixed(2)}
              </h3>
            </div>
            <div className="p-2 bg-yellow-50 text-yellow-600 rounded-lg"><Icons.Cash /></div>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
          <div className="p-6 border-b border-gray-100">
            <h3 className="font-semibold text-gray-800">Recent Activity</h3>
          </div>
          <div className="divide-y divide-gray-100">
            {data.logs.map(log => (
              <div key={log.id} className="p-4 flex items-center">
                 <div className="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-600 font-bold shrink-0">
                  {log.user.charAt(0)}
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-800">{log.action}</p>
                  <p className="text-xs text-gray-500">by {log.user} • {new Date(log.time).toLocaleDateString()}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
           <h3 className="font-semibold text-gray-800 mb-4">Quick Stats</h3>
           <div className="space-y-3">
              <div className="flex justify-between items-center text-sm">
                <span className="text-gray-500">New Users (This Week)</span>
                <span className="font-medium">3</span>
              </div>
              <div className="flex justify-between items-center text-sm">
                <span className="text-gray-500">Completed Courses</span>
                <span className="font-medium">12</span>
              </div>
              <div className="flex justify-between items-center text-sm">
                <span className="text-gray-500">Certificates Issued</span>
                <span className="font-medium">8</span>
              </div>
           </div>
        </div>
      </div>
    </div>
  );
};
