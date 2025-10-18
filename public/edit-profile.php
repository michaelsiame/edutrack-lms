<?php
/**
 * Edutrack Computer Training College
 * Edit Profile Page
 */

require_once '../src/middleware/authenticate.php';
require_once '../src/classes/User.php';

// Get current user
$user = User::current();

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        // Update Profile Information
        if ($action === 'update_profile') {
            $data = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'gender' => $_POST['gender'] ?? null,
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'province' => trim($_POST['province'] ?? ''),
                'nrc_number' => trim($_POST['nrc_number'] ?? ''),
                'education_level' => trim($_POST['education_level'] ?? ''),
                'occupation' => trim($_POST['occupation'] ?? ''),
                'linkedin_url' => trim($_POST['linkedin_url'] ?? ''),
                'facebook_url' => trim($_POST['facebook_url'] ?? ''),
                'twitter_url' => trim($_POST['twitter_url'] ?? '')
            ];
            
            // Validate
            $validation = validate($data, [
                'first_name' => 'required|min:2|max:100',
                'last_name' => 'required|min:2|max:100',
                'phone' => 'phone'
            ]);
            
            if (!$validation['valid']) {
                $errors = $validation['errors'];
            } else {
                if ($user->update($data)) {
                    // Update session
                    $_SESSION['user_first_name'] = $data['first_name'];
                    $_SESSION['user_last_name'] = $data['last_name'];
                    
                    $success = true;
                    flash('success', 'Profile updated successfully!', 'success');
                } else {
                    $errors[] = 'Failed to update profile. Please try again.';
                }
            }
        }
        
        // Upload Avatar
        elseif ($action === 'upload_avatar') {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $result = $user->uploadAvatar($_FILES['avatar']);
                if ($result['success']) {
                    $success = true;
                    flash('success', 'Avatar uploaded successfully!', 'success');
                    redirect(url('edit-profile.php'));
                } else {
                    $errors[] = $result['message'];
                }
            } else {
                $errors[] = 'Please select an image to upload.';
            }
        }
        
        // Delete Avatar
        elseif ($action === 'delete_avatar') {
            $user->deleteAvatar();
            $success = true;
            flash('success', 'Avatar deleted successfully!', 'success');
            redirect(url('edit-profile.php'));
        }
        
        // Change Password
        elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $errors[] = 'All password fields are required.';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'New passwords do not match.';
            } elseif (!verifyPassword($current_password, $user->password_hash)) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $passwordCheck = validatePasswordStrength($new_password);
                if (!$passwordCheck['valid']) {
                    $errors = $passwordCheck['errors'];
                } else {
                    if ($user->updatePassword($new_password)) {
                        $success = true;
                        flash('success', 'Password changed successfully!', 'success');
                    } else {
                        $errors[] = 'Failed to change password. Please try again.';
                    }
                }
            }
        }
    }
}

