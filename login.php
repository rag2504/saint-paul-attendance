<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Login';
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

require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Hide the default navbar for login page */
    .modern-navbar {
        display: none !important;
    }
    
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Override container fluid */
    .container-fluid {
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }
    
    .login-wrapper {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .login-card {
        background: white;
        border-radius: 20px;
        padding: 3rem 2.5rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: none;
        max-width: 480px;
        width: 100%;
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }
    
    .login-logo {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .login-logo i {
        font-size: 2.5rem;
        color: white;
    }
    
    .login-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .login-subtitle {
        color: #718096;
        font-size: 0.95rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .input-icon {
        position: relative;
    }
    
    .input-icon i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 1.1rem;
    }
    
    .input-icon .form-control {
        padding-left: 2.75rem;
    }
    
    .role-selector {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .role-option {
        flex: 1;
        position: relative;
    }
    
    .role-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    
    .role-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .role-label i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #a0aec0;
    }
    
    .role-label span {
        font-weight: 600;
        color: #4a5568;
    }
    
    .role-option input[type="radio"]:checked + .role-label {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }
    
    .role-option input[type="radio"]:checked + .role-label i {
        color: #667eea;
    }
    
    .role-option input[type="radio"]:checked + .role-label span {
        color: #667eea;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.85rem 2rem;
        border-radius: 10px;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        font-size: 1rem;
        width: 100%;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        color: white;
    }
    
    .btn-login:active {
        transform: translateY(0);
    }
    
    .alert-custom {
        border-radius: 10px;
        border: none;
        padding: 1rem 1.25rem;
        font-weight: 500;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-custom i {
        font-size: 1.25rem;
    }
    
    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #a0aec0;
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }
    
    .password-toggle:hover {
        color: #667eea;
    }
    
    .login-footer {
        text-align: center;
        margin-top: 1.5rem;
        color: #718096;
        font-size: 0.85rem;
    }
    
    @media (max-width: 576px) {
        .login-card {
            padding: 2rem 1.5rem;
        }
        
        .login-logo {
            width: 70px;
            height: 70px;
        }
        
        .login-logo i {
            font-size: 2rem;
        }
        
        .login-title {
            font-size: 1.5rem;
        }
        
        .role-label {
            padding: 1rem;
        }
        
        .role-label i {
            font-size: 1.5rem;
        }
        
        .login-wrapper {
            padding: 1rem;
        }
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to access your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-custom">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?php echo e($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="role-selector">
                <div class="role-option">
                    <input type="radio" name="role" value="admin" id="role-admin" <?php echo $selected_role === 'admin' ? 'checked' : ''; ?>>
                    <label for="role-admin" class="role-label">
                        <i class="bi bi-shield-check"></i>
                        <span>Admin</span>
                    </label>
                </div>
                <div class="role-option">
                    <input type="radio" name="role" value="student" id="role-student" <?php echo $selected_role === 'student' ? 'checked' : ''; ?>>
                    <label for="role-student" class="role-label">
                        <i class="bi bi-person-circle"></i>
                        <span>Student</span>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person-badge me-1"></i>Login ID</label>
                <div class="input-icon">
                    <i class="bi bi-hash"></i>
                    <input type="text" name="login_id" class="form-control" placeholder="Enter your ID" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="bi bi-key me-1"></i>Password</label>
                <div class="input-icon" style="position: relative;">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="login-footer">
            <i class="bi bi-shield-lock me-1"></i>
            Secure Login • Saint Paul School
        </div>
    </div>
</div>

<script>
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>