<?php
/**
 * Edutrack computer training college
 * Login Page
 */

require_once '../src/bootstrap.php';
require_once '../src/templates/alerts.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(getRedirectUrl(currentUserRole()));
}

$errors = [];
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validate inputs
        if (empty($email)) {
            $errors[] = 'Email is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        // Attempt login
        if (empty($errors)) {
            $result = loginUser($email, $password, $remember);
            
            if ($result['success']) {
                flash('success', $result['message'], 'success');
                redirect($result['redirect']);
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$page_title = "Login - Edutrack computer training college";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <img src="<?= asset('images/logo.png') ?>" alt="Edutrack Logo" class="h-16 mx-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back!</h2>
            <p class="text-gray-600">Login to continue your learning journey</p>
            <div class="mt-3">
                <?php teveta_badge(); ?>
            </div>
        </div>
        
        <!-- Login Form -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <?php if (!empty($errors)): ?>
                <div class="mb-6">
                    <?php foreach ($errors as $error): ?>
                        <?php errorAlert($error); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <?= csrfField() ?>
                
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
                               value="<?= sanitize($email) ?>"
                               required 
                               autocomplete="email"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                               placeholder="your.email@example.com">
                    </div>
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
                               autocomplete="current-password"
                               class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Enter your password">
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i id="password-icon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    
                    <a href="<?= url('forgot-password.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full btn-primary py-3 px-4 rounded-md font-medium flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login to Your Account
                </button>
            </form>
            
            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Don't have an account?</span>
                </div>
            </div>
            
            <!-- Register Link -->
            <a href="<?= url('register.php') ?>" class="w-full block text-center py-3 px-4 border border-primary-600 rounded-md text-primary-600 font-medium hover:bg-primary-50 transition">
                <i class="fas fa-user-plus mr-2"></i>
                Create New Account
            </a>
        </div>
        
        <!-- Additional Links -->
        <div class="text-center text-sm text-gray-600">
            <a href="<?= url() ?>" class="hover:text-primary-600">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Homepage
            </a>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');
    
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

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all required fields');
    }
});
</script>

<?php require_once '../src/templates/footer.php'; ?>