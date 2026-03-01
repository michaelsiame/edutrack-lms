<?php
/**
 * Instructor - Course Templates
 * Pre-built course structures for quick starts
 */

require_once '../../../src/bootstrap.php';
require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';
require_once '../../../src/classes/Category.php';
require_once '../../../src/classes/Instructor.php';

$db = Database::getInstance();
$userId = currentUserId();
$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();

// Handle template application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['template_id'])) {
    validateCSRF();
    
    $templateId = $_POST['template_id'];
    $courseId = $_POST['course_id'] ?? null;
    
    // If no course ID, create new course from template
    if (!$courseId) {
        $courseData = [
            'instructor_id' => $instructorId,
            'title' => $_POST['course_title'] ?? 'New Course',
            'slug' => slugify($_POST['course_title'] ?? 'New Course'),
            'description' => $_POST['course_description'] ?? '',
            'category_id' => $_POST['category_id'] ?? null,
            'course_level' => $_POST['level'] ?? 'beginner',
            'status' => 'draft'
        ];
        
        $courseId = Course::create($courseData);
    }
    
    if ($courseId) {
        applyTemplate($templateId, $courseId, $db);
        flash('message', 'Template applied successfully! You can now customize the content.', 'success');
        redirect(url('instructor/courses/modules.php?id=' . $courseId));
    }
}

function applyTemplate($templateId, $courseId, $db) {
    $templates = getTemplates();
    $template = $templates[$templateId] ?? null;
    
    if (!$template) return;
    
    foreach ($template['modules'] as $moduleData) {
        // Create module
        $db->query(
            "INSERT INTO modules (course_id, title, description, display_order) VALUES (?, ?, ?, ?)",
            [$courseId, $moduleData['title'], $moduleData['description'] ?? '', $moduleData['order'] ?? 0]
        );
        $moduleId = $db->lastInsertId();
        
        // Create lessons
        if (!empty($moduleData['lessons'])) {
            foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                $db->query(
                    "INSERT INTO lessons (module_id, title, slug, description, lesson_type, duration, display_order, is_preview) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $moduleId,
                        $lessonData['title'],
                        slugify($lessonData['title']),
                        $lessonData['description'] ?? '',
                        $lessonData['type'] ?? 'video',
                        $lessonData['duration'] ?? null,
                        $lessonIndex,
                        $lessonData['is_preview'] ?? 0
                    ]
                );
            }
        }
    }
}

