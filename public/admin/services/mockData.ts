
export const initialData = {
  users: [
    { id: 1, name: 'System Administrator', email: 'admin@edutrack.edu', role: 'Super Admin', status: 'active', joined: '2025-11-18' },
    { id: 2, name: 'James Mwanza', email: 'james.mwanza@edutrack.edu', role: 'Instructor', status: 'active', joined: '2025-11-18' },
    { id: 27, name: 'Chilala Moonga', email: 'marvinmoonga69@gmail.com', role: 'Admin', status: 'active', joined: '2025-12-04' },
    { id: 29, name: 'Edward Musole', email: 'edwardmusole76@gmail.com', role: 'Admin', status: 'active', joined: '2025-12-05' },
    { id: 6, name: 'Michael Siame', email: 'michael.siame@edutrack.edu', role: 'Instructor', status: 'active', joined: '2025-11-18' },
    { id: 8, name: 'John Tembo', email: 'john.tembo@email.com', role: 'Student', status: 'active', joined: '2025-11-18' },
    { id: 9, name: 'Mary Lungu', email: 'mary.lungu@email.com', role: 'Student', status: 'active', joined: '2025-11-18' },
    { id: 10, name: 'David Sakala', email: 'david.sakala@email.com', role: 'Student', status: 'active', joined: '2025-11-18' },
    { id: 25, name: 'taona ndlovuli', email: 'taona@gmail.com', role: 'Student', status: 'inactive', joined: '2025-11-22' }
  ],
  courses: [
    { 
      id: 1, 
      title: 'Certificate in Microsoft Office Suite', 
      instructor: 'James Mwanza', 
      category: 'ICT & Digital Skills', 
      price: 2500.00, 
      status: 'published', 
      students: 5,
      level: 'Beginner',
      start_date: '2025-01-15',
      end_date: '2025-04-15',
      description: 'Transform your productivity with comprehensive Microsoft Office training covering Word, Excel, PowerPoint, and more.'
    },
    { 
      id: 5, 
      title: 'Certificate in Python Programming', 
      instructor: 'James Mwanza', 
      category: 'Programming', 
      price: 3000.00, 
      status: 'published', 
      students: 12,
      level: 'Beginner',
      start_date: '2025-01-10',
      end_date: '2025-04-10',
      description: 'Launch your programming career with Python, the worlds most popular and versatile programming language.'
    },
    { 
      id: 7, 
      title: 'Certificate in Web Development', 
      instructor: 'James Mwanza', 
      category: 'Programming', 
      price: 3000.00, 
      status: 'published', 
      students: 8,
      level: 'Intermediate',
      start_date: '2025-01-15',
      end_date: '2025-04-30',
      description: 'Build stunning, professional websites from scratch with our comprehensive full-stack web development program.'
    },
    { 
      id: 11, 
      title: 'Certificate in Cyber Security', 
      instructor: 'Peter Phiri', 
      category: 'Data, Security & Networks', 
      price: 2500.00, 
      status: 'published', 
      students: 6,
      level: 'Advanced',
      start_date: '2025-02-15',
      end_date: '2025-06-30',
      description: 'Defend organizations against evolving cyber threats with industry-recognized cybersecurity training.'
    },
    { 
      id: 18, 
      title: 'Certificate in Entrepreneurship', 
      instructor: 'Grace Chanda', 
      category: 'Business', 
      price: 2500.00, 
      status: 'published', 
      students: 15,
      level: 'Beginner',
      start_date: '2025-01-10',
      end_date: '2025-04-10',
      description: 'Transform your business idea into reality with comprehensive entrepreneurship training.'
    },
    { 
      id: 14, 
      title: 'Certificate in Internet of Things', 
      instructor: 'Peter Phiri', 
      category: 'Emerging Tech', 
      price: 450.00, 
      status: 'archived', 
      students: 0,
      level: 'Intermediate',
      start_date: '2025-02-20',
      end_date: '2025-05-20',
      description: 'Build smart, connected devices with Internet of Things (IoT) technology.'
    }
  ],
  modules: [
    { id: 1, course_id: 5, title: 'Introduction to Python', description: 'Getting started with Python programming, installation, and basic syntax', duration_minutes: 300 },
    { id: 2, course_id: 5, title: 'Data Types and Variables', description: 'Understanding Python data types, variables, and operators', duration_minutes: 360 },
    { id: 3, course_id: 5, title: 'Control Flow', description: 'Conditional statements, loops, and flow control in Python', duration_minutes: 420 },
    { id: 4, course_id: 5, title: 'Functions and Modules', description: 'Creating functions, working with modules and packages', duration_minutes: 480 },
    { id: 5, course_id: 5, title: 'Object-Oriented Programming', description: 'Classes, objects, inheritance, and OOP principles', duration_minutes: 540 },
    { id: 7, course_id: 7, title: 'HTML5 Fundamentals', description: 'Introduction to HTML5 structure, elements, and semantic markup', duration_minutes: 400 },
    { id: 8, course_id: 7, title: 'CSS3 Styling', description: 'Styling web pages with CSS3, layouts, and responsive design', duration_minutes: 480 },
    { id: 9, course_id: 7, title: 'JavaScript Basics', description: 'JavaScript fundamentals, DOM manipulation, and events', duration_minutes: 540 }
  ],
  lessons: [
    { id: 1, module_id: 1, title: 'Installing Python and IDE Setup', type: 'Video', content: 'video_url_here', duration: '15m' },
    { id: 2, module_id: 1, title: 'Your First Python Program', type: 'Reading', content: 'text_content_here', duration: '10m' },
    { id: 3, module_id: 1, title: 'Python Syntax Basics', type: 'Video', content: 'video_url_here', duration: '25m' },
    { id: 4, module_id: 2, title: 'Numbers in Python', type: 'Video', content: 'video_url_here', duration: '20m' },
    { id: 5, module_id: 2, title: 'Strings and String Methods', type: 'Reading', content: 'text_content_here', duration: '30m' },
    { id: 6, module_id: 7, title: 'What is HTML?', type: 'Video', content: 'video_url_here', duration: '10m' }
  ],
  enrollments: [
    { id: 1, user_id: 8, course_id: 1, enrolled_at: '2025-01-15', status: 'Enrolled', progress: 100 },
    { id: 2, user_id: 8, course_id: 5, enrolled_at: '2025-01-15', status: 'Enrolled', progress: 75 },
    { id: 4, user_id: 9, course_id: 7, enrolled_at: '2025-01-15', status: 'Enrolled', progress: 100 },
    { id: 7, user_id: 10, course_id: 18, enrolled_at: '2025-01-10', status: 'Enrolled', progress: 100 },
    { id: 10, user_id: 10, course_id: 5, enrolled_at: '2025-01-10', status: 'Dropped', progress: 15 }
  ],
  transactions: [
    { id: 'TXN-2025-000001', student: 'John Tembo', amount: 250.00, date: '2025-01-15', status: 'Completed', method: 'Cash', type: 'Course Fee' },
    { id: 'TXN-2025-000016', student: 'Patrick Mutale', amount: 540.00, date: '2025-02-15', status: 'Pending', method: 'Bank Transfer', type: 'Course Fee' },
    { id: 'TXN-2025-000017', student: 'Siamem Siame', amount: 250.00, date: '2025-11-25', status: 'Completed', method: 'Mobile Money', type: 'Registration' }
  ],
  announcements: [
    { id: 1, title: 'Welcome to Python Programming', content: 'Welcome to the Certificate in Python Programming!', type: 'Course', priority: 'Normal', is_published: true, date: '2025-01-08' },
    { id: 2, title: 'Project Deadline Extension', content: 'Portfolio project deadline extended by 3 days.', type: 'Course', priority: 'High', is_published: true, date: '2025-03-15' },
    { id: 3, title: 'Platform Maintenance Schedule', content: 'EduTrack LMS will undergo scheduled maintenance on Sunday.', type: 'System', priority: 'Urgent', is_published: true, date: '2025-02-20' },
    { id: 4, title: 'Guest Lecture on Ethical Hacking', content: 'Join us for a special guest lecture.', type: 'Course', priority: 'High', is_published: true, date: '2025-02-28' }
  ],
  categories: [
    { id: 1, name: 'Core ICT & Digital Skills', description: 'Fundamental computer and digital literacy courses', color: '#3b82f6', count: 2 },
    { id: 2, name: 'Programming & Software Development', description: 'Programming languages and software engineering', color: '#8b5cf6', count: 3 },
    { id: 3, name: 'Data, Security & Networks', description: 'Cybersecurity and database management', color: '#ef4444', count: 1 },
    { id: 4, name: 'Emerging Technologies', description: 'AI, ML, and IoT', color: '#10b981', count: 1 },
    { id: 5, name: 'Digital Media & Design', description: 'Graphic design and content creation', color: '#f59e0b', count: 0 },
    { id: 6, name: 'Business & Management', description: 'Entrepreneurship and management', color: '#6366f1', count: 1 }
  ],
  certificates: [
    { id: 1, code: 'EDTRK-2025-000001', student: 'John Tembo', course: 'Certificate in Microsoft Office Suite', date: '2025-04-10', verified: true },
    { id: 2, code: 'EDTRK-2025-000002', student: 'Mary Lungu', course: 'Certificate in Web Development', date: '2025-04-25', verified: true },
    { id: 3, code: 'EDTRK-2025-000003', student: 'David Sakala', course: 'Certificate in Entrepreneurship', date: '2025-04-08', verified: true },
    { id: 4, code: 'EDTRK-2025-000004', student: 'Mary Lungu', course: 'Certificate in Python Programming', date: '2025-04-12', verified: true },
    { id: 5, code: 'EDTRK-2025-000005', student: 'Alice Mulenga', course: 'Certificate in Data Analysis', date: '2025-04-05', verified: true },
    { id: 6, code: 'EDTRK-2025-000006', student: 'Patrick Mutale', course: 'Certificate in Cyber Security', date: '2025-03-15', verified: true }
  ],
  settings: {
    site_name: 'EduTrack LMS',
    registration_fee: 150.00,
    currency: 'ZMW',
    allow_partial_payments: true,
    maintenance_mode: false
  },
  logs: [
    { id: 1, user: 'Chilala Moonga', action: 'Updated system settings', time: '2025-12-18T19:11:42' },
    { id: 2, user: 'Edward Musole', action: 'Verified payment for John Tembo', time: '2025-12-09T11:34:48' },
    { id: 3, user: 'Michael Siame', action: 'Uploaded new module to "Python Programming"', time: '2025-12-18T20:42:20' }
  ]
};
