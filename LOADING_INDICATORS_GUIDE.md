# Loading Indicators Usage Guide

## Overview

The loading indicator system provides a comprehensive set of visual feedback tools for asynchronous operations. This guide shows you how to use each type of loader in your PHP and JavaScript code.

---

## 📦 What's Included

- **Full-page loaders** - For page-level operations
- **Button loaders** - For submit buttons and action buttons
- **Container loaders** - For specific sections/cards
- **Skeleton screens** - For content placeholders while loading
- **Progress bars** - For upload/download operations
- **Inline loaders** - For small inline operations

---

## 🚀 Quick Start

### 1. Automatic Form Loading

Add `data-loading` attribute to any form:

```php
<form method="POST" data-loading data-loading-text="Saving changes...">
    <!-- form fields -->
    <button type="submit" class="btn-primary">
        <i class="fas fa-save mr-2"></i>Save Changes
    </button>
</form>
```

**Result:** Submit button automatically shows spinner when form is submitted.

### 2. Async Button Actions

Add `data-async-action` to buttons that trigger AJAX:

```php
<button data-async-action
        data-loading-text="Deleting..."
        onclick="deleteItem(123)">
    <i class="fas fa-trash mr-2"></i>Delete
</button>
```

---

## 📖 Detailed Usage

### Full-Page Loader

#### Show/Hide Programmatically

```javascript
// Show full-page loader
window.loading.showPageLoader('Processing your request...');

// Hide full-page loader
window.loading.hidePageLoader();
```

#### Complete Example (AJAX Request)

```javascript
async function enrollStudent(courseId) {
    window.loading.showPageLoader('Enrolling you in the course...');

    try {
        const response = await fetch('/api/enroll.php', {
            method: 'POST',
            body: JSON.stringify({ course_id: courseId })
        });

        const data = await response.json();

        if (data.success) {
            window.toast.success('Successfully enrolled!');
            window.location.href = '/dashboard.php';
        } else {
            window.toast.error(data.message);
            window.loading.hidePageLoader();
        }
    } catch (error) {
        window.toast.error('An error occurred');
        window.loading.hidePageLoader();
    }
}
```

---

### Button Loading States

#### Manual Control

```javascript
const button = document.getElementById('submitBtn');

// Show loading state
window.loading.showButtonLoader(button, 'Processing...');

// Hide loading state
window.loading.hideButtonLoader(button);
```

#### Real Example (Delete Action)

```javascript
function deleteLesson(lessonId, button) {
    if (!confirm('Are you sure you want to delete this lesson?')) {
        return;
    }

    window.loading.showButtonLoader(button, 'Deleting...');

    fetch(`/actions/delete-lesson.php?id=${lessonId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.toast.success('Lesson deleted successfully');
            // Remove the lesson row
            button.closest('tr').remove();
        } else {
            window.toast.error(data.message);
            window.loading.hideButtonLoader(button);
        }
    })
    .catch(error => {
        window.toast.error('Failed to delete lesson');
        window.loading.hideButtonLoader(button);
    });
}
```

#### PHP Usage

```php
<button onclick="deleteLesson(<?= $lesson['id'] ?>, this)"
        class="text-red-600 hover:text-red-700">
    <i class="fas fa-trash mr-1"></i>Delete
</button>
```

---

### Container Loading

#### Show Spinner in a Container

```javascript
const container = document.getElementById('courseList');

// Show loading spinner
window.loading.showContainerLoader(container, 'medium', 'Loading courses...');

// Hide and restore original content
window.loading.hideContainerLoader(container);
```

#### Real Example (Load Courses Dynamically)

```javascript
async function loadCourses(category) {
    const container = document.getElementById('courseList');

    window.loading.showContainerLoader(container, 'large', 'Loading courses...');

    try {
        const response = await fetch(`/api/courses.php?category=${category}`);
        const data = await response.json();

        // Manually build HTML
        container.innerHTML = data.courses.map(course => `
            <div class="course-card">
                <h3>${course.title}</h3>
                <p>${course.description}</p>
            </div>
        `).join('');

    } catch (error) {
        container.innerHTML = '<p class="text-red-600">Failed to load courses</p>';
    }
}
```

#### PHP HTML

```php
<div id="courseList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Initial content -->
</div>
```

---

### Skeleton Screens

Best for initial page loads. Shows placeholder content while real data loads.

#### Show Skeleton

```javascript
const container = document.getElementById('studentList');

// Show 5 skeleton list items
window.loading.showSkeletonLoader(container, 'list', 5);

// Or show 3 skeleton cards
window.loading.showSkeletonLoader(container, 'card', 3);

