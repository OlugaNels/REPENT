<?php
/**
 * ============================================================
 * REPENT† — Logout
 * File: pages/logout.php
 *
 * PURPOSE: Destroys the current user session and redirects
 * to the homepage. No HTML is rendered on this page.
 * ============================================================
 */

// Start session so we can destroy it
session_start();

// Remove all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect to homepage
header("Location: ../index.php");
exit();
