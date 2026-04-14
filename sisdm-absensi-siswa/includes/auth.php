<?php
/**
 * Authentication Helper Functions (Deprecated - Use Auth class instead)
 * Kept for backward compatibility
 */

function isLoggedIn() {
    $auth = Auth::getInstance();
    return $auth->isLoggedIn();
}

function isAdmin() {
    $auth = Auth::getInstance();
    return $auth->isAdmin();
}

function isOfficer() {
    $auth = Auth::getInstance();
    return $auth->isOfficer();
}

function requireLogin() {
    $auth = Auth::getInstance();
    $auth->requireLogin();
}

function requireAdmin() {
    $auth = Auth::getInstance();
    $auth->requireAdmin();
}

function requireOfficer() {
    $auth = Auth::getInstance();
    $auth->requireOfficer();
}

function logout() {
    $auth = Auth::getInstance();
    $auth->logout();
}

function getCurrentUser() {
    $auth = Auth::getInstance();
    return $auth->getCurrentUser();
}

function getActiveOfficer() {
    $auth = Auth::getInstance();
    return $auth->getActiveOfficer();
}
