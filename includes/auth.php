<?php
/**
 * ============================================================
 * REPENT† — Authentication Helper
 * File: includes/auth.php
 *
 * PURPOSE: Starts the PHP session and provides helper 
 * functions used across the website to check login status,
 * roles, and protect pages that require authentication.
 * ============================================================
 */

// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * isLoggedIn()
 * Returns true if a user is currently logged in.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * isAdmin()
 * Returns true if the logged-in user has the 'admin' role.
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * requireLogin()
 * Redirects to the login page if the user is not logged in.
 * Use this at the top of any page that requires authentication.
 */
function requireLogin($redirect = '') {
    if (!isLoggedIn()) {
        $url = '../pages/login.php';
        if ($redirect) $url .= '?redirect=' . urlencode($redirect);
        header("Location: $url");
        exit();
    }
}

/**
 * requireAdmin()
 * Redirects to the homepage if the user is not an admin.
 * Use this at the top of admin-only pages.
 */
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit();
    }
}

/**
 * getCurrentUser()
 * Returns an array with the current user's session data,
 * or null if no user is logged in.
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name']  ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['role']        ?? 'customer',
        ];
    }
    return null;
}
