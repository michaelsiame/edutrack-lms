<?php
/**
 * Instructor Courses Management
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';

$user = User::current();
$instructorId = $user->getId();

// Get instructor's courses
$courses = Course::all(['instructor_id' => $instructorId]);

// Get statistics
$stats = [
    'total' => count($courses),
    'published' => count(array_filter($courses, fn($c) => $c['status'] == 'published')),
    'draft' => count(array_filter($courses, fn($c) => $c['status'] == 'draft')),
    'total_students' => $db->fetchColumn("
        SELECT COUNT(DISTINCT e.user_id)
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ?
    ", [$instructorId])
];

$page_title = 'My Courses - Instructor';
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
                <p class="text-gray-600 mt-1">Manage your courses and content</p>
            </div>
            <a href="<?= url('instructor/courses/create.php') ?>" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Create Course
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Published</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats['published'] ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Drafts</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2"><?= $stats['draft'] ?></p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-edit text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2"><?= $stats['total_students'] ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courses)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Yet</h3>
                <p class="text-gray-500 mb-6">Create your first course to start teaching</p>
                <a href="<?= url('instructor/courses/create.php') ?>" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i> Create Your First Course
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <!-- Thumbnail -->
                        <div class="relative h-48">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?= uploadUrl($course['thumbnail']) ?>" 
                                     alt="<?= htmlspecialchars($course['title']) ?>"
                                     class="w-full h-full object-cover rounded-t-lg">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-primary-400 to-primary-600 rounded-t-lg flex items-center justify-center">
                                    <i class="fas fa-book text-white text-5xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <?php if ($course['status'] == 'published'): ?>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        Published
                                    </span>
                                <?php elseif ($course['status'] == 'draft'): ?>
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        Draft
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        Archived
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
                                <?= htmlspecialchars($course['title']) ?>
                            </h3>
                            
                            <div class="flex items-center text-sm text-gray-600 space-x-4 mb-4">
                                <span><i class="fas fa-users mr-1"></i> <?= $course['total_students'] ?? 0 ?></span>
                                <span><i class="fas fa-book-open mr-1"></i> <?= $course['total_lessons'] ?? 0 ?> lessons</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center justify-between space-x-2">
                                <a href="<?= url('instructor/courses/edit.php?id=' . $course['id']) ?>" 
                                   class="flex-1 btn btn-primary btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <a href="<?= url('instructor/courses/modules.php?id=' . $course['id']) ?>" 
                                   class="flex-1 btn btn-secondary btn-sm">
                                    <i class="fas fa-list mr-1"></i> Content
                                </a>
                                <a href="<?= url('course.php?slug=' . $course['slug']) ?>" 
                                   target="_blank"
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>