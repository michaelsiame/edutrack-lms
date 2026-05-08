# Information Architecture: Student Interface

## Site Map

- **Dashboard** `/dashboard.php`
  - Active courses (Continue Learning)
  - Completed courses (Recently Completed)
  - Onboarding checklist (new students)
  - Stats overview
  - Learning activity chart
  - Upcoming deadlines
  - Recommended courses
- **Student Hub** `/student/index.php`
  - Quick action grid (My Courses, Assignments, Quizzes, etc.)
  - Recent activity feed
  - Stats summary
- **My Courses** `/my-courses.php`
  - Filter tabs: All / Active / Completed
  - Course cards with progress
  - Grid/List view toggle
- **Course Learning** `/learn.php?course={slug}`
  - Lesson sidebar (modules, lessons, quizzes, assignments)
  - Video/content area
  - Lesson navigation (prev/next)
  - Mark complete action
  - Resources download
- **Assignments** `/student/assignments.php`
  - Filter: All / Pending / Submitted / Graded
  - Assignment cards with due dates
- **Quizzes** `/student/quizzes.php`
  - Filter: All / Not Attempted / Completed
  - Quiz cards with best scores
- **Certificates** `/my-certificates.php`
  - Certificate grid
  - Download/share/verify actions
- **Payments** `/my-payments.php`
  - Registration fee status
  - Course payment history
  - Outstanding balances
- **Profile** `/profile.php`
  - View profile
  - Edit profile `/edit-profile.php`

## Navigation Model

- **Primary**: Top nav dropdown (Dashboard, Student Hub, My Courses, Payments, Certificates, Profile)
- **Secondary**: Quick links sidebar on dashboard
- **Utility**: Account dropdown, role switcher, logout
- **Mobile**: Hamburger menu with same links

## Content Hierarchy

### Dashboard
1. Welcome header + action buttons
2. Onboarding checklist (if incomplete)
3. Active courses (highest priority - what to do next)
4. Recently completed courses
5. Learning activity chart
6. Upcoming deadlines
7. Recommended courses
8. Profile summary sidebar

### Learning Interface
1. Course header (title, instructor, progress, lesson counter)
2. Lesson content (video/text)
3. Resources
4. Lesson navigation (prev / mark complete / next)
5. Sidebar: Module list with completion status

## User Flows

### New Student Onboarding
1. Lands on Dashboard
2. Sees onboarding checklist
3. Clicks "Pay registration fee" → registration-fee.php
4. Returns to Dashboard → clicks "Browse Courses" → courses.php
5. Enrolls in course → my-courses.php
6. Clicks Continue → learn.php (auto-resumes at first lesson)
7. Completes lesson → sees progress update

### Returning Student Continues Learning
1. Lands on Dashboard
2. Sees "Continue Learning" with active courses
3. Clicks Continue on desired course
4. learn.php opens at last accessed lesson
5. Watches video / reads content
6. Clicks "Mark as Complete" → auto-advances to next lesson

### Graduate Views Certificate
1. Lands on Dashboard
2. Sees "Recently Completed" section
3. Clicks "Certificate" button → my-certificates.php
4. Clicks Download → download-certificate.php

## Naming Conventions

| Concept | Label in UI |
|---------|-------------|
| Course enrollment | "Enroll" |
| Lesson completion | "Mark as Complete" |
| Course progress | "X% complete" |
| Certificate | "Certificate" |
| Assignment submission | "Submit Assignment" |
| Quiz attempt | "Take Quiz" |
| Payment plan | "Payment Plan" |
| Registration fee | "Registration Fee" |

## Component Reuse Map

| Component | Used on | Behavior differences |
|-----------|---------|---------------------|
| Stat card | Dashboard, Student Hub | Icon/color varies by metric |
| Course card | My Courses, Dashboard, Recommended | Shows progress on enrolled, price on non-enrolled |
| Progress ring | My Courses, Dashboard, Learn header | Size varies (sm/md/lg) |
| Empty state | All list pages | Icon, message, and CTA vary |
| Lesson list item | Learn sidebar | Active/hover/completed states |
