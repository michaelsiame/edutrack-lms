
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Modal, Spinner } from '../components/Shared';

export const Announcements = () => {
  const { data, actions, isLoading } = useAdmin();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({ title: '', content: '', type: 'General', priority: 'Normal' });

  if (isLoading) return <Spinner />;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await actions.addAnnouncement({ ...formData, is_published: true });
    setIsModalOpen(false);
    setFormData({ title: '', content: '', type: 'General', priority: 'Normal' });
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">Announcements</h2>
        <button onClick={() => setIsModalOpen(true)} className="flex items-center px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
          <Icons.Plus />
          <span className="ml-2">Create New</span>
        </button>
      </div>

      <div className="grid gap-6">
        {data.announcements.map((ann: any) => (
          <div key={ann.id} className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-start justify-between">
            <div className="space-y-2">
              <div className="flex items-center space-x-2">
                <Badge color={ann.type === 'System' ? 'purple' : 'blue'}>{ann.type}</Badge>
                {ann.priority === 'Urgent' && <Badge color="red">Urgent</Badge>}
                {ann.priority === 'High' && <Badge color="yellow">High Priority</Badge>}
                <span className="text-xs text-gray-500">• {ann.date}</span>
              </div>
              <h3 className="text-lg font-semibold text-gray-800">{ann.title}</h3>
              <p className="text-gray-600 text-sm">{ann.content}</p>
            </div>
            <div className="mt-4 md:mt-0 md:ml-4 flex items-center space-x-2">
                {ann.is_published ? <span className="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">Published</span> : <span className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Draft</span>}
            </div>
          </div>
        ))}
        {data.announcements.length === 0 && <p className="text-gray-500 text-center py-8">No announcements found.</p>}
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Post Announcement">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Title</label>
            <input required type="text" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.title} onChange={e => setFormData({...formData, title: e.target.value})} />
          </div>
          <div className="grid grid-cols-2 gap-4">
             <div>
                <label className="block text-sm font-medium text-gray-700">Type</label>
                <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.type} onChange={e => setFormData({...formData, type: e.target.value})}>
                  <option value="General">General</option>
                  <option value="Course">Course</option>
                  <option value="System">System</option>
                </select>
             </div>
             <div>
                <label className="block text-sm font-medium text-gray-700">Priority</label>
                <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.priority} onChange={e => setFormData({...formData, priority: e.target.value})}>
                  <option value="Low">Low</option>
                  <option value="Normal">Normal</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
                </select>
             </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Content</label>
            <textarea required rows={4} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.content} onChange={e => setFormData({...formData, content: e.target.value})} />
          </div>
          <div className="mt-5 sm:mt-6">
            <button type="submit" className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:text-sm">
              Publish
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
};
