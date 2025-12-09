<?php
/**
 * Registration Action Handler
 * Processes registration form submissions
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(baseUrl('pages/auth/register.php'));
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request. Please try again.');
    redirect(baseUrl('pages/auth/register.php'));
}

// Get form data
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$namaLengkap = trim($_POST['nama_lengkap'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

// Store old input for form repopulation
$_SESSION['old_input'] = [
    'username' => $username,
    'email' => $email,
    'nama_lengkap' => $namaLengkap,
    'alamat' => $alamat
];

// Validation
$errors = [];

// Username validation
if (empty($username)) {
    $errors[] = 'Username is required.';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
} elseif (strlen($username) > 50) {
    $errors[] = 'Username must not exceed 50 characters.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores.';
}

// Email validation
if (empty($email)) {
    $errors[] = 'Email is required.';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
}

// Full name validation
if (empty($namaLengkap)) {
    $errors[] = 'Full name is required.';
}

// Password validation
if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (!isValidPassword($password)) {
    $errors[] = 'Password must be at least 6 characters.';
}

// Password confirmation
if ($password !== $passwordConfirm) {
    $errors[] = 'Passwords do not match.';
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    setFlash('error', implode('<br>', $errors));
    redirect(baseUrl('pages/auth/register.php'));
}

try {
    $pdo = db();
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT UserID FROM gallery_user WHERE Username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        setFlash('error', 'Username is already taken. Please choose another.');
        redirect(baseUrl('pages/auth/register.php'));
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT UserID FROM gallery_user WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('error', 'Email is already registered. Please use another email or login.');
        redirect(baseUrl('pages/auth/register.php'));
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO gallery_user (Username, Password, Email, NamaLengkap, Alamat, Level)
        VALUES (?, ?, ?, ?, ?, 'User')
    ");
    
    $stmt->execute([
        $username,
        $hashedPassword,
        $email,
        $namaLengkap,
        $alamat ?: null
    ]);
    
    // Clear old input
    unset($_SESSION['old_input']);
    
    // Success message
    setFlash('success', 'Registration successful! Please login with your credentials.');
    redirect(baseUrl('pages/auth/login.php'));
    
} catch (PDOException $e) {
    // Log error in production
    error_log("Registration error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again later.');
    redirect(baseUrl('pages/auth/register.php'));
}
