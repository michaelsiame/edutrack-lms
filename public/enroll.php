<?php
/**
 * Enrollment Handler
 * Handles course enrollment process
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

// Must be logged in to enroll
if (!isLoggedIn()) {
    setFlashMessage('Please login to enroll in courses', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Get course ID
$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    setFlashMessage('Invalid course', 'error');
    redirect('courses.php');
}

// Get course
$course = Course::find($courseId);

if (!$course || !$course->isPublished()) {
    setFlashMessage('Course not found', 'error');
    redirect('courses.php');
}

// Check if already enrolled
$userId = $_SESSION['user_id'];
if (Enrollment::isEnrolled($userId, $courseId)) {
    setFlashMessage('You are already enrolled in this course', 'info');
    redirect('learn.php?course=' . $course->getSlug());
}

// If course is free, enroll immediately
if ($course->isFree()) {
    // Use correct schema enum values:
    // enrollment_status: 'Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired'
    // payment_status: 'pending', 'completed', 'failed', 'refunded'
    $enrollmentData = [
        'user_id' => $userId,
        'course_id' => $courseId,
        'enrollment_status' => 'Enrolled',
        'payment_status' => 'completed', // Free course = already paid
        'amount_paid' => 0
    ];

    $enrollmentId = Enrollment::create($enrollmentData);
    
    if ($enrollmentId) {
        setFlashMessage('Successfully enrolled in ' . $course->getTitle(), 'success');
        redirect('learn.php?course=' . $course->getSlug());
    } else {
        setFlashMessage('Failed to enroll. Please try again.', 'error');
        redirect('course.php?slug=' . $course->getSlug());
    }
}

// For paid courses, redirect to checkout
$page_title = 'Enroll in ' . htmlspecialchars($course->getTitle()) . ' - Edutrack';
require_once '../src/templates/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Back Button -->
    <a href="<?= $course->getUrl() ?>" class="inline-flex items-center text-primary-600 hover:text-primary-700 mb-6">
        <i class="fas fa-arrow-left mr-2"></i> Back to Course
    </a>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white p-8">
            <h1 class="text-3xl font-bold mb-2">Complete Your Enrollment</h1>
            <p class="text-primary-100">You're one step away from starting your learning journey</p>
        </div>
        
        <div class="p-8">
            
            <!-- Course Summary -->
            <div class="border border-gray-200 rounded-lg p-6 mb-8">
                <div class="flex items-start space-x-4">
                    <img src="<?= $course->getThumbnailUrl() ?>" 
                         alt="<?= htmlspecialchars($course->getTitle()) ?>"
                         class="w-32 h-32 object-cover rounded-lg">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">
                            <?= htmlspecialchars($course->getTitle()) ?>
                        </h2>
                        <p class="text-gray-600 mb-3">
                            <?= htmlspecialchars($course->getShortDescription()) ?>
                        </p>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span><i class="fas fa-clock mr-1"></i> <?= $course->getDuration() ?> hours</span>
                            <span><i class="fas fa-signal mr-1"></i> <?= ucfirst($course->getLevel()) ?></span>
                            <?php if ($course->isTeveta()): ?>
                            <span class="text-secondary-600 font-semibold">
                                <i class="fas fa-certificate mr-1"></i> TEVETA Certified
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Payment Details</h3>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-700">
                        <span>Course Price:</span>
                        <span class="font-semibold"><?= formatCurrency($course->getPrice()) ?></span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Processing Fee:</span>
                        <span class="font-semibold">ZMW 0.00</span>
                    </div>
                    <div class="border-t border-gray-300 pt-3 flex justify-between text-lg font-bold text-gray-900">
                        <span>Total:</span>
                        <span class="text-primary-600"><?= formatCurrency($course->getPrice()) ?></span>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    One-time payment. Full lifetime access to course materials.
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Select Payment Method</h3>
                
                <form action="<?= url('checkout.php') ?>" method="POST" id="payment-form">
                    <input type="hidden" name="course_id" value="<?= $course->getId() ?>">
                    <input type="hidden" name="amount" value="<?= $course->getPrice() ?>">
                    <?= csrfField() ?>
                    
                    <div class="space-y-4">
                        
                        <!-- MTN Mobile Money -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="mtn" 
                                   class="w-5 h-5 text-primary-600"
                                   required>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center">
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-bold mr-3">MTN</span>
                                    <span class="font-semibold text-gray-900">MTN Mobile Money</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Pay using MTN Mobile Money</p>
                            </div>
                        </label>
                        
                        <!-- Airtel Money -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="airtel" 
                                   class="w-5 h-5 text-primary-600">
                            <div class="ml-4 flex-1">
                                <div class="flex items-center">
                                    <span class="bg-red-600 text-white px-3 py-1 rounded text-xs font-bold mr-3">AIRTEL</span>
                                    <span class="font-semibold text-gray-900">Airtel Money</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Pay using Airtel Money</p>
                            </div>
                        </label>
                        
                        <!-- Zamtel Kwacha -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="zamtel" 
                                   class="w-5 h-5 text-primary-600">
                            <div class="ml-4 flex-1">
                                <div class="flex items-center">
                                    <span class="bg-green-600 text-white px-3 py-1 rounded text-xs font-bold mr-3">ZAMTEL</span>
                                    <span class="font-semibold text-gray-900">Zamtel Kwacha</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Pay using Zamtel Kwacha</p>
                            </div>
                        </label>
                        
                        <!-- Bank Transfer (Manual) -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="bank_transfer" 
                                   class="w-5 h-5 text-primary-600">
                            <div class="ml-4 flex-1">
                                <div class="flex items-center">
                                    <i class="fas fa-university text-primary-600 text-xl mr-3"></i>
                                    <span class="font-semibold text-gray-900">Bank Transfer</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Transfer to our bank account (Manual verification)</p>
                            </div>
                        </label>
                        
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="mt-6">
                        <label class="flex items-start">
                            <input type="checkbox" 
                                   name="agree_terms" 
                                   class="mt-1 rounded border-gray-300 text-primary-600"
                                   required>
                            <span class="ml-2 text-sm text-gray-700">
                                I agree to the <a href="<?= url('terms.php') ?>" class="text-primary-600 hover:text-primary-700" target="_blank">Terms of Service</a> 
                                and <a href="<?= url('privacy.php') ?>" class="text-primary-600 hover:text-primary-700" target="_blank">Privacy Policy</a>
                            </span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="mt-8 flex space-x-4">
                        <button type="submit" 
                                class="flex-1 bg-primary-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-primary-700 transition">
                            <i class="fas fa-lock mr-2"></i> Proceed to Payment
                        </button>
                        <a href="<?= $course->getUrl() ?>" 
                           class="flex-1 bg-gray-200 text-gray-700 text-center py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                    
                </form>
            </div>
            
            <!-- Security Notice -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-start text-sm text-gray-600">
                    <i class="fas fa-shield-alt text-green-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-900 mb-1">Secure Payment</p>
                        <p>Your payment information is encrypted and secure. We never store your payment details.</p>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- What Happens Next -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">What happens after payment?</h3>
        <ol class="space-y-3">
            <li class="flex items-start">
                <span class="bg-primary-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">1</span>
                <span class="text-gray-700">Complete payment using your preferred method</span>
            </li>
            <li class="flex items-start">
                <span class="bg-primary-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">2</span>
                <span class="text-gray-700">Receive instant confirmation via email</span>
            </li>
            <li class="flex items-start">
                <span class="bg-primary-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">3</span>
                <span class="text-gray-700">Get immediate access to all course materials</span>
            </li>
            <li class="flex items-start">
                <span class="bg-primary-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">4</span>
                <span class="text-gray-700">Start learning at your own pace</span>
            </li>
            <?php if ($course->hasCertificate()): ?>
            <li class="flex items-start">
                <span class="bg-primary-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">5</span>
                <span class="text-gray-700">Earn your <?= $course->isTeveta() ? 'TEVETA-certified ' : '' ?>certificate upon completion</span>
            </li>
            <?php endif; ?>
        </ol>
    </div>
    
</div>

<?php require_once '../src/templates/footer.php'; ?>