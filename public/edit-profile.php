<?php
/**
 * Edutrack Computer Training College
 * Edit Profile Page - Finalized
 */

require_once '../src/bootstrap.php';

// 1. Authentication Check
if (!isLoggedIn()) {
    redirect('login.php');
    exit;
}

$user = User::current();
$db = Database::getInstance();

// Initialize view variables
$errors = [];
$success_msg = '';

// 2. Handle POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Security: CSRF Check ---
    if (!verifyCsrfToken()) {
        flash('error', 'Security token expired. Please refresh.', 'error');
        redirect(url('edit-profile.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ==========================================
    // ACTION: UPLOAD AVATAR
    // ==========================================
    if ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please select an image to upload.';
        } else {
            $file = $_FILES['avatar'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $errors[] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed.';
            } elseif ($file['size'] > $maxFileSize) {
                $errors[] = 'File is too large. Maximum size is 2MB.';
            } else {
                try {
                    $result = $user->uploadAvatar($file);
                    if ($result['success']) {
                        flash('success', 'Avatar updated successfully!', 'success');
                        redirect(url('edit-profile.php'));
                        exit;
                    } else {
                        $errors[] = $result['message'];
                    }
                } catch (Exception $e) {
                    error_log("Avatar Upload Error: " . $e->getMessage());
                    $errors[] = 'An error occurred while uploading the image.';
                }
            }
        }
    }

    // ==========================================
    // ACTION: DELETE AVATAR
    // ==========================================
    elseif ($action === 'delete_avatar') {
        try {
            $user->deleteAvatar();
            flash('success', 'Avatar removed successfully!', 'success');
            redirect(url('edit-profile.php'));
            exit;
        } catch (Exception $e) {
            error_log("Avatar Delete Error: " . $e->getMessage());
            $errors[] = 'Could not remove avatar.';
        }
    }

    // ==========================================
    // ACTION: UPDATE PROFILE
    // ==========================================
    elseif ($action === 'update_profile') {
        
        $raw_phone = $_POST['phone'] ?? '';
        $clean_phone = preg_replace('/[^0-9+]/', '', $raw_phone);

        // Map POST data to Database Columns
        $data = [
            // Users Table Fields
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            
            // User Profiles Table Fields
            'phone'      => $clean_phone,
            'bio'        => trim(strip_tags($_POST['bio'] ?? '')),
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender'     => $_POST['gender'] ?? null,
            'address'    => trim($_POST['address'] ?? ''),
            'city'       => trim($_POST['city'] ?? ''),
            'province'   => trim($_POST['province'] ?? ''),
            'country'    => trim($_POST['country'] ?? ''),      // Added Missing Field
            'postal_code'=> trim($_POST['postal_code'] ?? ''),  // Added Missing Field
            'nrc_number' => trim($_POST['nrc_number'] ?? ''),
            'education_level' => trim($_POST['education_level'] ?? ''),
            'occupation' => trim($_POST['occupation'] ?? ''),
            'linkedin_url' => filter_var($_POST['linkedin_url'] ?? '', FILTER_VALIDATE_URL) ?: '',
            'facebook_url' => filter_var($_POST['facebook_url'] ?? '', FILTER_VALIDATE_URL) ?: '',
            'twitter_url'  => filter_var($_POST['twitter_url'] ?? '', FILTER_VALIDATE_URL) ?: ''
        ];

        // Validation
        $validationRules = [
            'first_name' => 'required|min:2|max:100',
            'last_name'  => 'required|min:2|max:100',
            'nrc_number' => 'max:20', // Ensure it fits DB
        ];

        if (!empty($data['phone'])) $validationRules['phone'] = 'min:10|max:15';

        $validation = validate($data, $validationRules);
        if (!$validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }

        // Password Handling
        $new_password = $_POST['new_password'] ?? '';
        $passwordChangeRequested = !empty($new_password);
        $current_password = $_POST['current_password'] ?? '';

        if ($passwordChangeRequested) {
            $confirm_password = $_POST['confirm_password'] ?? '';
            if (empty($current_password)) {
                $errors[] = 'Current password is required to set a new password.';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'New passwords do not match.';
            } else {
                if (!verifyPassword($current_password, $user->password_hash)) {
                    $errors[] = 'The current password provided is incorrect.';
                }
                $passCheck = validatePasswordStrength($new_password);
                if (!$passCheck['valid']) {
                    $errors = array_merge($errors, $passCheck['errors']);
                }
            }
        }

        // Database Update
        if (empty($errors)) {
            try {
                $db->beginTransaction();

                // IMPORTANT: Your User model update() method must handle splitting 
                // first_name/last_name (users table) vs the rest (user_profiles table)
                if (!$user->update($data)) {
                    throw new Exception("Failed to update profile record.");
                }

                if ($passwordChangeRequested) {
                    if (!$user->updatePassword($new_password)) {
                        throw new Exception("Failed to update password.");
                    }
                }

                $db->commit();
                $_SESSION['user_first_name'] = $data['first_name'];
                $_SESSION['user_last_name'] = $data['last_name'];

                flash('success', 'Profile settings updated successfully!', 'success');
                redirect(url('edit-profile.php'));
                exit;

            } catch (Exception $e) {
                $db->rollBack();
                error_log("Profile Update Error: " . $e->getMessage());
                $errors[] = 'System error: Could not save changes.';
            }
        }
    }
}