// Or show table skeleton
window.loading.showSkeletonLoader(tableBody, 'table', 10);
```

#### Skeleton Types

- `'card'` - For grid/card layouts
- `'list'` - For list items with avatar
- `'table'` - For table rows
- `'text'` - For text paragraphs

#### Real Example (Dashboard Stats)

```javascript
document.addEventListener('DOMContentLoaded', async function() {
    const statsContainer = document.getElementById('statsCards');

    // Show skeleton while loading
    window.loading.showSkeletonLoader(statsContainer, 'card', 4);

    const response = await fetch('/api/dashboard-stats.php');
    const stats = await response.json();

    // Replace skeleton with real content
    statsContainer.innerHTML = `
        <div class="stat-card">Total Students: ${stats.students}</div>
        <div class="stat-card">Active Courses: ${stats.courses}</div>
        <div class="stat-card">Completion Rate: ${stats.completion}%</div>
        <div class="stat-card">Revenue: ZMW ${stats.revenue}</div>
    `;
});
```

---

### AJAX Helper with Auto-Loading

Use the built-in `fetchWithLoading()` helper:

#### Example 1: With Button Loader

```javascript
const button = document.getElementById('submitBtn');

const response = await fetchWithLoading('/api/submit-assignment.php', {
    method: 'POST',
    body: formData,
    button: button,
    buttonText: 'Submitting...'
});

const result = await response.json();
```

#### Example 2: With Page Loader

```javascript
const response = await fetchWithLoading('/api/generate-report.php', {
    method: 'POST',
    showPageLoader: true,
    pageLoaderMessage: 'Generating your report...'
});

const blob = await response.blob();
// Download file
```

#### Example 3: With Container Loader

```javascript
const container = document.getElementById('results');

const response = await fetchWithLoading('/api/search.php?q=' + query, {
    container: container,
    containerSize: 'medium',
    containerMessage: 'Searching...'
});

const results = await response.json();
```

---

## 🎨 CSS Classes You Can Use

### Pre-built Loaders

```html
<!-- Small spinner -->
<div class="spinner-small"></div>

<!-- Medium spinner (default) -->
<div class="spinner-medium"></div>

<!-- Large spinner -->
<div class="spinner-large"></div>

<!-- Loading dots -->
<div class="loading-dots">
    <span></span>
    <span></span>
    <span></span>
</div>
```

### Skeleton Elements

```html
<!-- Text skeleton -->
<div class="skeleton skeleton-text"></div>
<div class="skeleton skeleton-text"></div>
<div class="skeleton skeleton-text" style="width: 60%"></div>

<!-- Avatar skeleton -->
<div class="skeleton skeleton-avatar"></div>

<!-- Card skeleton -->
<div class="skeleton skeleton-card"></div>
```

### Progress Bar

```html
<!-- Determinate progress -->
<div class="progress-bar">
    <div class="progress-bar-fill" style="width: 65%"></div>
</div>

<!-- Indeterminate progress -->
<div class="progress-bar">
    <div class="progress-bar-indeterminate"></div>
</div>
```

---

## 🔧 Common Use Cases

### Case 1: Course Enrollment

```javascript
function enrollInCourse(courseId, button) {
    window.loading.showButtonLoader(button, 'Enrolling...');

    fetch('/actions/enroll.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ course_id: courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.toast.success('Successfully enrolled in course!');
            window.location.href = '/my-courses.php';
        } else {
            window.toast.error(data.message);
            window.loading.hideButtonLoader(button);
        }
    });
}
```

### Case 2: Load More Pagination

```javascript
let currentPage = 1;

async function loadMoreCourses() {
    const button = document.getElementById('loadMoreBtn');
    const container = document.getElementById('courseGrid');

    window.loading.showButtonLoader(button, 'Loading more...');

    const response = await fetch(`/api/courses.php?page=${++currentPage}`);
    const data = await response.json();

    // Append new courses
    container.innerHTML += data.html;

    // Hide button if no more courses
    if (!data.hasMore) {
        button.style.display = 'none';
    } else {
        window.loading.hideButtonLoader(button);
    }
}
```

### Case 3: File Upload with Progress

```javascript
async function uploadAssignment(file) {
    window.loading.showPageLoader('Uploading assignment...');

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('/actions/upload-assignment.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        window.loading.hidePageLoader();

        if (data.success) {
            window.toast.success('Assignment uploaded successfully!');
        } else {
            window.toast.error(data.message);
        }
    } catch (error) {
        window.loading.hidePageLoader();
        window.toast.error('Upload failed. Please try again.');
    }
}
```

### Case 4: Search with Debounce

```javascript
let searchTimeout;

