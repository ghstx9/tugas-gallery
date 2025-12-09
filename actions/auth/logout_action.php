<?php
/**
 * Logout Action Handler
 * Destroys user session and redirects to login
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

// Destroy the session
destroySession();

// Start a new session for flash message
initSession();

// Set success message
setFlash('success', 'You have been logged out successfully.');

// Redirect to login page
redirect('/tugasgallery/pages/auth/login.php');
