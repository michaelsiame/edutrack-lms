
import React from 'react';
import { useAdmin } from '../context/AdminContext';
import { Icons, Spinner } from '../components/Shared';

export const Reports = () => {
  const { data, isLoading } = useAdmin();

  if (isLoading) return <Spinner />;

  // Calculate some basic stats
  const totalRevenue = data.transactions
    .filter((t: any) => t.status === 'Completed')
    .reduce((sum: number, t: any) => sum + t.amount, 0);

  const studentsCount = data.users.filter((u: any) => u.role === 'Student').length;
  const courseCount = data.courses.length;
  const enrollmentCount = data.enrollments.length;

  // Mock revenue data for chart visualization
  const revenueData = [40, 55, 35, 70, 45, 60, 75, 50, 65, 80, 55, 90]; 
  const maxVal = Math.max(...revenueData);

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">System Reports</h2>
        <button className="flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
          <Icons.Document />
          <span className="ml-2">Export Data</span>
        </button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <h3 className="text-gray-500 text-sm font-medium mb-2">Total Revenue</h3>
           <p className="text-3xl font-bold text-gray-900">{data.settings.currency} {totalRevenue.toFixed(2)}</p>
           <p className="text-green-600 text-xs mt-2 flex items-center"><Icons.Check /> +12% this month</p>
        </div>
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <h3 className="text-gray-500 text-sm font-medium mb-2">Total Enrollments</h3>
           <p className="text-3xl font-bold text-gray-900">{enrollmentCount}</p>
           <p className="text-blue-600 text-xs mt-2">Across {courseCount} courses</p>
        </div>
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <h3 className="text-gray-500 text-sm font-medium mb-2">Avg. Completion Rate</h3>
           <p className="text-3xl font-bold text-gray-900">68%</p>
           <p className="text-gray-400 text-xs mt-2">Based on active students</p>
        </div>
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <h3 className="text-gray-500 text-sm font-medium mb-2">Active Students</h3>
           <p className="text-3xl font-bold text-gray-900">{studentsCount}</p>
           <p className="text-purple-600 text-xs mt-2">Platform wide</p>
        </div>
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue Chart (CSS Bars) */}
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <h3 className="text-lg font-bold text-gray-800 mb-6">Revenue Overview (Yearly)</h3>
           <div className="h-64 flex items-end justify-between space-x-2">
             {revenueData.map((val, idx) => (
               <div key={idx} className="w-full flex flex-col items-center group">
                 <div 
                   className="w-full bg-blue-100 rounded-t-sm hover:bg-blue-500 transition-colors relative"
                   style={{ height: `${(val / maxVal) * 100}%` }}
                 >
                    <div className="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      {val}k
                    </div>
                 </div>
                 <span className="text-xs text-gray-400 mt-2">{['J','F','M','A','M','J','J','A','S','O','N','D'][idx]}</span>
               </div>
             ))}
           </div>
        </div>

        {/* Top Courses List */}
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
           <h3 className="text-lg font-bold text-gray-800 mb-6">Top Performing Courses</h3>
           <div className="space-y-4">
             {data.courses.slice(0, 5).map((course: any, idx: number) => (
               <div key={course.id} className="flex items-center">
                 <span className="w-6 text-gray-400 font-bold">{idx + 1}</span>
                 <div className="flex-1 ml-2">
                   <div className="flex justify-between mb-1">
                     <span className="text-sm font-medium text-gray-700">{course.title}</span>
                     <span className="text-xs text-gray-500">{course.students} students</span>
                   </div>
                   <div className="w-full bg-gray-100 rounded-full h-2">
                     <div className="bg-purple-500 h-2 rounded-full" style={{ width: `${Math.min(course.students * 10, 100)}%` }}></div>
                   </div>
                 </div>
               </div>
             ))}
           </div>
        </div>
      </div>
    </div>
  );
};
