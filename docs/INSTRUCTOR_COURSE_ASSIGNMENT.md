# Instructor and Course Assignment Guide

## Overview
This guide explains how to manage instructors and assign them to courses in the EduTrack LMS system.

## Database Fixes Applied

### 1. Instructor Records Migration
A SQL migration script has been created at `/migrations/fix-instructor-records.sql` to ensure all users with instructor roles have proper instructor records.

**What it does:**
- Creates instructor records for Michael Siame (user_id 6) - Head of ICT
- Creates instructor records for Chilala Moonga (user_id 27) - Principal
- Automatically creates instructor records for all users with the instructor role who don't have one yet

**To apply this migration:**
```bash
mysql -u root -p edutrack_lms < migrations/fix-instructor-records.sql
```

### 2. Team Members as Instructors
The system now properly supports team members who are also instructors. The relationship is maintained through:
- `team_members` table: Contains team member information and position
- `instructors` table: Contains instructor-specific information (bio, specialization)
- `users` table: The central user account linked by `user_id`

## Using the Course Assignment Interface

### Accessing the Interface
1. Log in as an administrator
2. Navigate to the Admin Dashboard
3. Click on **"Course Assignments"** in the left sidebar menu

### Managing Instructors

#### Viewing Available Instructors
The top section shows all available instructors with:
- Name and email
- Specialization (if set)
- Position (for team members)
- Verification status (checkmark icon)
- Team member badge (if applicable)

#### Assigning Instructors to Courses

1. **Find the course** you want to assign instructors to in the courses table
2. **Click "Manage Assignments"** in the Actions column
3. **Select instructors** by clicking on their cards (checkboxes will appear)
4. **Set a lead instructor** (optional):
   - Click "Set as Lead" button next to an assigned instructor
   - Only one instructor can be the lead
   - The lead instructor is marked as the primary contact for the course
5. **Save** your changes by clicking "Save Assignments"

#### Unassigning Instructors
1. Click "Manage Assignments" for the course
2. Uncheck the instructors you want to remove
3. Click "Save Assignments"

### Current Assignments View
The courses table shows:
- Course name, category, and status
- All assigned instructors as badges
- Lead instructor is marked with "(Lead)" and shown in blue
- Regular assigned instructors shown in gray badges

## Backend API Endpoints

The following API endpoints support the course assignment interface:

### GET /api/instructors
Returns all instructors with their details:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 6,
      "name": "Michael Siame",
      "email": "michael@edutrack.edu.zm",
      "specialization": "Information Technology",
      "is_verified": true,
      "position": "Head of ICT",
      "is_team_member": true
    }
  ]
}
```

### GET /api/course-assignments
Returns all current course-instructor assignments:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "course_id": 5,
      "instructor_id": 2,
      "is_lead": true
    }
  ]
}
```

### POST /api/course-assignments/update
Updates course assignments for a specific course:
```json
{
  "course_id": 5,
  "instructor_ids": [1, 2, 3],
  "lead_instructor_id": 2
}
```

Response:
```json
{
  "success": true,
  "message": "Course assignments updated successfully",
  "data": {
    "course_id": 5,
    "instructor_count": 3,
    "lead_instructor_id": 2
  }
}
```

## Database Schema

### course_instructors Table
```sql
CREATE TABLE course_instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    is_lead TINYINT(1) DEFAULT 0,
    assigned_at DATETIME,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE
);
```

### Key Relationships
- Each course can have multiple instructors (many-to-many relationship)
- One instructor per course can be designated as the lead instructor
- Team members can have both `team_members` and `instructors` records linked by `user_id`
- When a team member is deleted, their instructor record remains intact (different tables)

## Permissions

- **Admin users**: Full access to view and manage all course assignments
- **Instructors**: Can view their assigned courses in the instructor dashboard
- **Students**: Cannot access course assignment features

## Troubleshooting

### Issue: Instructor not appearing in the list
**Solution**: Ensure the user has:
1. An account in the `users` table
2. The instructor role assigned in `user_roles` (role_id = 3)
3. A record in the `instructors` table

Run the migration script to automatically fix missing instructor records.

### Issue: Changes not saving
**Solution**: Check that:
1. You are logged in as an admin
2. The course exists in the database
3. Selected instructors exist in the instructors table
4. Browser console for JavaScript errors

### Issue: Team member not showing as instructor
**Solution**:
1. Verify the team member has a `user_id` set in the `team_members` table
2. Run the migration script to create their instructor record
3. Assign them the instructor role in `user_roles`

## Next Steps

After setting up instructors:
1. Instructors can access their courses via the Instructor Dashboard
2. Instructors can manage course content, modules, and lessons
3. Lead instructors have primary responsibility for course delivery
4. Multiple instructors can collaborate on the same course

## Support

For additional help:
- Check the main README.md
- Review the database schema documentation
- Contact system administrators
