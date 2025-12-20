
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Modal, Spinner } from '../components/Shared';

export const Enrollments = () => {
  const { data, actions, isLoading } = useAdmin();
  const [filter, setFilter] = useState('All');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [newEnrollment, setNewEnrollment] = useState({ user_id: 0, course_id: 0 });

  if (isLoading) return <Spinner />;

  const filteredEnrollments = data.enrollments.filter((e: any) => 
    filter === 'All' || e.status === filter
  );

  const students = data.users.filter((u: any) => u.role === 'Student' && u.status === 'active');
  const activeCourses = data.courses.filter((c: any) => c.status === 'published');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (newEnrollment.user_id && newEnrollment.course_id) {
      await actions.addEnrollment({
        user_id: Number(newEnrollment.user_id),
        course_id: Number(newEnrollment.course_id)
      });
      setIsModalOpen(false);
      setNewEnrollment({ user_id: 0, course_id: 0 });
    }
  };

  const updateStatus = async (id: number, status: string) => {
    await actions.updateEnrollmentStatus(id, status);
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">Enrollment Management</h2>
        <button onClick={() => setIsModalOpen(true)} className="flex items-center px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
          <Icons.Plus />
          <span className="ml-2">Enroll Student</span>
        </button>
      </div>

      <div className="flex space-x-2 border-b border-gray-200 pb-2">
        {['All', 'Enrolled', 'In Progress', 'Completed', 'Dropped'].map(f => (
          <button 
            key={f}
            onClick={() => setFilter(f)}
            className={`px-4 py-2 text-sm font-medium rounded-lg transition-colors ${filter === f ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100'}`}
          >
            {f}
          </button>
        ))}
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th className="px-6 py-4">Student</th>
              <th className="px-6 py-4">Course</th>
              <th className="px-6 py-4">Date</th>
              <th className="px-6 py-4">Progress</th>
              <th className="px-6 py-4">Status</th>
              <th className="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {filteredEnrollments.map((enr: any) => (
              <tr key={enr.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 font-medium text-gray-900">{enr.user_name}</td>
                <td className="px-6 py-4 text-gray-700">{enr.course_title}</td>
                <td className="px-6 py-4 text-sm text-gray-500">{enr.enrolled_at}</td>
                <td className="px-6 py-4">
                  <div className="flex items-center">
                    <span className="text-xs font-medium mr-2">{enr.progress}%</span>
                    <div className="w-24 h-2 bg-gray-200 rounded-full">
                      <div className="h-2 bg-blue-500 rounded-full" style={{ width: `${enr.progress}%` }}></div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <Badge color={enr.status === 'Completed' ? 'green' : enr.status === 'Dropped' ? 'red' : 'blue'}>{enr.status}</Badge>
                </td>
                <td className="px-6 py-4 text-right">
                  <select 
                    className="text-xs border-gray-300 rounded shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                    value={enr.status}
                    onChange={(e) => updateStatus(enr.id, e.target.value)}
                  >
                    <option value="Enrolled">Enrolled</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Dropped">Dropped</option>
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Manual Enrollment">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Select Student</label>
            <select required className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newEnrollment.user_id} onChange={e => setNewEnrollment({...newEnrollment, user_id: parseInt(e.target.value)})}>
              <option value={0}>-- Select Student --</option>
              {students.map((s: any) => (
                <option key={s.id} value={s.id}>{s.name} ({s.email})</option>
              ))}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Select Course</label>
            <select required className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={newEnrollment.course_id} onChange={e => setNewEnrollment({...newEnrollment, course_id: parseInt(e.target.value)})}>
              <option value={0}>-- Select Course --</option>
              {activeCourses.map((c: any) => (
                <option key={c.id} value={c.id}>{c.title} ({data.settings.currency} {c.price})</option>
              ))}
            </select>
          </div>
          <div className="mt-5 sm:mt-6">
            <button type="submit" className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:text-sm">
              Enroll Student
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
};
