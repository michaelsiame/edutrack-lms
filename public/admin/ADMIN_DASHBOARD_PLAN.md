# EduTrack LMS - Admin Dashboard Feature Plan

Based on your database schema and existing file structure, here is the proposed feature set for the Admin Dashboard. We will utilize the existing `src/` directory for logic and `public/admin/` for views.

## 1. Dashboard Overview (Home)
*   **Key Metrics Cards:**
    *   Total Students (`students` table)
    *   Total Revenue (Sum of `payments` where status = 'Completed')
    *   Active Courses (`courses` where status = 'published')
    *   Pending Tasks (Pending payments, Verification requests).
*   **Charts (using Recharts/Chart.js logic via JS):**
    *   Enrollment trends over last 6 months.
    *   Revenue vs. Outstanding Balances.
*   **Recent Activity:** List latest entries from `activity_logs`.

## 2. User Management
*   **All Users:** List view with filters (Role, Status).
    *   *Actions:* Edit, Suspend/Activate, Reset Password, View Profile.
*   **Role Management:** Manage `user_roles`.
*   **Instructors:** Specific view to manage Instructor bio, specialization, and verification status (`instructors` table).
*   **Students:** Detailed student view including:
    *   Enrolled courses.
    *   Payment history.
    *   Academic progress/grades.

## 3. Course Management
*   **Course Listing:** Filter by Category, Instructor, Status (Draft/Published/Archived).
*   **Course Editor:**
    *   Basic Info (Title, Slug, Price, Dates).
    *   **Modules & Lessons:** AJAX-driven builder to manage `modules` and `lessons`.
    *   **Assignments/Quizzes:** Link existing assessments to courses.
*   **Categories:** CRUD operations for `course_categories`.

## 4. Financial Management (Critical)
*   **Transaction Log:** Full history from `transactions` and `payments` tables.
*   **Payment Verification:**
    *   Interface to view `payments` where `payment_status` = 'Pending' (specifically Manual/Bank Transfers).
    *   Action to Approve (updates balance, unlocks course) or Reject payment.
*   **Debtors List:** View from `enrollment_payment_plans` showing students with outstanding balances.
*   **Registration Fees:** Track `registration_fees` payments.

## 5. Enrollment & Academic
*   **Enrollment Manager:** Manually enroll a student into a course (e.g., if paid by cash).
*   **Certificates:**
    *   View issued certificates (`certificates` table).
    *   Manually issue a certificate if automation fails.
    *   Verification lookup.

## 6. Content & Communication
*   **Announcements:** CRUD for global or course-specific `announcements`.
*   **Team Members:** Manage the "Meet the Team" section (`team_members` table).
*   **Discussions:** Moderation view for `discussions` and `discussion_replies`.

## 7. System Settings
*   **General Config:** Update `system_settings` (Site Name, Payment methods enabled/disabled, Registration fee amount).
*   **Audit Logs:** Read-only view of `activity_logs`.

## Technical Approach
*   **Styling:** Tailwind CSS via CDN (as requested).
*   **Backend:** Native PHP accessing your existing `src/classes/` and `src/includes/database.php`.
*   **Frontend:** Vanilla JS for dynamic interactions (modals, async chart loading).
*   **Layout:** We will rebuild `admin-header.php` and `admin-sidebar.php` to use a modern Tailwind sidebar layout.

---

**Please confirm if this scope covers your needs or if you would like to add/remove specific modules before we generate the code.**