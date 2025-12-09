<?php
/**
 * Create Album Action Handler
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(baseUrl('pages/album/index.php'));
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request. Please try again.');
    redirect(baseUrl('pages/album/index.php'));
}

// Get form data
$namaAlbum = trim($_POST['nama_album'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] === 1 : true;

// Validate album name
if (empty($namaAlbum)) {
    setFlash('error', 'Please enter an album name.');
    redirect(baseUrl('pages/album/index.php'));
}

if (strlen($namaAlbum) > 255) {
    setFlash('error', 'Album name must not exceed 255 characters.');
    redirect(baseUrl('pages/album/index.php'));
}

try {
    $pdo = db();
    
    // Insert new album
    $stmt = $pdo->prepare("
        INSERT INTO gallery_album (NamaAlbum, Deskripsi, TanggalDibuat, UserID, is_public)
        VALUES (?, ?, CURDATE(), ?, ?)
    ");
    
    $stmt->execute([
        $namaAlbum,
        $deskripsi ?: null,
        getCurrentUserId(),
        $isPublic ? 1 : 0
    ]);
    
    $albumId = $pdo->lastInsertId();
    
    setFlash('success', 'Album "' . e($namaAlbum) . '" created successfully!');
    redirect(baseUrl('pages/album/view.php?id=' . $albumId));
    
} catch (PDOException $e) {
    error_log("Create album error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    redirect(baseUrl('pages/album/index.php'));
}