function getTemplates() {
    return [
        'standard_course' => [
            'name' => 'Standard Course',
            'icon' => 'fa-graduation-cap',
            'color' => 'blue',
            'description' => 'A traditional course structure with introduction, core modules, assessments, and conclusion.',
            'duration_estimate' => '4-8 weeks',
            'best_for' => 'Comprehensive skill-building courses',
            'modules' => [
                [
                    'title' => 'Introduction',
                    'order' => 0,
                    'lessons' => [
                        ['title' => 'Welcome to the Course', 'type' => 'Video', 'duration' => 5, 'is_preview' => 1],
                        ['title' => 'Course Overview', 'type' => 'Video', 'duration' => 10],
                        ['title' => 'How to Use This Course', 'type' => 'Reading', 'duration' => 5],
                        ['title' => 'Setting Up Your Environment', 'type' => 'Video', 'duration' => 15]
                    ]
                ],
                [
                    'title' => 'Module 1: Foundations',
                    'order' => 1,
                    'lessons' => [
                        ['title' => 'Key Concepts', 'type' => 'Video', 'duration' => 20],
                        ['title' => 'Basic Principles', 'type' => 'Video', 'duration' => 25],
                        ['title' => 'Common Terminology', 'type' => 'Reading', 'duration' => 15],
                        ['title' => 'Practice Exercise 1', 'type' => 'Reading', 'duration' => 30]
                    ]
                ],
                [
                    'title' => 'Module 2: Core Skills',
                    'order' => 2,
                    'lessons' => [
                        ['title' => 'Skill Building Part 1', 'type' => 'Video', 'duration' => 30],
                        ['title' => 'Skill Building Part 2', 'type' => 'Video', 'duration' => 30],
                        ['title' => 'Hands-on Practice', 'type' => 'Reading', 'duration' => 45],
                        ['title' => 'Common Mistakes to Avoid', 'type' => 'Video', 'duration' => 15]
                    ]
                ],
                [
                    'title' => 'Module 3: Advanced Topics',
                    'order' => 3,
                    'lessons' => [
                        ['title' => 'Taking It Further', 'type' => 'Video', 'duration' => 25],
                        ['title' => 'Advanced Techniques', 'type' => 'Video', 'duration' => 35],
                        ['title' => 'Real-world Applications', 'type' => 'Video', 'duration' => 20],
                        ['title' => 'Case Study', 'type' => 'Reading', 'duration' => 30]
                    ]
                ],
                [
                    'title' => 'Assessment & Practice',
                    'order' => 4,
                    'lessons' => [
                        ['title' => 'Review Quiz', 'type' => 'Reading', 'duration' => 20],
                        ['title' => 'Practical Assignment', 'type' => 'Reading', 'duration' => 60],
                        ['title' => 'Peer Review Guidelines', 'type' => 'Reading', 'duration' => 10]
                    ]
                ],
                [
                    'title' => 'Conclusion',
                    'order' => 5,
                    'lessons' => [
                        ['title' => 'Course Summary', 'type' => 'Video', 'duration' => 15],
                        ['title' => 'Next Steps', 'type' => 'Video', 'duration' => 10],
                        ['title' => 'Additional Resources', 'type' => 'Reading', 'duration' => 10],
                        ['title' => 'Certificate of Completion', 'type' => 'Reading', 'duration' => 5]
                    ]
                ]
            ]
        ],
        
        'bootcamp' => [
            'name' => 'Intensive Bootcamp',
            'icon' => 'fa-fire',
            'color' => 'red',
            'description' => 'Fast-paced, intensive learning experience with daily lessons over 2-4 weeks.',
            'duration_estimate' => '2-4 weeks',
            'best_for' => 'Immersive learning experiences',
            'modules' => [
                ['title' => 'Week 1: Basics', 'order' => 0, 'lessons' => [
                    ['title' => 'Day 1: Getting Started', 'duration' => 60],
                    ['title' => 'Day 2: Core Concepts', 'duration' => 60],
                    ['title' => 'Day 3: Hands-on Practice', 'duration' => 90],
                    ['title' => 'Day 4: Building Projects', 'duration' => 90],
                    ['title' => 'Day 5: Week 1 Review', 'duration' => 60]
                ]],
                ['title' => 'Week 2: Intermediate', 'order' => 1, 'lessons' => [
                    ['title' => 'Day 6: Leveling Up', 'duration' => 60],
                    ['title' => 'Day 7: Advanced Basics', 'duration' => 60],
                    ['title' => 'Day 8: Problem Solving', 'duration' => 90],
                    ['title' => 'Day 9: Real Projects', 'duration' => 90],
                    ['title' => 'Day 10: Mid-point Assessment', 'duration' => 60]
                ]],
                ['title' => 'Week 3: Advanced', 'order' => 2, 'lessons' => [
                    ['title' => 'Day 11: Complex Topics', 'duration' => 60],
                    ['title' => 'Day 12: Best Practices', 'duration' => 60],
                    ['title' => 'Day 13: Optimization', 'duration' => 90],
                    ['title' => 'Day 14: Industry Standards', 'duration' => 90],
                    ['title' => 'Day 15: Capstone Prep', 'duration' => 60]
                ]],
                ['title' => 'Final Week: Capstone', 'order' => 3, 'lessons' => [
                    ['title' => 'Day 16: Project Kickoff', 'duration' => 60],
                    ['title' => 'Day 17-19: Project Work', 'duration' => 180],
                    ['title' => 'Day 20: Presentations & Graduation', 'duration' => 120]
                ]]
            ]
        ],
        
        'tutorial_series' => [
            'name' => 'Tutorial Series',
            'icon' => 'fa-list-ol',
            'color' => 'green',
            'description' => 'Short, focused lessons perfect for bite-sized learning and specific skill tutorials.',
            'duration_estimate' => 'Self-paced',
            'best_for' => 'Quick skill tutorials',
            'modules' => [
                ['title' => 'Getting Started', 'order' => 0, 'lessons' => [
                    ['title' => 'What You Will Learn', 'duration' => 5, 'is_preview' => 1],
                    ['title' => 'Tools & Requirements', 'duration' => 10],
                    ['title' => 'Setup Guide', 'duration' => 15]
                ]],
                ['title' => 'Tutorials', 'order' => 1, 'lessons' => [
                    ['title' => 'Tutorial 1: Basics', 'duration' => 15],
                    ['title' => 'Tutorial 2: Step by Step', 'duration' => 20],
                    ['title' => 'Tutorial 3: Common Patterns', 'duration' => 20],
                    ['title' => 'Tutorial 4: Tips & Tricks', 'duration' => 15],
                    ['title' => 'Tutorial 5: Advanced Usage', 'duration' => 25]
                ]],
                ['title' => 'Practice Projects', 'order' => 2, 'lessons' => [
                    ['title' => 'Mini Project 1', 'duration' => 30],
                    ['title' => 'Mini Project 2', 'duration' => 45],
                    ['title' => 'Challenge Exercise', 'duration' => 60]
                ]],
                ['title' => 'Wrap Up', 'order' => 3, 'lessons' => [
                    ['title' => 'Review & Summary', 'duration' => 10],
                    ['title' => 'Where to Go Next', 'duration' => 5]
                ]]
            ]
        ],
        
        'certification_prep' => [
            'name' => 'Certification Prep',
            'icon' => 'fa-certificate',
            'color' => 'purple',
            'description' => 'Structured preparation for professional certifications with theory, practice exams, and review.',
            'duration_estimate' => '6-12 weeks',
            'best_for' => 'Exam preparation courses',
            'modules' => [
                ['title' => 'Exam Overview', 'order' => 0, 'lessons' => [
                    ['title' => 'About the Certification', 'duration' => 15],
                    ['title' => 'Exam Format & Rules', 'duration' => 20],
                    ['title' => 'Study Plan', 'duration' => 10],
                    ['title' => 'Registration Process', 'duration' => 10]
                ]],
                ['title' => 'Domain 1: Core Knowledge', 'order' => 1, 'lessons' => [
                    ['title' => '1.1 Key Concepts', 'duration' => 30],
                    ['title' => '1.2 Theory & Principles', 'duration' => 30],
                    ['title' => '1.3 Practice Questions', 'duration' => 20]
                ]],
                ['title' => 'Domain 2: Applied Skills', 'order' => 2, 'lessons' => [
                    ['title' => '2.1 Practical Application', 'duration' => 30],
                    ['title' => '2.2 Case Studies', 'duration' => 30],
                    ['title' => '2.3 Practice Questions', 'duration' => 20]
                ]],
                ['title' => 'Practice Exams', 'order' => 3, 'lessons' => [
                    ['title' => 'Practice Exam 1', 'duration' => 120],
                    ['title' => 'Practice Exam 1 Review', 'duration' => 45],
                    ['title' => 'Practice Exam 2', 'duration' => 120],
                    ['title' => 'Practice Exam 2 Review', 'duration' => 45]
                ]],
                ['title' => 'Final Review', 'order' => 4, 'lessons' => [
                    ['title' => 'Key Topics Review', 'duration' => 60],
                    ['title' => 'Last Minute Tips', 'duration' => 20],
                    ['title' => 'Exam Day Strategy', 'duration' => 15]
                ]]
            ]
        ],
        
        'workshop' => [
            'name' => 'Interactive Workshop',
            'icon' => 'fa-users',
            'color' => 'orange',
            'description' => 'Single-session or short workshop format with pre-work, live session, and follow-up materials.',
            'duration_estimate' => '1-3 days',
            'best_for' => 'Live workshops & training',
            'modules' => [
                ['title' => 'Pre-Workshop', 'order' => 0, 'lessons' => [
                    ['title' => 'Welcome & Introduction', 'duration' => 10],
                    ['title' => 'Pre-workshop Materials', 'duration' => 30],
                    ['title' => 'Pre-assessment', 'duration' => 15]
                ]],
                ['title' => 'Workshop Session', 'order' => 1, 'lessons' => [
                    ['title' => 'Opening & Objectives', 'duration' => 15],
                    ['title' => 'Main Content Part 1', 'duration' => 45],
                    ['title' => 'Break', 'duration' => 15],
                    ['title' => 'Main Content Part 2', 'duration' => 45],
                    ['title' => 'Hands-on Activity', 'duration' => 60],
                    ['title' => 'Q&A and Discussion', 'duration' => 30]
                ]],
                ['title' => 'Post-Workshop', 'order' => 2, 'lessons' => [
                    ['title' => 'Session Recording', 'duration' => 120],
                    ['title' => 'Additional Resources', 'duration' => 20],
                    ['title' => 'Action Items Template', 'duration' => 10],
                    ['title' => 'Follow-up Assessment', 'duration' => 15]
                ]]
            ]
        ],
        
        'project_based' => [
            'name' => 'Project-Based Learning',
            'icon' => 'fa-project-diagram',
            'color' => 'indigo',
            'description' => 'Learn by building real projects. Each module focuses on completing a tangible project.',
            'duration_estimate' => '8-12 weeks',
            'best_for' => 'Portfolio-building courses',
            'modules' => [
                ['title' => 'Project 1: Getting Started', 'order' => 0, 'lessons' => [
                    ['title' => 'Project Overview', 'duration' => 15],
                    ['title' => 'Planning & Setup', 'duration' => 30],
                    ['title' => 'Building Part 1', 'duration' => 45],
                    ['title' => 'Building Part 2', 'duration' => 45],
                    ['title' => 'Testing & Refinement', 'duration' => 30]
                ]],
                ['title' => 'Project 2: Level Up', 'order' => 1, 'lessons' => [
                    ['title' => 'Project Introduction', 'duration' => 15],
                    ['title' => 'Requirements Analysis', 'duration' => 30],
                    ['title' => 'Design & Architecture', 'duration' => 45],
                    ['title' => 'Implementation', 'duration' => 90],
                    ['title' => 'Review & Feedback', 'duration' => 30]
                ]],
                ['title' => 'Project 3: Advanced Build', 'order' => 2, 'lessons' => [
                    ['title' => 'Complex Project Brief', 'duration' => 20],
                    ['title' => 'Research & Planning', 'duration' => 45],
                    ['title' => 'Development Phase', 'duration' => 120],
                    ['title' => 'Polish & Optimization', 'duration' => 45]
                ]],
                ['title' => 'Final Project', 'order' => 3, 'lessons' => [
                    ['title' => 'Capstone Project Requirements', 'duration' => 30],
                    ['title' => 'Mentorship & Guidance', 'duration' => 60],
                    ['title' => 'Building Your Portfolio Piece', 'duration' => 180],
                    ['title' => 'Final Presentation', 'duration' => 60]
                ]]
            ]
        ]
    ];
}

