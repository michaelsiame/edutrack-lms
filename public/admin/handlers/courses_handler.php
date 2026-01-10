<?php
/**
 * Courses Handler - Processes form submissions before HTML output
 */

$action = $_POST['action'] ?? '';

// Update course status
if ($action === 'update_status' && isset($_POST['course_id'], $_POST['status'])) {
    $courseId = (int)$_POST['course_id'];
    $status = in_array($_POST['status'], ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
    $db->update('courses', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$courseId]);
    header('Location: ?page=courses&msg=status_updated');
    exit;
}

// Toggle featured
if ($action === 'toggle_featured' && isset($_POST['course_id'])) {
    $courseId = (int)$_POST['course_id'];
    $course = $db->fetchOne("SELECT is_featured FROM courses WHERE id = ?", [$courseId]);
    $newFeatured = ($course['is_featured'] ?? 0) ? 0 : 1;
    $db->update('courses', ['is_featured' => $newFeatured], 'id = ?', [$courseId]);
    header('Location: ?page=courses&msg=featured_updated');
    exit;
}

// Delete course
if ($action === 'delete' && isset($_POST['course_id'])) {
    $courseId = (int)$_POST['course_id'];
    // Check for enrollments
    $enrollmentCount = $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE course_id = ?", [$courseId]);
    if ($enrollmentCount > 0) {
        header('Location: ?page=courses&msg=cannot_delete');
        exit;
    }
    // Delete related data
    $db->delete('modules', 'course_id = ?', [$courseId]);
    $db->delete('course_instructors', 'course_id = ?', [$courseId]);
    $db->delete('courses', 'id = ?', [$courseId]);
    header('Location: ?page=courses&msg=deleted');
    exit;
}

// Add new course
if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $instructorId = (int)($_POST['instructor_id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $level = in_array($_POST['level'] ?? '', ['Beginner', 'Intermediate', 'Advanced']) ? $_POST['level'] : 'Beginner';
    $durationWeeks = (int)($_POST['duration_weeks'] ?? 0);
    $totalHours = floatval($_POST['total_hours'] ?? 0);
    $maxStudents = (int)($_POST['max_students'] ?? 30);
    $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if ($title) {
        $db->insert('courses', [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'short_description' => $shortDescription,
            'price' => $price,
            'discount_price' => $discountPrice,
            'instructor_id' => $instructorId ?: null,
            'category_id' => $categoryId ?: null,
            'level' => $level,
            'duration_weeks' => $durationWeeks ?: null,
            'total_hours' => $totalHours ?: null,
            'max_students' => $maxStudents,
            'thumbnail_url' => $thumbnailUrl ?: null,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $newCourseId = $db->lastInsertId();
        if ($newCourseId && $instructorId) {
            $db->insert('course_instructors', [
                'course_id' => $newCourseId,
                'instructor_id' => $instructorId,
                'role' => 'Lead',
                'assigned_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        header('Location: ?page=courses&msg=added');
        exit;
    }
}

// Edit course
if ($action === 'edit' && isset($_POST['course_id'])) {
    $courseId = (int)$_POST['course_id'];
    $title = trim($_POST['title'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $instructorId = (int)($_POST['instructor_id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $level = in_array($_POST['level'] ?? '', ['Beginner', 'Intermediate', 'Advanced']) ? $_POST['level'] : 'Beginner';
    $durationWeeks = (int)($_POST['duration_weeks'] ?? 0);
    $totalHours = floatval($_POST['total_hours'] ?? 0);
    $maxStudents = (int)($_POST['max_students'] ?? 30);
    $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if ($title) {
        $db->update('courses', [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'short_description' => $shortDescription,
            'price' => $price,
            'discount_price' => $discountPrice,
            'instructor_id' => $instructorId ?: null,
            'category_id' => $categoryId ?: null,
            'level' => $level,
            'duration_weeks' => $durationWeeks ?: null,
            'total_hours' => $totalHours ?: null,
            'max_students' => $maxStudents,
            'thumbnail_url' => $thumbnailUrl ?: null,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$courseId]);

        header('Location: ?page=courses&msg=updated');
        exit;
    }
}
