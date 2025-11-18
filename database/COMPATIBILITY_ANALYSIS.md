# Database Schema Compatibility Analysis

## Executive Summary

**Status**: ⚠️ **CRITICAL COMPATIBILITY ISSUES FOUND**

The existing application code is **NOT compatible** with the new complete LMS database schema. Significant refactoring is required to align the application code with the new database structure.

---

## Critical Issues Identified

### 1. Table Naming Mismatch

**Issue**: The application uses lowercase table names, but the new schema uses PascalCase (capitalized) table names.

| Application Code | New Schema | Status |
|-----------------|------------|--------|
| `users` | `Users` | ❌ Mismatch |
| `courses` | `Courses` | ❌ Mismatch |
| `enrollments` | `Enrollments` | ❌ Mismatch |
| `course_categories` | `Course_Categories` | ❌ Mismatch |
| `modules` | `Modules` | ❌ Mismatch |
| `lessons` | `Lessons` | ❌ Mismatch |
| `assignments` | `Assignments` | ❌ Mismatch |
| `quizzes` | `Quizzes` | ❌ Mismatch |

**Impact**: All database queries will fail with "table doesn't exist" errors.

**MySQL Note**: MySQL table names are case-sensitive on Linux/Unix systems but case-insensitive on Windows/macOS.

---

### 2. Primary Key Column Naming

**Issue**: Application expects `id` as primary key, but new schema uses descriptive names.

| Table | Application Expects | New Schema Has | Status |
|-------|-------------------|----------------|--------|
| Users | `id` | `user_id` | ❌ Mismatch |
| Courses | `id` | `course_id` | ❌ Mismatch |
| Enrollments | `id` | `enrollment_id` | ❌ Mismatch |
| Modules | `id` | `module_id` | ❌ Mismatch |
| Lessons | `id` | `lesson_id` | ❌ Mismatch |

**Files Affected**:
- `src/classes/Course.php` (lines 32, 58, etc.)
- `src/classes/User.php` (lines 30, 49, 53)
- `src/classes/Enrollment.php` (lines 29, 54, 63)
- All other model classes

---

### 3. Missing Tables in New Schema

**Issue**: Application code references tables that don't exist in the new schema.

| Missing Table | Used In | Purpose |
|--------------|---------|---------|
| `user_profiles` | `User.php:33, 55` | Extended user information |
| `course_reviews` | `Course.php:27, 75` | Course ratings/reviews |

**Impact**:
- User profile loading will fail
- Course ratings display will fail
- Review system will not work

---

### 4. Structural Differences

#### A. Users Table Structure

**Application Expectation**:
```sql
users
├── id (PK)
├── email
├── password
├── first_name
├── last_name
└── ... (all user fields in one table)

user_profiles (separate table)
├── user_id (FK)
├── bio
├── phone
└── ... (extended profile fields)
```

**New Schema**:
```sql
Users
├── user_id (PK)
├── username ⚠️ New field
├── email
├── password_hash ⚠️ Different name
├── first_name
├── last_name
├── phone ⚠️ Moved here
├── avatar_url
├── status
├── email_verified
└── last_login

Students (separate table)
├── student_id (PK)
├── user_id (FK) ⚠️ Links to Users
├── date_of_birth
├── gender
└── ... (student-specific fields)

Instructors (separate table)
├── instructor_id (PK)
├── user_id (FK) ⚠️ Links to Users
├── bio
├── specialization
└── ... (instructor-specific fields)
```

**Impact**: User authentication and profile management requires complete refactoring.

---

#### B. Course-Instructor Relationship

**Application Expectation**:
```sql
courses
├── id
├── instructor_id (FK to users) ⚠️ Direct relationship
└── ...
```

**New Schema**:
```sql
Courses
├── course_id
└── ... (NO instructor_id)

Course_Instructors (junction table)
├── course_instructor_id
├── course_id (FK)
├── instructor_id (FK)
└── role (Lead, Assistant, Guest)
```

**Impact**:
- `Course.php:31, 79` queries will fail
- Multi-instructor support requires query refactoring
- Instructor assignment logic needs rewrite

