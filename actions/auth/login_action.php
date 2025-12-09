<?php
/**
 * Login Action Handler
 * Processes login form submissions
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(baseUrl('pages/auth/login.php'));
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request. Please try again.');
    redirect(baseUrl('pages/auth/login.php'));
}

// Get form data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    setFlash('error', 'Please enter both username/email and password.');
    redirect(baseUrl('pages/auth/login.php'));
}

try {
    $pdo = db();
    
    // Check if input is email or username
    $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
    
    if ($isEmail) {
        $stmt = $pdo->prepare("SELECT * FROM gallery_user WHERE Email = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM gallery_user WHERE Username = ?");
    }
    
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Verify user exists and password is correct
    if (!$user || !verifyPassword($password, $user['Password'])) {
        setFlash('error', 'Invalid username/email or password.');
        redirect(baseUrl('pages/auth/login.php'));
    }
    
    // Set user session
    setUserSession($user);
    
    // Success message
    setFlash('success', 'Welcome back, ' . e($user['NamaLengkap']) . '!');
    
    // Redirect to intended page or gallery
    $redirectUrl = getRedirectAfterLogin();
    redirect($redirectUrl);
    
} catch (PDOException $e) {
    // Log error in production
    error_log("Login error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again later.');
    redirect(baseUrl('pages/auth/login.php'));
}
