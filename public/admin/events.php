<?php
/**
 * Admin Events Management
 * Manage recent events, upload photos, and add stories
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Event.php';

$db = Database::getInstance();
$page_title = "Manage Events - Admin";

// Handle Actions
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Delete event
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event = new Event((int)$_GET['delete']);
    if ($event->getId()) {
        $event->delete();
        $message = "Event deleted successfully.";
    }
}

// Delete image
if (isset($_GET['delete_image']) && is_numeric($_GET['delete_image'])) {
    Event::deleteImage((int)$_GET['delete_image']);
    $message = "Image deleted successfully.";
}

// Create/Update event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    $eventId = $_POST['event_id'] ?? null;
    
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'summary' => trim($_POST['summary'] ?? ''),
        'story' => trim($_POST['story'] ?? ''),
        'event_date' => $_POST['event_date'] ?: null,
        'location' => trim($_POST['location'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'status' => $_POST['status'] ?? 'draft'
    ];
    
    // Validate
    if (empty($data['title'])) {
        $error = "Event title is required.";
    } else {
        // Handle file uploads
        $uploadedImages = [];
        $coverImage = null;
        
        if (!empty($_FILES['event_images']['name'][0])) {
            $uploadDir = __DIR__ . '/../../public/uploads/events/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($_FILES['event_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['event_images']['error'][$index] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . $index . '_' . basename($_FILES['event_images']['name'][$index]);
                    $targetPath = $uploadDir . $fileName;
                    
                    // Validate image
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = mime_content_type($tmpName);
                    
                    if (in_array($fileType, $allowedTypes)) {
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $uploadedImages[] = [
                                'path' => $fileName,
                                'caption' => $_POST['image_captions'][$index] ?? '',
                                'order' => $index
                            ];
                            
                            // First image becomes cover if no cover set
                            if ($index === 0 && empty($_POST['existing_cover'])) {
                                $coverImage = $fileName;
                            }
                        }
                    }
                }
            }
        }
        
        // Handle cover image selection from existing images
        if (!empty($_POST['cover_image'])) {
            $coverImage = $_POST['cover_image'];
        }
        
        $data['cover_image'] = $coverImage;
        $data['images'] = $uploadedImages;
        
        if ($eventId) {
            // Update
            $event = new Event((int)$eventId);
            if ($event->getId()) {
                $event->update($data);
                $message = "Event updated successfully.";
            }
        } else {
            // Create
            $data['created_by'] = $_SESSION['user_id'];
            $event = Event::create($data);
            $message = "Event created successfully.";
        }
    }
}

// Get event for editing
$editEvent = null;
$editImages = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editEvent = new Event((int)$_GET['edit']);
    if ($editEvent->getId()) {
        $editImages = $editEvent->getImages();
    }
}

// Get all events
$events = Event::getAll(['limit' => 50]);

require_once '../../src/templates/admin-header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <?php require_once '../../src/templates/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Manage Events</h1>
                        <p class="text-gray-600 mt-1">Create and manage recent events with photos and stories</p>
                    </div>
                    <?php if ($action === 'list'): ?>
                    <a href="?action=create" class="btn-primary px-6 py-3 rounded-lg font-medium inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add New Event
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                    <span class="text-green-800"><?= htmlspecialchars($message) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                    <span class="text-red-800"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'create' || $action === 'edit'): ?>
                <!-- Event Form -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">
                            <?= $editEvent ? 'Edit Event' : 'Create New Event' ?>
                        </h2>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-6">
                        <?= csrfField() ?>
                        <input type="hidden" name="event_id" value="<?= $editEvent ? $editEvent->getId() : '' ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Title -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Event Title *</label>
                                <input type="text" name="title" required
                                       value="<?= $editEvent ? htmlspecialchars($editEvent->get('title')) : '' ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="e.g., 2024 Graduation Ceremony">
                            </div>
                            
                            <!-- Event Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
                                <input type="date" name="event_date"
                                       value="<?= $editEvent ? $editEvent->get('event_date') : '' ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            
                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <input type="text" name="location"
                                       value="<?= $editEvent ? htmlspecialchars($editEvent->get('location')) : '' ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="e.g., Edutrack Main Campus, Kalomo">
                            </div>
                            
                            <!-- Summary -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Short Summary</label>
                                <textarea name="summary" rows="2"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                          placeholder="Brief summary for event listings (2-3 sentences)"><?= $editEvent ? htmlspecialchars($editEvent->get('summary')) : '' ?></textarea>
                            </div>
                            
                            <!-- Full Story -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Story</label>
                                <textarea name="story" rows="8"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                          placeholder="Write the full story about this event..."><?= $editEvent ? htmlspecialchars($editEvent->get('story')) : '' ?></textarea>
                                <p class="text-sm text-gray-500 mt-1">Describe what happened, who attended, key moments, and outcomes.</p>
                            </div>
                            
                            <!-- Existing Images -->
                            <?php if (!empty($editImages)): ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Current Images</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <?php foreach ($editImages as $image): ?>
                                    <div class="relative group">
                                        <img src="/uploads/events/<?= htmlspecialchars($image['image_path']) ?>" 
                                             alt="Event image" class="w-full h-32 object-cover rounded-lg">
                                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition rounded-lg flex items-center justify-center">
                                            <a href="?delete_image=<?= $image['id'] ?>&edit=<?= $editEvent->getId() ?>" 
                                               onclick="return confirm('Delete this image?')"
                                               class="text-white hover:text-red-400">
                                                <i class="fas fa-trash text-xl"></i>
                                            </a>
                                        </div>
                                        <?php if ($editEvent->get('cover_image') === $image['image_path']): ?>
                                        <span class="absolute top-2 left-2 bg-primary-600 text-white text-xs px-2 py-1 rounded">Cover</span>
                                        <?php endif; ?>
                                        <input type="radio" name="cover_image" value="<?= $image['image_path'] ?>" 
                                               <?= $editEvent->get('cover_image') === $image['image_path'] ? 'checked' : '' ?>
                                               class="absolute top-2 right-2 w-5 h-5"
                                               title="Set as cover image">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="existing_cover" value="<?= $editEvent->get('cover_image') ?>">
                                <p class="text-sm text-gray-500 mt-2">Click the radio button on an image to set it as the cover photo.</p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Upload New Images -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Images</label>
                                <div id="image-upload-container" class="space-y-3">
                                    <div class="image-upload-row flex gap-3 items-start">
                                        <input type="file" name="event_images[]" accept="image/*"
                                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                        <input type="text" name="image_captions[]" placeholder="Image caption (optional)"
                                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                        <button type="button" onclick="addImageRow()" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">Upload photos from the event. First image will be used as cover if none selected.</p>
                            </div>
                            
                            <!-- Settings -->
                            <div class="md:col-span-2 flex gap-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_featured" value="1"
                                           <?= ($editEvent && $editEvent->get('is_featured')) ? 'checked' : '' ?>
                                           class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-gray-700">Feature this event on homepage</span>
                                </label>
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                    <option value="draft" <?= ($editEvent && $editEvent->get('status') === 'draft') ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= ($editEvent && $editEvent->get('status') === 'published') ? 'selected' : '' ?>>Published</option>
                                    <option value="archived" <?= ($editEvent && $editEvent->get('status') === 'archived') ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex gap-4 pt-6 border-t border-gray-200">
                            <button type="submit" class="btn-primary px-8 py-3 rounded-lg font-medium">
                                <i class="fas fa-save mr-2"></i> <?= $editEvent ? 'Update Event' : 'Create Event' ?>
                            </button>
                            <a href="?action=list" class="px-8 py-3 border border-gray-300 rounded-lg font-medium hover:bg-gray-50">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
                <script>
                function addImageRow() {
                    const container = document.getElementById('image-upload-container');
                    const row = document.createElement('div');
                    row.className = 'image-upload-row flex gap-3 items-start';
                    row.innerHTML = `
                        <input type="file" name="event_images[]" accept="image/*"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <input type="text" name="image_captions[]" placeholder="Image caption (optional)"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <button type="button" onclick="this.parentElement.remove()" class="px-4 py-3 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    container.appendChild(row);
                }
                </script>
                
                <?php else: ?>
                <!-- Events List -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex gap-4">
                            <input type="text" placeholder="Search events..." 
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                   onkeyup="filterEvents(this.value)">
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Event</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Images</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="events-table-body" class="divide-y divide-gray-200">
                                <?php foreach ($events as $event): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php if ($event['cover_image']): ?>
                                            <img src="/uploads/events/<?= htmlspecialchars($event['cover_image']) ?>" 
                                                 alt="" class="w-12 h-12 object-cover rounded-lg">
                                            <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-medium text-gray-900"><?= htmlspecialchars($event['title']) ?></div>
                                                <?php if ($event['is_featured']): ?>
                                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Featured</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= $event['event_date'] ? date('M j, Y', strtotime($event['event_date'])) : 'Not set' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= $event['image_count'] ?> images
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        $statusColors = [
                                            'published' => 'bg-green-100 text-green-800',
                                            'draft' => 'bg-yellow-100 text-yellow-800',
                                            'archived' => 'bg-gray-100 text-gray-800'
                                        ];
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$event['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                            <?= ucfirst($event['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="/event.php?slug=<?= $event['slug'] ?>" target="_blank"
                                               class="text-gray-600 hover:text-primary-600 px-2" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&edit=<?= $event['id'] ?>"
                                               class="text-blue-600 hover:text-blue-800 px-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $event['id'] ?>" 
                                               onclick="return confirm('Are you sure you want to delete this event?')"
                                               class="text-red-600 hover:text-red-800 px-2" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <script>
                function filterEvents(searchTerm) {
                    const rows = document.querySelectorAll('#events-table-body tr');
                    const term = searchTerm.toLowerCase();
                    
                    rows.forEach(row => {
                        const title = row.querySelector('td:first-child').textContent.toLowerCase();
                        row.style.display = title.includes(term) ? '' : 'none';
                    });
                }
                </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
