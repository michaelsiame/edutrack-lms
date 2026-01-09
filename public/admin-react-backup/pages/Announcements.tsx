
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Modal, Spinner, Button, ConfirmDialog, EmptyState } from '../components/Shared';

interface AnnouncementFormData {
  title: string;
  content: string;
  type: string;
  priority: string;
  is_published: boolean;
}

const defaultFormData: AnnouncementFormData = {
  title: '',
  content: '',
  type: 'General',
  priority: 'Normal',
  is_published: true
};

export const Announcements = () => {
  const { data, actions, isLoading } = useAdmin();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formData, setFormData] = useState<AnnouncementFormData>(defaultFormData);
  const [isSaving, setIsSaving] = useState(false);
  const [deleteConfirm, setDeleteConfirm] = useState<{ isOpen: boolean; id: number | null }>({
    isOpen: false,
    id: null
  });
  const [filterType, setFilterType] = useState('all');
  const [searchTerm, setSearchTerm] = useState('');

  if (isLoading) return <Spinner />;

  const filteredAnnouncements = data.announcements.filter(ann => {
    const matchesSearch = ann.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      ann.content.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesType = filterType === 'all' || ann.type === filterType;
    return matchesSearch && matchesType;
  });

  const handleOpenModal = (announcement?: any) => {
    if (announcement) {
      setEditingId(announcement.id);
      setFormData({
        title: announcement.title,
        content: announcement.content,
        type: announcement.type,
        priority: announcement.priority,
        is_published: announcement.is_published
      });
    } else {
      setEditingId(null);
      setFormData(defaultFormData);
    }
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingId(null);
    setFormData(defaultFormData);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);

    if (editingId) {
      await actions.updateAnnouncement(editingId, formData);
    } else {
      await actions.addAnnouncement(formData);
    }

    setIsSaving(false);
    handleCloseModal();
  };

  const handleDelete = async () => {
    if (deleteConfirm.id) {
      await actions.deleteAnnouncement(deleteConfirm.id);
      setDeleteConfirm({ isOpen: false, id: null });
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'Urgent': return 'red';
      case 'High': return 'orange';
      case 'Normal': return 'blue';
      case 'Low': return 'gray';
      default: return 'gray';
    }
  };

  const getTypeColor = (type: string) => {
    switch (type) {
      case 'System': return 'purple';
      case 'Course': return 'green';
      case 'General': return 'blue';
      default: return 'gray';
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">Announcements</h2>
          <p className="text-sm text-gray-500 mt-1">Create and manage system announcements</p>
        </div>
        <Button onClick={() => handleOpenModal()}>
          <Icons.Plus />
          <span className="ml-2">Create Announcement</span>
        </Button>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <p className="text-sm text-gray-500">Total</p>
          <p className="text-2xl font-bold text-gray-800">{data.announcements.length}</p>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <p className="text-sm text-gray-500">Published</p>
          <p className="text-2xl font-bold text-green-600">
            {data.announcements.filter(a => a.is_published).length}
          </p>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <p className="text-sm text-gray-500">Urgent</p>
          <p className="text-2xl font-bold text-red-600">
            {data.announcements.filter(a => a.priority === 'Urgent').length}
          </p>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <p className="text-sm text-gray-500">Drafts</p>
          <p className="text-2xl font-bold text-gray-600">
            {data.announcements.filter(a => !a.is_published).length}
          </p>
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1 relative">
            <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <Icons.Search />
            </div>
            <input
              type="text"
              placeholder="Search announcements..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
          </div>
          <select
            value={filterType}
            onChange={(e) => setFilterType(e.target.value)}
            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
          >
            <option value="all">All Types</option>
            <option value="General">General</option>
            <option value="Course">Course</option>
            <option value="System">System</option>
          </select>
        </div>
      </div>

      {/* Announcements List */}
      <div className="space-y-4">
        {filteredAnnouncements.length === 0 ? (
          <div className="bg-white rounded-xl shadow-sm border border-gray-100">
            <EmptyState
              title="No announcements found"
              description={searchTerm || filterType !== 'all'
                ? "Try adjusting your search or filter criteria"
                : "Create your first announcement to communicate with users"}
              action={
                <Button onClick={() => handleOpenModal()}>
                  Create Announcement
                </Button>
              }
            />
          </div>
        ) : (
          filteredAnnouncements.map((ann: any) => (
            <div key={ann.id} className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
              <div className="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div className="flex-1 space-y-2">
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge color={getTypeColor(ann.type)}>{ann.type}</Badge>
                    <Badge color={getPriorityColor(ann.priority)}>{ann.priority}</Badge>
                    {!ann.is_published && <Badge color="gray">Draft</Badge>}
                    <span className="text-xs text-gray-500">• {ann.date}</span>
                  </div>
                  <h3 className="text-lg font-semibold text-gray-800">{ann.title}</h3>
                  <p className="text-gray-600 text-sm line-clamp-2">{ann.content}</p>
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => handleOpenModal(ann)}
                    className="p-2 text-gray-400 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <Icons.Edit />
                  </button>
                  <button
                    onClick={() => setDeleteConfirm({ isOpen: true, id: ann.id })}
                    className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <Icons.Trash />
                  </button>
                </div>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Create/Edit Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={handleCloseModal}
        title={editingId ? 'Edit Announcement' : 'Create Announcement'}
        size="lg"
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input
              required
              type="text"
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              value={formData.title}
              onChange={e => setFormData({...formData, title: e.target.value})}
              placeholder="Enter announcement title"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
                value={formData.type}
                onChange={e => setFormData({...formData, type: e.target.value})}
              >
                <option value="General">General</option>
                <option value="Course">Course</option>
                <option value="System">System</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
                value={formData.priority}
                onChange={e => setFormData({...formData, priority: e.target.value})}
              >
                <option value="Low">Low</option>
                <option value="Normal">Normal</option>
                <option value="High">High</option>
                <option value="Urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Content</label>
            <textarea
              required
              rows={5}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
              value={formData.content}
              onChange={e => setFormData({...formData, content: e.target.value})}
              placeholder="Enter announcement content..."
            />
          </div>

          <div className="flex items-center">
            <input
              type="checkbox"
              id="is_published"
              checked={formData.is_published}
              onChange={e => setFormData({...formData, is_published: e.target.checked})}
              className="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
            />
            <label htmlFor="is_published" className="ml-2 text-sm text-gray-700">
              Publish immediately
            </label>
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t">
            <Button variant="secondary" onClick={handleCloseModal}>
              Cancel
            </Button>
            <Button type="submit" loading={isSaving}>
              {editingId ? 'Update' : 'Publish'}
            </Button>
          </div>
        </form>
      </Modal>

      {/* Delete Confirmation */}
      <ConfirmDialog
        isOpen={deleteConfirm.isOpen}
        onClose={() => setDeleteConfirm({ isOpen: false, id: null })}
        onConfirm={handleDelete}
        title="Delete Announcement"
        message="Are you sure you want to delete this announcement? This action cannot be undone."
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
};
