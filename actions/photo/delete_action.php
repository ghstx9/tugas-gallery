<?php
/**
 * Delete Photo Action Handler
 * Deletes a photo (Owner or Admin)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

// Get photo ID from query string (for GET) or POST
$photoId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0);

if ($photoId <= 0) {
    setFlash('error', 'Invalid photo.');
    redirect(baseUrl('pages/gallery/index.php'));
}

try {
    $pdo = db();
    
    // Get photo info (need file path to delete and to check ownership)
    $stmt = $pdo->prepare("SELECT FotoID, LokasiFile, UserID FROM gallery_foto WHERE FotoID = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if (!$photo) {
        setFlash('error', 'Foto tidak ditemukan.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    // Check if user is owner or admin
    $isOwner = $photo['UserID'] == getCurrentUserId();
    if (!$isOwner && !isAdmin()) {
        setFlash('error', 'Anda tidak memiliki izin untuk menghapus foto ini.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    // Delete from database (cascades to comments and likes)
    $deleteStmt = $pdo->prepare("DELETE FROM gallery_foto WHERE FotoID = ?");
    $deleteStmt->execute([$photoId]);
    
    // Delete file from server
    $filePath = __DIR__ . '/../../uploads/' . $photo['LokasiFile'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    setFlash('success', 'Foto berhasil dihapus.');
    redirect(baseUrl('pages/gallery/index.php'));
    
} catch (PDOException $e) {
    error_log("Delete photo error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan. Silahkan coba lagi.');
    redirect(baseUrl('pages/gallery/index.php'));
}