**Files Affected**:
- `src/classes/Course.php:24-34, 72-80`

---

#### C. Enrollment Structure

**Application Expectation**:
```sql
enrollments
├── id
├── user_id (FK to users) ⚠️
├── course_id
├── enrollment_status
├── payment_status
├── amount_paid
├── enrolled_at
└── ...
```

**New Schema**:
```sql
Enrollments
├── enrollment_id
├── student_id (FK to Students, NOT Users) ⚠️
├── course_id
├── enrollment_date (not enrolled_at)
├── progress (0-100%)
├── final_grade
├── status (ENUM: 'Enrolled', 'In Progress', 'Completed', 'Dropped')
├── completion_date
├── certificate_issued
└── ... (NO payment fields here)

Payments (separate table) ⚠️
├── payment_id
├── student_id
├── course_id
├── enrollment_id
├── amount
├── payment_status
└── ...
```

**Impact**:
- Enrollment creation will fail
- Payment tracking broken
- Progress tracking incompatible

**Files Affected**:
- `src/classes/Enrollment.php:26-28, 54-55, 87-100`
- `public/api/enroll.php`
- `public/api/payment.php`

---

#### D. Course Categories

**Application Expectation**:
```sql
course_categories
├── id
├── name ⚠️
├── slug
└── ...
```

**New Schema**:
```sql
Course_Categories
├── category_id
├── category_name ⚠️ (not just 'name')
├── category_description
├── parent_category_id (supports nesting)
├── display_order
└── ...
```

**Impact**: Category queries will fail

**Files Affected**:
- `src/classes/Course.php:24, 30, 72, 78`
- `src/classes/Category.php` (likely entire file)

---

### 5. Column Name Mismatches

| Table | Application | New Schema | Status |
|-------|-------------|------------|--------|
| Users | `password` | `password_hash` | ❌ Different |
| Courses | `level` | `difficulty_level` | ❌ Different |
| Courses | `status = 'published'` | `status = 'Published'` | ❌ Case mismatch |
| Enrollments | `enrolled_at` | `enrollment_date` | ❌ Different |
| Enrollments | `user_id` | `student_id` | ❌ Different concept |

---

### 6. Missing Features in Application Code

The new schema includes features not yet implemented in the application:

| Feature | New Schema Tables | Application Support |
|---------|------------------|-------------------|
| Roles & Permissions | `Roles`, `User_Roles` | ❌ Not implemented |
| Course Content | `Modules`, `Lessons`, `Lesson_Resources` | ⚠️ Partial |
| Lesson Progress | `Lesson_Progress` | ✅ Exists (`lesson-progress.php`) |
| Assignments | `Assignments`, `Assignment_Submissions` | ✅ Exists (`assignment.php`) |
| Quizzes | `Quizzes`, `Questions`, `Quiz_Questions`, `Quiz_Attempts` | ✅ Exists (`quiz.php`) |
| Discussions | `Discussions`, `Discussion_Replies` | ⚠️ Partial (`course-discussions.php`) |
| Announcements | `Announcements` | ⚠️ Unknown |
| Messages | `Messages` | ❌ Not implemented |
| Certificates | `Certificates` | ⚠️ Partial (`CertificateGenerator.php`) |
| Badges | `Badges`, `Student_Achievements` | ❌ Not implemented |
| Payments | `Payments`, `Transactions`, `Payment_Methods` | ⚠️ Partial (`payment.php`) |
| Notifications | `Notifications` | ✅ Exists (`notifications.php`) |
| Activity Logs | `Activity_Logs` | ❌ Not implemented |
| Email Templates | `Email_Templates` | ⚠️ Partial (mail templates exist) |
| System Settings | `System_Settings` | ❌ Not implemented |

---

## Compatibility Matrix

### Fully Compatible
- ❌ **None** - No components are fully compatible

### Partially Compatible (Requires Modifications)
- ⚠️ `config/database.php` - DB config OK, but table names wrong
- ⚠️ `src/includes/database.php` - Connection class OK, queries need fixing
- ⚠️ Lesson Progress tracking
- ⚠️ Assignment system
- ⚠️ Quiz system
- ⚠️ Payment processing (structure different)