$page_title = "Edit Profile - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
                <p class="text-gray-600 mt-1">Manage your personal information and settings</p>
            </div>
            <a href="<?= url('profile.php') ?>" class="text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Profile
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="mb-6"><?php displayValidationErrors($errors); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                <?php unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Avatar Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Profile Picture</h2>
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                <img src="<?= htmlspecialchars($user->getAvatarUrl()) ?>" 
                     alt="Avatar" class="w-24 h-24 rounded-full border-4 border-gray-200 object-cover" id="avatar-preview">
                
                <div class="flex-1 text-center sm:text-left">
                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="upload_avatar">
                        <div>
                            <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" class="btn-primary px-4 py-2 rounded-md text-sm"><i class="fas fa-upload mr-2"></i>Upload</button>
                            <?php if ($user->avatar): ?>
                            <button type="button" onclick="confirmAvatarDelete()" class="px-4 py-2 border border-red-500 text-red-600 rounded-md hover:bg-red-50 text-sm"><i class="fas fa-trash mr-2"></i>Remove</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Form -->
        <form method="POST" action="" class="space-y-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" name="first_name" value="<?= sanitize($user->first_name) ?>" required class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" name="last_name" value="<?= sanitize($user->last_name) ?>" required class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" value="<?= sanitize($user->email) ?>" disabled class="block w-full border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" value="<?= sanitize($user->phone ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= sanitize($user->date_of_birth ?? '') ?>" max="<?= date('Y-m-d') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select name="gender" class="form-select block w-full border-gray-300 rounded-md">
                            <option value="">Select Gender</option>
                            <option value="male" <?= $user->gender === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $user->gender === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NRC Number</label>
                        <input type="text" name="nrc_number" value="<?= sanitize($user->nrc_number ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Education Level</label>
                        <select name="education_level" class="form-select block w-full border-gray-300 rounded-md">
                            <option value="">Select Level</option>
                            <?php foreach(['Grade 12', 'Certificate', 'Diploma', "Bachelor's Degree", "Master's Degree", 'PhD'] as $lvl): ?>
                                <option value="<?= $lvl ?>" <?= $user->education_level === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                        <input type="text" name="occupation" value="<?= sanitize($user->occupation ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" rows="4" class="form-textarea block w-full border-gray-300 rounded-md"><?= sanitize($user->bio ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Location (Updated with Country & Postal Code) -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Location</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <input type="text" name="address" value="<?= sanitize($user->address ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <input type="text" name="city" value="<?= sanitize($user->city ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <select name="province" class="form-select block w-full border-gray-300 rounded-md">
                            <option value="">Select Province</option>
                            <?php foreach(['Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka', 'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'] as $prov): ?>
                                <option value="<?= $prov ?>" <?= $user->province === $prov ? 'selected' : '' ?>><?= $prov ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Added Missing Fields -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                        <input type="text" name="postal_code" value="<?= sanitize($user->postal_code ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <input type="text" name="country" value="<?= sanitize($user->country ?? 'Zambia') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Social Links</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="<?= sanitize($user->linkedin_url ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook URL</label>
                        <input type="url" name="facebook_url" value="<?= sanitize($user->facebook_url ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Twitter URL</label>
                        <input type="url" name="twitter_url" value="<?= sanitize($user->twitter_url ?? '') ?>" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Change Password</h2>
                <div class="max-w-md space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="new_password" autocomplete="new-password" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" autocomplete="new-password" class="form-input block w-full border-gray-300 rounded-md">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end pb-8">
                <button type="submit" class="btn-primary px-8 py-3 rounded-md font-medium shadow-lg hover:shadow-xl transition-all">Save All Changes</button>
            </div>
        </form>
    </div>
</div>

<form id="delete-avatar-form" method="POST" action="" style="display: none;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="delete_avatar">
</form>

<script>
document.getElementById('avatar-input').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('File is too large. Maximum size is 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

function confirmAvatarDelete() {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        document.getElementById('delete-avatar-form').submit();
    }
}
</script>

<?php require_once '../src/templates/footer.php'; ?>