# Edutrack LMS API

## Overview

The Edutrack LMS REST API provides programmatic access to the learning management system. The API is organized around REST, uses JSON for request/response bodies, and uses standard HTTP response codes.

## Base URL

```
https://your-domain.com/api/v1/
```

## Authentication

The API supports two authentication methods:

### 1. JWT Bearer Token (Recommended for mobile/external apps)

```bash
# Login to get token
POST /api/v1/auth.php
{
  "action": "login",
  "email": "user@example.com",
  "password": "password"
}

# Use token in subsequent requests
GET /api/v1/notifications.php
Authorization: Bearer {access_token}
```

### 2. Session-based (Web applications)

Standard PHP session authentication using cookies.

## Versioning

The API is versioned to ensure backward compatibility. Always use the version prefix in your requests:

- **Current Version**: `v1`
- **Endpoint Format**: `/api/v1/{endpoint}.php`

### Version History

| Version | Status | Release Date | End of Support |
|---------|--------|--------------|----------------|
| v1      | Current| 2024-11-01   | -              |

### Upgrading Between Versions

When a new API version is released:
1. Existing v1 endpoints will continue to work
2. New features will be added to the new version
3. Deprecated features will be documented
4. At least 6 months notice before version sunset

## Available Endpoints

### Authentication (`/api/v1/auth.php`)

**POST** - Login
```json
{
  "action": "login",
  "email": "user@example.com",
  "password": "password"
}
```

Response:
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJh...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "email": "user@example.com",
    "role": "student"
  }
}
```

**POST** - Register
```json
{
  "action": "register",
  "email": "newuser@example.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe"
}
```

**POST** - Refresh Token
```json
{
  "action": "refresh",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJh..."
}
```

**POST** - Verify Token
```json
{
  "action": "verify_token",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**GET** - Check Authentication Status
```
GET /api/v1/auth.php
Authorization: Bearer {token}
```

**DELETE** - Logout
```
DELETE /api/v1/auth.php
Authorization: Bearer {token}
```

### Notifications (`/api/v1/notifications.php`)

**GET** - List Notifications
```
GET /api/v1/notifications.php?action=list&limit=20&offset=0&unread_only=1
```

**GET** - Unread Count
```
GET /api/v1/notifications.php?action=unread_count
```

**POST** - Mark as Read
```json
{
  "action": "mark_as_read",
  "id": 123
}
```

**POST** - Mark All as Read
```json
{
  "action": "mark_all_as_read"
}
```

**DELETE** - Delete Notification
```
DELETE /api/v1/notifications.php?id=123
```

### Courses (`/api/v1/courses.php`)

**GET** - List Courses
```
GET /api/v1/courses.php?category=programming&level=beginner&limit=10
```

### Enrollment (`/api/v1/enroll.php`)

**POST** - Enroll in Course
```json
{
  "course_id": 5
}
```

### Progress (`/api/v1/progress.php`)

**GET** - Get Progress
```
GET /api/v1/progress.php?course_id=5
```

**POST** - Update Progress
```json
{
  "lesson_id": 25,
  "completed": true,
  "time_spent": 1200
}
```

### Quizzes (`/api/v1/quiz.php`)

**GET** - Get Quiz
```
GET /api/v1/quiz.php?quiz_id=10
```

**POST** - Submit Quiz
```json
{
  "quiz_id": 10,
  "answers": {
    "1": "A",
    "2": "C",
    "3": "B"
  }
}
```

### Assignments (`/api/v1/assignment.php`)

**POST** - Submit Assignment
```json
{
  "assignment_id": 15,
  "submission_text": "My solution...",
  "file": "{base64_encoded_file}"
}
```

### Payments (`/api/v1/payment.php`)

**POST** - Initiate Payment
```json
{
  "course_id": 5,
  "payment_method": "mtn_mobile_money",
  "phone_number": "260971234567"
}
```

### Notes (`/api/v1/notes.php`)

**GET** - List Notes
```
GET /api/v1/notes.php?course_id=5
```

**POST** - Create Note
```json
{
  "course_id": 5,
  "lesson_id": 25,
  "content": "Important concept..."
}
```

**PUT** - Update Note
```json
{
  "id": 100,
  "content": "Updated content..."
}
```

**DELETE** - Delete Note
```
DELETE /api/v1/notes.php?id=100
```

### File Upload (`/api/v1/upload.php`)

**POST** - Upload File
```
POST /api/v1/upload.php
Content-Type: multipart/form-data

file: [binary data]
type: assignment|profile|course
```

## Response Format

### Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed successfully"
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message",
  "code": 400
}
```

### Validation Error
```json
{
  "success": false,
  "error": "Validation failed",
  "details": {
    "validation_errors": {
      "email": ["Email is required", "Email must be valid"],
      "password": ["Password must be at least 8 characters"]
    }
  }
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

## Rate Limiting

Rate limiting will be implemented in future versions. Current implementation does not enforce rate limits.

## CORS

The API supports Cross-Origin Resource Sharing (CORS) for browser-based applications:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

## Pagination

List endpoints support pagination:

```
GET /api/v1/notifications.php?limit=20&offset=40
```

Parameters:
- `limit`: Number of items per page (default: 20, max: 100)
- `offset`: Number of items to skip

Response includes:
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "limit": 20,
    "offset": 40,
    "total": 150,
    "has_more": true
  }
}
```

## Filtering

Most list endpoints support filtering:

```
GET /api/v1/courses.php?category=5&level=beginner&search=python
```

## Sorting

Use `sort` and `order` parameters:

```
GET /api/v1/courses.php?sort=created_at&order=desc
```

## Error Handling

Always check the `success` field in responses:

```javascript
fetch('/api/v1/notifications.php')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Handle success
      console.log(data.notifications);
    } else {
      // Handle error
      console.error(data.error);
    }
  });
```

## SDK & Libraries

### JavaScript/TypeScript
```javascript
// Example using fetch
const api = {
  baseUrl: 'https://your-domain.com/api/v1',
  token: localStorage.getItem('access_token'),

  async request(endpoint, options = {}) {
    const response = await fetch(`${this.baseUrl}/${endpoint}`, {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.token}`,
        ...options.headers
      }
    });
    return response.json();
  },

  // Usage
  async getNotifications() {
    return this.request('notifications.php?action=list');
  }
};
```

### Python
```python
import requests

class EdutracAPI:
    def __init__(self, base_url, token=None):
        self.base_url = f"{base_url}/api/v1"
        self.token = token

    def request(self, endpoint, method='GET', data=None):
        headers = {'Authorization': f'Bearer {self.token}'} if self.token else {}
        url = f"{self.base_url}/{endpoint}"

        if method == 'GET':
            response = requests.get(url, headers=headers)
        elif method == 'POST':
            response = requests.post(url, json=data, headers=headers)

        return response.json()

# Usage
api = EdutrackAPI('https://your-domain.com', token='your_token')
notifications = api.request('notifications.php?action=list')
```

## Webhooks

Webhook support for payment callbacks is available at:
```
POST /api/v1/payment-callback.php
```

## Support

For API support, contact:
- Email: api-support@edutrack.ac.zm
- Documentation: https://docs.edutrack.ac.zm/api

## Changelog

### v1.0.0 (2024-11-01)
- Initial API release
- Authentication endpoints
- Notifications system
- Course management
- Progress tracking
- Quiz and assignment submission
- Payment integration
