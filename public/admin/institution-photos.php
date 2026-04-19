<?php
/**
 * Admin Institution Photos Management
 * Manage campus photos and hero slides
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/InstitutionPhoto.php';

$db = Database::getInstance();
$page_title = "Manage Institution Photos - Admin";

$action = $_GET['action'] ?? 'photos';
$message = '';
$error = '';

// Handle photo deletion
if (isset($_GET['delete_photo']) && is_numeric($_GET['delete_photo'])) {
    $photo = new InstitutionPhoto((int)$_GET['delete_photo']);
    if ($photo->getId()) {
        $photo->delete();
        $message = "Photo deleted successfully.";
    }
}

// Handle slide deletion
if (isset($_GET['delete_slide']) && is_numeric($_GET['delete_slide'])) {
    $slide = new HeroSlide((int)$_GET['delete_slide']);
    if ($slide->getId()) {
        $slide->delete();
        $message = "Hero slide deleted successfully.";
    }
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    $title = trim($_POST['photo_title'] ?? '');
    $description = trim($_POST['photo_description'] ?? '');
    $category = $_POST['photo_category'] ?? 'campus';
    $isFeatured = isset($_POST['photo_featured']) ? 1 : 0;
    
    if (empty($title)) {
        $error = "Photo title is required.";
    } elseif (empty($_FILES['photo_image']['name'])) {
        $error = "Please select an image to upload.";
    } else {
        $uploadDir = __DIR__ . '/../../public/uploads/institution/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['photo_image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['photo_image']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
        } elseif (move_uploaded_file($_FILES['photo_image']['tmp_name'], $targetPath)) {
            InstitutionPhoto::create([
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'image_path' => $fileName,
                'is_featured' => $isFeatured,
                'uploaded_by' => $_SESSION['user_id']
            ]);
            $message = "Photo uploaded successfully.";
        } else {
            $error = "Failed to upload image.";
        }
    }
}

// Handle hero slide creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_slide'])) {
    $slideId = $_POST['slide_id'] ?? null;
    $data = [
        'title' => trim($_POST['slide_title'] ?? ''),
        'subtitle' => trim($_POST['slide_subtitle'] ?? ''),
        'description' => trim($_POST['slide_description'] ?? ''),
        'cta_text' => trim($_POST['slide_cta_text'] ?? 'Get Started'),
        'cta_link' => trim($_POST['slide_cta_link'] ?? 'courses.php'),
        'secondary_cta_text' => trim($_POST['slide_secondary_cta_text'] ?? ''),
        'secondary_cta_link' => trim($_POST['slide_secondary_cta_link'] ?? ''),
        'is_active' => isset($_POST['slide_active']) ? 1 : 0,
        'display_order' => (int)($_POST['slide_order'] ?? 0)
    ];
    
    if (empty($data['title'])) {
        $error = "Slide title is required.";
    } else {
        // Handle image upload
        if (!empty($_FILES['slide_image']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/hero/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'hero_' . time() . '_' . basename($_FILES['slide_image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['slide_image']['tmp_name']);
            
            if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['slide_image']['tmp_name'], $targetPath)) {
                $data['image_path'] = $fileName;
                
                // Delete old image if updating
                if ($slideId) {
                    $oldSlide = new HeroSlide((int)$slideId);
                    if ($oldSlide->get('image_path')) {
                        $oldPath = $uploadDir . $oldSlide->get('image_path');
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                }
            }
        }
        
        if ($slideId) {
            $slide = new HeroSlide((int)$slideId);
            if ($slide->getId()) {
                $slide->update($data);
                $message = "Hero slide updated successfully.";
            }
        } else {
            $data['created_by'] = $_SESSION['user_id'];
            HeroSlide::create($data);
            $message = "Hero slide created successfully.";
        }
    }
}

// Get data for display
$photos = [];
$heroSlides = [];
try {
    $photos = InstitutionPhoto::getAll();
} catch (Throwable $e) {
    error_log("Admin photos error: " . $e->getMessage());
}
try {
    $heroSlides = HeroSlide::getAll();
} catch (Throwable $e) {
    error_log("Admin hero slides error: " . $e->getMessage());
}
$categories = InstitutionPhoto::getCategories();

// Get slide for editing
$editSlide = null;
if (isset($_GET['edit_slide']) && is_numeric($_GET['edit_slide'])) {
    $editSlide = new HeroSlide((int)$_GET['edit_slide']);
}

require_once '../../src/templates/admin-header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="flex">
        <?php require_once '../../src/templates/admin-sidebar.php'; ?>
        
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Institution Photos</h1>
                        <p class="text-gray-600 mt-1">Manage campus photos and homepage hero slides</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="?action=photos" class="px-4 py-2 rounded-lg font-medium transition <?= $action === 'photos' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                            <i class="fas fa-images mr-2"></i>Campus Photos
                        </a>
                        <a href="?action=hero" class="px-4 py-2 rounded-lg font-medium transition <?= $action === 'hero' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                            <i class="fas fa-desktop mr-2"></i>Hero Slides
                        </a>
                    </div>
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
                
                <?php if ($action === 'hero'): ?>
                <!-- Hero Slides Management -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Slide Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">
                                <?= $editSlide ? 'Edit Slide' : 'Add Hero Slide' ?>
                            </h2>
                            
                            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" name="slide_id" value="<?= $editSlide ? $editSlide->getId() : '' ?>">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Slide Title *</label>
                                    <input type="text" name="slide_title" required
                                           value="<?= $editSlide ? htmlspecialchars($editSlide->get('title')) : '' ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                           placeholder="e.g., Launch Your Tech Career">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" name="slide_subtitle"
                                           value="<?= $editSlide ? htmlspecialchars($editSlide->get('subtitle')) : '' ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                           placeholder="e.g., With Industry-Recognized Skills">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="slide_description" rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                              placeholder="Brief description for the slide"><?= $editSlide ? htmlspecialchars($editSlide->get('description')) : '' ?></textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">CTA Text</label>
                                        <input type="text" name="slide_cta_text"
                                               value="<?= $editSlide ? htmlspecialchars($editSlide->get('cta_text')) : 'Get Started' ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">CTA Link</label>
                                        <input type="text" name="slide_cta_link"
                                               value="<?= $editSlide ? htmlspecialchars($editSlide->get('cta_link')) : 'courses.php' ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Secondary CTA</label>
                                        <input type="text" name="slide_secondary_cta_text"
                                               value="<?= $editSlide ? htmlspecialchars($editSlide->get('secondary_cta_text')) : '' ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                               placeholder="e.g., Learn More">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Link</label>
                                        <input type="text" name="slide_secondary_cta_link"
                                               value="<?= $editSlide ? htmlspecialchars($editSlide->get('secondary_cta_link')) : '' ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Background Image</label>
                                    <?php if ($editSlide && $editSlide->get('image_path')): ?>
                                    <div class="mb-2">
                                        <img src="/uploads/hero/<?= htmlspecialchars($editSlide->get('image_path')) ?>" 
                                             alt="Current" class="h-32 object-cover rounded-lg">
                                        <p class="text-xs text-gray-500 mt-1">Current image. Upload new to replace.</p>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" name="slide_image" accept="image/*" 
                                           <?= $editSlide ? '' : 'required' ?>
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                    <p class="text-xs text-gray-500 mt-1">Recommended: 1920x700 pixels</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                        <input type="number" name="slide_order" min="0"
                                               value="<?= $editSlide ? $editSlide->get('display_order') : '0' ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div class="flex items-center">
                                        <label class="flex items-center cursor-pointer mt-6">
                                            <input type="checkbox" name="slide_active" value="1"
                                                   <?= ($editSlide && $editSlide->get('is_active')) || !$editSlide ? 'checked' : '' ?>
                                                   class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500">
                                            <span class="ml-2 text-gray-700">Active</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" name="save_slide" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition">
                                        <i class="fas fa-save mr-2"></i><?= $editSlide ? 'Update' : 'Create' ?> Slide
                                    </button>
                                    <?php if ($editSlide): ?>
                                    <a href="?action=hero" class="px-6 py-3 border border-gray-300 rounded-lg font-medium hover:bg-gray-50 transition">
                                        Cancel
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Slides List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-xl font-bold text-gray-900">Hero Slides</h2>
                                <p class="text-sm text-gray-600">These slides appear in the homepage carousel</p>
                            </div>
                            
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($heroSlides as $slide): ?>
                                <div class="p-6 flex gap-4">
                                    <div class="w-48 h-28 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                        <?php if ($slide['image_path']): ?>
                                        <img src="/uploads/hero/<?= htmlspecialchars($slide['image_path']) ?>" 
                                             alt="" class="w-full h-full object-cover">
                                        <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image text-2xl"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($slide['title']) ?></h3>
                                                <p class="text-sm text-gray-600"><?= htmlspecialchars($slide['subtitle'] ?? '') ?></p>
                                                <div class="flex gap-3 mt-2 text-sm">
                                                    <span class="text-gray-500">Order: <?= $slide['display_order'] ?></span>
                                                    <?php if ($slide['is_active']): ?>
                                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Active</span>
                                                    <?php else: ?>
                                                    <span class="text-gray-400"><i class="fas fa-circle mr-1"></i>Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="?action=hero&edit_slide=<?= $slide['id'] ?>" 
                                                   class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-200 transition"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=hero&delete_slide=<?= $slide['id'] ?>" 
                                                   onclick="return confirm('Delete this slide?')"
                                                   class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition"
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Campus Photos Management -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Upload Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">Upload New Photo</h2>
                            
                            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo Title *</label>
                                    <input type="text" name="photo_title" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                           placeholder="e.g., Main Computer Lab">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="photo_description" rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                              placeholder="Brief description of the photo"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="photo_category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                        <?php foreach ($categories as $key => $label): ?>
                                        <option value="<?= $key ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Image *</label>
                                    <input type="file" name="photo_image" accept="image/*" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" name="photo_featured" id="photo_featured" class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500">
                                    <label for="photo_featured" class="ml-2 text-gray-700">Feature on Campus page</label>
                                </div>
                                
                                <button type="submit" name="upload_photo" class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition">
                                    <i class="fas fa-upload mr-2"></i>Upload Photo
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Photos Grid -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-900">Campus Photos</h2>
                                <span class="text-sm text-gray-600"><?= count($photos) ?> photos</span>
                            </div>
                            
                            <?php if (!empty($photos)): ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <?php foreach ($photos as $photoData): 
                                    $photoId = $photoData['id'] ?? 0;
                                    $imageUrl = !empty($photoData['image_path']) ? '/uploads/institution/' . $photoData['image_path'] : '';
                                    $title = $photoData['title'] ?? '';
                                    $category = $photoData['category'] ?? 'campus';
                                    $isFeatured = $photoData['is_featured'] ?? 0;
                                ?>
                                <div class="group relative rounded-lg overflow-hidden">
                                    <img src="<?= $imageUrl ?>" 
                                         alt="<?= htmlspecialchars($title) ?>"
                                         class="w-full h-40 object-cover">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <div class="flex gap-2">
                                            <a href="?delete_photo=<?= $photoId ?>" 
                                               onclick="return confirm('Delete this photo?')"
                                               class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition"
                                               title="Delete">
                                                <i class="fas fa-trash text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <?php if ($isFeatured): ?>
                                    <span class="absolute top-2 left-2 bg-yellow-500 text-gray-900 text-xs px-2 py-1 rounded-full font-medium">
                                        <i class="fas fa-star mr-1"></i>Featured
                                    </span>
                                    <?php endif; ?>
                                    <div class="absolute bottom-0 left-0 right-0 bg-black/70 p-3">
                                        <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($title) ?></p>
                                        <p class="text-gray-300 text-xs"><?= $categories[$category] ?? 'Campus' ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-images text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">No photos uploaded yet.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
