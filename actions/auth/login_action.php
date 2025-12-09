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
    setFlash('error', 'Permintaan tidak valid. Silakan coba lagi.');
    redirect(baseUrl('pages/auth/login.php'));
}

// Get form data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    setFlash('error', 'Silakan masukkan username/email dan kata sandi.');
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
        setFlash('error', 'Username/email atau kata sandi tidak valid.');
        redirect(baseUrl('pages/auth/login.php'));
    }
    
    // Set user session
    setUserSession($user);
    
    // Success message
    setFlash('success', 'Selamat datang kembali, ' . e($user['NamaLengkap']) . '!');
    
    // Redirect to intended page or gallery
    $redirectUrl = getRedirectAfterLogin();
    redirect($redirectUrl);
    
} catch (PDOException $e) {
    // Log error in production
    error_log("Login error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silakan coba lagi nanti.');
    redirect(baseUrl('pages/auth/login.php'));
}
