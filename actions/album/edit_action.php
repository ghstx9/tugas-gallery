<?php
/**
 * Edit Album Action Handler
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
$albumId = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
$namaAlbum = trim($_POST['nama_album'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$coverPhotoId = !empty($_POST['cover_photo_id']) ? (int)$_POST['cover_photo_id'] : null;
$isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] === 1 : true;

if ($albumId <= 0) {
    setFlash('error', 'Invalid album.');
    redirect(baseUrl('pages/album/index.php'));
}

// Validate album name
if (empty($namaAlbum)) {
    setFlash('error', 'Please enter an album name.');
    redirect(baseUrl('pages/album/edit.php?id=' . $albumId));
}

if (strlen($namaAlbum) > 255) {
    setFlash('error', 'Album name must not exceed 255 characters.');
    redirect(baseUrl('pages/album/edit.php?id=' . $albumId));
}

try {
    $pdo = db();
    
    // Check if album exists and belongs to user
    $stmt = $pdo->prepare("SELECT AlbumID FROM gallery_album WHERE AlbumID = ? AND UserID = ?");
    $stmt->execute([$albumId, getCurrentUserId()]);
    if (!$stmt->fetch()) {
        setFlash('error', 'Album not found or you do not have permission to edit it.');
        redirect(baseUrl('pages/album/index.php'));
    }
    
    // Validate cover photo if provided
    if ($coverPhotoId !== null) {
        $photoStmt = $pdo->prepare("SELECT FotoID FROM gallery_foto WHERE FotoID = ? AND AlbumID = ?");
        $photoStmt->execute([$coverPhotoId, $albumId]);
        if (!$photoStmt->fetch()) {
            $coverPhotoId = null; // Invalid cover photo, set to null
        }
    }
    
    // Update album
    $updateStmt = $pdo->prepare("
        UPDATE gallery_album 
        SET NamaAlbum = ?, Deskripsi = ?, cover_photo_id = ?, is_public = ?
        WHERE AlbumID = ?
    ");
    
    $updateStmt->execute([
        $namaAlbum,
        $deskripsi ?: null,
        $coverPhotoId,
        $isPublic ? 1 : 0,
        $albumId
    ]);
    
    setFlash('success', 'Album updated successfully.');
    redirect(baseUrl('pages/album/view.php?id=' . $albumId));
    
} catch (PDOException $e) {
    error_log("Edit album error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    redirect(baseUrl('pages/album/edit.php?id=' . $albumId));
}
