<?php
/**
 * Edutrack computer training college
 * Registration Page
 */

require_once '../src/bootstrap.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url('dashboard.php'));
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
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];
        
        // Validate
        $validation = validate($formData, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'phone',
            'password' => 'required|min:8',
            'password_confirm' => 'required|matches:password'
        ]);
        
        if (!$validation['valid']) {
            $errors = $validation['errors'];
        } else {
            // Check password strength
            $passwordCheck = validatePasswordStrength($formData['password']);
            if (!$passwordCheck['valid']) {
                $errors['password'] = $passwordCheck['errors'];
            }
        }
        
        // Attempt registration
        if (empty($errors)) {
            $result = registerUser($formData);
            
            if ($result['success']) {
                flash('success', $result['message'], 'success');
                redirect(url('login.php'));
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$page_title = "Register - Edutrack computer training college";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="<?= asset('images/logo.png') ?>" alt="Edutrack Logo" class="h-16 mx-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Create Your Account</h2>
            <p class="text-gray-600">Start your learning journey with Edutrack</p>
            <div class="mt-3">
                <?php teveta_badge(); ?>
            </div>
        </div>
        
        <!-- Registration Form -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <?php if (!empty($errors) && isset($errors[0])): ?>
                <div class="mb-6">
                    <?php foreach ($errors as $error): ?>
                        <?php if (is_string($error)): ?>
                            <?php errorAlert($error); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <?= csrfField() ?>
                
                <!-- Name Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="<?= sanitize($formData['first_name']) ?>"
                               required 
                               autocomplete="given-name"
                               class="block w-full px-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 <?= hasError($errors, 'first_name') ? 'border-red-500' : '' ?>"
                               placeholder="John">
                        <?= validationError($errors, 'first_name') ?>
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="<?= sanitize($formData['last_name']) ?>"
                               required 
                               autocomplete="family-name"
                               class="block w-full px-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 <?= hasError($errors, 'last_name') ? 'border-red-500' : '' ?>"
                               placeholder="Doe">
                        <?= validationError($errors, 'last_name') ?>
                    </div>
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
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
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 <?= hasError($errors, 'email') ? 'border-red-500' : '' ?>"
                               placeholder="john.doe@example.com">
                    </div>
                    <?= validationError($errors, 'email') ?>
                </div>
                
                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number (Optional)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?= sanitize($formData['phone']) ?>"
                               autocomplete="tel"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 <?= hasError($errors, 'phone') ? 'border-red-500' : '' ?>"
                               placeholder="0977123456">
                    </div>
                    <?= validationError($errors, 'phone') ?>
                    <p class="mt-1 text-xs text-gray-500">Format: 09XXXXXXXX</p>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
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
                               class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 <?= hasError($errors, 'password') ? 'border-red-500' : '' ?>"
                               placeholder="Create a strong password">
                        <button type="button" 
                                onclick="togglePassword('password')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i id="password-icon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    <?= validationError($errors, 'password') ?>
                    <div class="mt-2 text-xs text-gray-600">
                        <p class="font-medium mb-1">Password must contain:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One number</li>
                            <li>One special character</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Confirm Password -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
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
                               class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 <?= hasError($errors, 'password_confirm') ? 'border-red-500' : '' ?>"
                               placeholder="Re-enter your password">
                        <button type="button" 
                                onclick="togglePassword('password_confirm')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i id="password_confirm-icon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    <?= validationError($errors, 'password_confirm') ?>
                </div>
                
                <!-- Terms Agreement -->
                <div class="flex items-start">
                    <input type="checkbox" 
                           id="terms" 
                           name="terms" 
                           required
                           class="h-4 w-4 mt-1 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        I agree to the <a href="<?= url('terms.php') ?>" target="_blank" class="text-primary-600 hover:text-primary-700">Terms of Service</a> 
                        and <a href="<?= url('privacy.php') ?>" target="_blank" class="text-primary-600 hover:text-primary-700">Privacy Policy</a>
                        <span class="text-red-500">*</span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full btn-secondary py-3 px-4 rounded-md font-medium flex items-center justify-center">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>
            
            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                </div>
            </div>
            
            <!-- Login Link -->
            <a href="<?= url('login.php') ?>" class="w-full block text-center py-3 px-4 border border-primary-600 rounded-md text-primary-600 font-medium hover:bg-primary-50 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login to Your Account
            </a>
        </div>
        
        <!-- Additional Links -->
        <div class="text-center text-sm text-gray-600 mt-6">
            <a href="<?= url() ?>" class="hover:text-primary-600">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Homepage
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

// Password match validation
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if (confirm && password !== confirm) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once '../src/templates/footer.php'; ?>