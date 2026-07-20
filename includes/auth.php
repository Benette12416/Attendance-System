<?php
/**
 * Include this at the TOP of every protected admin page.
 * Requires config.php to already be included (session_start happens there).
 */

function requireLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}
