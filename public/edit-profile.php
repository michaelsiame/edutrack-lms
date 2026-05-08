<?php
/**
 * Edutrack Computer Training College - Edit Profile Page
 */

require_once __DIR__ . '/../src/bootstrap.php';

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
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="min-h-screen py-8" style="background-color: var(--surface-primary);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold" style="color: var(--text-primary);">Edit Profile</h1>
                <p class="mt-1" style="color: var(--text-secondary);">Manage your personal information and settings</p>
            </div>
            <a href="<?= url('profile.php') ?>" class="mt-4 sm:mt-0 flex items-center transition-colors duration-200" style="color: var(--accent-primary);">
                <i class="fas fa-arrow-left mr-2"></i>Back to Profile
            </a>
        </div>
        
        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 border-l-4 p-4 rounded-r-lg" style="background-color: var(--status-error-bg); border-color: var(--status-error);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle" style="color: var(--status-error);"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium" style="color: var(--status-error);">There were errors with your submission</h3>
                        <div class="mt-2 text-sm" style="color: var(--status-error);">
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
            $flashBg = ($flash['type'] ?? 'info') === 'success' ? 'var(--status-success-bg)' : 
                     (($flash['type'] ?? 'info') === 'error' ? 'var(--status-error-bg)' : 'var(--surface-tertiary)');
            $flashBorder = ($flash['type'] ?? 'info') === 'success' ? 'var(--status-success)' : 
                          (($flash['type'] ?? 'info') === 'error' ? 'var(--status-error)' : 'var(--accent-primary)');
            $flashColor = ($flash['type'] ?? 'info') === 'success' ? 'var(--status-success)' : 
                         (($flash['type'] ?? 'info') === 'error' ? 'var(--status-error)' : 'var(--text-primary)');
        ?>
            <div class="mb-6 border-l-4 p-4 rounded-r-lg" style="background-color: <?= $flashBg ?>; border-color: <?= $flashBorder ?>; color: <?= $flashColor ?>;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Avatar Section -->
        <div class="p-6 mb-6 card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
            <h2 class="text-xl font-bold mb-4" style="color: var(--text-primary);">Profile Picture</h2>
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                <img src="<?= htmlspecialchars($user->getAvatarUrl()) ?>" 
                     alt="Avatar" class="w-24 h-24 rounded-full object-cover" style="border: 3px solid var(--border-primary);" id="avatar-preview">
                
                <div class="flex-1 text-center sm:text-left">
                    <form method="POST" enctype="multipart/form-data" class="space-y-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="upload_avatar">
                        <div>
                            <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                   class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold transition-colors duration-200"
                                   style="color: var(--text-secondary);"
                                   onchange="this.style.color='var(--text-primary)'">
                        </div>
                        <div class="flex space-x-3 justify-center sm:justify-start">
                            <button type="submit" class="text-white px-4 py-2 text-sm font-medium transition-all duration-200 hover:opacity-90" style="background-color: var(--accent-primary); border-radius: var(--radius-lg);">
                                <i class="fas fa-upload mr-2"></i>Upload
                            </button>
                            <?php if ($user->avatar): ?>
                            <button type="button" onclick="confirmAvatarDelete()" class="px-4 py-2 text-sm font-medium transition-all duration-200" style="border: 1px solid var(--status-error); color: var(--status-error); border-radius: var(--radius-lg);">
                                <i class="fas fa-trash mr-2"></i>Remove
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <p class="text-xs mt-2" style="color: var(--text-muted);">JPG, PNG, GIF or WEBP. Max size 2MB.</p>
                </div>
            </div>
        </div>
        
        <!-- Main Info Form -->
        <form method="POST" class="space-y-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            
            <!-- Personal Information -->
            <div class="p-6 card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                <h2 class="text-xl font-bold mb-4 pb-2" style="color: var(--text-primary); border-bottom: 1px solid var(--border-primary);">Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">First Name <span style="color: var(--status-error);">*</span></label>
                        <input type="text" name="first_name" value="<?= sanitize($user->first_name) ?>" required 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Last Name <span style="color: var(--status-error);">*</span></label>
                        <input type="text" name="last_name" value="<?= sanitize($user->last_name) ?>" required 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Email</label>
                        <input type="email" value="<?= sanitize($user->email) ?>" disabled 
                               class="w-full cursor-not-allowed"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: var(--surface-tertiary); color: var(--text-muted); padding: 0.5rem 0.75rem;">
                        <p class="text-xs mt-1" style="color: var(--text-muted);">Contact support to change email.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Phone Number</label>
                        <input type="tel" name="phone" value="<?= sanitize($user->phone ?? '') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= sanitize($user->date_of_birth ?? '') ?>" max="<?= date('Y-m-d') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Gender</label>
                        <select name="gender" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                                style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                                onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($user->gender === 'Male' || $user->gender === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user->gender === 'Female' || $user->gender === 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($user->gender === 'Other' || $user->gender === 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">NRC Number</label>
                        <input type="text" name="nrc_number" value="<?= sanitize($user->nrc_number ?? '') ?>" placeholder="e.g. 123456/10/1"
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Education Level</label>
                        <select name="education_level" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                                style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                                onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                            <option value="">Select Level</option>
                            <?php foreach(['Grade 12', 'Certificate', 'Diploma', "Bachelor's Degree", "Master's Degree", 'PhD'] as $lvl): ?>
                                <option value="<?= $lvl ?>" <?= $user->education_level === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Occupation</label>
                        <input type="text" name="occupation" value="<?= sanitize($user->occupation ?? '') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Bio</label>
                        <textarea name="bio" rows="4" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200" placeholder="Tell us a bit about yourself..."
                                  style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none; resize: vertical;"
                                  onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                  onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'"><?= sanitize($user->bio ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="p-6 card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                <h2 class="text-xl font-bold mb-4 pb-2" style="color: var(--text-primary); border-bottom: 1px solid var(--border-primary);">Location</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Address</label>
                        <input type="text" name="address" value="<?= sanitize($user->address ?? '') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">City</label>
                        <input type="text" name="city" value="<?= sanitize($user->city ?? '') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Province</label>
                        <select name="province" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                                style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                                onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                            <option value="">Select Province</option>
                            <?php foreach(['Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka', 'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'] as $prov): ?>
                                <option value="<?= $prov ?>" <?= $user->province === $prov ? 'selected' : '' ?>><?= $prov ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Postal Code</label>
                        <input type="text" name="postal_code" value="<?= sanitize($user->postal_code ?? '') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Country</label>
                        <input type="text" name="country" value="<?= sanitize($user->country ?? 'Zambia') ?>" 
                               class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="p-6 card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                <h2 class="text-xl font-bold mb-4 pb-2" style="color: var(--text-primary); border-bottom: 1px solid var(--border-primary);">Social Links</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">LinkedIn URL</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm" style="border: 1px solid var(--border-primary); border-right: none; border-radius: var(--radius-lg) 0 0 var(--radius-lg); background-color: var(--surface-tertiary); color: var(--text-muted);"><i class="fab fa-linkedin"></i></span>
                            <input type="url" name="linkedin_url" value="<?= sanitize($user->linkedin_url ?? '') ?>" placeholder="https://linkedin.com/in/..." class="flex-1 block w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                                   style="border: 1px solid var(--border-primary); border-radius: 0 var(--radius-lg) var(--radius-lg) 0; background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                                   onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                   onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Facebook URL</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm" style="border: 1px solid var(--border-primary); border-right: none; border-radius: var(--radius-lg) 0 0 var(--radius-lg); background-color: var(--surface-tertiary); color: var(--text-muted);"><i class="fab fa-facebook"></i></span>
                            <input type="url" name="facebook_url" value="<?= sanitize($user->facebook_url ?? '') ?>" placeholder="https://facebook.com/..." class="flex-1 block w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                                   style="border: 1px solid var(--border-primary); border-radius: 0 var(--radius-lg) var(--radius-lg) 0; background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                                   onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                   onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Twitter URL</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm" style="border: 1px solid var(--border-primary); border-right: none; border-radius: var(--radius-lg) 0 0 var(--radius-lg); background-color: var(--surface-tertiary); color: var(--text-muted);"><i class="fab fa-twitter"></i></span>
                            <input type="url" name="twitter_url" value="<?= sanitize($user->twitter_url ?? '') ?>" placeholder="https://twitter.com/..." class="flex-1 block w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                                   style="border: 1px solid var(--border-primary); border-radius: 0 var(--radius-lg) var(--radius-lg) 0; background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                                   onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                                   onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="p-6 card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                <h2 class="text-xl font-bold mb-4 pb-2" style="color: var(--text-primary); border-bottom: 1px solid var(--border-primary);">Change Password</h2>
                <div class="max-w-md space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Current Password</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">New Password</label>
                        <input type="password" name="new_password" autocomplete="new-password" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                        <p class="text-xs mt-1" style="color: var(--text-muted);">Leave blank if you don't want to change it.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Confirm New Password</label>
                        <input type="password" name="confirm_password" autocomplete="new-password" class="w-full shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200"
                               style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg); background-color: #fff; color: var(--text-primary); padding: 0.5rem 0.75rem; outline: none;"
                               onfocus="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(45,95,171,0.15)'"
                               onblur="this.style.borderColor='var(--border-primary)'; this.style.boxShadow='none'">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end pb-8">
                <button type="submit" class="text-white px-8 py-3 font-medium transition-all duration-200 hover:opacity-90"
                        style="background-color: var(--accent-primary); border-radius: var(--radius-lg); box-shadow: var(--shadow-card);">
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

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
