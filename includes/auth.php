<?php
/**
 * Authentication Helpers
 * Functions for user authentication and authorization
 */

require_once __DIR__ . '/session.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'Admin';
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data from session
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'nama_lengkap' => $_SESSION['nama_lengkap'] ?? '',
        'level' => $_SESSION['user_level'] ?? 'User'
    ];
}

/**
 * Set user session data after login
 */
function setUserSession(array $user): void {
    regenerateSession(); // Prevent session fixation
    
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['email'] = $user['Email'];
    $_SESSION['nama_lengkap'] = $user['NamaLengkap'];
    $_SESSION['user_level'] = $user['Level'];
    $_SESSION['login_time'] = time();
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /tugasgallery/pages/auth/login.php');
        exit;
    }
}

/**
 * Require admin role - redirect if not admin
 */
function requireAdmin(): void {
    requireAuth();
    
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: /tugasgallery/pages/gallery/index.php');
        exit;
    }
}

/**
 * Redirect logged-in users away from auth pages
 */
function redirectIfLoggedIn(): void {
    if (isLoggedIn()) {
        header('Location: /tugasgallery/pages/gallery/index.php');
        exit;
    }
}

/**
 * Get redirect URL after login
 */
function getRedirectAfterLogin(): string {
    $redirect = $_SESSION['redirect_after_login'] ?? '/tugasgallery/pages/gallery/index.php';
    unset($_SESSION['redirect_after_login']);
    return $redirect;
}
