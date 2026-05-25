<?php
/**
 * _auth.php — Include at the TOP of every protected admin page (before any output).
 * Handles:
 *   1. No-cache headers so the browser back button cannot show cached pages after logout.
 *   2. Session validation — redirects to login if not authenticated.
 */

session_start();

// Prevent browser from caching any protected page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
?>
