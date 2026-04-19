<?php
/**
 * Newsletter Subscription Handler
 */

require_once __DIR__ . '/../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email'])) {
    redirect('events.php');
}

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlashMessage('Please enter a valid email address.', 'error');
    redirect('events.php');
}

try {
    $db = Database::getInstance();
    
    // Check if email already exists
    $exists = $db->fetchColumn("SELECT id FROM newsletter_subscribers WHERE email = ?", [$email]);
    
    if ($exists) {
        setFlashMessage('You are already subscribed to our newsletter!', 'info');
    } else {
        // Add subscriber
        $db->query(
            "INSERT INTO newsletter_subscribers (email, subscribed_at, status) VALUES (?, NOW(), 'active')",
            [$email]
        );
        
        // TODO: Send confirmation email (requires email service setup)
        // Email::sendMail($email, 'Welcome to Edutrack Newsletter', 'Thank you for subscribing...');
        
        setFlashMessage('Thank you for subscribing! Check your inbox for confirmation.', 'success');
    }
    
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    setFlashMessage('Something went wrong. Please try again later.', 'error');
}

// Redirect back with success parameter
header('Location: events.php?newsletter=success');
exit;
