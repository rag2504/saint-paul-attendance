<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$total_students = $mysqli->query('SELECT COUNT(*) AS c FROM students')->fetch_assoc()['c'] ?? 0;
$total_classes = $mysqli->query('SELECT COUNT(DISTINCT CONCAT(class,"-",section)) AS c FROM students')->fetch_assoc()['c'] ?? 0;

$today = date('Y-m-d');
$stmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM attendance WHERE attendance_date = ? AND status = "Present"');
$stmt->bind_param('s', $today);
$stmt->execute();
$today_present = $stmt->get_result()->fetch_assoc()['c'] ?? 0;

// Calculate attendance percentage for today
$attendance_percentage = $total_students > 0 ? round(($today_present / $total_students) * 100, 1) : 0;
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .dashboard-header h2 {
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .dashboard-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: none;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--primary-gradient);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card.purple::before {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card.pink::before {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card.blue::before {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }
    
    .stat-icon.purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stat-icon.pink {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .stat-icon.blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0.5rem 0;
    }
    
    .stat-label {
        color: #718096;
        font-size: 0.95rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .quick-actions-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-top: 2rem;
    }
    
    .quick-actions-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
    }
    
    .action-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
    }
    
    .action-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .action-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .action-btn-secondary {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    .action-btn-secondary:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    
    .action-btn-success {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
    }
    
    .action-btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(240, 147, 251, 0.4);
        color: white;
    }
    
    .attendance-widget {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-top: 2rem;
    }
    
    .attendance-widget h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
    }
    
    .progress-custom {
        height: 12px;
        border-radius: 10px;
        background: #e2e8f0;
    }
    
    .progress-bar-custom {
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        border-radius: 10px;
    }
    
    .attendance-label {
        font-size: 0.9rem;
        color: #718096;
        margin-bottom: 0.5rem;
    }
    
    .attendance-percent {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .action-btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="dashboard-header">
    <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
    <p>Welcome back! Here's what's happening today.</p>
</div>

<div class="row g-4">
    <div class="col-lg-4 col-md-6">
        <div class="stat-card purple">
            <div class="d-flex align-items-center">
                <div class="stat-icon purple">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="ms-auto text-end">
                    <p class="stat-label mb-1">Total Students</p>
                    <h3 class="stat-number"><?php echo number_format($total_students); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card pink">
            <div class="d-flex align-items-center">
                <div class="stat-icon pink">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="ms-auto text-end">
                    <p class="stat-label mb-1">Classes & Sections</p>
                    <h3 class="stat-number"><?php echo number_format($total_classes); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card blue">
            <div class="d-flex align-items-center">
                <div class="stat-icon blue">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
                <div class="ms-auto text-end">
                    <p class="stat-label mb-1">Present Today</p>
                    <h3 class="stat-number"><?php echo number_format($today_present); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="quick-actions-card">
            <h5><i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions</h5>
            <div class="d-flex gap-3 flex-wrap">
                <a class="action-btn action-btn-primary" href="/saint-paul/admin/students.php">
                    <i class="bi bi-person-plus me-2"></i>Manage Students
                </a>
                <a class="action-btn action-btn-secondary" href="/saint-paul/admin/timetable.php">
                    <i class="bi bi-calendar-week me-2"></i>Manage Timetable
                </a>
                <a class="action-btn action-btn-success" href="/saint-paul/admin/attendance.php">
                    <i class="bi bi-check2-circle me-2"></i>Record Attendance
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="attendance-widget">
            <h5><i class="bi bi-graph-up me-2"></i>Today's Attendance</h5>
            <div class="text-center mb-3">
                <p class="attendance-percent mb-0"><?php echo $attendance_percentage; ?>%</p>
                <p class="attendance-label">Attendance Rate</p>
            </div>
            <div class="progress progress-custom">
                <div class="progress-bar progress-bar-custom" role="progressbar" 
                     style="width: <?php echo $attendance_percentage; ?>%;" 
                     aria-valuenow="<?php echo $attendance_percentage; ?>" 
                     aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <small class="text-muted"><?php echo $today_present; ?> present</small>
                <small class="text-muted"><?php echo $total_students - $today_present; ?> absent</small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>