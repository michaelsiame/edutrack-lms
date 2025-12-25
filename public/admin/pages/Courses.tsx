
import React, { useState, useEffect } from 'react';
import { useAdmin } from '../context/AdminContext';
import { Badge, Icons, Modal, Spinner } from '../components/Shared';
import { Module, Lesson } from '../services/db';

export const Courses = () => {
  const { data, actions, isLoading } = useAdmin();
  
  // Modal & Form State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'add' | 'edit'>('add');
  const [selectedCourse, setSelectedCourse] = useState<any>(null);
  const [activeTab, setActiveTab] = useState<'details' | 'curriculum'>('details');
  
  // Course Content State
  const [courseModules, setCourseModules] = useState<Module[]>([]);
  const [moduleLessons, setModuleLessons] = useState<Record<number, Lesson[]>>({});
  const [loadingCurriculum, setLoadingCurriculum] = useState(false);
  
  // Lesson Adding State
  const [addingLessonToModuleId, setAddingLessonToModuleId] = useState<number | null>(null);
  const [newLessonData, setNewLessonData] = useState({ title: '', type: 'Video', content: '' });

  // Form Data for Course Details
  const [formData, setFormData] = useState({ 
    title: '', 
    category: '', 
    price: 0, 
    instructor: '', 
    status: 'draft',
    level: 'Beginner',
    start_date: '',
    end_date: '',
    description: ''
  });

  const [newModuleTitle, setNewModuleTitle] = useState('');

  if (isLoading) return <Spinner />;

  const fetchCurriculum = async (courseId: number) => {
    setLoadingCurriculum(true);
    const mods = await actions.getModules(courseId);
    setCourseModules(mods);
    
    // Fetch lessons for all modules
    const lessonsMap: Record<number, Lesson[]> = {};
    await Promise.all(mods.map(async (m) => {
      const lessons = await actions.getModuleLessons(m.id);
      lessonsMap[m.id] = lessons;
    }));
    
    setModuleLessons(lessonsMap);
    setLoadingCurriculum(false);
  };

  const handleOpenAdd = () => {
    setModalMode('add');
    setFormData({ 
      title: '', category: '', price: 0, instructor: '', status: 'draft',
      level: 'Beginner', start_date: '', end_date: '', description: ''
    });
    setCourseModules([]);
    setModuleLessons({});
    setSelectedCourse(null);
    setActiveTab('details');
    setIsModalOpen(true);
  };

  const handleOpenEdit = async (course: any) => {
    setModalMode('edit');
    setFormData({ 
      title: course.title, 
      category: course.category, 
      price: course.price, 
      instructor: course.instructor,
      status: course.status,
      level: course.level || 'Beginner',
      start_date: course.start_date || '',
      end_date: course.end_date || '',
      description: course.description || ''
    });
    setSelectedCourse(course);
    setActiveTab('details');
    setIsModalOpen(true);
    await fetchCurriculum(course.id);
  };

  const handleDelete = async (id: number) => {
    if (confirm('Are you sure you want to delete this course?')) {
      await actions.deleteCourse(id);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (modalMode === 'add') {
      await actions.addCourse(formData);
    } else {
      await actions.editCourse(selectedCourse.id, formData);
    }
    setIsModalOpen(false);
  };

  // --- MODULE ACTIONS ---
  const handleAddModule = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newModuleTitle || !selectedCourse) return;
    
    await actions.addModule({
      course_id: selectedCourse.id,
      title: newModuleTitle,
      description: '',
      duration_minutes: 0
    });
    setNewModuleTitle('');
    await fetchCurriculum(selectedCourse.id);
  };

  const handleDeleteModule = async (id: number) => {
    if (confirm('Delete this module and all its lessons?')) {
      await actions.deleteModule(id);
      if (selectedCourse) await fetchCurriculum(selectedCourse.id);
    }
  };

  // --- LESSON ACTIONS ---
  const startAddLesson = (moduleId: number) => {
    setAddingLessonToModuleId(moduleId);
    setNewLessonData({ title: '', type: 'Video', content: '' });
  };

  const cancelAddLesson = () => {
    setAddingLessonToModuleId(null);
  };

  const saveNewLesson = async () => {
    if (!addingLessonToModuleId || !newLessonData.title) return;
    await actions.addLesson({
      module_id: addingLessonToModuleId,
      ...newLessonData,
      duration: '0m' // Default
    });
    setAddingLessonToModuleId(null);
    await fetchCurriculum(selectedCourse.id);
  };

  const handleDeleteLesson = async (id: number) => {
    if (confirm('Delete this lesson?')) {
      await actions.deleteLesson(id);
      if (selectedCourse) await fetchCurriculum(selectedCourse.id);
    }
  };

  const getLessonIcon = (type: string) => {
    switch (type) {
      case 'Video': return <Icons.Video />;
      case 'Reading': return <Icons.Document />;
      case 'Quiz': return <Icons.Quiz />;
      default: return <Icons.Document />;
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-800">Courses</h2>
        <button onClick={handleOpenAdd} className="flex items-center px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
          <Icons.Plus />
          <span className="ml-2">Create Course</span>
        </button>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th className="px-6 py-4">Title</th>
              <th className="px-6 py-4">Instructor</th>
              <th className="px-6 py-4">Level</th>
              <th className="px-6 py-4">Start Date</th>
              <th className="px-6 py-4">Price</th>
              <th className="px-6 py-4">Status</th>
              <th className="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {data.courses.map((course: any) => (
              <tr key={course.id} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div className="font-medium text-gray-900">{course.title}</div>
                  <div className="text-xs text-gray-500">{course.category}</div>
                </td>
                <td className="px-6 py-4 text-sm text-gray-500">{course.instructor}</td>
                <td className="px-6 py-4 text-sm text-gray-500">{course.level}</td>
                <td className="px-6 py-4 text-sm text-gray-500">{course.start_date || 'N/A'}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{data.settings.currency} {course.price}</td>
                <td className="px-6 py-4">
                  <Badge color={course.status === 'published' ? 'green' : course.status === 'draft' ? 'yellow' : 'gray'}>{course.status}</Badge>
                </td>
                <td className="px-6 py-4 text-right">
                  <div className="flex items-center justify-end space-x-2">
                    <button 
                      onClick={() => handleOpenEdit(course)}
                      className="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                      title="Edit Course"
                    >
                      <Icons.Edit />
                    </button>
                    <button 
                      onClick={() => handleDelete(course.id)}
                      className="p-1 text-gray-500 hover:text-red-600 transition-colors"
                      title="Delete Course"
                    >
                      <Icons.Trash />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title={modalMode === 'add' ? "Create New Course" : "Edit Course"}>
        {/* Tabs */}
        {modalMode === 'edit' && (
          <div className="flex border-b border-gray-200 mb-4">
            <button 
              className={`py-2 px-4 text-sm font-medium ${activeTab === 'details' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}
              onClick={() => setActiveTab('details')}
            >
              Details
            </button>
            <button 
              className={`py-2 px-4 text-sm font-medium ${activeTab === 'curriculum' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}
              onClick={() => setActiveTab('curriculum')}
            >
              Curriculum
            </button>
          </div>
        )}

        {/* DETAILS TAB */}
        <div className={activeTab === 'details' ? 'block' : 'hidden'}>
          <form onSubmit={handleSubmit} className="space-y-4">
             <div>
              <label className="block text-sm font-medium text-gray-700">Course Title</label>
              <input required type="text" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.title} onChange={e => setFormData({...formData, title: e.target.value})} />
             </div>
             
             <div className="grid grid-cols-2 gap-4">
               <div>
                <label className="block text-sm font-medium text-gray-700">Category</label>
                <input required type="text" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.category} onChange={e => setFormData({...formData, category: e.target.value})} />
               </div>
               <div>
                <label className="block text-sm font-medium text-gray-700">Instructor</label>
                <input required type="text" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.instructor} onChange={e => setFormData({...formData, instructor: e.target.value})} />
               </div>
             </div>

             <div className="grid grid-cols-2 gap-4">
               <div>
                <label className="block text-sm font-medium text-gray-700">Price ({data.settings.currency})</label>
                <input required type="number" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.price} onChange={e => setFormData({...formData, price: parseFloat(e.target.value)})} />
               </div>
               <div>
                <label className="block text-sm font-medium text-gray-700">Level</label>
                <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.level} onChange={e => setFormData({...formData, level: e.target.value})}>
                  <option value="Beginner">Beginner</option>
                  <option value="Intermediate">Intermediate</option>
                  <option value="Advanced">Advanced</option>
                </select>
               </div>
             </div>

             <div className="grid grid-cols-2 gap-4">
               <div>
                <label className="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.start_date} onChange={e => setFormData({...formData, start_date: e.target.value})} />
               </div>
               <div>
                <label className="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.end_date} onChange={e => setFormData({...formData, end_date: e.target.value})} />
               </div>
             </div>

             <div>
              <label className="block text-sm font-medium text-gray-700">Description</label>
              <textarea rows={3} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} />
             </div>

             {modalMode === 'edit' && (
               <div>
                <label className="block text-sm font-medium text-gray-700">Status</label>
                <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" value={formData.status} onChange={e => setFormData({...formData, status: e.target.value})}>
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
               </div>
             )}
             <div className="mt-5 sm:mt-6 pt-4 border-t border-gray-100">
              <button type="submit" className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:text-sm">
                {modalMode === 'add' ? 'Create Course' : 'Save Changes'}
              </button>
            </div>
          </form>
        </div>

        {/* CURRICULUM TAB */}
        <div className={activeTab === 'curriculum' ? 'block' : 'hidden'}>
          <div className="space-y-6">
            <div className="flex space-x-2">
              <input 
                type="text" 
                placeholder="New Module Title..." 
                className="flex-1 rounded-md border-gray-300 shadow-sm p-2 border"
                value={newModuleTitle}
                onChange={e => setNewModuleTitle(e.target.value)}
              />
              <button onClick={handleAddModule} className="px-4 py-2 bg-dark text-white rounded-md text-sm hover:bg-gray-800">Add Module</button>
            </div>

            {loadingCurriculum ? <div className="text-center py-4 text-gray-500">Loading curriculum...</div> : (
              <div className="space-y-4">
                {courseModules.length === 0 && <p className="text-center text-gray-500 text-sm">No modules yet. Add one above.</p>}
                
                {courseModules.map((module) => (
                  <div key={module.id} className="border border-gray-200 rounded-lg overflow-hidden">
                    <div className="bg-gray-100 p-3 flex justify-between items-center">
                      <h4 className="font-semibold text-gray-800 text-sm">{module.title}</h4>
                      <button onClick={() => handleDeleteModule(module.id)} className="text-red-500 hover:text-red-700 p-1">
                        <Icons.Trash />
                      </button>
                    </div>
                    
                    <div className="p-3 bg-white space-y-2">
                      {/* List Lessons */}
                      {(moduleLessons[module.id] || []).map(lesson => (
                        <div key={lesson.id} className="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-100">
                          <div className="flex items-center space-x-2">
                            <span className="text-gray-500">{getLessonIcon(lesson.type)}</span>
                            <span className="text-sm text-gray-700">{lesson.title}</span>
                            <span className="text-xs text-gray-400 bg-gray-200 px-1 rounded">{lesson.type}</span>
                          </div>
                          <div className="flex items-center space-x-2">
                            <a
                              href={`/instructor/courses/lesson-resources.php?lesson_id=${lesson.id}`}
                              target="_blank"
                              className="text-purple-600 hover:text-purple-800 text-xs flex items-center space-x-1 px-2 py-1 bg-purple-50 rounded hover:bg-purple-100"
                              title="Manage Resources (PDFs, documents, etc.)"
                            >
                              <Icons.Download size={14} />
                              <span>Resources</span>
                            </a>
                            <button onClick={() => handleDeleteLesson(lesson.id)} className="text-gray-400 hover:text-red-500"><Icons.X /></button>
                          </div>
                        </div>
                      ))}

                      {/* Add Lesson Area */}
                      {addingLessonToModuleId === module.id ? (
                        <div className="mt-2 p-2 border border-blue-200 rounded bg-blue-50">
                          <input 
                            type="text" 
                            className="w-full mb-2 p-1 border rounded text-sm" 
                            placeholder="Lesson Title"
                            value={newLessonData.title}
                            onChange={e => setNewLessonData({...newLessonData, title: e.target.value})}
                          />
                          <div className="flex space-x-2 mb-2">
                            <select 
                              className="p-1 border rounded text-sm flex-1"
                              value={newLessonData.type}
                              onChange={e => setNewLessonData({...newLessonData, type: e.target.value})}
                            >
                              <option value="Video">Video</option>
                              <option value="Reading">Reading</option>
                              <option value="Quiz">Quiz</option>
                              <option value="Assignment">Assignment</option>
                            </select>
                            <input 
                              type="text" 
                              className="flex-1 p-1 border rounded text-sm" 
                              placeholder="Content URL / Description"
                              value={newLessonData.content}
                              onChange={e => setNewLessonData({...newLessonData, content: e.target.value})}
                            />
                          </div>
                          <div className="flex justify-end space-x-2">
                            <button onClick={cancelAddLesson} className="text-xs text-gray-600 hover:text-gray-800">Cancel</button>
                            <button onClick={saveNewLesson} className="text-xs bg-primary text-white px-3 py-1 rounded hover:bg-blue-600">Save</button>
                          </div>
                        </div>
                      ) : (
                        <button 
                          onClick={() => startAddLesson(module.id)}
                          className="w-full py-1.5 border-2 border-dashed border-gray-300 text-gray-500 rounded text-xs hover:border-primary hover:text-primary transition-colors flex justify-center items-center"
                        >
                          <Icons.Plus /> <span className="ml-1">Add Material</span>
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </Modal>
    </div>
  );
};
