
import React, { useState } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Icons, Modal, Spinner, Button, ConfirmDialog, EmptyState } from '../components/Shared';

interface CategoryFormData {
  name: string;
  description: string;
  color: string;
}

const defaultFormData: CategoryFormData = {
  name: '',
  description: '',
  color: '#3B82F6'
};

const colorPresets = [
  '#3B82F6', // Blue
  '#10B981', // Green
  '#F59E0B', // Yellow
  '#EF4444', // Red
  '#8B5CF6', // Purple
  '#EC4899', // Pink
  '#06B6D4', // Cyan
  '#F97316', // Orange
  '#6366F1', // Indigo
  '#14B8A6', // Teal
];

export const Categories = () => {
  const { data, actions, isLoading } = useAdmin();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formData, setFormData] = useState<CategoryFormData>(defaultFormData);
  const [isSaving, setIsSaving] = useState(false);
  const [deleteConfirm, setDeleteConfirm] = useState<{ isOpen: boolean; id: number | null; name: string }>({
    isOpen: false,
    id: null,
    name: ''
  });
  const [searchTerm, setSearchTerm] = useState('');

  if (isLoading) return <Spinner />;

  const filteredCategories = data.categories.filter(cat =>
    cat.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    cat.description.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleOpenModal = (category?: any) => {
    if (category) {
      setEditingId(category.id);
      setFormData({
        name: category.name,
        description: category.description || '',
        color: category.color || '#3B82F6'
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
      await actions.updateCategory(editingId, formData);
    } else {
      await actions.addCategory(formData);
    }

    setIsSaving(false);
    handleCloseModal();
  };

  const handleDelete = async () => {
    if (deleteConfirm.id) {
      await actions.deleteCategory(deleteConfirm.id);
      setDeleteConfirm({ isOpen: false, id: null, name: '' });
    }
  };

  const totalCourses = data.categories.reduce((sum, cat) => sum + (cat.count || 0), 0);

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">Course Categories</h2>
          <p className="text-sm text-gray-500 mt-1">Organize courses into categories for better navigation</p>
        </div>
        <Button onClick={() => handleOpenModal()}>
          <Icons.Plus />
          <span className="ml-2">Add Category</span>
        </Button>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Total Categories</p>
              <p className="text-2xl font-bold text-gray-800">{data.categories.length}</p>
            </div>
            <div className="p-3 bg-blue-50 rounded-full text-blue-600">
              <Icons.Tag />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Total Courses</p>
              <p className="text-2xl font-bold text-gray-800">{totalCourses}</p>
            </div>
            <div className="p-3 bg-green-50 rounded-full text-green-600">
              <Icons.Book />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Avg per Category</p>
              <p className="text-2xl font-bold text-gray-800">
                {data.categories.length > 0 ? Math.round(totalCourses / data.categories.length) : 0}
              </p>
            </div>
            <div className="p-3 bg-purple-50 rounded-full text-purple-600">
              <Icons.ChartBar />
            </div>
          </div>
        </div>
      </div>

      {/* Search */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div className="relative">
          <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
            <Icons.Search />
          </div>
          <input
            type="text"
            placeholder="Search categories..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          />
        </div>
      </div>

      {/* Categories Grid */}
      {filteredCategories.length === 0 ? (
        <div className="bg-white rounded-xl shadow-sm border border-gray-100">
          <EmptyState
            title="No categories found"
            description={searchTerm
              ? "Try adjusting your search criteria"
              : "Create your first category to organize courses"}
            action={
              <Button onClick={() => handleOpenModal()}>
                Add Category
              </Button>
            }
          />
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredCategories.map((cat: any) => (
            <div key={cat.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow group">
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <div className="flex items-center mb-3">
                    <span
                      className="w-4 h-4 rounded-full mr-3 shadow-sm"
                      style={{ backgroundColor: cat.color }}
                    ></span>
                    <h3 className="font-bold text-gray-800 text-lg">{cat.name}</h3>
                  </div>
                  <p className="text-sm text-gray-500 mb-4 line-clamp-2 min-h-[40px]">
                    {cat.description || 'No description provided'}
                  </p>
                  <div className="flex items-center justify-between">
                    <div className="flex items-center text-sm text-gray-600">
                      <Icons.Book />
                      <span className="ml-1">{cat.count || 0} Courses</span>
                    </div>
                    <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button
                        onClick={() => handleOpenModal(cat)}
                        className="p-2 text-gray-400 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                        title="Edit"
                      >
                        <Icons.Edit />
                      </button>
                      <button
                        onClick={() => setDeleteConfirm({ isOpen: true, id: cat.id, name: cat.name })}
                        className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete"
                      >
                        <Icons.Trash />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Create/Edit Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={handleCloseModal}
        title={editingId ? 'Edit Category' : 'Add Category'}
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              required
              type="text"
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              value={formData.name}
              onChange={e => setFormData({...formData, name: e.target.value})}
              placeholder="e.g., Web Development"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              rows={3}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
              value={formData.description}
              onChange={e => setFormData({...formData, description: e.target.value})}
              placeholder="Brief description of the category..."
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Color Tag</label>
            <div className="flex flex-wrap gap-2 mb-3">
              {colorPresets.map(color => (
                <button
                  key={color}
                  type="button"
                  onClick={() => setFormData({...formData, color})}
                  className={`w-8 h-8 rounded-full transition-transform hover:scale-110 ${
                    formData.color === color ? 'ring-2 ring-offset-2 ring-gray-400' : ''
                  }`}
                  style={{ backgroundColor: color }}
                />
              ))}
            </div>
            <div className="flex items-center gap-3">
              <span className="text-sm text-gray-500">Custom:</span>
              <input
                type="color"
                className="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer"
                value={formData.color}
                onChange={e => setFormData({...formData, color: e.target.value})}
              />
              <span className="text-sm text-gray-500 font-mono">{formData.color}</span>
            </div>
          </div>

          <div className="flex items-center p-3 bg-gray-50 rounded-lg">
            <div
              className="w-6 h-6 rounded-full mr-3"
              style={{ backgroundColor: formData.color }}
            ></div>
            <span className="font-medium text-gray-700">
              {formData.name || 'Category Preview'}
            </span>
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t">
            <Button variant="secondary" onClick={handleCloseModal}>
              Cancel
            </Button>
            <Button type="submit" loading={isSaving}>
              {editingId ? 'Update' : 'Create'}
            </Button>
          </div>
        </form>
      </Modal>

      {/* Delete Confirmation */}
      <ConfirmDialog
        isOpen={deleteConfirm.isOpen}
        onClose={() => setDeleteConfirm({ isOpen: false, id: null, name: '' })}
        onConfirm={handleDelete}
        title="Delete Category"
        message={`Are you sure you want to delete "${deleteConfirm.name}"? Courses in this category will become uncategorized.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
};
