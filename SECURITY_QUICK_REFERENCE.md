# EduTrack LMS - Security Quick Reference

## Quick Security Checklist ✓

Use this checklist when creating or reviewing PHP code in the EduTrack LMS.

---

## 1. Database Queries (SQL Injection Prevention)

### ❌ NEVER do this:
```php
$query = "SELECT * FROM users WHERE id = $userId";
$query = "INSERT INTO courses (title) VALUES ('$title')";
```

### ✅ ALWAYS do this:
```php
// Using Database class
$db = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
$db->insert('courses', ['title' => $title]);
```

---

## 2. Output to HTML (XSS Prevention)

### ❌ NEVER do this:
```php
<div><?= $user['name'] ?></div>
<input value="<?= $_POST['email'] ?>">
```

### ✅ ALWAYS do this:
```php
<div><?= sanitize($user['name']) ?></div>
<input value="<?= sanitize($_POST['email']) ?>">
```

---

## 3. Forms (CSRF Prevention)

### ❌ NEVER do this:
```php
<form method="POST">
    <input name="user_id" value="123">
    <button>Delete</button>
</form>
```

### ✅ ALWAYS do this:
```php
<form method="POST">
    <?= csrfField() ?>
    <input name="user_id" value="123">
    <button>Delete</button>
</form>

// In handler:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF(); // Dies if invalid
    // Process form
}
```

---

## 4. Authentication & Authorization

### ❌ NEVER do this:
```php
<?php
session_start();
// No authentication check!
?>
```

### ✅ ALWAYS do this:
```php
<?php
// At top of every protected page
require_once '../../src/middleware/authenticate.php'; // For logged-in users
require_once '../../src/middleware/admin-only.php';   // For admins only
require_once '../../src/middleware/instructor-only.php'; // For instructors
require_once '../../src/middleware/finance-only.php';    // For finance staff
?>
```

---

## 5. Password Handling

### ❌ NEVER do this:
```php
$password = md5($_POST['password']);
$password = sha1($_POST['password']);
$password = hash('sha256', $_POST['password']);
```

### ✅ ALWAYS do this:
```php
// Hashing
$hash = hashPassword($_POST['password']);

// Verification
if (verifyPassword($_POST['password'], $storedHash)) {
    // Login success
}
```

---

## 6. File Uploads

### ❌ NEVER do this:
```php
$filename = $_FILES['file']['name'];
move_uploaded_file($_FILES['file']['tmp_name'], "uploads/$filename");
```

### ✅ ALWAYS do this:
```php
$result = validateFileUpload($_FILES['file'], [
    'allowed_types' => ['pdf', 'docx'],
    'max_size' => 5 * 1024 * 1024 // 5MB
]);

if ($result['valid']) {
    // File is safe
    $filename = $result['filename'];
}
```

---

## 7. Input Validation

### ❌ NEVER do this:
```php
$price = $_POST['price'];
$db->update('courses', ['price' => $price], 'id = ?', [$courseId]);
```

### ✅ ALWAYS do this:
```php
$price = floatval($_POST['price'] ?? 0);

if ($price < 0) {
    $errors[] = 'Price cannot be negative';
}

if ($price > 100000) {
    $errors[] = 'Price is too high';
}

if (empty($errors)) {
    $db->update('courses', ['price' => $price], 'id = ?', [$courseId]);
}
```

---

## 8. Error Handling

### ❌ NEVER do this:
```php
try {
    $db->query($sql);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); // Exposes internals!
}
```

### ✅ ALWAYS do this:
```php
try {
    $db->query($sql);
} catch (Exception $e) {
    logActivity("Error: " . $e->getMessage(), 'error'); // Log it
    flash('message', 'An error occurred. Please try again.', 'error'); // Generic message
}
```

---

## 9. Transactions (Data Integrity)

### ❌ NEVER do this:
```php
$db->insert('enrollments', [...]);
$db->insert('payments', [...]);
// If second fails, enrollment exists without payment!
```

