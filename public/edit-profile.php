<?php
/**
 * Edutrack Computer Training College - Edit Profile Page
 */

require_once '../src/bootstrap.php';

if (!isLoggedIn()) {
    redirect('login.php');
    exit;
}

$user = User::current();
$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlashMessage('Security token expired. Please refresh the page.', 'error');
        redirect(url('edit-profile.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    // Upload Avatar
    if ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please select an image to upload.';
        } else {
            $file = $_FILES['avatar'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'File upload failed with error code: ' . $file['error'];
            } else {
                try {
                    $result = $user->uploadAvatar($file);
                    if ($result['success']) {
                        setFlashMessage('Avatar updated successfully!', 'success');
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

    // Delete Avatar
    elseif ($action === 'delete_avatar') {
        try {
            if ($user->deleteAvatar()) {
                setFlashMessage('Avatar removed successfully!', 'success');
            } else {
                setFlashMessage('Could not remove avatar.', 'error');
            }
            redirect(url('edit-profile.php'));
            exit;
        } catch (Exception $e) {
            error_log("Avatar Delete Error: " . $e->getMessage());
            $errors[] = 'System error occurred.';
        }
    }

    // Update Profile
    elseif ($action === 'update_profile') {
        $raw_phone = $_POST['phone'] ?? '';
        $clean_phone = preg_replace('/[^0-9+]/', '', $raw_phone);

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'phone'      => $clean_phone,
            'bio'        => trim(strip_tags($_POST['bio'] ?? '')),
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender'     => $_POST['gender'] ?? null,
            'address'    => trim($_POST['address'] ?? ''),
            'city'       => trim($_POST['city'] ?? ''),
            'province'   => trim($_POST['province'] ?? ''),
            'country'    => trim($_POST['country'] ?? ''),
            'postal_code'=> trim($_POST['postal_code'] ?? ''),
            'nrc_number' => trim($_POST['nrc_number'] ?? ''),
            'education_level' => trim($_POST['education_level'] ?? ''),
            'occupation' => trim($_POST['occupation'] ?? ''),
            'linkedin_url' => filter_var($_POST['linkedin_url'] ?? '', FILTER_VALIDATE_URL) ?: '',
            'facebook_url' => filter_var($_POST['facebook_url'] ?? '', FILTER_VALIDATE_URL) ?: '',
            'twitter_url'  => filter_var($_POST['twitter_url'] ?? '', FILTER_VALIDATE_URL) ?: ''
        ];

        // Validation
        if (empty($data['first_name'])) $errors[] = "First name is required.";
        if (empty($data['last_name'])) $errors[] = "Last name is required.";
        
        if (!empty($data['phone']) && strlen($data['phone']) < 10) {
            $errors[] = "Phone number is too short.";
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
                $db = Database::getInstance();
                $currentHash = $db->fetchColumn("SELECT password_hash FROM users WHERE id = ?", [$user->getId()]);
                
                if (!password_verify($current_password, $currentHash)) {
                    $errors[] = 'The current password provided is incorrect.';
                }
                
                if (strlen($new_password) < 6) {
                    $errors[] = 'New password must be at least 6 characters.';
                }
            }
        }

        // Execute Update if No Errors
        if (empty($errors)) {
            try {
                if (!$user->update($data)) {
                    throw new Exception("Failed to update profile record.");
                }

                if ($passwordChangeRequested) {
                    if (!$user->updatePassword($new_password)) {
                        throw new Exception("Failed to update password.");
                    }
                }

                $_SESSION['user_first_name'] = $data['first_name'];
                $_SESSION['user_last_name'] = $data['last_name'];

                setFlashMessage('Profile settings updated successfully!', 'success');
                redirect(url('edit-profile.php'));
                exit;

            } catch (Exception $e) {
                error_log("Edit Profile Error: " . $e->getMessage());
                $errors[] = 'System error: Could not save changes. Please try again.';
            }
        }
    }
}

$page_title = "Edit Profile - " . APP_NAME;
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
                <p class="text-gray-600 mt-1">Manage your personal information and settings</p>
            </div>
            <a href="<?= url('profile.php') ?>" class="mt-4 sm:mt-0 text-primary-600 hover:text-primary-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Profile
            </a>
        </div>
        
        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php 
        $flash = getFlashMessage();
        if (!$flash && isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        
        if ($flash && !empty($flash['message'])): 
            $flashClass = ($flash['type'] ?? 'info') === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 
                         (($flash['type'] ?? 'info') === 'error' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-blue-100 border-blue-500 text-blue-700');
        ?>
            <div class="mb-6 border-l-4 p-4 <?= $flashClass ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Avatar Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Profile Picture</h2>
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                <img src="<?= htmlspecialchars($user->getAvatarUrl()) ?>" 
                     alt="Avatar" class="w-24 h-24 rounded-full border-4 border-gray-200 object-cover" id="avatar-preview">
                
                <div class="flex-1 text-center sm:text-left">
                    <form method="POST" enctype="multipart/form-data" class="space-y-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="upload_avatar">
                        <div>
                            <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>
                        <div class="flex space-x-3 justify-center sm:justify-start">
                            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md text-sm hover:bg-primary-700 transition">
                                <i class="fas fa-upload mr-2"></i>Upload
                            </button>
                            <?php if ($user->avatar): ?>
                            <button type="button" onclick="confirmAvatarDelete()" class="px-4 py-2 border border-red-500 text-red-600 rounded-md hover:bg-red-50 text-sm transition">
                                <i class="fas fa-trash mr-2"></i>Remove
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">JPG, PNG, GIF or WEBP. Max size 2MB.</p>
                </div>
            </div>
        </div>
        
        <!-- Main Info Form -->
        <form method="POST" class="space-y-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="<?= sanitize($user->first_name) ?>" required 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="<?= sanitize($user->last_name) ?>" required 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="<?= sanitize($user->email) ?>" disabled 
                               class="w-full border-gray-300 rounded-md bg-gray-100 text-gray-500 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Contact support to change email.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="<?= sanitize($user->phone ?? '') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= sanitize($user->date_of_birth ?? '') ?>" max="<?= date('Y-m-d') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select name="gender" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($user->gender === 'Male' || $user->gender === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user->gender === 'Female' || $user->gender === 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($user->gender === 'Other' || $user->gender === 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NRC Number</label>
                        <input type="text" name="nrc_number" value="<?= sanitize($user->nrc_number ?? '') ?>" placeholder="e.g. 123456/10/1"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                        <select name="education_level" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Level</option>
                            <?php foreach(['Grade 12', 'Certificate', 'Diploma', "Bachelor's Degree", "Master's Degree", 'PhD'] as $lvl): ?>
                                <option value="<?= $lvl ?>" <?= $user->education_level === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                        <input type="text" name="occupation" value="<?= sanitize($user->occupation ?? '') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                        <textarea name="bio" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500" placeholder="Tell us a bit about yourself..."><?= sanitize($user->bio ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Location</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="address" value="<?= sanitize($user->address ?? '') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" value="<?= sanitize($user->city ?? '') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                        <select name="province" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Province</option>
                            <?php foreach(['Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka', 'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'] as $prov): ?>
                                <option value="<?= $prov ?>" <?= $user->province === $prov ? 'selected' : '' ?>><?= $prov ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                        <input type="text" name="postal_code" value="<?= sanitize($user->postal_code ?? '') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <input type="text" name="country" value="<?= sanitize($user->country ?? 'Zambia') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Social Links</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                        <div class="flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"><i class="fab fa-linkedin"></i></span>
                            <input type="url" name="linkedin_url" value="<?= sanitize($user->linkedin_url ?? '') ?>" placeholder="https://linkedin.com/in/..." class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                        <div class="flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"><i class="fab fa-facebook"></i></span>
                            <input type="url" name="facebook_url" value="<?= sanitize($user->facebook_url ?? '') ?>" placeholder="https://facebook.com/..." class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter URL</label>
                        <div class="flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"><i class="fab fa-twitter"></i></span>
                            <input type="url" name="twitter_url" value="<?= sanitize($user->twitter_url ?? '') ?>" placeholder="https://twitter.com/..." class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Change Password</h2>
                <div class="max-w-md space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password" autocomplete="new-password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-500 mt-1">Leave blank if you don't want to change it.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" autocomplete="new-password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end pb-8">
                <button type="submit" class="bg-primary-600 text-white px-8 py-3 rounded-md font-medium shadow-lg hover:bg-primary-700 hover:shadow-xl transition-all duration-200">
                    <i class="fas fa-save mr-2"></i>Save All Changes
                </button>
            </div>
        </form>
    </div>
</div>

<form id="delete-avatar-form" method="POST" style="display: none;">
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