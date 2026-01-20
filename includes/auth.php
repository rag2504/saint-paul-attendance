<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_role'], $_SESSION['user_id']);
}

function requireRole(string $role): void
{
    if (!isLoggedIn() || $_SESSION['user_role'] !== $role) {
        header('Location: /saint-paul/login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireRole('admin');
}

function requireStudent(): void
{
    requireRole('student');
}

function logout(): void
{
    session_unset();
    session_destroy();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

