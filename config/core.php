<?php
// Core configuration
session_start();

define('BASE_URL', '/smart-advisor');
define('APP_NAME', 'Smart Advisor Eswatini');

// Include database connection
require_once __DIR__ . '/database.php';

// Helper function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Ensure user is logged in
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

// Ensure specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        // Redirect to their appropriate dashboard
        if (hasRole('admin')) redirect('/admin/dashboard.php');
        elseif (hasRole('officer')) redirect('/officer/dashboard.php');
        else redirect('/farmer/dashboard.php');
    }
}

// Format currency
function formatSZL($amount) {
    return 'E' . number_format($amount, 2);
}
?>
