<?php
// auth_check.php - Include this file to protect pages that require authentication

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=login_required");
    exit();
}

// Check session timeout (optional - 4 hours)
$timeout_duration = 4 * 60 * 60; // 4 hours in seconds
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
    // Session has expired
    session_unset();
    session_destroy();
    header("Location: login.php?message=session_expired");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Helper function to check user role
function hasRole($required_role)
{
    return isset($_SESSION['posisi']) && $_SESSION['posisi'] === $required_role;
}

// Helper function to check if user is admin
function isAdmin()
{
    return hasRole('Manager') || hasRole('Admin');
}

// Helper function to get current user info
function getCurrentUser()
{
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nama' => $_SESSION['nama_karyawan'] ?? null,
        'posisi' => $_SESSION['posisi'] ?? null
    ];
}
