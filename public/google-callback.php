<?php
/**
 * Edutrack Computer Training College
 * Google OAuth Callback Handler
 *
 * Google redirects here after the user authorises the app.
 * Exchanges the authorisation code for user info, then logs in or registers.
 */

require_once '../src/bootstrap.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(getRedirectUrl(currentUserRole()));
}

// ------------------------------------------------------------------
// 1. Validate the response from Google
// ------------------------------------------------------------------
if (isset($_GET['error'])) {
    flash('error', 'Google sign-in was cancelled or denied.', 'error');
    redirect(url('login.php'));
    exit;
}

$code = $_GET['code'] ?? '';
if (empty($code)) {
    flash('error', 'Invalid Google sign-in response.', 'error');
    redirect(url('login.php'));
    exit;
}

// CSRF: verify state parameter
$state = $_GET['state'] ?? '';
if (empty($state) || !isset($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
    unset($_SESSION['google_oauth_state']);
    flash('error', 'Invalid request state. Please try again.', 'error');
    redirect(url('login.php'));
    exit;
}
unset($_SESSION['google_oauth_state']);

// ------------------------------------------------------------------
// 2. Exchange the authorisation code for an access token & user info
// ------------------------------------------------------------------
try {
    $client = new Google_Client();
    $client->setClientId(config('google_oauth.client_id'));
    $client->setClientSecret(config('google_oauth.client_secret'));
    $client->setRedirectUri(config('google_oauth.redirect_uri'));

    $token = $client->fetchAccessTokenWithAuthCode($code);

    if (isset($token['error'])) {
        throw new Exception('Token error: ' . ($token['error_description'] ?? $token['error']));
    }

    $client->setAccessToken($token);

    // Fetch user profile from Google
    $oauth2 = new Google_Service_Oauth2($client);
    $googleUser = $oauth2->userinfo->get();

    if (empty($googleUser->getEmail())) {
        throw new Exception('Could not retrieve email from Google account.');
    }

    $userData = [
        'id'          => $googleUser->getId(),
        'email'       => $googleUser->getEmail(),
        'given_name'  => $googleUser->getGivenName(),
        'family_name' => $googleUser->getFamilyName(),
        'picture'     => $googleUser->getPicture(),
    ];

} catch (Exception $e) {
    error_log("Google OAuth error: " . $e->getMessage());
    flash('error', 'Google sign-in failed. Please try again.', 'error');
    redirect(url('login.php'));
    exit;
}

// ------------------------------------------------------------------
// 3. Login or register the user (defaults to student role)
// ------------------------------------------------------------------
$result = googleLoginOrRegister($userData);

if ($result['success']) {
    flash('success', $result['message'], 'success');
    redirect($result['redirect']);
} else {
    flash('error', $result['message'], 'error');
    redirect(url('login.php'));
}
