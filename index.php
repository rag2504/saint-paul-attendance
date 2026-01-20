<?php
$page_title = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <h1 class="h4 mb-3">Welcome to Saint Paul School Portal</h1>
            <p class="text-muted mb-4">Choose your panel to continue.</p>
            <div class="d-grid gap-3">
                <a href="/saint-paul/login.php?role=admin" class="btn btn-primary btn-lg">Admin Login</a>
                <a href="/saint-paul/login.php?role=student" class="btn btn-outline-primary btn-lg">Student Login</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 sidebar-card">
            <h2 class="h6 text-uppercase text-muted mb-3">Getting Started</h2>
            <ol class="small mb-0">
                <li>Import <code>database.sql</code> in phpMyAdmin.</li>
                <li>Update DB creds in <code>includes/config.php</code> if needed.</li>
                <li>Login as admin with ID <strong>admin001</strong> / <strong>Admin@123</strong>.</li>
                <li>Create students and set their passwords.</li>
            </ol>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

