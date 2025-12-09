<?php
/**
 * Delete Album Action Handler
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

// Get album ID
$albumId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($albumId <= 0) {
    setFlash('error', 'Invalid album.');
    redirect(baseUrl('pages/album/index.php'));
}

try {
    $pdo = db();
    
    // Check if album exists and belongs to user
    $stmt = $pdo->prepare("SELECT AlbumID, NamaAlbum FROM gallery_album WHERE AlbumID = ? AND UserID = ?");
    $stmt->execute([$albumId, getCurrentUserId()]);
    $album = $stmt->fetch();
    
    if (!$album) {
        setFlash('error', 'Album not found or you do not have permission to delete it.');
        redirect(baseUrl('pages/album/index.php'));
    }
    
    // Delete album (photos will be orphaned due to ON DELETE SET NULL)
    $deleteStmt = $pdo->prepare("DELETE FROM gallery_album WHERE AlbumID = ?");
    $deleteStmt->execute([$albumId]);
    
    setFlash('success', 'Album "' . e($album['NamaAlbum']) . '" deleted successfully. Photos have been moved out of the album.');
    redirect(baseUrl('pages/album/index.php'));
    
} catch (PDOException $e) {
    error_log("Delete album error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    redirect(baseUrl('pages/album/index.php'));
}
