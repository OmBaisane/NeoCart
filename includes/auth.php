<?php
// simple auth helpers. Include this when you need programmatic checks.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        // if file is in /pages/* this redirect works
        $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        header('Location: pages/login.php?redirect=' . urlencode($redirect));
        exit();
    }
}

function require_admin() {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        header('Location: admin/login.php');
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}