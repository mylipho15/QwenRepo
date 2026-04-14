<?php
/**
 * Authentication Helper Functions
 */

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isOfficer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'officer';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php?page=login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php?page=dashboard');
        exit;
    }
}

function requireOfficer() {
    requireLogin();
    if (!isOfficer() && !isAdmin()) {
        header('Location: ../index.php?page=dashboard');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    return $user;
}

function getActiveOfficer() {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM attendance_officers WHERE date = CURDATE() AND status = 'active'");
}