$templates = getTemplates();
$categories = Category::all(['active_only' => true]);

$page_title = 'Course Templates';
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-magic text-primary-600 mr-3"></i>Course Templates
            </h1>
            <p class="text-gray-500 max-w-2xl mx-auto">
                Start with a pre-built course structure designed by educational experts. 
                Save time and follow best practices for online learning.
            </p>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($templates as $id => $template): 
                $colorMap = [
                    'blue' => 'bg-blue-500 text-blue-600',
                    'red' => 'bg-red-500 text-red-600',
                    'green' => 'bg-green-500 text-green-600',
                    'purple' => 'bg-purple-500 text-purple-600',
                    'orange' => 'bg-orange-500 text-orange-600',
                    'indigo' => 'bg-indigo-500 text-indigo-600'
                ];
                $colorClass = $colorMap[$template['color']] ?? $colorMap['blue'];
                list($bgColor, $textColor) = explode(' ', $colorClass);
            ?>
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden hover:shadow-card-hover transition group">
                <!-- Header -->
                <div class="h-32 <?= $bgColor ?> relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="fas <?= $template['icon'] ?> text-white text-5xl opacity-80"></i>
                    </div>
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 bg-white/90 rounded-full text-xs font-semibold <?= $textColor ?>">
                            <?= $template['duration_estimate'] ?>
                        </span>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?= $template['name'] ?></h3>
                    <p class="text-sm text-gray-500 mb-4"><?= $template['description'] ?></p>
                    
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                        <span><i class="fas fa-folder mr-1"></i><?= count($template['modules']) ?> modules</span>
                        <span><i class="fas fa-play-circle mr-1"></i><?= array_sum(array_map(fn($m) => count($m['lessons'] ?? []), $template['modules'])) ?> lessons</span>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <p class="text-xs text-gray-600">
                            <span class="font-semibold">Best for:</span> <?= $template['best_for'] ?>
                        </p>
                    </div>
                    
                    <button onclick="showTemplateModal('<?= $id ?>')" 
                            class="w-full px-4 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition font-medium">
                        <i class="fas fa-plus mr-2"></i>Use This Template
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Custom Template CTA -->
        <div class="mt-12 bg-gradient-to-r from-primary-600 to-purple-600 rounded-2xl p-8 text-white text-center">
            <h3 class="text-2xl font-bold mb-2">Have Your Own Template?</h3>
            <p class="text-white/80 mb-6 max-w-xl mx-auto">
                Create a course structure that works for you and save it as a reusable template. 
                Perfect for consistent branding across multiple courses.
            </p>
            <button onclick="showToast('Custom template feature coming soon!', 'info')" 
                    class="px-6 py-3 bg-white text-primary-600 rounded-xl font-semibold hover:bg-gray-100 transition">
                <i class="fas fa-save mr-2"></i>Save Current Course as Template
            </button>
        </div>

    </div>
</div>

<!-- Template Application Modal -->
<div id="templateModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Apply Template</h3>
            <button onclick="closeModal('templateModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="template_id" id="selected_template_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course Title *</label>
                <input type="text" name="course_title" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                       placeholder="e.g., Introduction to Web Development">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category_id" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                    <option value="">Select category...</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category->getId() ?>"><?= htmlspecialchars($category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                <select name="level" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                    <option value="all levels">All Levels</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="course_description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                          placeholder="Brief description of your course..."></textarea>
            </div>
            
            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    This will create a new course with the template structure. You can customize everything afterward.
                </p>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('templateModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition">
                    <i class="fas fa-magic mr-2"></i>Create Course
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showTemplateModal(templateId) {
    document.getElementById('selected_template_id').value = templateId;
    openModal('templateModal');
}
</script>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
