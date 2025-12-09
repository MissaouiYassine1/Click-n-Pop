<?php
require_once __DIR__ . '/../config/auth.php';

try {
    auth()->logout();
    header('Location: /auth?message=logged_out');
    exit;
} catch (Exception $e) {
    header('Location: /auth?error=logout_failed');
    exit;
}
?>