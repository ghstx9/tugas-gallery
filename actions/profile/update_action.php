<?php
/**
 * Update Profile Action Handler
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(baseUrl('pages/profile/index.php'));
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request. Please try again.');
    redirect(baseUrl('pages/profile/edit.php'));
}

// Get form data
$namaLengkap = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Store old input for repopulation
$_SESSION['old_input'] = [
    'nama_lengkap' => $namaLengkap,
    'email' => $email,
    'alamat' => $alamat
];

// Validate name
if (empty($namaLengkap)) {
    setFlash('error', 'Silahkan masukkan nama lengkap.');
    redirect(baseUrl('pages/profile/edit.php'));
}

if (strlen($namaLengkap) > 255) {
    setFlash('error', 'Nama tidak boleh melebihi 255 karakter.');
    redirect(baseUrl('pages/profile/edit.php'));
}

// Validate email
if (empty($email)) {
    setFlash('error', 'Silahkan masukkan alamat email.');
    redirect(baseUrl('pages/profile/edit.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Silahkan masukkan alamat email yang valid.');
    redirect(baseUrl('pages/profile/edit.php'));
}

try {
    $pdo = db();
    $userId = getCurrentUserId();
    
    // Get current user data
    $userStmt = $pdo->prepare("SELECT * FROM gallery_user WHERE UserID = ?");
    $userStmt->execute([$userId]);
    $currentUser = $userStmt->fetch();
    
    // Check if email is already taken by another user
    if ($email !== $currentUser['Email']) {
        $emailStmt = $pdo->prepare("SELECT UserID FROM gallery_user WHERE Email = ? AND UserID != ?");
        $emailStmt->execute([$email, $userId]);
        if ($emailStmt->fetch()) {
            setFlash('error', 'Email sudah digunakan oleh pengguna lain.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
    }
    
    // Handle password change
    $passwordUpdate = '';
    $passwordParam = null;
    
    if (!empty($newPassword)) {
        // Validate current password
        if (empty($currentPassword)) {
            setFlash('error', 'Silahkan masukkan password Anda untuk mengubahnya.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        if (!password_verify($currentPassword, $currentUser['Password'])) {
            setFlash('error', 'Password saat ini tidak sesuai.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            setFlash('error', 'Password baru harus memiliki minimal 6 karakter.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'Password baru tidak sesuai.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        $passwordParam = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    // Update user
    if ($passwordParam) {
        $updateStmt = $pdo->prepare("
            UPDATE gallery_user 
            SET NamaLengkap = ?, Email = ?, Alamat = ?, Password = ?
            WHERE UserID = ?
        ");
        $updateStmt->execute([$namaLengkap, $email, $alamat ?: null, $passwordParam, $userId]);
    } else {
        $updateStmt = $pdo->prepare("
            UPDATE gallery_user 
            SET NamaLengkap = ?, Email = ?, Alamat = ?
            WHERE UserID = ?
        ");
        $updateStmt->execute([$namaLengkap, $email, $alamat ?: null, $userId]);
    }
    
    // Update session data
    $_SESSION['user']['nama_lengkap'] = $namaLengkap;
    
    // Clear old input
    unset($_SESSION['old_input']);
    
    setFlash('success', 'Profile berhasil diperbarui!');
    redirect(baseUrl('pages/profile/index.php'));
    
} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silahkan coba lagi.');
    redirect(baseUrl('pages/profile/edit.php'));
}
