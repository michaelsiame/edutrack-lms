<?php
/**
 * Contact Us Page
 * Contact form and information
 */

require_once '../src/bootstrap.php';

$page_title = "Contact Us - Edutrack computer training college";

// Handle form submission
$formSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission';
    } else {
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validate
        if (empty($name)) {
            $errors[] = 'Name is required';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }

        if (empty($message)) {
            $errors[] = 'Message is required';
        } elseif (strlen($message) < 10) {
            $errors[] = 'Message must be at least 10 characters';
        }

        // If no errors, process the message
        if (empty($errors)) {
            try {
                // Save to database
                $db = Database::getInstance();

                // Store in contacts table if it exists
                try {
                    $db->query("
                        INSERT INTO contacts (name, email, phone, subject, message, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ", [$name, $email, $phone, $subject, $message]);
                } catch (Exception $e) {
                    // Table might not exist, just log
                    error_log("Could not save contact to database: " . $e->getMessage());
                }

                // Send notification email to admin using Email class
                $adminEmail = defined('SITE_EMAIL') ? SITE_EMAIL : 'admin@edutrack.edu';

                $emailSubject = "New Contact Form: " . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
                $emailBody = "
                    <h2>New Contact Form Submission</h2>
                    <p><strong>From:</strong> " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . ")</p>
                    <p><strong>Phone:</strong> " . ($phone ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : 'Not provided') . "</p>
                    <p><strong>Subject:</strong> " . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "</p>
                    <hr>
                    <h3>Message:</h3>
                    <p>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "</p>
                    <hr>
                    <p><small>Sent from IP: " . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') . "</small></p>
                ";

                // Send email (commented out if Email class isn't fully configured, uncomment to use)
                if (class_exists('Email')) {
                    Email::sendMail($adminEmail, $emailSubject, $emailBody);
                }

                $formSubmitted = true;
                // Use flash if available, otherwise just set flag
                if (function_exists('flash')) {
                    flash('success', 'Thank you for contacting us! We\'ll get back to you soon.', 'success');
                }

            } catch (Exception $e) {
                $errors[] = 'Failed to send message. Please try again.';
                error_log("Contact form error: " . $e->getMessage());
            }
        }
    }
}

require_once '../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-envelope mr-3"></i>
                Contact Us
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- Contact Information Column -->
            <div class="lg:col-span-1 space-y-8">
                
                <!-- NEW: Admissions Office (Featured Block) -->
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500 transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-graduate text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Official Admissions</h3>
                            <p class="text-xs text-gray-500 mb-3 uppercase tracking-wide">For New Intakes & Inquiries</p>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">Call / WhatsApp:</p>
                                    <p class="text-primary-600 font-bold">
                                        <a href="tel:0770666937">0770 666 937</a> / 
                                        <a href="tel:0965992967">0965 992 967</a>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">Email:</p>
                                    <p class="text-sm text-primary-600 break-all">
                                        <a href="mailto:edutrackcomputertrainingschool@gmail.com">edutrackcomputertrainingschool@gmail.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-primary-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Visit Us</h3>
                            <p class="text-gray-600">
                                <?= defined('SITE_NAME') ? SITE_NAME : 'Edutrack' ?><br>
                                <?= defined('SITE_ADDRESS') ? SITE_ADDRESS : 'Lusaka, Zambia' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- General Phone (System Config) -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Main Office</h3>
                            <p class="text-gray-600">
                                <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="text-gray-600 hover:text-primary-700">
                                    <?= defined('SITE_PHONE') ? SITE_PHONE : 'Contact Main Office' ?>
                                </a><br>
                                Mon-Fri 8:00 AM - 5:00 PM
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <?php if (function_exists('config') && config('social.facebook')): ?>
                        <a href="<?= config('social.facebook') ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                        <!-- Static Social Placeholders if config is missing -->
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-pink-600 rounded-full flex items-center justify-center text-white hover:bg-pink-700 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center text-white hover:bg-blue-500 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>

                    <?php if ($formSubmitted): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <h3 class="text-lg font-semibold text-green-900 mb-2">Message Sent Successfully!</h3>
                                    <p class="text-green-700">
                                        Thank you for contacting us. We've received your message and will get back to you within 24 hours.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors) && function_exists('displayValidationErrors')): ?>
                        <?php displayValidationErrors($errors); ?>
                    <?php elseif (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <ul class="text-red-600 list-disc list-inside">
                                <?php foreach($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!$formSubmitted): ?>
                    <form method="POST" action="" class="space-y-6">
                        <?php if(function_exists('csrfField')) echo csrfField(); ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="+260"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <select id="subject"
                                        name="subject"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Select a subject</option>
                                    <option value="admission" <?= ($_POST['subject'] ?? '') === 'admission' ? 'selected' : '' ?>>Admission Inquiry</option>
                                    <option value="general" <?= ($_POST['subject'] ?? '') === 'general' ? 'selected' : '' ?>>General Inquiry</option>
                                    <option value="course" <?= ($_POST['subject'] ?? '') === 'course' ? 'selected' : '' ?>>Course Information</option>
                                    <option value="payment" <?= ($_POST['subject'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment Issues</option>
                                    <option value="technical" <?= ($_POST['subject'] ?? '') === 'technical' ? 'selected' : '' ?>>Technical Support</option>
                                </select>
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea id="message"
                                      name="message"
                                      rows="6"
                                      required
                                      placeholder="Tell us how we can help you..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">Minimum 10 characters</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-8 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>

                <!-- FAQ Section -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                        Frequently Asked Questions
                    </h3>
                    <div class="space-y-3 text-sm">
                        <p class="text-gray-700">
                            <strong>Q: How can I enroll?</strong><br>
                            You can apply online via the admission form or visit our campus. Call the admission lines above for assistance.
                        </p>
                        <p class="text-gray-700">
                            <strong>Q: Can I visit your campus?</strong><br>
                            Yes! We welcome visits. Please call ahead to schedule a tour.
                        </p>
                        <p class="text-gray-700">
                            <strong>Q: Do you offer corporate training?</strong><br>
                            Yes, we provide customized training programs for organizations.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>