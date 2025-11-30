# EduTrack LMS - Comprehensive Implementation & Refactoring Guide
## Native PHP Course Management System - Security & Modernization Blueprint

---

## Table of Contents
1. [Essential Architectural Patterns](#essential-architectural-patterns)
2. [Admin Role Features](#admin-role-features)
3. [Instructor Role Features](#instructor-role-features)
4. [Student Role Features](#student-role-features)
5. [Finance Role Features](#finance-role-features)
6. [Security Best Practices](#security-best-practices)
7. [Testing & Validation](#testing--validation)

---

## Essential Architectural Patterns

### 1. Single Entry Point (Front Controller Pattern)

**Current State:** EduTrack uses a **hybrid approach** - direct file access with middleware protection on each page.

**Legacy Approach (Not Recommended):**
```php
// Direct access to pages without any centralized routing
// public/admin/users.php - directly accessible
// public/instructor/courses.php - directly accessible
// Risk: Easy to forget middleware, inconsistent URL structure
```

**Modern Approach - Front Controller:**
```php
// public/index.php - Single Entry Point
<?php
require_once '../src/bootstrap.php';

// Simple routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Route definitions
$routes = [
    '' => 'pages/home.php',
    'dashboard' => 'pages/dashboard.php',
    'admin/users' => 'pages/admin/users.php',
    'instructor/courses' => 'pages/instructor/courses.php',
];

// Find matching route
if (array_key_exists($uri, $routes)) {
    require_once $routes[$uri];
} else {
    // 404 page
    http_response_code(404);
    require_once 'pages/404.php';
}
```

**Why This is Better:**
- Centralized request handling
- Consistent URL structure
- Easier to apply global middleware
- Better security (all requests go through one entry point)

---

### 2. Database Connection Management

**Current State:** EduTrack uses **Singleton pattern with PDO** (✓ Excellent approach)

**Legacy/Insecure Example:**
```php
// ❌ DANGEROUS: Direct mysqli connection, no error handling
<?php
$host = 'localhost';
$user = 'root';
$pass = 'password123'; // Hardcoded credentials!
$db = 'edutrack';

// Old mysql_* functions (deprecated, removed in PHP 7)
$conn = mysql_connect($host, $user, $pass);
mysql_select_db($db);

// Vulnerable to SQL injection
$userId = $_GET['id'];
$query = "SELECT * FROM users WHERE id = $userId";
$result = mysql_query($query); // No prepared statements!

// Or with mysqli but still vulnerable
$conn = new mysqli($host, $user, $pass, $db);
$userId = $_GET['id'];
$query = "SELECT * FROM users WHERE id = " . $userId; // Direct concatenation!
$result = $conn->query($query);
```

**Modern/Secure Implementation (EduTrack's Current Approach):**
```php
// ✓ SECURE: Singleton pattern with PDO
// src/includes/database.php
<?php
class Database {
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct() {
        $this->loadConfig(); // Load from config file, not hardcoded
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $connection = $this->config['connections'][$this->config['default']];

            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $connection['driver'],
                $connection['host'],
                $connection['port'],
                $connection['database'],
                $connection['charset'] // UTF-8 support
            );

            $this->pdo = new PDO(
                $dsn,
                $connection['username'],
                $connection['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            $this->logError('Connection failed: ' . $e->getMessage());
            die("Database connection error");
        }
    }

    // Prepared statement wrapper
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Query Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
}

// Usage in application
$db = Database::getInstance();
$userId = $_GET['id'] ?? 0;

// Safe parameterized query
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
```

**Why This is Better:**
- ✓ No hardcoded credentials (loaded from config)
- ✓ PDO with prepared statements (SQL injection prevention)
- ✓ Singleton pattern (single connection instance)
- ✓ Proper error handling and logging
- ✓ UTF-8 charset for international characters
- ✓ Connection reuse (performance)

---

### 3. Separation of Concerns

**Legacy Approach (Mixed Logic):**
```php
// ❌ BAD: Business logic, HTML, and database queries all mixed
<?php
session_start();
require 'db.php';

// No authentication check!
$userId = $_SESSION['user_id'];

// SQL injection vulnerable
$sql = "SELECT * FROM courses WHERE instructor_id = $userId";
$result = mysql_query($sql);

// HTML mixed with PHP logic
?>
<!DOCTYPE html>
<html>
<head><title>My Courses</title></head>
<body>
    <h1>My Courses</h1>
    <?php while($course = mysql_fetch_assoc($result)): ?>
        <!-- Vulnerable to XSS -->
        <div><?= $course['title'] ?></div>
        <div><?= $course['description'] ?></div>
    <?php endwhile; ?>
</body>
</html>
```

**Modern Approach (Separation of Concerns):**

**Step 1: Middleware (Authentication/Authorization)**
```php
// src/middleware/instructor-only.php
<?php
require_once __DIR__ . '/../bootstrap.php';

// Validate session
if (!validateSession()) {
    redirect(url('login.php'));
    exit;
}

// Check instructor role
if (!hasRole(['instructor', 'admin'])) {
    accessDenied('You must be an instructor to access this page');
    exit;
}
```

**Step 2: Data Layer (Class/Model)**
```php
// src/classes/Course.php
<?php
class Course {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get courses by instructor (with prepared statement)
     */
    public static function getByInstructor($instructorId) {
        $db = Database::getInstance();
        $sql = "SELECT c.*,
                COUNT(e.enrollment_id) as enrollment_count
                FROM courses c
                LEFT JOIN enrollments e ON e.course_id = c.id
                WHERE c.instructor_id = ?
                GROUP BY c.id
                ORDER BY c.created_at DESC";

        return $db->fetchAll($sql, [$instructorId]);
    }
}
```

**Step 3: Controller/Page Logic**
```php
// public/instructor/courses.php
<?php
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';

// Business logic
$instructorId = currentUserId();
$courses = Course::getByInstructor($instructorId);

// Set page variables
$page_title = 'My Courses';
$active_page = 'courses';

// Load template
require_once '../../src/templates/instructor-header.php';
?>
<!-- Clean HTML template below -->
```

**Step 4: Presentation Layer (Template)**
```php
<!-- public/instructor/courses.php (continued) -->
<div class="container">
    <h1><?= xssClean($page_title) ?></h1>

    <?php foreach ($courses as $course): ?>
        <div class="course-card">
            <!-- XSS protection with htmlspecialchars wrapper -->
            <h3><?= sanitize($course['title']) ?></h3>
            <p><?= sanitize($course['description']) ?></p>
            <span><?= (int)$course['enrollment_count'] ?> students</span>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
```

**Why This is Better:**
- ✓ Clear separation: Middleware → Model → Controller → View
- ✓ Security checks happen first (authentication/authorization)
- ✓ Database logic isolated in classes (reusable)
- ✓ Clean templates (easy to maintain)
- ✓ XSS protection on all output
- ✓ SQL injection prevention with prepared statements

---

## Admin Role Features

### Feature 1: Creating a New Course

**Problem: Legacy/Insecure Code Example**

```php
// ❌ DANGEROUS: SQL Injection, No CSRF Protection, No Validation
<?php
session_start();

// No authentication check!
// No authorization check!

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // No CSRF token validation

    // Direct POST access without sanitization
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $instructor_id = $_POST['instructor_id'];

    // SQL INJECTION VULNERABILITY - string concatenation!
    $query = "INSERT INTO courses (title, description, price, instructor_id, status)
              VALUES ('$title', '$description', $price, $instructor_id, 'draft')";

    mysql_query($query); // Old deprecated function

    echo "Course created!"; // No redirect, no proper feedback
}
?>

<form method="POST">
    <!-- No CSRF token! -->
    <input type="text" name="title">
    <textarea name="description"></textarea>
    <input type="number" name="price">
    <select name="instructor_id">
        <?php
        // Another SQL injection vulnerability
        $instructors = mysql_query("SELECT * FROM instructors");
        while($row = mysql_fetch_assoc($instructors)) {
            // XSS vulnerability - no output escaping
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>
    <button type="submit">Create</button>
</form>
```

**Security Issues:**
1. ❌ No authentication/authorization check
2. ❌ No CSRF token validation
3. ❌ SQL injection via string concatenation
4. ❌ No input validation or sanitization
5. ❌ XSS vulnerability in output
6. ❌ Uses deprecated mysql_* functions
7. ❌ No error handling
8. ❌ No activity logging

---

**Solution: Secure & Modern Native PHP Code**

```php
// ✓ SECURE: Prepared Statements, CSRF Protection, Validation
<?php
// public/admin/courses/create.php

// 1. AUTHENTICATION & AUTHORIZATION (Middleware)
require_once '../../../src/middleware/admin-only.php';

// 2. LOAD DEPENDENCIES
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Instructor.php';

$db = Database::getInstance();
$errors = [];
$success = false;

// 3. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 3a. CSRF Protection
    if (!verifyCsrfToken()) {
        http_response_code(403);
        die('CSRF token validation failed');
    }

    // 3b. INPUT VALIDATION
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $instructorId = intval($_POST['instructor_id'] ?? 0);
    $categoryId = intval($_POST['category_id'] ?? 0);

    if (empty($title)) {
        $errors[] = 'Course title is required';
    } elseif (strlen($title) < 5) {
        $errors[] = 'Title must be at least 5 characters';
    }

    if (empty($description)) {
        $errors[] = 'Description is required';
    }

    if ($price < 0) {
        $errors[] = 'Price must be a positive number';
    }

    if ($instructorId <= 0) {
        $errors[] = 'Please select an instructor';
    }

    // Check if instructor exists
    $instructor = $db->fetchOne(
        "SELECT id FROM instructors WHERE id = ?",
        [$instructorId]
    );

    if (!$instructor) {
        $errors[] = 'Invalid instructor selected';
    }

    // 3c. PROCESS IF NO ERRORS
    if (empty($errors)) {
        try {
            // Begin transaction for data integrity
            $db->beginTransaction();

            // Generate unique slug
            $slug = generateSlug($title);
            $originalSlug = $slug;
            $counter = 1;

            while ($db->exists('courses', 'slug = ?', [$slug])) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // 3d. PREPARED STATEMENT INSERT (SQL Injection Prevention)
            $courseId = $db->insert('courses', [
                'title' => $title,
                'slug' => $slug,
                'description' => $description,
                'price' => $price,
                'category_id' => $categoryId,
                'instructor_id' => $instructorId,
                'status' => 'draft',
                'created_by' => currentUserId()
            ]);

            // Associate instructor with course
            $db->insert('course_instructors', [
                'course_id' => $courseId,
                'instructor_id' => $instructorId,
                'role' => 'primary'
            ]);

            // Commit transaction
            $db->commit();

            // 3e. ACTIVITY LOGGING (Audit Trail)
            logActivity(
                "Created new course: $title (ID: $courseId)",
                'course_create',
                'course',
                $courseId
            );

            // 3f. SUCCESS FEEDBACK
            flash('message', 'Course created successfully!', 'success');
            redirect(url("admin/courses/edit.php?id=$courseId"));
            exit;

        } catch (Exception $e) {
            // Rollback on error
            $db->rollback();

            // Log error (don't expose to user)
            logActivity("Course creation error: " . $e->getMessage(), 'error');

            $errors[] = 'An error occurred. Please try again.';
        }
    }

    // Show errors
    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

// 4. LOAD DATA FOR FORM
$instructors = $db->fetchAll(
    "SELECT i.id, u.first_name, u.last_name, i.specialization
     FROM instructors i
     JOIN users u ON i.user_id = u.id
     WHERE u.status = 'active'
     ORDER BY u.first_name, u.last_name"
);

$categories = $db->fetchAll(
    "SELECT id, category_name FROM course_categories ORDER BY category_name"
);

// 5. LOAD TEMPLATE
$page_title = 'Create New Course';
require_once '../../../src/templates/admin-header.php';
?>

<!-- 6. PRESENTATION LAYER (Clean HTML) -->
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Create New Course</h1>

        <form method="POST" class="bg-white rounded-lg shadow p-6">
            <!-- 6a. CSRF TOKEN (Required for security) -->
            <?= csrfField() ?>

            <!-- Course Title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium mb-2">
                    Course Title <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= sanitize($_POST['title'] ?? '') ?>"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                    maxlength="255"
                >
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium mb-2">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                ><?= sanitize($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Category -->
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select
                    id="category_id"
                    name="category_id"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                >
                    <option value="">Select a category...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>">
                            <?= sanitize($category['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Instructor (XSS Protection) -->
            <div class="mb-4">
                <label for="instructor_id" class="block text-sm font-medium mb-2">
                    Primary Instructor <span class="text-red-500">*</span>
                </label>
                <select
                    id="instructor_id"
                    name="instructor_id"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                >
                    <option value="">Select an instructor...</option>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?= (int)$instructor['id'] ?>">
                            <?= sanitize($instructor['first_name'] . ' ' . $instructor['last_name']) ?>
                            <?php if ($instructor['specialization']): ?>
                                - <?= sanitize($instructor['specialization']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Price -->
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium mb-2">
                    Price (ZMW) <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    step="0.01"
                    min="0"
                    value="<?= sanitize($_POST['price'] ?? '0') ?>"
                    class="w-full px-4 py-2 border rounded-lg"
                    required
                >
            </div>

            <!-- Submit -->
            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Create Course
                </button>
                <a href="<?= url('admin/courses/index.php') ?>" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
```

**Why This is Better:**
1. ✓ **Authentication**: `admin-only.php` middleware ensures only admins access this page
2. ✓ **Authorization**: Role checked before any code executes
3. ✓ **CSRF Protection**: Token generated and validated
4. ✓ **Input Validation**: All inputs validated before processing
5. ✓ **SQL Injection Prevention**: PDO prepared statements with parameter binding
6. ✓ **XSS Prevention**: All output sanitized with `sanitize()` function
7. ✓ **Transaction Support**: Database changes are atomic (all-or-nothing)
8. ✓ **Activity Logging**: Audit trail for compliance
9. ✓ **Error Handling**: Try-catch with rollback on failure
10. ✓ **Proper Redirects**: PRG pattern (Post-Redirect-Get) prevents double submission

---

### Feature 2: Managing User Accounts (Admin)

**Problem: Legacy/Insecure Code Example**

```php
// ❌ DANGEROUS: Multiple vulnerabilities
<?php
// No authentication!
$userId = $_GET['id']; // No validation

// Delete user - SQL injection vulnerability
$query = "DELETE FROM users WHERE id = $userId";
mysql_query($query);

// Update user role - SQL injection
$newRole = $_POST['role']; // No validation
$query = "UPDATE users SET role = '$newRole' WHERE id = $userId";
mysql_query($query);

echo "User updated!";
?>
```

**Security Issues:**
1. ❌ No authentication check
2. ❌ SQL injection (GET parameter concatenated directly)
3. ❌ No CSRF protection
4. ❌ No input validation
5. ❌ Destructive action (delete) without confirmation
6. ❌ No activity logging

---

**Solution: Secure & Modern Native PHP Code**

```php
// ✓ SECURE: Role Management with Validation
<?php
// public/admin/users/update-role.php

require_once '../../../src/middleware/admin-only.php';

$db = Database::getInstance();

// GET: Display form
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = intval($_GET['id'] ?? 0);

    if ($userId <= 0) {
        flash('message', 'Invalid user ID', 'error');
        redirect(url('admin/users/index.php'));
        exit;
    }

    // Fetch user with current role (prepared statement)
    $user = $db->fetchOne("
        SELECT u.id, u.email, u.first_name, u.last_name, u.status,
               r.role_name, r.id as role_id
        FROM users u
        LEFT JOIN user_roles ur ON ur.user_id = u.id
        LEFT JOIN roles r ON r.id = ur.role_id
        WHERE u.id = ?
    ", [$userId]);

    if (!$user) {
        flash('message', 'User not found', 'error');
        redirect(url('admin/users/index.php'));
        exit;
    }

    // Prevent modifying own account
    if ($user['id'] == currentUserId()) {
        flash('message', 'You cannot modify your own role', 'error');
        redirect(url('admin/users/index.php'));
        exit;
    }

    // Get all available roles
    $roles = $db->fetchAll("SELECT id, role_name, description FROM roles ORDER BY role_name");
}

// POST: Process role change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    validateCSRF();

    $userId = intval($_POST['user_id'] ?? 0);
    $newRoleId = intval($_POST['role_id'] ?? 0);

    // Validation
    $errors = [];

    if ($userId <= 0) {
        $errors[] = 'Invalid user ID';
    }

    if ($newRoleId <= 0) {
        $errors[] = 'Please select a role';
    }

    // Check if user exists
    $user = $db->fetchOne("SELECT id, first_name, last_name FROM users WHERE id = ?", [$userId]);

    if (!$user) {
        $errors[] = 'User not found';
    }

    // Check if role exists
    $role = $db->fetchOne("SELECT id, role_name FROM roles WHERE id = ?", [$newRoleId]);

    if (!$role) {
        $errors[] = 'Invalid role selected';
    }

    // Prevent self-modification
    if ($userId == currentUserId()) {
        $errors[] = 'You cannot change your own role';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Remove existing role(s)
            $db->delete('user_roles', 'user_id = ?', [$userId]);

            // Assign new role (prepared statement)
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $newRoleId
            ]);

            $db->commit();

            // Log activity
            logActivity(
                "Changed role for user {$user['first_name']} {$user['last_name']} to {$role['role_name']}",
                'user_role_change',
                'user',
                $userId
            );

            flash('message', 'User role updated successfully', 'success');
            redirect(url('admin/users/index.php'));
            exit;

        } catch (Exception $e) {
            $db->rollback();
            logActivity("Role update error: " . $e->getMessage(), 'error');
            $errors[] = 'An error occurred while updating the role';
        }
    }

    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

$page_title = 'Update User Role';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Update User Role</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <!-- User Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded">
                <h3 class="font-semibold">User Information</h3>
                <p class="text-gray-700">
                    <strong>Name:</strong> <?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?><br>
                    <strong>Email:</strong> <?= sanitize($user['email']) ?><br>
                    <strong>Current Role:</strong>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                        <?= sanitize($user['role_name'] ?? 'No role assigned') ?>
                    </span>
                </p>
            </div>

            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">

                <div class="mb-4">
                    <label for="role_id" class="block text-sm font-medium mb-2">
                        Select New Role <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="role_id"
                        name="role_id"
                        class="w-full px-4 py-2 border rounded-lg"
                        required
                    >
                        <option value="">Select a role...</option>
                        <?php foreach ($roles as $roleOption): ?>
                            <option
                                value="<?= (int)$roleOption['id'] ?>"
                                <?= ($roleOption['id'] == $user['role_id']) ? 'selected' : '' ?>
                            >
                                <?= sanitize($roleOption['role_name']) ?>
                                <?php if ($roleOption['description']): ?>
                                    - <?= sanitize($roleOption['description']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary">
                        Update Role
                    </button>
                    <a href="<?= url('admin/users/index.php') ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
```

**Why This is Better:**
1. ✓ **Proper authorization**: Only admins can change roles
2. ✓ **Input validation**: All inputs validated and sanitized
3. ✓ **SQL injection prevention**: Prepared statements only
4. ✓ **CSRF protection**: Token required for POST requests
5. ✓ **Self-protection**: Prevents admins from changing their own role
6. ✓ **Transactions**: Atomic operations (delete old role + insert new role)
7. ✓ **Activity logging**: Audit trail of all role changes
8. ✓ **User feedback**: Clear success/error messages

---

## Instructor Role Features

### Feature 3: Creating and Grading Assignments

**Problem: Legacy/Insecure Code Example**

```php
// ❌ DANGEROUS: Assignment Grading Vulnerability
<?php
// No authentication!
$submissionId = $_GET['id'];
$points = $_POST['points'];

// SQL injection - direct concatenation
$query = "UPDATE assignment_submissions
          SET points_earned = $points, status = 'graded'
          WHERE submission_id = $submissionId";
mysql_query($query);

// No authorization check - any instructor can grade any assignment!
echo "Graded!";
?>
```

**Security Issues:**
1. ❌ No authentication
2. ❌ No ownership verification (any instructor can grade any submission)
3. ❌ SQL injection vulnerability
4. ❌ No validation (points could exceed max points)
5. ❌ No student notification

---

**Solution: Secure & Modern Native PHP Code**

```php
// ✓ SECURE: Assignment Grading with Authorization
<?php
// public/instructor/grade-assignment.php

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Assignment.php';
require_once '../../src/classes/AssignmentSubmission.php';

$db = Database::getInstance();
$submissionId = intval($_GET['id'] ?? 0);

if ($submissionId <= 0) {
    flash('message', 'Invalid submission ID', 'error');
    redirect(url('instructor/assignments.php'));
    exit;
}

// Fetch submission with authorization check
$submission = $db->fetchOne("
    SELECT asub.*, a.title as assignment_title, a.max_points, a.course_id,
           u.first_name, u.last_name, u.email,
           c.instructor_id
    FROM assignment_submissions asub
    JOIN assignments a ON asub.assignment_id = a.assignment_id
    JOIN courses c ON a.course_id = c.id
    JOIN students s ON asub.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE asub.submission_id = ?
", [$submissionId]);

if (!$submission) {
    flash('message', 'Submission not found', 'error');
    redirect(url('instructor/assignments.php'));
    exit;
}

// CRITICAL: Verify instructor owns this course
if ($submission['instructor_id'] != currentUserId()) {
    // Check if instructor is associated with course
    $isAssociated = $db->exists(
        'course_instructors',
        'course_id = ? AND instructor_id = (SELECT id FROM instructors WHERE user_id = ?)',
        [$submission['course_id'], currentUserId()]
    );

    if (!$isAssociated) {
        flash('message', 'You do not have permission to grade this assignment', 'error');
        redirect(url('instructor/assignments.php'));
        exit;
    }
}

// Handle grading
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $pointsEarned = floatval($_POST['points_earned'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');

    $errors = [];

    // Validation
    if ($pointsEarned < 0) {
        $errors[] = 'Points cannot be negative';
    }

    if ($pointsEarned > $submission['max_points']) {
        $errors[] = "Points cannot exceed maximum ({$submission['max_points']})";
    }

    if (empty($feedback)) {
        $errors[] = 'Please provide feedback to the student';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Update submission (prepared statement)
            $db->update('assignment_submissions', [
                'points_earned' => $pointsEarned,
                'feedback' => $feedback,
                'status' => 'graded',
                'graded_by' => currentUserId(),
                'graded_at' => date('Y-m-d H:i:s')
            ], 'submission_id = ?', [$submissionId]);

            // Create notification for student
            $db->insert('notifications', [
                'user_id' => $submission['student_id'],
                'title' => 'Assignment Graded',
                'message' => "Your submission for '{$submission['assignment_title']}' has been graded. You earned {$pointsEarned}/{$submission['max_points']} points.",
                'notification_type' => 'assignment_graded',
                'related_id' => $submissionId
            ]);

            $db->commit();

            // Send email notification
            sendAssignmentGradedEmail(
                $submission['email'],
                $submission['first_name'],
                $submission['assignment_title'],
                $pointsEarned,
                $submission['max_points'],
                $feedback
            );

            // Log activity
            logActivity(
                "Graded assignment submission ID {$submissionId}: {$pointsEarned}/{$submission['max_points']} points",
                'assignment_graded',
                'assignment_submission',
                $submissionId
            );

            flash('message', 'Assignment graded successfully!', 'success');
            redirect(url('instructor/assignments.php'));
            exit;

        } catch (Exception $e) {
            $db->rollback();
            logActivity("Grading error: " . $e->getMessage(), 'error');
            $errors[] = 'An error occurred while grading';
        }
    }

    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

$page_title = 'Grade Assignment Submission';
require_once '../../src/templates/instructor-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Grade Submission</h1>

        <!-- Submission Details -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Submission Details</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Student</p>
                    <p class="font-medium">
                        <?= sanitize($submission['first_name'] . ' ' . $submission['last_name']) ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Assignment</p>
                    <p class="font-medium"><?= sanitize($submission['assignment_title']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Submitted At</p>
                    <p class="font-medium"><?= formatDate($submission['submitted_at']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Maximum Points</p>
                    <p class="font-medium"><?= (int)$submission['max_points'] ?></p>
                </div>
            </div>

            <?php if ($submission['file_url']): ?>
            <div class="mt-4">
                <a href="<?= url('uploads/assignments/submissions/' . basename($submission['file_url'])) ?>"
                   target="_blank"
                   class="btn btn-secondary">
                    <i class="fas fa-download mr-2"></i>Download Submission
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Grading Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Grade Submission</h2>

            <form method="POST">
                <?= csrfField() ?>

                <div class="mb-4">
                    <label for="points_earned" class="block text-sm font-medium mb-2">
                        Points Earned <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            id="points_earned"
                            name="points_earned"
                            step="0.5"
                            min="0"
                            max="<?= (float)$submission['max_points'] ?>"
                            value="<?= (float)($submission['points_earned'] ?? 0) ?>"
                            class="px-4 py-2 border rounded-lg w-32"
                            required
                        >
                        <span class="text-gray-600">
                            / <?= (int)$submission['max_points'] ?> points
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="feedback" class="block text-sm font-medium mb-2">
                        Feedback <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="feedback"
                        name="feedback"
                        rows="8"
                        class="w-full px-4 py-2 border rounded-lg"
                        placeholder="Provide detailed feedback to help the student improve..."
                        required
                    ><?= sanitize($submission['feedback'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-2"></i>Submit Grade
                    </button>
                    <a href="<?= url('instructor/assignments.php') ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
```

**Why This is Better:**
1. ✓ **Authentication**: Only logged-in instructors can access
2. ✓ **Authorization**: Verifies instructor owns/teaches the course
3. ✓ **Input validation**: Points cannot exceed max, must have feedback
4. ✓ **SQL injection prevention**: Prepared statements throughout
5. ✓ **Student notification**: Email + in-app notification sent
6. ✓ **Activity logging**: Audit trail of all grading actions
7. ✓ **Transaction safety**: Grade + notification saved atomically

---

## Student Role Features

### Feature 4: Enrolling in a Course with Payment

**Problem: Legacy/Insecure Code Example**

```php
// ❌ DANGEROUS: Payment Processing Vulnerability
<?php
// No authentication
$courseId = $_GET['course_id'];
$userId = $_SESSION['user_id']; // If session even exists!

// SQL injection
$query = "INSERT INTO enrollments (user_id, course_id, status)
          VALUES ($userId, $courseId, 'enrolled')";
mysql_query($query);

// No payment verification!
// Student enrolled without paying!

echo "Enrolled successfully!";
?>
```

**Security Issues:**
1. ❌ No payment verification
2. ❌ SQL injection
3. ❌ No duplicate enrollment check
4. ❌ No course availability check
5. ❌ No transaction (enrollment could succeed but payment fail)

---

**Solution: Secure & Modern Native PHP Code**

```php
// ✓ SECURE: Course Enrollment with Payment Verification
<?php
// public/enroll.php

require_once '../src/middleware/authenticate.php'; // Must be logged in
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';
require_once '../src/classes/Payment.php';

$db = Database::getInstance();
$courseId = intval($_GET['course_id'] ?? 0);

if ($courseId <= 0) {
    flash('message', 'Invalid course ID', 'error');
    redirect(url('courses.php'));
    exit;
}

// Fetch course details (prepared statement)
$course = Course::find($courseId);

if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('courses.php'));
    exit;
}

// Check if course is published
if ($course->getStatus() !== 'published') {
    flash('message', 'This course is not available for enrollment', 'error');
    redirect(url('courses.php'));
    exit;
}

// Check if already enrolled
if (Enrollment::isEnrolled(currentUserId(), $courseId)) {
    flash('message', 'You are already enrolled in this course', 'info');
    redirect(url('courses/view.php?id=' . $courseId));
    exit;
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $paymentMethod = $_POST['payment_method'] ?? '';
    $agreeTerms = isset($_POST['agree_terms']);

    $errors = [];

    // Validation
    if (!$agreeTerms) {
        $errors[] = 'You must agree to the terms and conditions';
    }

    if (empty($paymentMethod)) {
        $errors[] = 'Please select a payment method';
    }

    // Validate payment method exists
    $paymentMethodData = $db->fetchOne(
        "SELECT id, type, provider FROM payment_methods WHERE id = ? AND is_active = 1",
        [$paymentMethod]
    );

    if (!$paymentMethodData) {
        $errors[] = 'Invalid payment method selected';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Create payment record first (prevents free enrollment)
            $paymentId = Payment::create([
                'user_id' => currentUserId(),
                'course_id' => $courseId,
                'amount' => $course->getDiscountPrice() > 0 ? $course->getDiscountPrice() : $course->getPrice(),
                'currency' => 'ZMW',
                'payment_method_id' => $paymentMethod,
                'payment_status' => 'Pending'
            ]);

            if (!$paymentId) {
                throw new Exception('Failed to create payment record');
            }

            // Process payment based on method
            $payment = Payment::find($paymentId);

            if ($paymentMethodData['type'] === 'mobile_money') {
                // Redirect to mobile money payment page
                $db->commit();

                flash('message', 'Please complete payment on your mobile device', 'info');
                redirect(url("payments/mobile-money.php?payment_id=$paymentId"));
                exit;

            } elseif ($paymentMethodData['type'] === 'bank_transfer') {
                // Show bank details for manual transfer
                $db->commit();

                flash('message', 'Please complete bank transfer and upload proof', 'info');
                redirect(url("payments/bank-transfer.php?payment_id=$paymentId"));
                exit;

            } elseif ($paymentMethodData['type'] === 'gateway') {
                // Redirect to payment gateway (PayPal, Stripe, etc.)
                // Payment gateway will call webhook to mark payment as complete

                $db->commit();

                $gatewayUrl = processPaymentGateway($payment, $paymentMethodData);
                redirect($gatewayUrl);
                exit;
            }

            // If cash payment (handled by finance team)
            if ($paymentMethodData['type'] === 'cash') {
                $db->commit();

                flash('message', 'Please visit our office to complete cash payment', 'info');
                redirect(url('dashboard.php'));
                exit;
            }

        } catch (Exception $e) {
            $db->rollback();
            logActivity("Enrollment error: " . $e->getMessage(), 'error');
            flash('message', 'An error occurred. Please try again.', 'error');
        }
    }

    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

// Get available payment methods
$paymentMethods = $db->fetchAll(
    "SELECT id, method_name, type, provider, description
     FROM payment_methods
     WHERE is_active = 1
     ORDER BY display_order"
);

$page_title = 'Enroll in ' . $course->getTitle();
require_once '../src/templates/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Enroll in Course</h1>

        <!-- Course Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-2">
                <?= sanitize($course->getTitle()) ?>
            </h2>
            <p class="text-gray-600 mb-4">
                <?= sanitize($course->getDescription()) ?>
            </p>

            <div class="flex items-center gap-4">
                <div>
                    <span class="text-sm text-gray-600">Price:</span>
                    <?php if ($course->getDiscountPrice() > 0): ?>
                        <span class="text-2xl font-bold text-green-600">
                            K<?= number_format($course->getDiscountPrice(), 2) ?>
                        </span>
                        <span class="text-lg text-gray-400 line-through ml-2">
                            K<?= number_format($course->getPrice(), 2) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-2xl font-bold">
                            K<?= number_format($course->getPrice(), 2) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Enrollment Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4">Select Payment Method</h3>

            <form method="POST">
                <?= csrfField() ?>

                <div class="space-y-3 mb-6">
                    <?php foreach ($paymentMethods as $method): ?>
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input
                                type="radio"
                                name="payment_method"
                                value="<?= (int)$method['id'] ?>"
                                class="mr-3"
                                required
                            >
                            <div class="flex-1">
                                <p class="font-medium">
                                    <?= sanitize($method['method_name']) ?>
                                </p>
                                <?php if ($method['description']): ?>
                                    <p class="text-sm text-gray-600">
                                        <?= sanitize($method['description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if ($method['type'] === 'mobile_money'): ?>
                                <i class="fas fa-mobile-alt text-2xl text-blue-600"></i>
                            <?php elseif ($method['type'] === 'bank_transfer'): ?>
                                <i class="fas fa-university text-2xl text-green-600"></i>
                            <?php elseif ($method['type'] === 'gateway'): ?>
                                <i class="fas fa-credit-card text-2xl text-purple-600"></i>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input
                            type="checkbox"
                            name="agree_terms"
                            class="mt-1 mr-3"
                            required
                        >
                        <span class="text-sm text-gray-700">
                            I agree to the
                            <a href="<?= url('terms.php') ?>" target="_blank" class="text-blue-600 underline">
                                Terms and Conditions
                            </a>
                            and understand that payment is non-refundable after course access.
                        </span>
                    </label>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary flex-1">
                        <i class="fas fa-lock mr-2"></i>Proceed to Payment
                    </button>
                    <a href="<?= url('courses/view.php?id=' . $courseId) ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
```

**Why This is Better:**
1. ✓ **Payment required**: Cannot enroll without initiating payment
2. ✓ **Duplicate prevention**: Checks if already enrolled
3. ✓ **Course validation**: Verifies course exists and is published
4. ✓ **Transaction safety**: Payment record created before enrollment
5. ✓ **Multiple payment methods**: Supports mobile money, bank transfer, gateways
6. ✓ **Terms acceptance**: Legal protection
7. ✓ **SQL injection prevention**: All queries use prepared statements

---

## Finance Role Features

### Feature 5: Recording Manual Payments

**Problem: Legacy/Insecure Code Example**

```php
// ❌ DANGEROUS: Payment Recording Vulnerability
<?php
// Minimal authentication
if ($_SESSION['role'] != 'admin') die('Access denied');

// Record payment - SQL injection
$studentId = $_POST['student_id'];
$amount = $_POST['amount'];
$course = $_POST['course_id'];

$query = "INSERT INTO payments (student_id, course_id, amount, status)
          VALUES ($studentId, $course, $amount, 'completed')";
mysql_query($query);

// No receipt generated
// No payment plan update
// No notification to student
echo "Payment recorded";
?>
```

**Security Issues:**
1. ❌ Weak authorization (only checks if admin, not finance role)
2. ❌ SQL injection vulnerability
3. ❌ No validation (negative amounts possible)
4. ❌ No balance tracking
5. ❌ No audit trail
6. ❌ No student notification

---

**Solution: Secure & Modern Native PHP Code (Finance Dashboard)**

This is the **actual implementation from EduTrack** (`public/admin/finance/record-payment.php`):

```php
// ✓ SECURE: Cash Payment Recording with Full Validation
<?php
// public/admin/finance/record-payment.php

// 1. STRICT AUTHORIZATION - Finance role only
require_once '../../../src/middleware/finance-only.php';

require_once '../../../src/classes/Payment.php';
require_once '../../../src/classes/PaymentPlan.php';
require_once '../../../src/classes/Enrollment.php';
require_once '../../../src/classes/Course.php';

$db = Database::getInstance();

// Get cash payment method ID from config (avoid hard-coding)
$cashPaymentMethodId = config('payment.method_ids.cash', 5);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $paymentType = $_POST['payment_type'] ?? '';
    $userId = $_POST['user_id'] ?? null;
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $receiptNumber = trim($_POST['receipt_number'] ?? '');

    $errors = [];

    // VALIDATION
    if (!$userId) {
        $errors[] = 'Please select a student';
    }

    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than 0';
    }

    if ($paymentType === 'existing' && empty($_POST['plan_id'])) {
        $errors[] = 'Please select an enrollment to apply payment to';
    }

    if ($paymentType === 'new' && empty($_POST['course_id'])) {
        $errors[] = 'Please select a course for new enrollment';
    }

    if (empty($errors)) {
        // Get student_id from user_id (prepared statement)
        $student = $db->query(
            "SELECT id FROM students WHERE user_id = :user_id",
            ['user_id' => $userId]
        )->fetch();

        if (!$student) {
            $errors[] = 'Student record not found';
        } else {
            $studentId = $student['id'];

            if ($paymentType === 'existing') {
                // Apply payment to existing enrollment
                $planId = $_POST['plan_id'];
                $plan = PaymentPlan::find($planId);

                if (!$plan) {
                    $errors[] = 'Payment plan not found';
                } else {
                    try {
                        $db->beginTransaction();

                        // Create payment record (prepared statement)
                        $sql = "INSERT INTO payments (
                            student_id, course_id, enrollment_id, payment_plan_id,
                            amount, currency, payment_method_id, payment_type,
                            payment_status, transaction_id, recorded_by, notes, payment_date
                        ) VALUES (
                            :student_id, :course_id, :enrollment_id, :plan_id,
                            :amount, 'ZMW', :payment_method_id, 'partial_payment',
                            'Completed', :reference, :recorded_by, :notes, NOW()
                        )";

                        $params = [
                            'student_id' => $studentId,
                            'course_id' => $plan->getCourseId(),
                            'enrollment_id' => $plan->getEnrollmentId(),
                            'plan_id' => $planId,
                            'amount' => $amount,
                            'payment_method_id' => $cashPaymentMethodId,
                            'reference' => 'CASH-' . date('Ymd') . '-' . ($receiptNumber ?: uniqid()),
                            'recorded_by' => $_SESSION['user_id'], // Audit trail
                            'notes' => $notes
                        ];

                        $db->query($sql, $params);
                        $paymentId = $db->lastInsertId();

                        // Update payment plan balance
                        $plan->recordPayment($amount, $paymentId);

                        $db->commit();

                        // Log activity
                        logActivity(
                            "Recorded cash payment of K" . number_format($amount, 2) . " for student ID {$userId}",
                            'payment_recorded',
                            'payment',
                            $paymentId
                        );

                        flash('message', 'Cash payment recorded successfully.', 'success');
                        redirect(url('admin/finance/record-payment.php'));
                        exit;

                    } catch (Exception $e) {
                        $db->rollback();
                        logActivity("Payment recording error: " . $e->getMessage(), 'error');
                        flash('message', 'Failed to record payment', 'error');
                    }
                }
            } else {
                // New enrollment with payment
                $courseId = $_POST['course_id'];
                $course = Course::find($courseId);

                if (!$course) {
                    $errors[] = 'Course not found';
                } else {
                    // Check for duplicate enrollment
                    if (Enrollment::isEnrolled($userId, $courseId)) {
                        $errors[] = 'Student is already enrolled in this course';
                    } else {
                        try {
                            $db->beginTransaction();

                            // Create enrollment
                            $enrollmentData = [
                                'user_id' => $userId,
                                'course_id' => $courseId,
                                'enrollment_status' => 'Enrolled',
                                'payment_status' => $amount >= $course->getPrice() ? 'completed' : 'pending',
                                'amount_paid' => $amount
                            ];

                            $enrollmentId = Enrollment::create($enrollmentData);

                            // Create payment plan
                            $planData = [
                                'enrollment_id' => $enrollmentId,
                                'user_id' => $userId,
                                'course_id' => $courseId,
                                'total_fee' => $course->getPrice(),
                                'total_paid' => $amount,
                                'payment_status' => $amount >= $course->getPrice() ? 'completed' : 'partial'
                            ];

                            $planId = PaymentPlan::create($planData);

                            // Create payment record
                            $sql = "INSERT INTO payments (
                                student_id, course_id, enrollment_id, payment_plan_id,
                                amount, currency, payment_method_id, payment_type,
                                payment_status, transaction_id, recorded_by, notes, payment_date
                            ) VALUES (
                                :student_id, :course_id, :enrollment_id, :plan_id,
                                :amount, 'ZMW', :payment_method_id, 'course_fee',
                                'Completed', :reference, :recorded_by, :notes, NOW()
                            )";

                            $params = [
                                'student_id' => $studentId,
                                'course_id' => $courseId,
                                'enrollment_id' => $enrollmentId,
                                'plan_id' => $planId,
                                'amount' => $amount,
                                'payment_method_id' => $cashPaymentMethodId,
                                'reference' => 'CASH-' . date('Ymd') . '-' . ($receiptNumber ?: uniqid()),
                                'recorded_by' => $_SESSION['user_id'],
                                'notes' => $notes
                            ];

                            $db->query($sql, $params);

                            $db->commit();

                            // Log activity
                            logActivity(
                                "Enrolled student and recorded payment: Course {$courseId}, Amount K{$amount}",
                                'enrollment_payment',
                                'enrollment',
                                $enrollmentId
                            );

                            flash('message', 'Student enrolled and payment recorded.', 'success');
                            redirect(url('admin/finance/record-payment.php'));
                            exit;

                        } catch (Exception $e) {
                            $db->rollback();
                            logActivity("Enrollment/payment error: " . $e->getMessage(), 'error');
                            flash('message', 'Failed to create enrollment', 'error');
                        }
                    }
                }
            }
        }
    }

    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

// ... (template code follows)
?>
```

**Why This is Better:**
1. ✓ **Role-specific authorization**: Only finance staff can access
2. ✓ **Comprehensive validation**: Amount, student, course all validated
3. ✓ **Duplicate prevention**: Checks for existing enrollments
4. ✓ **Transaction integrity**: Payment + enrollment + plan updated atomically
5. ✓ **Audit trail**: Records who created the payment (`recorded_by`)
6. ✓ **Balance tracking**: Payment plan automatically updated
7. ✓ **Receipt generation**: Unique transaction ID created
8. ✓ **Error handling**: Rollback on failure, clear error messages

---

## Security Best Practices

### 1. Password Security

**Legacy/Weak:**
```php
// ❌ NEVER DO THIS
$password = md5($_POST['password']); // MD5 is broken!
$password = sha1($_POST['password']); // SHA1 is also weak
$password = hash('sha256', $_POST['password']); // No salt!

// Store in database
$query = "UPDATE users SET password = '$password' WHERE id = $userId";
```

**Modern/Secure:**
```php
// ✓ SECURE: bcrypt with automatic salt
function hashPassword($password) {
    // PASSWORD_DEFAULT uses bcrypt (cost 10)
    // Automatically generates and stores salt
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    // Constant-time comparison prevents timing attacks
    return password_verify($password, $hash);
}

// Usage
$hash = hashPassword($_POST['password']);
$db->update('users', ['password_hash' => $hash], 'id = ?', [$userId]);

// Verification
$user = $db->fetchOne("SELECT password_hash FROM users WHERE email = ?", [$email]);
if (verifyPassword($_POST['password'], $user['password_hash'])) {
    // Login success
}
```

---

### 2. CSRF Protection

**Legacy/Vulnerable:**
```php
// ❌ NO CSRF PROTECTION
<form method="POST" action="delete-user.php">
    <input type="hidden" name="user_id" value="123">
    <button type="submit">Delete</button>
</form>

// Attacker can create their own form on evil.com:
<form method="POST" action="https://edutrack.com/delete-user.php">
    <input type="hidden" name="user_id" value="1"> <!-- Delete admin! -->
</form>
<script>document.forms[0].submit();</script>
```

**Modern/Secure:**
```php
// ✓ CSRF TOKEN GENERATION (Session-based)
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// ✓ CSRF TOKEN VALIDATION
function verifyCsrfToken($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }

    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Constant-time comparison
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Usage in forms:
<form method="POST">
    <?= csrfField() ?>
    <!-- form fields -->
</form>

// Validation in handler:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        http_response_code(403);
        die('CSRF validation failed');
    }
    // Process form
}
```

---

### 3. File Upload Security

**Legacy/Dangerous:**
```php
// ❌ EXTREMELY DANGEROUS
$fileName = $_FILES['upload']['name']; // Could be "shell.php"
$uploadPath = "/var/www/html/uploads/" . $fileName;
move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath);

// Attacker uploads "shell.php":
// <?php system($_GET['cmd']); ?>
// Then visits: https://site.com/uploads/shell.php?cmd=rm -rf /
```

**Modern/Secure:**
```php
// ✓ SECURE FILE UPLOAD
function validateAndUploadFile($file, $options = []) {
    $errors = [];

    // Default options
    $options = array_merge([
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'allowed_mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'upload_dir' => PUBLIC_PATH . '/uploads/'
    ], $options);

    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return ['success' => false, 'errors' => $errors];
    }

    // Check file size
    if ($file['size'] > $options['max_size']) {
        $errors[] = 'File too large (max ' . formatBytes($options['max_size']) . ')';
    }

    // Get real file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($extension, $options['allowed_extensions'])) {
        $errors[] = 'File type not allowed';
    }

    // Validate MIME type (not just extension!)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $options['allowed_mimes'])) {
        $errors[] = 'Invalid file type';
    }

    // Check for PHP code injection (even in images!)
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php/i', $content)) {
        $errors[] = 'File contains malicious code';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Generate safe filename
    $safeFileName = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = $options['upload_dir'] . $safeFileName;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Set permissions (not executable!)
        chmod($uploadPath, 0644);

        return [
            'success' => true,
            'filename' => $safeFileName,
            'path' => $uploadPath
        ];
    }

    return ['success' => false, 'errors' => ['Failed to save file']];
}

// Usage:
$result = validateAndUploadFile($_FILES['assignment'], [
    'allowed_extensions' => ['pdf', 'docx'],
    'allowed_mimes' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'max_size' => 10 * 1024 * 1024 // 10MB
]);

if ($result['success']) {
    $fileName = $result['filename'];
    // Save to database
} else {
    // Show errors
}
```

---

### 4. Session Security

**Legacy/Weak:**
```php
// ❌ INSECURE SESSION
session_start();
$_SESSION['user_id'] = $userId;
$_SESSION['logged_in'] = true;

// No session regeneration (session fixation vulnerability)
// No timeout (sessions live forever)
// No fingerprinting (session hijacking possible)
```

**Modern/Secure:**
```php
// ✓ SECURE SESSION MANAGEMENT
function createUserSession($user, $remember = false) {
    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Set timeouts
    $lifetime = $remember ? (30 * 24 * 60 * 60) : 7200; // 30 days or 2 hours
    $_SESSION['session_lifetime'] = time() + $lifetime;
    $_SESSION['last_activity'] = time();

    // Create fingerprint (detect session hijacking)
    $_SESSION['fingerprint'] = md5(
        $_SERVER['HTTP_USER_AGENT'] .
        $_SERVER['HTTP_ACCEPT_LANGUAGE']
    );

    // Store in database for multi-device logout
    $db = Database::getInstance();
    $sessionToken = bin2hex(random_bytes(32));

    $db->insert('user_sessions', [
        'user_id' => $user['id'],
        'session_token' => $sessionToken,
        'ip_address' => getClientIp(),
        'user_agent' => getUserAgent(),
        'expires_at' => date('Y-m-d H:i:s', time() + $lifetime)
    ]);

    $_SESSION['session_token'] = $sessionToken;
}

function validateSession() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Check absolute timeout
    if (isset($_SESSION['session_lifetime']) && time() > $_SESSION['session_lifetime']) {
        logoutUser();
        return false;
    }

    // Check inactivity timeout (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
        logoutUser();
        return false;
    }

    // Update last activity
    $_SESSION['last_activity'] = time();

    // Verify fingerprint (detect hijacking)
    $currentFingerprint = md5(
        $_SERVER['HTTP_USER_AGENT'] .
        $_SERVER['HTTP_ACCEPT_LANGUAGE']
    );

    if (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] !== $currentFingerprint) {
        logoutUser();
        return false;
    }

    // Regenerate session ID periodically (every 30 minutes)
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    return true;
}

// Use in middleware:
if (!validateSession()) {
    redirect(url('login.php'));
    exit;
}
```

---

### 5. Rate Limiting

**Legacy/No Protection:**
```php
// ❌ NO RATE LIMITING - Brute force possible
if (verifyPassword($_POST['password'], $user['password_hash'])) {
    // Login
}
// Attacker can try unlimited passwords!
```

**Modern/Secure:**
```php
// ✓ RATE LIMITING (EduTrack Implementation)
function checkLoginAttempts($identifier) {
    $key = 'login_' . $identifier . '_' . getClientIp();
    $maxAttempts = 5;
    $timeout = 900; // 15 minutes

    return checkRateLimit($key, $maxAttempts, $timeout);
}

function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 900) {
    $storageKey = 'rate_limit_' . md5($key);

    // Get current attempts from session
    $attempts = $_SESSION[$storageKey] ?? [
        'count' => 0,
        'reset_at' => time() + $timeWindow
    ];

    // Reset if time window expired
    if (time() > $attempts['reset_at']) {
        $attempts = [
            'count' => 0,
            'reset_at' => time() + $timeWindow
        ];
    }

    // Increment attempts
    $attempts['count']++;
    $_SESSION[$storageKey] = $attempts;

    // Check if exceeded
    return $attempts['count'] <= $maxAttempts;
}

// Usage in login:
if (!checkLoginAttempts($email)) {
    return [
        'success' => false,
        'message' => 'Too many failed attempts. Try again in 15 minutes.'
    ];
}

// On successful login, reset:
function resetLoginAttempts($identifier) {
    $key = 'login_' . $identifier . '_' . getClientIp();
    $storageKey = 'rate_limit_' . md5($key);
    unset($_SESSION[$storageKey]);
}
```

---

## Testing & Validation

### Manual Security Testing Checklist

**SQL Injection Testing:**
```
Test inputs:
- ' OR '1'='1
- '; DROP TABLE users; --
- 1' UNION SELECT password FROM users --
- admin'--

Expected: All should be safely escaped/parameterized
```

**XSS Testing:**
```
Test inputs:
- <script>alert('XSS')</script>
- <img src=x onerror=alert('XSS')>
- javascript:alert('XSS')
- <svg/onload=alert('XSS')>

Expected: All HTML entities escaped in output
```

**CSRF Testing:**
```
1. Submit form without CSRF token
Expected: 403 Forbidden

2. Submit form with invalid token
Expected: 403 Forbidden

3. Reuse token from different session
Expected: 403 Forbidden
```

**Authorization Testing:**
```
1. Student tries to access /admin/users.php
Expected: Access denied / redirect

2. Instructor tries to grade another instructor's assignment
Expected: Access denied

3. Student tries to modify enrollment_id in payment form
Expected: Validation error
```

---

## Summary: Key Improvements

| Legacy Pattern | Modern Secure Pattern | Security Benefit |
|----------------|----------------------|------------------|
| `mysql_query("SELECT * FROM users WHERE id=$id")` | `$db->fetchOne("SELECT * FROM users WHERE id = ?", [$id])` | Prevents SQL injection |
| `<div><?= $user['name'] ?></div>` | `<div><?= sanitize($user['name']) ?></div>` | Prevents XSS attacks |
| `<form method="POST">` (no token) | `<?= csrfField() ?>` in form + `validateCSRF()` | Prevents CSRF attacks |
| `if ($_SESSION['role'] == 'admin')` | `require 'middleware/admin-only.php'` | Centralized authorization |
| `password = md5($_POST['password'])` | `password_hash($password, PASSWORD_DEFAULT)` | Strong password hashing |
| No rate limiting | `checkLoginAttempts($email)` | Prevents brute force |
| `move_uploaded_file($file, $path)` | `validateAndUploadFile()` with MIME check | Prevents shell upload |
| No transactions | `$db->beginTransaction()` + `commit()` | Data integrity |
| No logging | `logActivity($action, $type, $id)` | Audit trail |

---

## Implementation Priority

**Phase 1 (Critical - Immediate):**
1. ✅ Ensure all SQL queries use prepared statements
2. ✅ Add CSRF tokens to all forms
3. ✅ Sanitize all output with `sanitize()` or `xssClean()`
4. ✅ Implement rate limiting on login/registration
5. ✅ Validate file uploads with MIME type checking

**Phase 2 (High Priority - This Week):**
1. Add transaction support to critical operations (enrollment, payment)
2. Implement comprehensive activity logging
3. Add authorization checks to all pages
4. Review and strengthen password policies
5. Implement session timeout and regeneration

**Phase 3 (Medium Priority - This Month):**
1. Add automated security testing
2. Implement CSP (Content Security Policy) headers
3. Add input validation library
4. Implement API rate limiting
5. Add security headers (X-Frame-Options, etc.)

---

## Conclusion

EduTrack LMS has a **solid foundation** with PDO, prepared statements, and good class organization. The key areas for improvement are:

1. **Consistency**: Ensure all pages follow the same security patterns
2. **Defense in Depth**: Multiple layers of security (CSRF + authorization + validation)
3. **Audit Trail**: Log all sensitive operations
4. **Testing**: Regular security audits and penetration testing

By following the patterns in this guide, you'll have a **secure, modern, and maintainable** native PHP application that protects your users and data.

---

**Document Version:** 1.0
**Last Updated:** 2025-11-30
**Author:** Senior PHP Security Auditor
**Target:** EduTrack LMS - Native PHP Course Management System