### ✅ ALWAYS do this:
```php
try {
    $db->beginTransaction();

    $enrollmentId = $db->insert('enrollments', [...]);
    $db->insert('payments', ['enrollment_id' => $enrollmentId, ...]);

    $db->commit();
} catch (Exception $e) {
    $db->rollback(); // Undo everything
    logActivity("Transaction error: " . $e->getMessage(), 'error');
}
```

---

## 10. Authorization Checks

### ❌ NEVER do this:
```php
// Instructor grading page
$submissionId = $_GET['id'];
// No check if instructor owns this assignment!
```

### ✅ ALWAYS do this:
```php
$submissionId = intval($_GET['id'] ?? 0);

$submission = $db->fetchOne("
    SELECT s.*, c.instructor_id
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    JOIN courses c ON a.course_id = c.id
    WHERE s.submission_id = ?
", [$submissionId]);

// Verify ownership
if ($submission['instructor_id'] != currentUserId()) {
    accessDenied('You do not have permission to grade this assignment');
    exit;
}
```

---

## Common Helper Functions (EduTrack)

```php
// Database
$db = Database::getInstance();
$db->fetchOne($sql, $params);
$db->fetchAll($sql, $params);
$db->insert($table, $data);
$db->update($table, $data, $where, $whereParams);
$db->delete($table, $where, $params);
$db->beginTransaction();
$db->commit();
$db->rollback();

// Security
sanitize($string);              // XSS protection
xssClean($string);              // XSS protection
hashPassword($password);        // bcrypt hash
verifyPassword($pass, $hash);   // Verify password
csrfField();                    // Generate CSRF field
csrfToken();                    // Get CSRF token
verifyCsrfToken();              // Validate CSRF
validateCSRF();                 // Validate or die

// Validation
validateEmail($email);
validateUrl($url);
validateFileUpload($file, $options);

// Session/Auth
isLoggedIn();
currentUserId();
getCurrentUser();
hasRole($roles);
requireAdmin();
requireInstructor();
accessDenied($reason);

// Helpers
flash($key, $message, $type);   // Flash messages
redirect($url);                 // Redirect helper
url($path);                     // Generate URL
logActivity($msg, $type);       // Activity log
formatDate($date);              // Date formatter
formatCurrency($amount);        // Currency formatter
```

---

## File Structure Template

```php
<?php
/**
 * Page Title
 * Description of what this page does
 */

// 1. MIDDLEWARE (Authentication/Authorization)
require_once '../../src/middleware/admin-only.php';

// 2. DEPENDENCIES
require_once '../../src/classes/Course.php';

// 3. VARIABLES
$db = Database::getInstance();
$errors = [];

// 4. HANDLE POST REQUEST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 4a. CSRF Validation
    validateCSRF();

    // 4b. Input Sanitization
    $title = trim($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    // 4c. Validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    }

    if ($price < 0) {
        $errors[] = 'Price cannot be negative';
    }

    // 4d. Process if Valid
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Database operations
            $courseId = $db->insert('courses', [
                'title' => $title,
                'price' => $price
            ]);

            $db->commit();

            // Log activity
            logActivity("Created course: $title", 'course_create', 'course', $courseId);

            // Redirect
            flash('message', 'Course created!', 'success');
            redirect(url('admin/courses/index.php'));
            exit;

        } catch (Exception $e) {
            $db->rollback();
            logActivity("Error: " . $e->getMessage(), 'error');
            $errors[] = 'An error occurred';
        }
    }

    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

// 5. LOAD DATA FOR VIEW
$courses = Course::all();

// 6. LOAD TEMPLATE
$page_title = 'Manage Courses';
require_once '../../src/templates/admin-header.php';
?>

<!-- 7. HTML TEMPLATE -->
<div class="container">
    <h1><?= sanitize($page_title) ?></h1>

    <form method="POST">
        <?= csrfField() ?>

        <input type="text" name="title" value="<?= sanitize($_POST['title'] ?? '') ?>">
        <input type="number" name="price" value="<?= sanitize($_POST['price'] ?? '') ?>">

        <button type="submit">Submit</button>
    </form>

    <?php foreach ($courses as $course): ?>
        <div><?= sanitize($course['title']) ?></div>
    <?php endforeach; ?>
</div>

<?php require_once '../../src/templates/admin-footer.php'; ?>
```