$page_title = "Edit Profile - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-600 mt-1">Manage your personal information and settings</p>
                </div>
                <a href="<?= url('profile.php') ?>" class="text-primary-600 hover:text-primary-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Profile
                </a>
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="mb-6">
                <?php displayValidationErrors($errors); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-6">
                <?php successAlert('Changes saved successfully!'); ?>
            </div>
        <?php endif; ?>
        
        <!-- Avatar Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Profile Picture</h2>
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                <img src="<?= $user->getAvatarUrl() ?>" 
                     alt="Current Avatar"
                     class="w-24 h-24 rounded-full border-4 border-gray-200"
                     id="avatar-preview">
                
                <div class="flex-1 text-center sm:text-left">
                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="upload_avatar">
                        
                        <div>
                            <input type="file" 
                                   name="avatar" 
                                   id="avatar-input"
                                   accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="btn-primary px-4 py-2 rounded-md text-sm">
                                <i class="fas fa-upload mr-2"></i>Upload
                            </button>
                            
                            <?php if ($user->avatar): ?>
                            <button type="button" 
                                    onclick="deleteAvatar()"
                                    class="px-4 py-2 border border-red-500 text-red-600 rounded-md hover:bg-red-50 text-sm">
                                <i class="fas fa-trash mr-2"></i>Remove
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF. Max size 2MB.</p>
                </div>
            </div>
        </div>
        
        <!-- Personal Information -->
        <form method="POST" action="" class="space-y-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Personal Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="<?= sanitize($user->first_name) ?>"
                               required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="<?= sanitize($user->last_name) ?>"
                               required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- Email (Read-only) -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               value="<?= sanitize($user->email) ?>"
                               disabled
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                        <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                    </div>
                    
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?= sanitize($user->phone ?? '') ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                            Date of Birth
                        </label>
                        <input type="date" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="<?= sanitize($user->date_of_birth ?? '') ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                            Gender
                        </label>
                        <select id="gender" 
                                name="gender"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Gender</option>
                            <option value="male" <?= $user->gender === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $user->gender === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $user->gender === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <!-- NRC Number -->
                    <div>
                        <label for="nrc_number" class="block text-sm font-medium text-gray-700 mb-2">
                            NRC Number
                        </label>
                        <input type="text" 
                               id="nrc_number" 
                               name="nrc_number" 
                               value="<?= sanitize($user->nrc_number ?? '') ?>"
                               placeholder="123456/78/9"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- Education Level -->
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-700 mb-2">
                            Education Level
                        </label>
                        <select id="education_level" 
                                name="education_level"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Level</option>
                            <option value="Grade 12" <?= $user->education_level === 'Grade 12' ? 'selected' : '' ?>>Grade 12</option>
                            <option value="Certificate" <?= $user->education_level === 'Certificate' ? 'selected' : '' ?>>Certificate</option>
                            <option value="Diploma" <?= $user->education_level === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                            <option value="Bachelor's Degree" <?= $user->education_level === "Bachelor's Degree" ? 'selected' : '' ?>>Bachelor's Degree</option>
                            <option value="Master's Degree" <?= $user->education_level === "Master's Degree" ? 'selected' : '' ?>>Master's Degree</option>
                            <option value="PhD" <?= $user->education_level === 'PhD' ? 'selected' : '' ?>>PhD</option>
                        </select>
                    </div>
                    
                    <!-- Occupation -->
                    <div class="md:col-span-2">
                        <label for="occupation" class="block text-sm font-medium text-gray-700 mb-2">
                            Occupation
                        </label>
                        <input type="text" 
                               id="occupation" 
                               name="occupation" 
                               value="<?= sanitize($user->occupation ?? '') ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- Bio -->
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                            Bio
                        </label>
                        <textarea id="bio" 
                                  name="bio" 
                                  rows="4"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Tell us about yourself..."><?= sanitize($user->bio ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Location</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <input type="text" 
                               id="address" 
                               name="address" 
                               value="<?= sanitize($user->address ?? '') ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                            City
                        </label>
                        <input type="text" 
                               id="city" 
                               name="city" 
                               value="<?= sanitize($user->city ?? '') ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="province" class="block text-sm font-medium text-gray-700 mb-2">
                            Province
                        </label>
                        <select id="province" 
                                name="province"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Province</option>
                            <option value="Central" <?= $user->province === 'Central' ? 'selected' : '' ?>>Central</option>
                            <option value="Copperbelt" <?= $user->province === 'Copperbelt' ? 'selected' : '' ?>>Copperbelt</option>
                            <option value="Eastern" <?= $user->province === 'Eastern' ? 'selected' : '' ?>>Eastern</option>
                            <option value="Luapula" <?= $user->province === 'Luapula' ? 'selected' : '' ?>>Luapula</option>
                            <option value="Lusaka" <?= $user->province === 'Lusaka' ? 'selected' : '' ?>>Lusaka</option>
                            <option value="Muchinga" <?= $user->province === 'Muchinga' ? 'selected' : '' ?>>Muchinga</option>
                            <option value="Northern" <?= $user->province === 'Northern' ? 'selected' : '' ?>>Northern</option>
                            <option value="North-Western" <?= $user->province === 'North-Western' ? 'selected' : '' ?>>North-Western</option>
                            <option value="Southern" <?= $user->province === 'Southern' ? 'selected' : '' ?>>Southern</option>
                            <option value="Western" <?= $user->province === 'Western' ? 'selected' : '' ?>>Western</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Social Links</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fab fa-linkedin text-blue-600 mr-2"></i>LinkedIn URL
                        </label>
                        <input type="url" 
                               id="linkedin_url" 
                               name="linkedin_url" 
                               value="<?= sanitize($user->linkedin_url ?? '') ?>"
                               placeholder="https://linkedin.com/in/yourprofile"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook URL
                        </label>
                        <input type="url" 
                               id="facebook_url" 
                               name="facebook_url" 
                               value="<?= sanitize($user->facebook_url ?? '') ?>"
                               placeholder="https://facebook.com/yourprofile"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="twitter_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter URL
                        </label>
                        <input type="url" 
                               id="twitter_url" 
                               name="twitter_url" 
                               value="<?= sanitize($user->twitter_url ?? '') ?>"
                               placeholder="https://twitter.com/yourprofile"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="btn-primary px-8 py-3 rounded-md font-medium">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Change Password</h2>
            
            <form method="POST" action="" class="max-w-md">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="change_password">
                
                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Current Password
                        </label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password
                        </label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm New Password
                        </label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <button type="submit" class="btn-primary px-6 py-2 rounded-md">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Form for Avatar Deletion -->
<form id="delete-avatar-form" method="POST" action="" style="display: none;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="delete_avatar">
</form>

<script>
// Avatar preview
document.getElementById('avatar-input').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Delete avatar
function deleteAvatar() {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        document.getElementById('delete-avatar-form').submit();
    }
}
</script>

<?php require_once '../src/templates/footer.php'; ?>