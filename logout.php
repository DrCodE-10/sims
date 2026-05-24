<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['user_id'])) {
    try {
        clear_remember_me(db(), (int) $_SESSION['user_id']);
    } catch (Throwable $e) {
        // continue logout
    }
}

$_SESSION = [];
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

header('Location: login.php');
exit();
