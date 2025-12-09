<?php
/**
 * Session Management
 * Handles secure session initialization and CSRF token management
 */

// Start session with secure settings
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => '',
            'secure' => false, // Set to true in production with HTTPS
            'httponly' => true, // Prevent JavaScript access
            'samesite' => 'Lax' // CSRF protection
        ]);
        
        session_start();
    }
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field for forms
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Regenerate session ID (call after login)
 */
function regenerateSession(): void {
    session_regenerate_id(true);
}

/**
 * Destroy session (logout)
 */
function destroySession(): void {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
}

// Initialize session automatically when this file is included
initSession();
