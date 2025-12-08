<?php
ob_start();
/**
 * Edutrack computer training college
 * Registration Page
 */

require_once '../src/bootstrap.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url('dashboard.php'));
    exit;
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => ''
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Rate limiting: Prevent spam registrations (5 attempts per 15 minutes per IP)
        $clientIp = getClientIp();
        if (!checkRateLimit('registration_' . $clientIp, 5, 900)) {
            $errors[] = 'Too many registration attempts. Please try again in 15 minutes.';
        }

        // Get form data
        $formData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'terms' => $_POST['terms'] ?? ''
        ];
        
        // Validate terms agreement
        if (empty($formData['terms'])) {
            $errors['terms'] = 'You must agree to the terms and conditions';
        }
        
        // Validate other fields
        $validation = validate($formData, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'phone|min:10|max:13',
            'password' => 'required|min:8',
            'password_confirm' => 'required|matches:password'
        ]);
        
        if (!$validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        } else {
            // Check password strength
            $passwordCheck = validatePasswordStrength($formData['password']);
            if (!$passwordCheck['valid']) {
                $errors['password'] = $passwordCheck['errors'];
            }
            
            // Validate Zambian phone number
            if (!empty($formData['phone'])) {
                $phoneValidation = validateZambianPhone($formData['phone']);
                if (!$phoneValidation['valid']) {
                    $errors['phone'] = $phoneValidation['message'];
                } else {
                    // Format phone number
                    $formData['phone'] = $phoneValidation['formatted'];
                }
            }
        }
        
        // Attempt registration
        if (empty($errors)) {
            $result = registerUser($formData);
            
            if ($result['success']) {
                flash('success', $result['message'], 'success');
                redirect(url('login.php'));
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$page_title = "Register - Edutrack computer training college";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <!-- Progress Steps (for multi-step form if implemented later) -->
    <div class="max-w-2xl mx-auto mb-8">
        <div class="flex items-center justify-center space-x-4">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold">
                    1
                </div>
                <div class="ml-2 text-sm font-medium text-primary-600">Account</div>
            </div>
            <div class="h-1 w-12 bg-gray-300"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold">
                    2
                </div>
                <div class="ml-2 text-sm font-medium text-gray-600">Profile</div>
            </div>
            <div class="h-1 w-12 bg-gray-300"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold">
                    3
                </div>
                <div class="ml-2 text-sm font-medium text-gray-600">Verify</div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="flex justify-center items-center space-x-3 mb-4">
                <img src="<?= asset('images/logo.png') ?>" alt="Edutrack Logo" class="h-14">
                <span class="text-2xl font-bold text-gray-900">Edutrack</span>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Join Our Learning Community</h2>
            <p class="text-gray-600 max-w-md mx-auto">Create your account to access courses, track progress, and connect with instructors.</p>
            <div class="mt-4">
                <?php teveta_badge(); ?>
            </div>
        </div>
        
        <!-- Registration Form -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <!-- Form Header -->
            <div class="bg-primary-600 text-white p-6">
                <h3 class="text-xl font-semibold">Create Account</h3>
                <p class="text-primary-100 text-sm">Fill in your details to get started</p>
            </div>
            
            <div class="p-8">
                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <h4 class="text-red-800 font-semibold">Please fix the following errors:</h4>
                        </div>
                        <ul class="mt-2 ml-8 list-disc text-red-700 text-sm">
                            <?php foreach ($errors as $key => $error): 
                                if (is_array($error)) {
                                    foreach($error as $e) {
                                        echo '<li>' . sanitize($e) . '</li>';
                                    }
                                } elseif (is_string($error)) {
                                    echo '<li>' . sanitize($error) . '</li>';
                                }
                            endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Success message (if any) -->
                <?php if (flash('success')): ?>
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                        <span class="text-green-800"><?= flash('success') ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6" id="registrationForm">
                    <?= csrfField() ?>
                    
                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?= sanitize($formData['first_name']) ?>"
                                       required 
                                       autocomplete="given-name"
                                       class="block w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 <?= hasError($errors, 'first_name') ? 'border-red-500 ring-1 ring-red-500' : '' ?>"
                                       placeholder="Enter your first name">
                            </div>
                            <?php if (hasError($errors, 'first_name')): ?>
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= validationError($errors, 'first_name') ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="relative">
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?= sanitize($formData['last_name']) ?>"
                                       required 
                                       autocomplete="family-name"
                                       class="block w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 <?= hasError($errors, 'last_name') ? 'border-red-500 ring-1 ring-red-500' : '' ?>"
                                       placeholder="Enter your last name">
                            </div>
                            <?php if (hasError($errors, 'last_name')): ?>
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= validationError($errors, 'last_name') ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="relative">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= sanitize($formData['email']) ?>"
                                   required 
                                   autocomplete="email"
                                   class="block w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 <?= hasError($errors, 'email') ? 'border-red-500 ring-1 ring-red-500' : '' ?>"
                                   placeholder="your.email@example.com">
                        </div>
                        <?php if (hasError($errors, 'email')): ?>
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= validationError($errors, 'email') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Phone Number with Zambian Specific Features -->
                    <div class="relative">
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone Number <span class="text-gray-500 text-sm font-normal">(Optional but recommended)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <div class="flex items-center space-x-2">
                                    <img src="https://flagcdn.com/w20/zm.png" alt="Zambia Flag" class="w-5 h-3 rounded">
                                    <span class="text-gray-400">+260</span>
                                </div>
                            </div>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= sanitize($formData['phone']) ?>"
                                   autocomplete="tel"
                                   class="block w-full pl-24 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 <?= hasError($errors, 'phone') ? 'border-red-500 ring-1 ring-red-500' : '' ?>"
                                   placeholder="97 123 4567"
                                   pattern="[0-9]{9,12}"
                                   maxlength="12">
                            <button type="button" 
                                    onclick="suggestCommonProvider()" 
                                    class="absolute right-12 top-1/2 transform -translate-y-1/2 text-primary-600 hover:text-primary-700 text-sm font-medium">
                                Suggest
                            </button>
                        </div>
                        <?php if (hasError($errors, 'phone')): ?>
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= validationError($errors, 'phone') ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- Phone Help Information -->
                        <div class="mt-2">
                            <div class="flex items-center text-xs text-gray-500 mb-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span>Zambian phone number formats:</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                <div class="bg-gray-50 p-2 rounded text-center">
                                    <div class="font-medium">Airtel</div>
                                    <div class="text-gray-600">09XX XXX XXX</div>
                                </div>
                                <div class="bg-gray-50 p-2 rounded text-center">
                                    <div class="font-medium">MTN</div>
                                    <div class="text-gray-600">07XX XXX XXX</div>
                                </div>
                                <div class="bg-gray-50 p-2 rounded text-center">
                                    <div class="font-medium">Zamtel</div>
                                    <div class="text-gray-600">08XX XXX XXX</div>
                                </div>
                                <div class="bg-gray-50 p-2 rounded text-center">
                                    <div class="font-medium">New</div>
                                    <div class="text-gray-600">06XX XXX XXX</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="relative">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autocomplete="new-password"
                                   class="block w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 <?= hasError($errors, 'password') ? 'border-red-500 ring-1 ring-red-500' : '' ?>"
                                   placeholder="Create a strong password"
                                   oninput="checkPasswordStrength(this.value)">
                            <button type="button" 
                                    onclick="togglePassword('password')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                <i id="password-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Meter -->
                        <div class="mt-3">
                            <div class="flex justify-between mb-1">
                                <span class="text-xs font-medium text-gray-700">Password strength</span>
                                <span id="password-strength-text" class="text-xs font-medium">Weak</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="password-strength-bar" class="h-2 rounded-full bg-red-500" style="width: 20%"></div>
                            </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="flex items-center">
                                <i id="req-length" class="fas fa-times text-red-500 mr-2 text-sm"></i>
                                <span class="text-xs text-gray-600">At least 8 characters</span>
                            </div>
                            <div class="flex items-center">
                                <i id="req-uppercase" class="fas fa-times text-red-500 mr-2 text-sm"></i>
                                <span class="text-xs text-gray-600">One uppercase letter</span>
                            </div>
                            <div class="flex items-center">
                                <i id="req-number" class="fas fa-times text-red-500 mr-2 text-sm"></i>
                                <span class="text-xs text-gray-600">One number</span>
                            </div>
                            <div class="flex items-center">
                                <i id="req-special" class="fas fa-times text-red-500 mr-2 text-sm"></i>
                                <span class="text-xs text-gray-600">One special character</span>
                            </div>
                        </div>
                        
                        <?php if (hasError($errors, 'password')): ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= validationError($errors, 'password') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="relative">
                        <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   required 
                                   autocomplete="new-password"
                                   class="block w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 <?= hasError($errors, 'password_confirm') ? 'border-red-500 ring-1 ring-red-500' : '' ?>"
                                   placeholder="Re-enter your password">
                            <button type="button" 
                                    onclick="togglePassword('password_confirm')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                <i id="password_confirm-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="password-match" class="mt-1 text-sm hidden">
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                            <span class="text-green-600">Passwords match</span>
                        </div>
                        <?php if (hasError($errors, 'password_confirm')): ?>
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= validationError($errors, 'password_confirm') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Terms Agreement -->
                    <div class="relative">
                        <div class="flex items-start p-4 bg-gray-50 rounded-lg border <?= hasError($errors, 'terms') ? 'border-red-500' : 'border-gray-200' ?>">
                            <input type="checkbox" 
                                   id="terms" 
                                   name="terms" 
                                   value="1"
                                   <?= !empty($formData['terms']) ? 'checked' : '' ?>
                                   class="h-5 w-5 mt-0.5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="terms" class="ml-3 block text-sm text-gray-700">
                                <span class="font-medium">I agree to the</span>
                                <a href="<?= url('terms.php') ?>" target="_blank" class="text-primary-600 hover:text-primary-700 font-medium mx-1">
                                    Terms of Service
                                </a>
                                <span class="font-medium">and</span>
                                <a href="<?= url('privacy.php') ?>" target="_blank" class="text-primary-600 hover:text-primary-700 font-medium mx-1">
                                    Privacy Policy
                                </a>
                                <span class="text-red-500 ml-1">*</span>
                                <p class="mt-1 text-gray-600 text-xs">
                                    By creating an account, you agree to receive important notifications about your courses and account updates.
                                </p>
                            </label>
                        </div>
                        <?php if (hasError($errors, 'terms')): ?>
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= validationError($errors, 'terms') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            id="submitBtn"
                            class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center">
                        <i class="fas fa-user-plus mr-3"></i>
                        <span>Create Account</span>
                        <div id="loadingSpinner" class="hidden ml-3">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Already have an account?</span>
                    </div>
                </div>
                
                <!-- Login Link -->
                <div class="text-center">
                    <a href="<?= url('login.php') ?>" 
                       class="inline-flex items-center justify-center w-full py-3 px-4 border-2 border-primary-600 rounded-lg text-primary-600 font-semibold hover:bg-primary-50 transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login to Your Account
                    </a>
                </div>
                
                <!-- Trust Badges -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <i class="fas fa-shield-alt text-green-500 text-2xl mb-2"></i>
                            <p class="text-xs text-gray-600">Secure Registration</p>
                        </div>
                        <div>
                            <i class="fas fa-graduation-cap text-blue-500 text-2xl mb-2"></i>
                            <p class="text-xs text-gray-600">TEVETA Certified</p>
                        </div>
                        <div>
                            <i class="fas fa-headset text-purple-500 text-2xl mb-2"></i>
                            <p class="text-xs text-gray-600">24/7 Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Links -->
        <div class="text-center text-sm text-gray-600 mt-8">
            <a href="<?= url() ?>" class="hover:text-primary-600 font-medium inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Homepage
            </a>
            <span class="mx-4">•</span>
            <a href="<?= url('courses.php') ?>" class="hover:text-primary-600 font-medium">
                Browse Courses
            </a>
            <span class="mx-4">•</span>
            <a href="<?= url('contact.php') ?>" class="hover:text-primary-600 font-medium">
                Need Help?
            </a>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const passwordIcon = document.getElementById(fieldId + '-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    // Requirements
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    let strength = 0;
    
    // Check requirements
    const hasLength = password.length >= 8;
    const hasUppercase = /[A-Z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    // Update requirement icons
    reqLength.className = hasLength ? 'fas fa-check text-green-500 mr-2 text-sm' : 'fas fa-times text-red-500 mr-2 text-sm';
    reqUppercase.className = hasUppercase ? 'fas fa-check text-green-500 mr-2 text-sm' : 'fas fa-times text-red-500 mr-2 text-sm';
    reqNumber.className = hasNumber ? 'fas fa-check text-green-500 mr-2 text-sm' : 'fas fa-times text-red-500 mr-2 text-sm';
    reqSpecial.className = hasSpecial ? 'fas fa-check text-green-500 mr-2 text-sm' : 'fas fa-times text-red-500 mr-2 text-sm';
    
    // Calculate strength
    if (hasLength) strength += 25;
    if (hasUppercase) strength += 25;
    if (hasNumber) strength += 25;
    if (hasSpecial) strength += 25;
    
    // Update strength bar and text
    strengthBar.style.width = strength + '%';
    
    if (strength < 50) {
        strengthBar.className = 'h-2 rounded-full bg-red-500';
        strengthText.textContent = 'Weak';
        strengthText.className = 'text-xs font-medium text-red-600';
    } else if (strength < 75) {
        strengthBar.className = 'h-2 rounded-full bg-yellow-500';
        strengthText.textContent = 'Fair';
        strengthText.className = 'text-xs font-medium text-yellow-600';
    } else if (strength < 100) {
        strengthBar.className = 'h-2 rounded-full bg-blue-500';
        strengthText.textContent = 'Good';
        strengthText.className = 'text-xs font-medium text-blue-600';
    } else {
        strengthBar.className = 'h-2 rounded-full bg-green-500';
        strengthText.textContent = 'Strong';
        strengthText.className = 'text-xs font-medium text-green-600';
    }
}

// Password match validation
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const matchIndicator = document.getElementById('password-match');
    
    if (confirm.length > 0) {
        if (password === confirm) {
            this.setCustomValidity('');
            matchIndicator.classList.remove('hidden');
        } else {
            this.setCustomValidity('Passwords do not match');
            matchIndicator.classList.add('hidden');
        }
    } else {
        matchIndicator.classList.add('hidden');
    }
});

// Format Zambian phone number as user types
document.getElementById('phone').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    
    // Remove leading 0 if present (we already show +260)
    if (value.startsWith('0')) {
        value = value.substring(1);
    }
    
    // Format with spaces for readability
    if (value.length > 0) {
        let formatted = '';
        if (value.length <= 2) {
            formatted = value;
        } else if (value.length <= 5) {
            formatted = value.substring(0, 2) + ' ' + value.substring(2);
        } else if (value.length <= 9) {
            formatted = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5);
        } else {
            formatted = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5, 9);
        }
        this.value = formatted;
    }
});

// Suggest common Zambian phone number prefix
function suggestCommonProvider() {
    const prefixes = ['97', '96', '95', '77', '76', '75'];
    const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const randomNumber = Math.floor(1000000 + Math.random() * 9000000);
    const phoneInput = document.getElementById('phone');
    
    phoneInput.value = randomPrefix + ' ' + randomNumber.toString().substring(0, 3) + ' ' + randomNumber.toString().substring(3, 7);
    phoneInput.focus();
}

// Form submission loading state
document.getElementById('registrationForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    submitBtn.disabled = true;
    loadingSpinner.classList.remove('hidden');
    submitBtn.innerHTML = '<i class="fas fa-user-plus mr-3"></i><span>Creating Account...</span>' + loadingSpinner.outerHTML;
});

// Real-time validation for email
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        this.setCustomValidity('Please enter a valid email address');
    } else {
        this.setCustomValidity('');
    }
});

// Initialize password strength check on page load
document.addEventListener('DOMContentLoaded', function() {
    checkPasswordStrength('');
});
</script>

<?php require_once '../src/templates/footer.php'; ?>