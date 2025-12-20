
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Icons, Modal, Spinner } from '../components/Shared';

export const Categories = () => {
  const { data, actions, isLoading } = useAdmin();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({ name: '', description: '', color: '#333333' });

  if (isLoading) return <Spinner />;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await actions.addCategory(formData);
    setIsModalOpen(false);
    setFormData({ name: '', description: '', color: '#333333' });
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">Course Categories</h2>
        <button onClick={() => setIsModalOpen(true)} className="flex items-center px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
          <Icons.Plus />
          <span className="ml-2">Add Category</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {data.categories.map((cat: any) => (
          <div key={cat.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div className="flex items-start justify-between">
               <div className="flex-1">
                 <div className="flex items-center mb-2">
                    <span className="w-3 h-3 rounded-full mr-2" style={{ backgroundColor: cat.color }}></span>
                    <h3 className="font-bold text-gray-800">{cat.name}</h3>
                 </div>
                 <p className="text-sm text-gray-500 mb-4 h-10 overflow-hidden text-ellipsis">{cat.description}</p>
                 <div className="text-xs font-medium text-gray-400 uppercase tracking-wider">
                    {cat.count || 0} Courses
                 </div>
               </div>
               <div className="text-gray-300">
                  <Icons.Tag />
               </div>
            </div>
          </div>
        ))}
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Add Category">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Name</label>
            <input required type="text" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Description</label>
            <textarea className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Color Tag</label>
            <input type="color" className="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm border p-1" value={formData.color} onChange={e => setFormData({...formData, color: e.target.value})} />
          </div>
          <div className="mt-5 sm:mt-6">
            <button type="submit" className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:text-sm">
              Save Category
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
};
