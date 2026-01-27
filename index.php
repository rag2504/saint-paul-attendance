<?php
$page_title = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Hide navbar on welcome page */
    .modern-navbar {
        display: none !important;
    }
    
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .container-fluid {
        padding: 2rem !important;
        max-width: 100% !important;
        width: 100% !important;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .welcome-wrapper {
        width: 100%;
        max-width: 1200px;
    }
    
    .welcome-hero {
        background: white;
        border-radius: 20px;
        padding: 4rem 3rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .hero-logo {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .hero-logo i {
        font-size: 4rem;
        color: white;
    }
    
    .welcome-title {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
    }
    
    .welcome-subtitle {
        font-size: 1.2rem;
        color: #718096;
        margin-bottom: 3rem;
    }
    
    .login-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .login-card {
        background: white;
        border: 3px solid #e2e8f0;
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .login-card:hover::before {
        transform: scaleX(1);
    }
    
    .login-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        border-color: #667eea;
    }
    
    .login-card-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        transition: all 0.3s ease;
    }
    
    .login-card:hover .login-card-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scale(1.1) rotate(5deg);
    }
    
    .login-card-icon i {
        font-size: 2.5rem;
        color: #667eea;
        transition: all 0.3s ease;
    }
    
    .login-card:hover .login-card-icon i {
        color: white;
    }
    
    .login-card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.75rem;
    }
    
    .login-card-description {
        color: #718096;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }
    
    .login-card-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    
    .login-card:hover .login-card-btn {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .info-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .info-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .info-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
    
    .info-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }
    
    .info-steps {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-steps li {
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .info-steps li:last-child {
        border-bottom: none;
    }
    
    .step-number {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    
    .step-content {
        flex: 1;
        padding-top: 0.25rem;
    }
    
    .step-text {
        color: #4a5568;
        line-height: 1.6;
        margin: 0;
    }
    
    .step-code {
        background: #f7fafc;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        color: #667eea;
        font-family: monospace;
        font-size: 0.9rem;
    }
    
    .step-highlight {
        color: #2d3748;
        font-weight: 600;
    }
    
    @media (max-width: 991px) {
        .login-options {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .welcome-hero {
            padding: 3rem 2rem;
        }
        
        .welcome-title {
            font-size: 2rem;
        }
        
        .welcome-subtitle {
            font-size: 1rem;
        }
        
        .hero-logo {
            width: 100px;
            height: 100px;
        }
        
        .hero-logo i {
            font-size: 3rem;
        }
        
        .login-card {
            padding: 2rem 1.5rem;
        }
        
        .info-card {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="welcome-wrapper">
    <div class="welcome-hero">
        <div class="hero-logo">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h1 class="welcome-title">Saint Paul School Portal</h1>
        <p class="welcome-subtitle">Your gateway to academic excellence and seamless school management</p>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="login-options">
                <a href="/saint-paul/login.php?role=admin" class="login-card">
                    <div class="login-card-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h2 class="login-card-title">Admin Portal</h2>
                    <p class="login-card-description">Manage students, timetables, and attendance records</p>
                    <span class="login-card-btn">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                        Login as Admin
                    </span>
                </a>
                
                <a href="/saint-paul/login.php?role=student" class="login-card">
                    <div class="login-card-icon">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h2 class="login-card-title">Student Portal</h2>
                    <p class="login-card-description">View your timetable, attendance, and academic information</p>
                    <span class="login-card-btn">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                        Login as Student
                    </span>
                </a>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="info-card">
                <div class="info-header">
                    <div class="info-icon">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                    </div>
                    <h2 class="info-title">Getting Started</h2>
                </div>
                <ol class="info-steps">
                    <li>
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <p class="step-text">Import <span class="step-code">database.sql</span> in phpMyAdmin</p>
                        </div>
                    </li>
                    <li>
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <p class="step-text">Update DB credentials in <span class="step-code">includes/config.php</span></p>
                        </div>
                    </li>
                    <li>
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <p class="step-text">Login as admin with ID <span class="step-highlight">admin001</span> / Password <span class="step-highlight">Admin@123</span></p>
                        </div>
                    </li>
                    <li>
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <p class="step-text">Create students and set their passwords to get started!</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>