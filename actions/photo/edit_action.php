<?php
/**
 * Edit Photo Action Handler
 * Processes photo edit form submissions
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(baseUrl('pages/gallery/index.php'));
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Permintaan tidak valid. Silahkan coba lagi.');
    redirect(baseUrl('pages/gallery/index.php'));
}

// Get form data
$photoId = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
$judul = trim($_POST['judul'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$albumId = !empty($_POST['album_id']) ? (int)$_POST['album_id'] : null;
$isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] === 1 : true;

if ($photoId <= 0) {
    setFlash('error', 'Foto tidak ditemukan.');
    redirect(baseUrl('pages/gallery/index.php'));
}

// Validate title
if (empty($judul)) {
    setFlash('error', 'Silahkan masukkan judul untuk foto.');
    redirect(baseUrl('pages/gallery/edit.php?id=' . $photoId));
}

if (strlen($judul) > 255) {
    setFlash('error', 'Judul tidak boleh melebihi 255 karakter.');
    redirect(baseUrl('pages/gallery/edit.php?id=' . $photoId));
}

try {
    $pdo = db();
    
    // Get photo info
    $stmt = $pdo->prepare("SELECT * FROM gallery_foto WHERE FotoID = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if (!$photo) {
        setFlash('error', 'Foto tidak ditemukan.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    // Check if user can edit this photo (owner or admin)
    if ($photo['UserID'] != getCurrentUserId() && !isAdmin()) {
        setFlash('error', 'Anda tidak memiliki izin untuk mengedit foto ini.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    // Validate album ownership if album is selected
    if ($albumId !== null) {
        $albumStmt = $pdo->prepare("SELECT AlbumID FROM gallery_album WHERE AlbumID = ? AND UserID = ?");
        $albumStmt->execute([$albumId, $photo['UserID']]);
        if (!$albumStmt->fetch()) {
            setFlash('error', 'Album tidak valid.');
            redirect(baseUrl('pages/gallery/edit.php?id=' . $photoId));
        }
    }
    
    // Update photo
    $updateStmt = $pdo->prepare("
        UPDATE gallery_foto 
        SET JudulFoto = ?, DeskripsiFoto = ?, AlbumID = ?, is_public = ?
        WHERE FotoID = ?
    ");
    
    $updateStmt->execute([
        $judul,
        $deskripsi ?: null,
        $albumId,
        $isPublic ? 1 : 0,
        $photoId
    ]);
    
    setFlash('success', 'Foto berhasil diedit.');
    redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    
} catch (PDOException $e) {
    error_log("Edit foto error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silahkan coba lagi.');
    redirect(baseUrl('pages/gallery/edit.php?id=' . $photoId));
}
