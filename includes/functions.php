<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Sanitize user input for display (prevent XSS)
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Check if flash message exists
 */
function hasFlash(): bool {
    return isset($_SESSION['flash']);
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate minimum password length
 */
function isValidPassword(string $password, int $minLength = 6): bool {
    return strlen($password) >= $minLength;
}

/**
 * Hash password using bcrypt
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Generate a unique filename for uploads
 */
function generateUniqueFilename(string $originalName): string {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return uniqid('photo_', true) . '.' . $extension;
}

/**
 * Validate uploaded image file
 */
function validateImageUpload(array $file, int $maxSize = 5242880): array {
    $errors = [];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Unggah file gagal.';
        return $errors;
    }
    
    // Check file size (default 5MB)
    if ($file['size'] > $maxSize) {
        $errors[] = 'Ukuran file tidak boleh melebihi ' . ($maxSize / 1024 / 1024) . ' MB.';
    }
    
    // Check MIME type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Hanya gambar JPG, PNG, GIF, dan WebP yang diperbolehkan.';
    }
    
    // Check extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = 'Ekstensi file tidak valid.';
    }
    
    return $errors;
}

/**
 * Get file extension from MIME type
 */
function getExtensionFromMime(string $mimeType): string {
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    
    return $map[$mimeType] ?? 'jpg';
}

/**
 * Format date for display
 */
function formatDate(string $date, string $format = 'd M Y'): string {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime(string $datetime, string $format = 'd M Y, H:i'): string {
    return date($format, strtotime($datetime));
}

/**
 * Get time ago string (e.g., "2 hours ago")
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' menit' . ($mins > 1 ? '' : '') . ' yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam' . ($hours > 1 ? '' : '') . ' yang lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari' . ($days > 1 ? '' : '') . ' yang lalu';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Truncate text with ellipsis
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get base URL
 */
function baseUrl(string $path = ''): string {
    return '/tugasgallery' . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get asset URL
 */
function asset(string $path): string {
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Get upload URL
 */
function uploadUrl(string $path): string {
    return baseUrl('uploads/' . ltrim($path, '/'));
}
