<?php
/**
 * Like/Unlike Photo Action Handler
 * Toggles like status for a photo (AJAX)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to like photos.']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Get photo ID
$photoId = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;

if ($photoId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid photo.']);
    exit;
}

try {
    $pdo = db();
    $userId = getCurrentUserId();
    
    // Check if photo exists
    $photoStmt = $pdo->prepare("SELECT FotoID FROM gallery_foto WHERE FotoID = ?");
    $photoStmt->execute([$photoId]);
    if (!$photoStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Photo not found.']);
        exit;
    }
    
    // Check if user already liked this photo
    $likeStmt = $pdo->prepare("SELECT LikeID FROM gallery_likefoto WHERE FotoID = ? AND UserID = ?");
    $likeStmt->execute([$photoId, $userId]);
    $existingLike = $likeStmt->fetch();
    
    if ($existingLike) {
        // Unlike - remove the like
        $deleteStmt = $pdo->prepare("DELETE FROM gallery_likefoto WHERE LikeID = ?");
        $deleteStmt->execute([$existingLike['LikeID']]);
        $liked = false;
    } else {
        // Like - add new like
        $insertStmt = $pdo->prepare("INSERT INTO gallery_likefoto (FotoID, UserID, TanggalLike) VALUES (?, ?, CURDATE())");
        $insertStmt->execute([$photoId, $userId]);
        $liked = true;
    }
    
    // Get updated like count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_likefoto WHERE FotoID = ?");
    $countStmt->execute([$photoId]);
    $likeCount = $countStmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'like_count' => (int)$likeCount
    ]);
    
} catch (PDOException $e) {
    error_log("Like action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred.']);
}
