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
    setFlash('error', 'Permintaan tidak valid. Silakan coba lagi.');
    redirect(baseUrl('pages/album/index.php'));
}

// Get form data
$namaAlbum = trim($_POST['nama_album'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] === 1 : true;

// Validate album name
if (empty($namaAlbum)) {
    setFlash('error', 'Silakan masukkan nama album.');
    redirect(baseUrl('pages/album/index.php'));
}

if (strlen($namaAlbum) > 255) {
    setFlash('error', 'Nama album tidak boleh melebihi 255 karakter.');
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
    
    setFlash('success', 'Album "' . e($namaAlbum) . '" berhasil dibuat!');
    redirect(baseUrl('pages/album/view.php?id=' . $albumId));
    
} catch (PDOException $e) {
    error_log("Create album error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silakan coba lagi.');
    redirect(baseUrl('pages/album/index.php'));
}
