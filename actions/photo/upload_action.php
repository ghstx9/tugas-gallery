<?php
/**
 * Photo Upload Action Handler
 * Processes photo upload form submissions
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(baseUrl('pages/gallery/upload.php'));
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request. Please try again.');
    redirect(baseUrl('pages/gallery/upload.php'));
}

// Check if file was uploaded
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
    setFlash('error', 'Please select a photo to upload.');
    redirect(baseUrl('pages/gallery/upload.php'));
}

// Get form data
$judul = trim($_POST['judul'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$albumId = !empty($_POST['album_id']) ? (int)$_POST['album_id'] : null;
$isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] === 1 : true;
$file = $_FILES['photo'];

// Validate title
if (empty($judul)) {
    setFlash('error', 'Please enter a title for your photo.');
    redirect(baseUrl('pages/gallery/upload.php'));
}

if (strlen($judul) > 255) {
    setFlash('error', 'Title must not exceed 255 characters.');
    redirect(baseUrl('pages/gallery/upload.php'));
}

// Validate file
$fileErrors = validateImageUpload($file);
if (!empty($fileErrors)) {
    setFlash('error', implode(' ', $fileErrors));
    redirect(baseUrl('pages/gallery/upload.php'));
}

// Validate album ownership if album is selected
if ($albumId !== null) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT AlbumID FROM gallery_album WHERE AlbumID = ? AND UserID = ?");
        $stmt->execute([$albumId, getCurrentUserId()]);
        if (!$stmt->fetch()) {
            setFlash('error', 'Invalid album selected.');
            redirect(baseUrl('pages/gallery/upload.php'));
        }
    } catch (PDOException $e) {
        error_log("Album validation error: " . $e->getMessage());
        setFlash('error', 'An error occurred. Please try again.');
        redirect(baseUrl('pages/gallery/upload.php'));
    }
}

// Generate unique filename
$uniqueFilename = generateUniqueFilename($file['name']);
$uploadDir = __DIR__ . '/../../uploads/photos/';
$uploadPath = $uploadDir . $uniqueFilename;
$dbPath = 'photos/' . $uniqueFilename;

// Create upload directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    setFlash('error', 'Failed to upload file. Please try again.');
    redirect(baseUrl('pages/gallery/upload.php'));
}

// Get file info
$fileSize = filesize($uploadPath);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $uploadPath);
finfo_close($finfo);
$fileType = getExtensionFromMime($mimeType);

try {
    $pdo = db();
    
    // Insert photo record
    $stmt = $pdo->prepare("
        INSERT INTO gallery_foto (
            JudulFoto, 
            DeskripsiFoto, 
            TanggalUnggah, 
            LokasiFile, 
            AlbumID, 
            UserID, 
            is_public, 
            view_count, 
            file_size, 
            file_type
        ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, 0, ?, ?)
    ");
    
    $stmt->execute([
        $judul,
        $deskripsi ?: null,
        $dbPath,
        $albumId,
        getCurrentUserId(),
        $isPublic ? 1 : 0,
        $fileSize,
        $fileType
    ]);
    
    $photoId = $pdo->lastInsertId();
    
    setFlash('success', 'Photo uploaded successfully!');
    redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    
} catch (PDOException $e) {
    // Delete uploaded file on database error
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    error_log("Photo upload error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    redirect(baseUrl('pages/gallery/upload.php'));
}
