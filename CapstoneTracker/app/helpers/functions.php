<?php
/**
 * Helper functions
 */

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login for protected pages
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login');
        exit();
    }
}

/**
 * Get flash message from session
 * @param string $key Message key
 * @return string|null Message content or null
 */
function getFlash($key) {
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

/**
 * Set flash message in session
 * @param string $key Message key
 * @param string $value Message content
 */
function setFlash($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Sanitize output
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>