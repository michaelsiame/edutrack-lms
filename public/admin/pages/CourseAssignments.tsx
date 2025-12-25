
import React, { useState, useEffect } from 'react';
import { Badge, Icons, Modal, Spinner } from '../components/Shared';

interface Instructor {
  id: number;
  user_id: number;
  name: string;
  email: string;
  specialization: string | null;
  is_verified: boolean;
  position: string | null;
  is_team_member: boolean;
}

interface Course {
  id: number;
  title: string;
  category: string;
  status: string;
}

interface CourseAssignment {
  id: number;
  course_id: number;
  instructor_id: number;
  is_lead: boolean;
}

export const CourseAssignments = () => {
  const [instructors, setInstructors] = useState<Instructor[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [assignments, setAssignments] = useState<CourseAssignment[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [selectedInstructors, setSelectedInstructors] = useState<number[]>([]);
  const [leadInstructorId, setLeadInstructorId] = useState<number | null>(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const [instructorsRes, coursesRes, assignmentsRes] = await Promise.all([
        fetch('/api/instructors', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'include'
        }),
        fetch('/api/courses', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'include'
        }),
        fetch('/api/course-assignments', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'include'
        })
      ]);

      if (instructorsRes.ok && coursesRes.ok && assignmentsRes.ok) {
        const instructorsData = await instructorsRes.json();
        const coursesData = await coursesRes.json();
        const assignmentsData = await assignmentsRes.json();

        setInstructors(instructorsData.data || instructorsData);
        setCourses(coursesData.data || coursesData);
        setAssignments(assignmentsData.data || assignmentsData);
      }
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleOpenAssignModal = (course: Course) => {
    setSelectedCourse(course);

    // Get current assignments for this course
    const currentAssignments = assignments.filter(a => a.course_id === course.id);
    const currentInstructorIds = currentAssignments.map(a => a.instructor_id);
    const leadAssignment = currentAssignments.find(a => a.is_lead);

    setSelectedInstructors(currentInstructorIds);
    setLeadInstructorId(leadAssignment ? leadAssignment.instructor_id : null);
    setIsModalOpen(true);
  };

  const handleToggleInstructor = (instructorId: number) => {
    setSelectedInstructors(prev => {
      if (prev.includes(instructorId)) {
        // Removing instructor
        if (leadInstructorId === instructorId) {
          setLeadInstructorId(null);
        }
        return prev.filter(id => id !== instructorId);
      } else {
        // Adding instructor
        return [...prev, instructorId];
      }
    });
  };

  const handleSetLead = (instructorId: number) => {
    if (selectedInstructors.includes(instructorId)) {
      setLeadInstructorId(instructorId);
    }
  };

  const handleSaveAssignments = async () => {
    if (!selectedCourse) return;

    try {
      const response = await fetch('/api/course-assignments/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({
          course_id: selectedCourse.id,
          instructor_ids: selectedInstructors,
          lead_instructor_id: leadInstructorId
        })
      });

      if (response.ok) {
        await fetchData();
        setIsModalOpen(false);
        alert('Course assignments updated successfully!');
      } else {
        const error = await response.json();
        alert(`Error: ${error.message || 'Failed to update assignments'}`);
      }
    } catch (error) {
      console.error('Error saving assignments:', error);
      alert('Failed to save assignments. Please try again.');
    }
  };

  const getAssignedInstructors = (courseId: number) => {
    const courseAssignments = assignments.filter(a => a.course_id === courseId);
    return courseAssignments.map(a => {
      const instructor = instructors.find(i => i.id === a.instructor_id);
      return {
        ...instructor,
        is_lead: a.is_lead
      };
    }).filter(i => i.id);
  };

  if (isLoading) return <Spinner />;

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Course Assignments</h1>
        <p className="text-sm text-gray-600">Assign instructors to courses</p>
      </div>

      {/* Instructors Overview */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 className="text-lg font-semibold text-gray-700 mb-4">Available Instructors</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {instructors.map(instructor => (
            <div key={instructor.id} className="border border-gray-200 rounded-lg p-4 hover:border-blue-400 transition-colors">
              <div className="flex items-start justify-between">
                <div>
                  <h3 className="font-semibold text-gray-800">{instructor.name}</h3>
                  <p className="text-sm text-gray-600">{instructor.email}</p>
                  {instructor.specialization && (
                    <p className="text-xs text-gray-500 mt-1">{instructor.specialization}</p>
                  )}
                  {instructor.position && (
                    <Badge variant="info" className="mt-2">
                      {instructor.position}
                    </Badge>
                  )}
                </div>
                {instructor.is_verified && (
                  <Icons.Check className="text-green-500 w-5 h-5" />
                )}
              </div>
              {instructor.is_team_member && (
                <Badge variant="success" className="mt-2">Team Member</Badge>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Courses Table */}
      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <table className="w-full">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned Instructors</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {courses.map(course => {
              const assignedInstructors = getAssignedInstructors(course.id);
              return (
                <tr key={course.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4">
                    <div className="text-sm font-medium text-gray-900">{course.title}</div>
                  </td>
                  <td className="px-6 py-4">
                    <Badge variant="info">{course.category}</Badge>
                  </td>
                  <td className="px-6 py-4">
                    <Badge variant={course.status === 'published' ? 'success' : 'warning'}>
                      {course.status}
                    </Badge>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex flex-wrap gap-2">
                      {assignedInstructors.length > 0 ? (
                        assignedInstructors.map((inst: any) => (
                          <Badge
                            key={inst.id}
                            variant={inst.is_lead ? 'primary' : 'secondary'}
                          >
                            {inst.name} {inst.is_lead && '(Lead)'}
                          </Badge>
                        ))
                      ) : (
                        <span className="text-sm text-gray-400">No instructors assigned</span>
                      )}
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <button
                      onClick={() => handleOpenAssignModal(course)}
                      className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                      Manage Assignments
                    </button>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>

      {/* Assignment Modal */}
      {isModalOpen && selectedCourse && (
        <Modal onClose={() => setIsModalOpen(false)}>
          <div className="p-6">
            <h2 className="text-xl font-bold text-gray-800 mb-4">
              Assign Instructors to "{selectedCourse.title}"
            </h2>

            <div className="space-y-3 max-h-96 overflow-y-auto mb-6">
              {instructors.map(instructor => {
                const isSelected = selectedInstructors.includes(instructor.id);
                const isLead = leadInstructorId === instructor.id;

                return (
                  <div
                    key={instructor.id}
                    className={`border rounded-lg p-4 cursor-pointer transition-all ${
                      isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    }`}
                    onClick={() => handleToggleInstructor(instructor.id)}
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-3">
                        <input
                          type="checkbox"
                          checked={isSelected}
                          onChange={() => {}}
                          className="w-4 h-4 text-blue-600"
                        />
                        <div>
                          <h3 className="font-semibold text-gray-800">{instructor.name}</h3>
                          <p className="text-sm text-gray-600">{instructor.email}</p>
                          {instructor.specialization && (
                            <p className="text-xs text-gray-500">{instructor.specialization}</p>
                          )}
                        </div>
                      </div>
                      {isSelected && (
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            handleSetLead(instructor.id);
                          }}
                          className={`px-3 py-1 rounded text-sm font-medium ${
                            isLead
                              ? 'bg-blue-600 text-white'
                              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                          }`}
                        >
                          {isLead ? 'Lead Instructor' : 'Set as Lead'}
                        </button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="flex justify-end space-x-3">
              <button
                onClick={() => setIsModalOpen(false)}
                className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                onClick={handleSaveAssignments}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Save Assignments
              </button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
};
