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
        setFlash('error', 'Komentar tidak valid.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    try {
        $pdo = db();
        
        // Check if user owns the comment or is admin
        $stmt = $pdo->prepare("SELECT UserID FROM gallery_komentarfoto WHERE KomentarID = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        if (!$comment) {
            setFlash('error', 'Komentar tidak ditemukan.');
            redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        }
        
        if ($comment['UserID'] != getCurrentUserId() && !isAdmin()) {
            setFlash('error', 'Anda tidak memiliki izin untuk menghapus komentar ini.');
            redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        }
        
        // Delete the comment
        $deleteStmt = $pdo->prepare("DELETE FROM gallery_komentarfoto WHERE KomentarID = ?");
        $deleteStmt->execute([$commentId]);
        
        setFlash('success', 'Komentar berhasil dihapus.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        
    } catch (PDOException $e) {
        error_log("Delete comment error: " . $e->getMessage());
        setFlash('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
} else {
    // Add comment
    $photoId = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    
    if ($photoId <= 0) {
        setFlash('error', 'Foto tidak ditemukan.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    if (empty($comment)) {
        setFlash('error', 'Silahkan masukkan komentar.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    if (strlen($comment) > 1000) {
        setFlash('error', 'Komentar terlalu panjang. Maksimum 1000 karakter.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    try {
        $pdo = db();
        
        // Check if photo exists
        $photoStmt = $pdo->prepare("SELECT FotoID FROM gallery_foto WHERE FotoID = ?");
        $photoStmt->execute([$photoId]);
        if (!$photoStmt->fetch()) {
            setFlash('error', 'Foto tidak ditemukan.');
            redirect(baseUrl('pages/gallery/index.php'));
        }
        
        // Insert comment
        $stmt = $pdo->prepare("
            INSERT INTO gallery_komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->execute([$photoId, getCurrentUserId(), $comment]);
        
        setFlash('success', 'Komentar berhasil ditambahkan.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
        
    } catch (PDOException $e) {
        error_log("Add comment error: " . $e->getMessage());
        setFlash('error', 'Terjadi kesalahan. Silahkan coba lagi.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
}
