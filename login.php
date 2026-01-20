<?php
$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';

$error = '';
$selected_role = $_GET['role'] ?? 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'admin';
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($role === 'admin') {
        $stmt = $mysqli->prepare('SELECT admin_id, name, password_hash FROM admins WHERE admin_id = ?');
        $stmt->bind_param('s', $login_id);
    } else {
        $stmt = $mysqli->prepare('SELECT student_id, name, class, section, password_hash FROM students WHERE student_id = ?');
        $stmt->bind_param('s', $login_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_role'] = $role;
        $_SESSION['user_id'] = $role === 'admin' ? $user['admin_id'] : $user['student_id'];
        $_SESSION['user_name'] = $user['name'];
        if ($role === 'student') {
            $_SESSION['user_class'] = $user['class'];
            $_SESSION['user_section'] = $user['section'];
        }
        header('Location: ' . ($role === 'admin' ? '/saint-paul/admin/dashboard.php' : '/saint-paul/student/dashboard.php'));
        exit;
    } else {
        $error = 'Invalid credentials. Please try again.';
    }
}
?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card p-4">
            <h1 class="h4 mb-3 text-center">Sign In</h1>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Login as</label>
                    <select name="role" class="form-select">
                        <option value="admin" <?php echo $selected_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="student" <?php echo $selected_role === 'student' ? 'selected' : ''; ?>>Student</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">ID</label>
                    <input type="text" name="login_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

