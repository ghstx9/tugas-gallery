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
    setFlash('error', 'Permintaan tidak valid. Silakan coba lagi.');
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
    $errors[] = 'Username wajib diisi.';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username minimal 3 karakter.';
} elseif (strlen($username) > 50) {
    $errors[] = 'Username tidak boleh melebihi 50 karakter.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username hanya boleh berisi huruf, angka, dan garis bawah.';
}

// Email validation
if (empty($email)) {
    $errors[] = 'Email wajib diisi.';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Silakan masukkan alamat email yang valid.';
}

// Full name validation
if (empty($namaLengkap)) {
    $errors[] = 'Nama lengkap wajib diisi.';
}

// Password validation
if (empty($password)) {
    $errors[] = 'Kata sandi wajib diisi.';
} elseif (!isValidPassword($password)) {
    $errors[] = 'Kata sandi minimal 6 karakter.';
}

// Password confirmation
if ($password !== $passwordConfirm) {
    $errors[] = 'Kata sandi tidak cocok.';
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
        setFlash('error', 'Username sudah digunakan. Silakan pilih yang lain.');
        redirect(baseUrl('pages/auth/register.php'));
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT UserID FROM gallery_user WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('error', 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.');
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
    setFlash('success', 'Pendaftaran berhasil! Silakan masuk dengan kredensial Anda.');
    redirect(baseUrl('pages/auth/login.php'));
    
} catch (PDOException $e) {
    // Log error in production
    error_log("Registration error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silakan coba lagi nanti.');
    redirect(baseUrl('pages/auth/register.php'));
}
