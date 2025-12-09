<?php
/**
 * Comment Action Handler
 * Processes adding/deleting comments on photos
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
    setFlash('error', 'Invalid request. Please try again.');
    redirect($_SERVER['HTTP_REFERER'] ?? baseUrl('pages/gallery/index.php'));
}

// Get action type
$action = $_POST['action'] ?? 'add';

if ($action === 'delete') {
    // Delete comment
    $commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $photoId = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
    
    if ($commentId <= 0) {
        setFlash('error', 'Invalid comment.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    try {
        $pdo = db();
        
        // Check if user owns the comment or is admin
        $stmt = $pdo->prepare("SELECT UserID FROM gallery_komentarfoto WHERE KomentarID = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        if (!$comment) {
            setFlash('error', 'Comment not found.');
            redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        }
        
        if ($comment['UserID'] != getCurrentUserId() && !isAdmin()) {
            setFlash('error', 'You do not have permission to delete this comment.');
            redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        }
        
        // Delete the comment
        $deleteStmt = $pdo->prepare("DELETE FROM gallery_komentarfoto WHERE KomentarID = ?");
        $deleteStmt->execute([$commentId]);
        
        setFlash('success', 'Comment deleted successfully.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        
    } catch (PDOException $e) {
        error_log("Delete comment error: " . $e->getMessage());
        setFlash('error', 'An error occurred. Please try again.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
} else {
    // Add comment
    $photoId = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    
    if ($photoId <= 0) {
        setFlash('error', 'Invalid photo.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    if (empty($comment)) {
        setFlash('error', 'Please enter a comment.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    if (strlen($comment) > 1000) {
        setFlash('error', 'Comment is too long. Maximum 1000 characters.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    try {
        $pdo = db();
        
        // Check if photo exists
        $photoStmt = $pdo->prepare("SELECT FotoID FROM gallery_foto WHERE FotoID = ?");
        $photoStmt->execute([$photoId]);
        if (!$photoStmt->fetch()) {
            setFlash('error', 'Photo not found.');
            redirect(baseUrl('pages/gallery/index.php'));
        }
        
        // Insert comment
        $stmt = $pdo->prepare("
            INSERT INTO gallery_komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->execute([$photoId, getCurrentUserId(), $comment]);
        
        setFlash('success', 'Comment added successfully.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        
    } catch (PDOException $e) {
        error_log("Add comment error: " . $e->getMessage());
        setFlash('error', 'An error occurred. Please try again.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
}
