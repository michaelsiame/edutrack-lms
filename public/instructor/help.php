<?php
/**
 * Instructor Help & Documentation Center
 * Comprehensive guide for using the LMS as an instructor
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated and is an instructor
if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = User::current();
if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
    redirect('../dashboard.php');
}

$page_title = "Help Center - Instructor Guide";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold mb-4">Instructor Help Center</h1>
            <p class="text-xl text-blue-100 max-w-3xl">
                Everything you need to know about creating courses, managing content, and engaging with your students.
            </p>
            
            <!-- Quick Search -->
            <div class="mt-8 max-w-2xl">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           id="helpSearch"
                           placeholder="Search for topics, features, or questions..." 
                           class="w-full pl-12 pr-4 py-4 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-white/30">
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border sticky top-6">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Contents</h3>
                    </div>
                    <nav class="p-2 space-y-1 overflow-y-auto max-h-[calc(100vh-200px)]">
                        <a href="#getting-started" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-rocket w-6 text-blue-500"></i>
                            Getting Started
                        </a>
                        <a href="#glossary" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-book w-6 text-green-500"></i>
                            Glossary of Terms
                        </a>
                        <a href="#course-creation" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-plus-circle w-6 text-purple-500"></i>
                            Course Creation
                        </a>
                        <a href="#content-management" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-folder-open w-6 text-orange-500"></i>
                            Content Management
                        </a>
                        <a href="#study-materials" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-file-alt w-6 text-red-500"></i>
                            Study Materials
                        </a>
                        <a href="#quizzes-assignments" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-question-circle w-6 text-yellow-500"></i>
                            Quizzes & Assignments
                        </a>
                        <a href="#student-management" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-users w-6 text-teal-500"></i>
                            Student Management
                        </a>
                        <a href="#best-practices" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-star w-6 text-amber-500"></i>
                            Best Practices
                        </a>
                        <a href="#troubleshooting" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-wrench w-6 text-gray-500"></i>
                            Troubleshooting
                        </a>
                        <a href="#faq" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-700 transition">
                            <i class="fas fa-comments w-6 text-indigo-500"></i>
                            FAQ
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-12">
                
                <!-- Getting Started -->
                <section id="getting-started" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-rocket text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Getting Started</h2>
                    </div>
                    
                    <div class="prose max-w-none text-gray-600">
                        <p class="text-lg">Welcome to Edutrack LMS! As an instructor, you have powerful tools to create engaging courses and manage your students' learning journey.</p>
                        
                        <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Your First Steps</h3>
                        <ol class="space-y-4 list-decimal list-inside">
                            <li><strong>Complete Your Profile</strong> - Add your bio, profile picture, and expertise areas so students can learn about you.</li>
                            <li><strong>Explore the Dashboard</strong> - Familiarize yourself with the instructor dashboard where you can see your courses, student statistics, and quick actions.</li>
                            <li><strong>Create Your First Course</strong> - Use our course templates or start from scratch to build your content.</li>
                            <li><strong>Organize Your Content</strong> - Structure your course with modules and lessons for easy navigation.</li>
                            <li><strong>Engage with Students</strong> - Monitor progress, answer questions, and provide feedback.</li>
                        </ol>

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mt-6">
                            <p class="text-blue-800"><i class="fas fa-lightbulb mr-2"></i><strong>Pro Tip:</strong> Start with our pre-built course templates to quickly set up your course structure, then customize the content to match your teaching style.</p>
                        </div>
                    </div>
                </section>

                <!-- Glossary -->
                <section id="glossary" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-book text-green-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Glossary of Terms</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Course Structure -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-sitemap text-blue-500 mr-2"></i>
                                Course Structure
                            </h3>
                            <dl class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Course</dt>
                                    <dd class="text-gray-600 text-sm mt-1">A complete learning program containing multiple modules. Think of it as a textbook or a semester class.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Module</dt>
                                    <dd class="text-gray-600 text-sm mt-1">A chapter or section within a course. Modules group related lessons together. Example: "Introduction to Python" or "Advanced Techniques".</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Lesson</dt>
                                    <dd class="text-gray-600 text-sm mt-1">The smallest unit of content. A single topic or concept. Can be a video, reading material, quiz, or assignment.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Learning Path</dt>
                                    <dd class="text-gray-600 text-sm mt-1">The sequence in which students progress through your course, from first module to last.</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Content Types -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-file-alt text-orange-500 mr-2"></i>
                                Content Types
                            </h3>
                            <dl class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Video Lesson</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Lessons that use video as the primary content. Can include embedded YouTube/Vimeo videos or uploaded files.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Reading Material</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Text-based content including articles, PDFs, or written instructions. Supports rich text formatting.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Quiz</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Interactive assessments with various question types (multiple choice, true/false, short answer). Can be graded automatically.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Assignment</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Tasks requiring student submissions (files, text, or both). Requires manual grading.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Live Session</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Scheduled real-time online classes or webinars where you can interact with students directly.</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Student Management -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-users text-teal-500 mr-2"></i>
                                Student Terms
                            </h3>
                            <dl class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Enrollment</dt>
                                    <dd class="text-gray-600 text-sm mt-1">When a student joins your course. They get access to all published content and can track their progress.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Progress Tracking</dt>
                                    <dd class="text-gray-600 text-sm mt-1">System that records which lessons a student has viewed/completed, shown as a percentage.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Completion Certificate</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Digital certificate automatically generated when a student completes all required course content.</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Assessment -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-clipboard-check text-purple-500 mr-2"></i>
                                Assessment Terms
                            </h3>
                            <dl class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Points/Grading</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Numerical values assigned to assignments and quizzes. Used to calculate overall course grades.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Passing Score</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Minimum percentage required to pass a quiz. Can be set per quiz (default is usually 70%).</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Attempts</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Number of times a student can take a quiz. You can allow unlimited or limited attempts.</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="font-semibold text-gray-800">Due Date</dt>
                                    <dd class="text-gray-600 text-sm mt-1">Deadline for assignment submission. Students can still submit after due date but it may be marked as late.</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <!-- Course Creation -->
                <section id="course-creation" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-plus-circle text-purple-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Course Creation Guide</h2>
                    </div>

                    <div class="space-y-8">
                        <!-- Step 1 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">Plan Your Course Structure</h3>
                                <p class="text-gray-600 mt-2">Before creating content, outline your course:</p>
                                <ul class="list-disc list-inside mt-2 text-gray-600 space-y-1">
                                    <li>Define learning objectives (what will students know/be able to do?)</li>
                                    <li>Break content into logical modules (4-8 modules recommended)</li>
                                    <li>Plan 3-7 lessons per module</li>
                                    <li>Decide on assessment points (quizzes, assignments)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">Create the Course</h3>
                                <p class="text-gray-600 mt-2">Click "Create New Course" and fill in the details:</p>
                                <div class="bg-gray-50 rounded-lg p-4 mt-3">
                                    <ul class="space-y-2 text-gray-600">
                                        <li><strong>Title:</strong> Clear, descriptive, and searchable</li>
                                        <li><strong>Description:</strong> Explain what students will learn</li>
                                        <li><strong>Category:</strong> Helps students find your course</li>
                                        <li><strong>Thumbnail:</strong> Eye-catching image (1280x720 recommended)</li>
                                        <li><strong>Level:</strong> Beginner, Intermediate, or Advanced</li>
                                        <li><strong>Price:</strong> Set course fee (or free)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">Build Your Content Structure</h3>
                                <p class="text-gray-600 mt-2">Add modules and lessons in order:</p>
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-3">
                                    <p class="text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Important:</strong> Content flows from top to bottom. Students must progress through lessons in the order you set.</p>
                                </div>
                                <ol class="list-decimal list-inside mt-3 text-gray-600 space-y-2">
                                    <li>Create modules first (these are your chapters/sections)</li>
                                    <li>Add lessons to each module</li>
                                    <li>Set lesson types appropriately (Video, Reading, Quiz, etc.)</li>
                                    <li>Arrange in logical learning order</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">4</div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">Publish Your Course</h3>
                                <p class="text-gray-600 mt-2">When ready, change status from "Draft" to "Published":</p>
                                <ul class="list-disc list-inside mt-2 text-gray-600 space-y-1">
                                    <li>Review all content for errors</li>
                                    <li>Test all video links and downloads</li>
                                    <li>Preview as a student would see it</li>
                                    <li>Click "Publish" to make it live</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Content Management -->
                <section id="content-management" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-folder-open text-orange-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Content Management</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Using Course Templates</h3>
                            <p class="text-gray-600 mb-4">Save time by starting with a pre-built template:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-800">Standard Course</h4>
                                    <p class="text-sm text-blue-600 mt-1">4-8 week program with weekly modules and assessments</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4">
                                    <h4 class="font-semibold text-green-800">Intensive Bootcamp</h4>
                                    <p class="text-sm text-green-600 mt-1">2-4 week intensive with daily lessons</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-4">
                                    <h4 class="font-semibold text-purple-800">Tutorial Series</h4>
                                    <p class="text-sm text-purple-600 mt-1">Bite-sized tutorials without strict structure</p>
                                </div>
                                <div class="bg-orange-50 rounded-lg p-4">
                                    <h4 class="font-semibold text-orange-800">Certification Prep</h4>
                                    <p class="text-sm text-orange-600 mt-1">Exam preparation with practice tests</p>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Bulk Upload Features</h3>
                            <p class="text-gray-600 mb-4">Quickly add multiple lessons using these methods:</p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <i class="fas fa-file-archive text-blue-500 mt-1"></i>
                                    <div>
                                        <strong class="text-gray-800">ZIP Upload:</strong>
                                        <p class="text-gray-600 text-sm">Organize files in folders (Module 1, Module 2, etc.) and upload as one ZIP file. The system will auto-create the structure.</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="fas fa-file-csv text-green-500 mt-1"></i>
                                    <div>
                                        <strong class="text-gray-800">CSV Import:</strong>
                                        <p class="text-gray-600 text-sm">Create a spreadsheet with lesson titles, types, and durations. Import to bulk-create lessons.</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="fas fa-link text-purple-500 mt-1"></i>
                                    <div>
                                        <strong class="text-gray-800">Video URL Import:</strong>
                                        <p class="text-gray-600 text-sm">Paste multiple YouTube/Vimeo links at once to create video lessons.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Content Organization Tips</h3>
                            <div class="space-y-3 text-gray-600">
                                <p><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Consistent Naming:</strong> Use clear, descriptive titles for modules and lessons</p>
                                <p><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Logical Flow:</strong> Each lesson should build on the previous one</p>
                                <p><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Variety:</strong> Mix video, reading, and interactive content to keep students engaged</p>
                                <p><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Regular Assessments:</strong> Include quizzes every 3-5 lessons to check understanding</p>
                                <p><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Estimated Time:</strong> Always set duration so students can plan their study time</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Study Materials -->
                <section id="study-materials" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-alt text-red-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Preparing Study Materials</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                            <p class="text-blue-800"><strong>Good study materials make the difference</strong> between an average course and an exceptional one. Here's how to create materials that truly help your students learn.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-video text-red-500 mr-2"></i>
                                    Video Content
                                </h3>
                                <ul class="space-y-2 text-gray-600 text-sm">
                                    <li><strong>Keep videos 5-15 minutes</strong> - Attention spans drop after this</li>
                                    <li><strong>Use clear audio</strong> - Invest in a decent microphone</li>
                                    <li><strong>Show, don't just tell</strong> - Use screen recordings for demonstrations</li>
                                    <li><strong>Add captions</strong> - Improves accessibility</li>
                                    <li><strong>Include a summary</strong> - Recap key points at the end</li>
                                    <li><strong>Supported formats:</strong> YouTube, Vimeo, or direct MP4 upload</li>
                                </ul>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    Documents & PDFs
                                </h3>
                                <ul class="space-y-2 text-gray-600 text-sm">
                                    <li><strong>Use PDF for final documents</strong> - Preserves formatting</li>
                                    <li><strong>Include table of contents</strong> - For longer documents</li>
                                    <li><strong>Readable fonts</strong> - Minimum 11pt, clear headings</li>
                                    <li><strong>Break up text</strong> - Use bullet points, images, white space</li>
                                    <li><strong>Mobile-friendly</strong> - Many students use phones</li>
                                    <li><strong>Supported types:</strong> PDF, Word, PowerPoint, Excel</li>
                                </ul>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-images text-green-500 mr-2"></i>
                                    Images & Diagrams
                                </h3>
                                <ul class="space-y-2 text-gray-600 text-sm">
                                    <li><strong>High quality</strong> - Minimum 800px width</li>
                                    <li><strong>Compress files</strong> - Keep under 5MB each</li>
                                    <li><strong>Use diagrams</strong> - Visuals aid understanding</li>
                                    <li><strong>Infographics</strong> - Great for summarizing complex info</li>
                                    <li><strong>Alt text</strong> - Describe images for accessibility</li>
                                    <li><strong>Formats:</strong> JPG, PNG, GIF recommended</li>
                                </ul>
                            </div>

                            <div class="border rounded-xl p-5">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-code text-purple-500 mr-2"></i>
                                    Code & Technical Content
                                </h3>
                                <ul class="space-y-2 text-gray-600 text-sm">
                                    <li><strong>Syntax highlighting</strong> - Makes code readable</li>
                                    <li><strong>Working examples</strong> - Provide complete, tested code</li>
                                    <li><strong>Line numbers</strong> - Reference specific lines</li>
                                    <li><strong>Comments</strong> - Explain what code does</li>
                                    <li><strong>Exercise files</strong> - Let students practice</li>
                                    <li><strong>Version notes</strong> - Specify software versions used</li>
                                </ul>
                            </div>
                        </div>

                        <div class="border rounded-xl p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">File Upload Guidelines</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left p-3 font-semibold text-gray-700">File Type</th>
                                            <th class="text-left p-3 font-semibold text-gray-700">Max Size</th>
                                            <th class="text-left p-3 font-semibold text-gray-700">Best For</th>
                                            <th class="text-left p-3 font-semibold text-gray-700">Tips</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="p-3"><i class="fas fa-file-pdf text-red-500 mr-2"></i>PDF</td>
                                            <td class="p-3">50 MB</td>
                                            <td class="p-3">Reading materials, guides</td>
                                            <td class="p-3">Optimize for web, use bookmarks</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><i class="fas fa-file-video text-blue-500 mr-2"></i>Video (MP4)</td>
                                            <td class="p-3">500 MB</td>
                                            <td class="p-3">Tutorials, lectures</td>
                                            <td class="p-3">Compress before upload, use 720p</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><i class="fas fa-file-audio text-green-500 mr-2"></i>Audio (MP3)</td>
                                            <td class="p-3">50 MB</td>
                                            <td class="p-3">Podcasts, interviews</td>
                                            <td class="p-3">Clear audio, 128kbps minimum</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><i class="fas fa-file-archive text-yellow-500 mr-2"></i>ZIP</td>
                                            <td class="p-3">100 MB</td>
                                            <td class="p-3">Multiple files, project files</td>
                                            <td class="p-3">Organize in folders, include README</td>
                                        </tr>
                                        <tr>
                                            <td class="p-3"><i class="fas fa-file-code text-gray-500 mr-2"></i>Code Files</td>
                                            <td class="p-3">10 MB</td>
                                            <td class="p-3">Source code, exercises</td>
                                            <td class="p-3">ZIP multiple files, comment code</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quizzes & Assignments -->
                <section id="quizzes-assignments" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-question-circle text-yellow-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Quizzes & Assignments</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Creating Effective Quizzes</h3>
                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Question Types</h4>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li><strong>Multiple Choice:</strong> Good for testing knowledge recall</li>
                                        <li><strong>True/False:</strong> Quick checks for understanding</li>
                                        <li><strong>Short Answer:</strong> Tests comprehension, requires manual grading</li>
                                        <li><strong>Fill in Blank:</strong> Tests specific knowledge</li>
                                        <li><strong>Matching:</strong> Good for terminology and definitions</li>
                                    </ul>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <h4 class="font-medium text-yellow-800 mb-2"><i class="fas fa-lightbulb mr-2"></i>Quiz Best Practices</h4>
                                    <ul class="text-sm text-yellow-700 space-y-1">
                                        <li>Mix question types to keep students engaged</li>
                                        <li>Include 1-2 questions from each lesson covered</li>
                                        <li>Set reasonable time limits (1-2 min per question)</li>
                                        <li>Allow multiple attempts for practice quizzes</li>
                                        <li>Provide explanations for correct answers</li>
                                        <li>Shuffle question order to prevent cheating</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Assignment Design</h3>
                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Assignment Types</h4>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li><strong>File Upload:</strong> Projects, essays, documents</li>
                                        <li><strong>Text Submission:</strong> Short answers, reflections</li>
                                        <li><strong>Practical Exercise:</strong> Hands-on tasks</li>
                                        <li><strong>Peer Review:</strong> Students review each other's work</li>
                                    </ul>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h4 class="font-medium text-green-800 mb-2"><i class="fas fa-clipboard-check mr-2"></i>Clear Instructions</h4>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>State exactly what students should submit</li>
                                        <li>Specify file formats (PDF, DOCX, etc.)</li>
                                        <li>Set clear due dates and late penalties</li>
                                        <li>Provide grading rubric or criteria</li>
                                        <li>Include examples of good submissions</li>
                                        <li>Set point values for each criterion</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border rounded-xl p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Grading Workflow</h3>
                        <div class="flex flex-col md:flex-row gap-4 text-center">
                            <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <span class="font-bold text-blue-600">1</span>
                                </div>
                                <h4 class="font-medium text-gray-800">Student Submits</h4>
                                <p class="text-sm text-gray-600 mt-1">Assignment appears in your grading queue</p>
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="fas fa-arrow-right text-gray-400 hidden md:block"></i>
                                <i class="fas fa-arrow-down text-gray-400 md:hidden"></i>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <span class="font-bold text-blue-600">2</span>
                                </div>
                                <h4 class="font-medium text-gray-800">You Review</h4>
                                <p class="text-sm text-gray-600 mt-1">Open submission, view files, add feedback</p>
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="fas fa-arrow-right text-gray-400 hidden md:block"></i>
                                <i class="fas fa-arrow-down text-gray-400 md:hidden"></i>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <span class="font-bold text-blue-600">3</span>
                                </div>
                                <h4 class="font-medium text-gray-800">Assign Grade</h4>
                                <p class="text-sm text-gray-600 mt-1">Enter score and overall feedback</p>
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="fas fa-arrow-right text-gray-400 hidden md:block"></i>
                                <i class="fas fa-arrow-down text-gray-400 md:hidden"></i>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <span class="font-bold text-blue-600">4</span>
                                </div>
                                <h4 class="font-medium text-gray-800">Student Notified</h4>
                                <p class="text-sm text-gray-600 mt-1">Automatic notification sent to student</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Student Management -->
                <section id="student-management" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-teal-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Student Management</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="border rounded-xl p-5">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fas fa-chart-line text-blue-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-2">Track Progress</h3>
                                <p class="text-sm text-gray-600">Monitor which lessons students have completed and their overall course progress percentage.</p>
                            </div>
                            <div class="border rounded-xl p-5">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fas fa-comments text-green-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-2">Communicate</h3>
                                <p class="text-sm text-gray-600">Send announcements to all students or messages to individuals.</p>
                            </div>
                            <div class="border rounded-xl p-5">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fas fa-exclamation-triangle text-purple-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-2">Identify Strugglers</h3>
                                <p class="text-sm text-gray-600">Quickly spot students who are falling behind or haven't engaged recently.</p>
                            </div>
                        </div>

                        <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-lg">
                            <h4 class="font-semibold text-teal-800 mb-2">Student Engagement Tips</h4>
                            <ul class="text-teal-700 space-y-1 text-sm">
                                <li><i class="fas fa-check mr-2"></i><strong>Welcome Message:</strong> Send a welcome announcement when students enroll</li>
                                <li><i class="fas fa-check mr-2"></i><strong>Regular Updates:</strong> Post weekly announcements about new content or reminders</li>
                                <li><i class="fas fa-check mr-2"></i><strong>Quick Feedback:</strong> Grade assignments within 48-72 hours when possible</li>
                                <li><i class="fas fa-check mr-2"></i><strong>Encourage Questions:</strong> Create a Q&A module where students can ask questions</li>
                                <li><i class="fas fa-check mr-2"></i><strong>Celebrate Success:</strong> Acknowledge students who complete milestones</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Best Practices -->
                <section id="best-practices" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-star text-amber-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Best Practices</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-lightbulb text-amber-500 mr-2"></i>
                                Course Design
                            </h3>
                            <ul class="space-y-2 text-gray-600 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Start with clear learning objectives</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Use the "Tell, Show, Do" method</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Include a "Getting Started" module</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>End each module with a summary</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Provide downloadable resources</span>
                                </li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Time Management
                            </h3>
                            <ul class="space-y-2 text-gray-600 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Set realistic course duration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Space out assignments evenly</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Allow buffer time before deadlines</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Schedule live sessions in advance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Respond to questions within 24-48 hours</span>
                                </li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-universal-access text-purple-500 mr-2"></i>
                                Accessibility
                            </h3>
                            <ul class="space-y-2 text-gray-600 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Add captions to all videos</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Use high contrast colors</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Provide transcripts for audio</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Use descriptive link text</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Structure content with headings</span>
                                </li>
                            </ul>
                        </div>

                        <div class="border rounded-xl p-5">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                                Academic Integrity
                            </h3>
                            <ul class="space-y-2 text-gray-600 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Create unique quiz questions each term</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Use question banks and shuffle order</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Set time limits appropriately</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Include academic honesty statement</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>Use plagiarism detection when available</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section id="troubleshooting" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-wrench text-gray-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Troubleshooting</h2>
                    </div>

                    <div class="space-y-4">
                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Video not playing or showing error</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p><strong>For YouTube videos:</strong> Ensure the video is public or unlisted (not private). Check that the URL is correct.</p>
                                <p><strong>For uploaded videos:</strong> Verify the file is MP4 format and under 500MB. Re-upload if necessary.</p>
                                <p><strong>Students report issues:</strong> Ask them to try a different browser or check their internet connection.</p>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Students can't see my course</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p>Check the following:</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Course status is set to "Published" (not "Draft")</li>
                                    <li>At least one module and lesson exist</li>
                                    <li>Student is properly enrolled</li>
                                    <li>Course start date has passed (if set)</li>
                                </ul>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Quiz scores not saving</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p>Ensure students click "Submit Quiz" at the end. If they close the browser without submitting, progress may be lost.</p>
                                <p>Check that the quiz has at least one question and correct answers are marked.</p>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">File uploads failing</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p>Check file size limits. Maximum sizes:</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Videos: 500 MB</li>
                                    <li>PDFs: 50 MB</li>
                                    <li>Other files: 10-50 MB depending on type</li>
                                </ul>
                                <p>Compress large files or use cloud storage links for very large files.</p>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <span class="font-medium text-gray-800">Students report progress not tracking</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="p-4 text-gray-600 text-sm space-y-2">
                                <p>Progress is marked complete when:</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Video lessons: Student watches at least 90%</li>
                                    <li>Reading lessons: Student marks as complete</li>
                                    <li>Quizzes: Student submits the quiz</li>
                                    <li>Assignments: Student submits work</li>
                                </ul>
                                <p>Progress may take a few moments to update. Refresh the page to see current status.</p>
                            </div>
                        </details>
                    </div>
                </section>

                <!-- FAQ -->
                <section id="faq" class="bg-white rounded-xl shadow-sm border p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-comments text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Frequently Asked Questions</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">How many courses can I create?</h3>
                            <p class="text-gray-600">There is no limit to the number of courses you can create. However, we recommend focusing on quality over quantity.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Can I copy content from one course to another?</h3>
                            <p class="text-gray-600">Yes! Use the "Duplicate" feature in course settings, or use course templates as a starting point for similar courses.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">How do I know if students are engaged?</h3>
                            <p class="text-gray-600">Check the "Students" tab in your course to see enrollment statistics, progress percentages, and last activity dates. Students who haven't logged in recently will be flagged.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Can I modify a course after students have enrolled?</h3>
                            <p class="text-gray-600">Yes, you can add new content anytime. However, be careful when removing content that students may have already started. Notify students of major changes via announcement.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">What happens when a student completes the course?</h3>
                            <p class="text-gray-600">Students who complete all required lessons automatically receive a completion certificate that they can download and share.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">How do I get paid for my courses?</h3>
                            <p class="text-gray-600">Contact the administration for payment setup and revenue sharing details.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Can I export my course content?</h3>
                            <p class="text-gray-600">Currently, course content cannot be exported. Keep backup copies of your original files (videos, documents) on your local computer.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Who do I contact for technical support?</h3>
                            <p class="text-gray-600">For technical issues, contact the system administrator at <?= SITE_EMAIL ?> or use the support ticket system.</p>
                        </div>
                    </div>
                </section>

                <!-- Need More Help -->
                <div class="bg-blue-600 rounded-xl p-8 text-center text-white">
                    <h2 class="text-2xl font-bold mb-3">Still Need Help?</h2>
                    <p class="text-blue-100 mb-6 max-w-2xl mx-auto">
                        Can't find what you're looking for? Our support team is here to help you get the most out of the platform.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="mailto:<?= SITE_EMAIL ?>" class="inline-flex items-center px-6 py-3 bg-white text-blue-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-envelope mr-2"></i>Contact Support
                        </a>
                        <a href="#" class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition">
                            <i class="fas fa-video mr-2"></i>Watch Video Tutorials
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
const searchInput = document.getElementById('helpSearch');
const sections = document.querySelectorAll('section');

searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    
    sections.forEach(section => {
        const text = section.textContent.toLowerCase();
        const headings = section.querySelectorAll('h2, h3, h4');
        let hasMatch = false;
        
        if (text.includes(query)) {
            hasMatch = true;
        }
        
        headings.forEach(heading => {
            if (heading.textContent.toLowerCase().includes(query)) {
                hasMatch = true;
                heading.style.backgroundColor = query ? '#fef3c7' : '';
            } else {
                heading.style.backgroundColor = '';
            }
        });
        
        section.style.display = (hasMatch || query === '') ? 'block' : 'none';
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

<?php require_once '../../src/templates/footer.php'; ?>