### Incompatible (Requires Complete Rewrite)
- ❌ `src/classes/User.php`
- ❌ `src/classes/Course.php`
- ❌ `src/classes/Enrollment.php`
- ❌ `src/classes/Category.php`
- ❌ Most API endpoints
- ❌ Authentication system

---

## Recommended Solutions

### Option 1: Update Database Schema to Match Application ⭐ RECOMMENDED FOR QUICK FIX

**Pros**:
- Faster implementation
- Minimal code changes
- Existing functionality preserved

**Cons**:
- Loses new features in complete schema
- Less normalized structure
- Missing role-based access control

**Action Items**:
1. Create new schema based on existing code structure
2. Keep lowercase table names
3. Use simple `id` primary keys
4. Maintain `user_id` in enrollments
5. Keep `user_profiles` table separate

### Option 2: Update Application Code to Match New Schema ⭐ RECOMMENDED FOR LONG-TERM

**Pros**:
- More robust, normalized database
- Support for new features (roles, badges, multi-instructor)
- Better scalability
- Industry best practices

**Cons**:
- Significant refactoring required
- Takes more time
- Risk of breaking existing functionality
- Requires thorough testing

**Action Items**:
1. Create database migration plan
2. Update all model classes
3. Refactor API endpoints
4. Update SQL queries throughout codebase
5. Implement new features
6. Comprehensive testing

**Estimated Effort**: 40-60 hours of development work

### Option 3: Create Compatibility Layer (Hybrid Approach)

**Description**: Create database views and stored procedures to bridge the gap

**Pros**:
- Allows gradual migration
- Can run both old and new simultaneously
- Lower risk

**Cons**:
- Adds complexity
- Performance overhead
- Temporary solution

---

## Immediate Action Required

### Critical Files Needing Updates

1. **`src/classes/User.php`** - Complete rewrite needed
   - Update table name: `users` → `Users`
   - Update PK: `id` → `user_id`
   - Remove `user_profiles` dependency
   - Implement `Students`/`Instructors` relationship

2. **`src/classes/Course.php`** - Major refactoring
   - Update table name: `courses` → `Courses`
   - Update PK: `id` → `course_id`
   - Fix category column: `name` → `category_name`
   - Implement `Course_Instructors` junction table queries
   - Remove `course_reviews` dependency (or add Reviews table)

3. **`src/classes/Enrollment.php`** - Complete rewrite
   - Update table name: `enrollments` → `Enrollments`
   - Update PK: `id` → `enrollment_id`
   - Change FK: `user_id` → `student_id`
   - Update column names (enrollment_status, enrolled_at, etc.)
   - Separate payment logic to `Payments` table

4. **`src/classes/Category.php`** - Update queries
   - Update table name
   - Update column: `name` → `category_name`

5. **All API Endpoints** (`public/api/*.php`)
   - Review and update all SQL queries
   - Test with new schema

---

## Migration Script Needed

If choosing Option 2, you'll need a data migration script to:

1. Migrate existing data from old schema to new schema
2. Transform user records into Users + Students/Instructors
3. Convert enrollments to use student_id
4. Migrate courses and update relationships
5. Preserve payment history
6. Handle category name changes

---

## Testing Requirements

Regardless of which option you choose:

1. **Unit Tests** - Test all model classes
2. **Integration Tests** - Test API endpoints
3. **User Acceptance Testing** - Test all user flows:
   - Registration and login
   - Course browsing and enrollment
   - Lesson access and progress tracking
   - Assignment submission
   - Quiz taking
   - Payment processing
   - Certificate generation

---

## Conclusion

The new complete LMS schema and existing application code are **fundamentally incompatible**. A decision must be made:

- **Quick Fix**: Modify the schema to match the existing code
- **Long-term Best Practice**: Refactor the application to use the new, more robust schema
- **Hybrid**: Create a compatibility layer for gradual migration

**Recommendation**: Choose Option 2 (refactor application code) for a production-grade system, or Option 1 (modify schema) if you need the system running quickly with minimal changes.

---

**Document Version**: 1.0
**Created**: 2025-11-18
**Status**: Awaiting Decision on Migration Strategy
