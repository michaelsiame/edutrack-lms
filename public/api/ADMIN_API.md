# Admin API Endpoints Documentation

This document provides detailed information about the Admin API endpoints for the EduTrack LMS system.

## Base URL
```
/api/
```

## Authentication
All admin endpoints require admin authentication. Include the session cookie or JWT token with your requests.

## Endpoints

### 1. Users API (`/api/users.php`)

Manages user accounts, roles, and status.

#### GET - List all users
```
GET /api/users.php
```
Returns all users with their roles, status, and join dates.

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "username": "john@example.com",
      "email": "john@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "status": "Active",
      "created_at": "2025-01-15 10:30:00",
      "role_name": "Admin, Instructor"
    }
  ]
}
```

#### POST - Create new user
```
POST /api/users.php
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "securepassword123",
  "role": "Student",
  "status": "Active"
}
```

#### PUT - Update user
```
PUT /api/users.php
Content-Type: application/json

{
  "id": 5,
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "role": "Instructor",
  "status": "Active"
}
```

#### DELETE - Delete user
```
DELETE /api/users.php?id=5
```

---

### 2. Courses API (`/api/courses.php`)

Manages courses, modules, and lessons.

#### GET - List all courses
```
GET /api/courses.php
```

Response includes instructor name and category name from joins.

#### GET - Get modules for a course
```
GET /api/courses.php/{course_id}/modules
```

#### GET - Get lessons for a module
```
GET /api/courses.php/modules/{module_id}/lessons
```

#### POST - Create new course
```
POST /api/courses.php
Content-Type: application/json

{
  "title": "Advanced Python Programming",
  "description": "Deep dive into Python",
  "category_id": 2,
  "instructor_id": 3,
  "level": "Advanced",
  "price": 5000,
  "status": "draft",
  "start_date": "2025-03-01",
  "end_date": "2025-06-30"
}
```

#### PUT - Update course
```
PUT /api/courses.php
Content-Type: application/json

{
  "id": 10,
  "title": "Updated Course Title",
  "status": "published",
  "price": 4500
}
```

#### DELETE - Delete course
```
DELETE /api/courses.php?id=10
```

---

### 3. Enrollments API (`/api/enrollments.php`)

Manages student course enrollments.

#### GET - List all enrollments
```
GET /api/enrollments.php
```

Returns enrollments with student names and course titles.

#### POST - Create enrollment
```
POST /api/enrollments.php
Content-Type: application/json

{
  "user_id": 8,
  "course_id": 5,
  "start_date": "2025-01-20"
}
```

Automatically creates or links to student record and updates course enrollment count.

#### PUT - Update enrollment status
```
PUT /api/enrollments.php
Content-Type: application/json

{
  "id": 15,
  "enrollment_status": "Completed",
  "progress": 100
}
```

#### DELETE - Remove enrollment
```
DELETE /api/enrollments.php?id=15
```

---

### 4. Transactions API (`/api/transactions.php`)

Manages financial transactions and payments.

#### GET - List all transactions
```
GET /api/transactions.php
```

Returns transactions with student names and payment method details.

#### POST - Create transaction
```
POST /api/transactions.php
Content-Type: application/json

{
  "user_id": 8,
  "amount": 2500,
  "type": "Payment",
  "status": "Completed",
  "description": "Course enrollment payment"
}
```

#### PUT - Update transaction status
```
PUT /api/transactions.php
Content-Type: application/json

{
  "id": "TXN-2025-12345",
  "status": "Completed"
}
```

#### DELETE - Delete transaction
```
DELETE /api/transactions.php?id=TXN-2025-12345
```

Note: Cannot delete completed transactions.

---

### 5. Categories API (`/api/categories.php`)

Manages course categories.

#### GET - List all categories
```
GET /api/categories.php
```

Returns categories with course count.

#### POST - Create category
```
POST /api/categories.php
Content-Type: application/json

{
  "name": "Data Science",
  "description": "Data analysis and machine learning courses",
  "color": "#FF5733"
}
```

#### PUT - Update category
```
PUT /api/categories.php
Content-Type: application/json

{
  "id": 7,
  "name": "Data Science & AI",
  "color": "#FF6644"
}
```

#### DELETE - Delete category
```
DELETE /api/categories.php?id=7
```

Note: Cannot delete categories with associated courses.

---

### 6. Announcements API (`/api/announcements.php`)

Manages system and course announcements.

#### GET - List all announcements
```
GET /api/announcements.php
```

#### POST - Create announcement
```
POST /api/announcements.php
Content-Type: application/json

{
  "title": "System Maintenance",
  "content": "The system will be down on Sunday...",
  "type": "System",
  "priority": "Urgent",
  "is_published": true,
  "course_id": null
}
```

#### PUT - Update announcement
```
PUT /api/announcements.php
Content-Type: application/json

{
  "id": 5,
  "title": "Updated Maintenance Schedule",
  "is_published": false
}
```

#### DELETE - Delete announcement
```
DELETE /api/announcements.php?id=5
```

---

### 7. Certificates API (`/api/certificates.php`)

Manages certificate issuance and verification.

#### GET - List all certificates
```
GET /api/certificates.php
```

Returns certificates with student and course information.

#### POST - Issue certificate
```
POST /api/certificates.php
Content-Type: application/json

{
  "enrollment_id": 25
}
```

Automatically generates certificate number and verification code, and updates enrollment status to completed.

---

### 8. Settings API (`/api/settings.php`)

Manages system settings.

#### GET - Get all settings
```
GET /api/settings.php
```

Returns settings organized by category:
- `general`: Site name, email, phone, address, timezone
- `email`: SMTP configuration
- `payments`: Currency, tax rate, payment settings
- `courses`: Course approval, auto-enroll, certificates
- `notifications`: Email and SMS notification settings

#### POST/PUT - Update settings
```
PUT /api/settings.php
Content-Type: application/json

{
  "general": {
    "siteName": "EduTrack LMS",
    "siteEmail": "info@edutrack.ac.zm"
  },
  "payments": {
    "currency": "ZMW",
    "taxRate": 16
  }
}
```

---

### 9. Logs API (`/api/logs.php`)

Retrieves activity logs.

#### GET - List activity logs
```
GET /api/logs.php?limit=100&offset=0
```

Returns paginated activity logs with user information.

Response:
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "total": 1500,
    "limit": 100,
    "offset": 0
  }
}
```

---

## Error Responses

All endpoints return standardized error responses:

```json
{
  "success": false,
  "error": "Error message here"
}
```

HTTP status codes:
- `200`: Success
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `500`: Internal Server Error

## Database Schema Integration

All endpoints properly integrate with the database schema:
- Proper table joins for related data
- Foreign key relationships maintained
- Transaction support for complex operations
- Automatic enrollment count updates
- Status transitions handled correctly

## Security Features

- Admin-only middleware protection
- SQL injection prevention via prepared statements
- CSRF protection via session validation
- Input validation and sanitization
- Transaction rollback on errors
- CORS headers for cross-origin requests

## Testing

Test endpoints using:
- Browser developer console
- Postman
- cURL commands
- Admin React dashboard

Example cURL:
```bash
curl -X GET http://localhost/api/users.php \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=your-session-id"
```