function searchCourses(query) {
    clearTimeout(searchTimeout);

    const resultsContainer = document.getElementById('searchResults');

    if (query.length < 2) {
        resultsContainer.innerHTML = '';
        return;
    }

    searchTimeout = setTimeout(async () => {
        window.loading.showContainerLoader(resultsContainer, 'small', 'Searching...');

        const response = await fetch(`/api/search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();

        resultsContainer.innerHTML = results.map(course => `
            <a href="/course.php?id=${course.id}" class="search-result-item">
                ${course.title}
            </a>
        `).join('');

    }, 300); // Wait 300ms after user stops typing
}
```

---

## ✅ Best Practices

### DO ✓

1. **Always hide loaders in finally blocks**
   ```javascript
   try {
       window.loading.showPageLoader();
       await doSomething();
   } finally {
       window.loading.hidePageLoader();
   }
   ```

2. **Use descriptive loading text**
   ```javascript
   // Good
   window.loading.showButtonLoader(btn, 'Submitting assignment...');

   // Bad
   window.loading.showButtonLoader(btn, 'Loading...');
   ```

3. **Use skeleton screens for initial page loads**
   ```javascript
   // On page load
   window.loading.showSkeletonLoader(container, 'card', 6);
   ```

4. **Use button loaders for user actions**
   ```javascript
   // When user clicks submit
   window.loading.showButtonLoader(submitBtn, 'Saving...');
   ```

### DON'T ✗

1. **Don't forget to hide loaders on errors**
   ```javascript
   // Bad - loader stays forever
   fetch('/api')
       .then(data => console.log(data));

   // Good - loader hidden on error
   fetch('/api')
       .then(data => console.log(data))
       .finally(() => window.loading.hidePageLoader());
   ```

2. **Don't show multiple page loaders simultaneously**
   ```javascript
   // Bad
   window.loading.showPageLoader('Loading A...');
   window.loading.showPageLoader('Loading B...'); // Overwrites first

   // Good - use container loaders instead
   window.loading.showContainerLoader(containerA, 'medium', 'Loading A...');
   window.loading.showContainerLoader(containerB, 'medium', 'Loading B...');
   ```

3. **Don't use page loaders for quick operations (<500ms)**
   ```javascript
   // Bad - too quick, annoying flicker
   window.loading.showPageLoader();
   await quickOperation(); // 100ms
   window.loading.hidePageLoader();

   // Good - use inline or button loader
   window.loading.showInlineLoader(element);
   ```

---

## 🎯 Quick Reference

| Scenario | Method | Example |
|----------|--------|---------|
| Form submission | `showButtonLoader(button, text)` | Submit, Save, Update buttons |
| Page navigation | `showPageLoader(message)` | Redirecting, loading new page |
| Load section | `showContainerLoader(el, size, msg)` | Load courses, students, reports |
| Initial load | `showSkeletonLoader(el, type, count)` | Dashboard, course list |
| File upload | `showPageLoader(message)` | "Uploading file..." |
| Delete action | `showButtonLoader(button, text)` | "Deleting..." |
| Search | `showContainerLoader(results, 'small')` | Live search results |
| Inline action | `showInlineLoader(element)` | Quick toggles, marks |

---

## 🐛 Troubleshooting

### Loader Doesn't Appear

**Problem:** Called `showPageLoader()` but nothing shows.

**Solutions:**
1. Check browser console for JavaScript errors
2. Verify `loading.css` and `loading.js` are loaded:
   ```javascript
   console.log(window.loading); // Should not be undefined
   ```
3. Make sure files are in correct paths:
   - `/public/assets/css/loading.css`
   - `/public/assets/js/loading.js`

### Loader Stays Forever

**Problem:** Loader doesn't disappear after operation completes.

**Solutions:**
1. Always use try/catch/finally:
   ```javascript
   try {
       await operation();
   } finally {
       window.loading.hidePageLoader();
   }
   ```

2. Use emergency cleanup:
   ```javascript
   // Clear all active loaders
   window.loading.clearAllLoaders();
   ```

### Button Not Clickable After Loading

**Problem:** Button stays disabled after hiding loader.

**Solution:** Make sure you're calling `hideButtonLoader()`:
```javascript
window.loading.showButtonLoader(button);
// ... operation ...
window.loading.hideButtonLoader(button); // Important!
```

---

## 📚 Additional Resources

- **Toast Notifications:** See `TOAST_NOTIFICATIONS_GUIDE.md` (if exists)
- **Form Validation:** Check validation.js documentation
- **AJAX Best Practices:** Refer to main.js

---

**Last Updated:** 2025-11-30
**Version:** 1.0
**Status:** Ready for Use