---

## Database Schema Reference

**Key Tables:**
- `users` - User accounts
- `user_roles` - User-role associations
- `roles` - Role definitions (admin, instructor, student, finance)
- `courses` - Course catalog
- `enrollments` - Student enrollments
- `payments` - Payment records
- `payment_plans` - Installment payment tracking
- `assignments` - Assignment metadata
- `assignment_submissions` - Student submissions
- `quizzes` - Quiz definitions
- `quiz_attempts` - Quiz attempts
- `activity_logs` - Audit trail

---

## Configuration Files

- `/config/app.php` - Application settings
- `/config/database.php` - Database connection
- `/config/mail.php` - Email configuration
- `/config/payment.php` - Payment gateway settings

---

## Middleware Files

- `/src/middleware/authenticate.php` - Requires logged-in user
- `/src/middleware/admin-only.php` - Requires admin role
- `/src/middleware/instructor-only.php` - Requires instructor role
- `/src/middleware/finance-only.php` - Requires finance role
- `/src/middleware/enrolled-only.php` - Requires course enrollment

---

## Security Headers (Already Implemented)

EduTrack already includes security headers in `src/includes/security-headers.php`:

```php
// X-Frame-Options (prevent clickjacking)
header('X-Frame-Options: SAMEORIGIN');

// X-Content-Type-Options (prevent MIME sniffing)
header('X-Content-Type-Options: nosniff');

// X-XSS-Protection
header('X-XSS-Protection: 1; mode=block');

// Referrer-Policy
header('Referrer-Policy: strict-origin-when-cross-origin');
```

---

## Testing Your Security

### 1. SQL Injection Test
```
Input: ' OR '1'='1
Expected: Safely escaped (no error, no unauthorized access)
```

### 2. XSS Test
```
Input: <script>alert('XSS')</script>
Expected: Displayed as text (HTML entities escaped)
```

### 3. CSRF Test
```
Action: Submit form without token
Expected: 403 Forbidden error
```

### 4. Authorization Test
```
Action: Student accesses /admin/
Expected: Access denied redirect
```

### 5. File Upload Test
```
Upload: shell.php containing <?php system('ls'); ?>
Expected: Rejected (invalid file type)
```

---

## Common Vulnerabilities to Avoid

| Vulnerability | Impact | Prevention |
|--------------|--------|------------|
| SQL Injection | Database compromise | Use prepared statements |
| XSS (Cross-Site Scripting) | Session hijacking | Sanitize all output |
| CSRF | Unauthorized actions | Use CSRF tokens |
| Session Fixation | Account takeover | Regenerate session on login |
| Brute Force | Unauthorized access | Rate limiting |
| Path Traversal | File system access | Validate file paths |
| Insecure File Upload | Remote code execution | Validate MIME type |
| Weak Passwords | Credential compromise | Strong hashing (bcrypt) |
| Missing Authorization | Privilege escalation | Check permissions |
| Information Disclosure | Data leakage | Generic error messages |

---

## Emergency Response

If you discover a security vulnerability:

1. **DO NOT** commit the vulnerable code
2. **DO NOT** expose details publicly
3. **DO** fix immediately
4. **DO** check all similar code patterns
5. **DO** review access logs for exploitation
6. **DO** notify users if data was compromised
7. **DO** document the fix in activity logs

---

## Additional Resources

- **Main Guide**: `/REFACTORING_GUIDE.md` - Comprehensive security guide
- **Live Sessions**: `/LIVE_LESSONS_GUIDE.md` - Jitsi integration guide
- **Database Schema**: `/database/complete_lms_schema.sql`
- **Migrations**: `/database/migrations/`

---

**Remember**: Security is not optional. Every page, every form, every query must follow these patterns.

**Last Updated**: 2025-11-30
