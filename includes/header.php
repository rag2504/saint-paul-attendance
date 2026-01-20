<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'Saint Paul';
$role = $_SESSION['user_role'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($page_title); ?> | Saint Paul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/saint-paul/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/saint-paul/index.php">Saint Paul</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/admin/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/admin/students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/admin/timetable.php">Timetable</a></li>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/admin/attendance.php">Attendance</a></li>
                <?php elseif ($role === 'student'): ?>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/student/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/student/timetable.php">Timetable</a></li>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/student/attendance.php">Attendance</a></li>
                <?php endif; ?>
                <?php if ($role): ?>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/saint-paul/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid py-4">

