<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'Saint Paul';
$role = $_SESSION['user_role'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'User';
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
    <style>
        body {
            background: #f8f9fa;
            padding-top: 70px;
        }
        
        /* Remove all underlines from links */
        a {
            text-decoration: none !important;
        }
        
        a:hover {
            text-decoration: none !important;
        }
        
        .modern-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        
        .navbar-brand-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link-modern {
            color: #4a5568 !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link-modern:hover {
            background: #f7fafc;
            color: #667eea !important;
        }
        
        .nav-link-modern.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
        }
        
        .user-badge {
            background: #f7fafc;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: #4a5568;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-link {
            color: #f5576c !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .logout-link:hover {
            background: #fff5f7;
            color: #f5576c !important;
        }
        
        .login-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            font-weight: 500;
            padding: 0.5rem 1.5rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .login-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 991px) {
            .modern-navbar .navbar-collapse {
                margin-top: 1rem;
            }
            
            .nav-link-modern,
            .logout-link {
                margin: 0.25rem 0;
            }
            
            .user-badge {
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg modern-navbar">
    <div class="container-fluid">
        <a class="navbar-brand navbar-brand-text" href="/saint-paul/index.php">
            <i class="bi bi-mortarboard-fill me-2"></i>Saint Paul
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/saint-paul/admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active' : ''; ?>" href="/saint-paul/admin/students.php">
                            <i class="bi bi-people-fill me-1"></i>Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'timetable.php' ? 'active' : ''; ?>" href="/saint-paul/admin/timetable.php">
                            <i class="bi bi-calendar-week-fill me-1"></i>Timetable
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>" href="/saint-paul/admin/attendance.php">
                            <i class="bi bi-clipboard-check-fill me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="user-badge">
                            <i class="bi bi-person-circle"></i>
                            <?php echo e($user_name); ?> (Admin)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="logout-link" href="/saint-paul/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php elseif ($role === 'student'): ?>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/saint-paul/student/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'timetable.php' ? 'active' : ''; ?>" href="/saint-paul/student/timetable.php">
                            <i class="bi bi-calendar-week-fill me-1"></i>Timetable
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>" href="/saint-paul/student/attendance.php">
                            <i class="bi bi-clipboard-check-fill me-1"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="user-badge">
                            <i class="bi bi-person-circle"></i>
                            <?php echo e($user_name); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="logout-link" href="/saint-paul/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="login-link" href="/saint-paul/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid py-4">