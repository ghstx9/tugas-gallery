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
    setFlash('error', 'Please enter your full name.');
    redirect(baseUrl('pages/profile/edit.php'));
}

if (strlen($namaLengkap) > 255) {
    setFlash('error', 'Name must not exceed 255 characters.');
    redirect(baseUrl('pages/profile/edit.php'));
}

// Validate email
if (empty($email)) {
    setFlash('error', 'Please enter your email address.');
    redirect(baseUrl('pages/profile/edit.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
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
            setFlash('error', 'Email is already taken by another user.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
    }
    
    // Handle password change
    $passwordUpdate = '';
    $passwordParam = null;
    
    if (!empty($newPassword)) {
        // Validate current password
        if (empty($currentPassword)) {
            setFlash('error', 'Please enter your current password to change it.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        if (!password_verify($currentPassword, $currentUser['Password'])) {
            setFlash('error', 'Current password is incorrect.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            setFlash('error', 'New password must be at least 6 characters.');
            redirect(baseUrl('pages/profile/edit.php'));
        }
        
        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'New passwords do not match.');
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
    
    setFlash('success', 'Profile updated successfully!');
    redirect(baseUrl('pages/profile/index.php'));
    
} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    redirect(baseUrl('pages/profile/edit.php'));
}
