<?php
$page_title = 'Student Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireStudent();

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];
$class = $_SESSION['user_class'];
$section = $_SESSION['user_section'];

$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

$stmt = $mysqli->prepare('SELECT 
    SUM(status="Present") AS present_count,
    SUM(status="Absent") AS absent_count,
    COUNT(*) AS total
    FROM attendance 
    WHERE student_id = ? AND attendance_date BETWEEN ? AND ?');
$stmt->bind_param('sss', $student_id, $month_start, $month_end);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$present_count = $stats['present_count'] ?? 0;
$absent_count = $stats['absent_count'] ?? 0;
$total_count = $stats['total'] ?? 0;
$attendance_percentage = $total_count > 0 ? round(($present_count / $total_count) * 100, 1) : 0;

$today = date('l');
$timetable_today_stmt = $mysqli->prepare('SELECT time_slot, subject FROM timetable WHERE class = ? AND section = ? AND day = ? ORDER BY time_slot');
$timetable_today_stmt->bind_param('sss', $class, $section, $today);
$timetable_today_stmt->execute();
$today_timetable = $timetable_today_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
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
    
    .student-info-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 10px;
        display: inline-block;
        margin-top: 0.5rem;
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
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card.purple::before {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card.green::before {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }
    
    .stat-card.red::before {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }
    
    .stat-icon.purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stat-icon.green {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: white;
    }
    
    .stat-icon.red {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .stat-label {
        color: #718096;
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }
    
    .timetable-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-top: 2rem;
    }
    
    .timetable-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .timetable-card h5 i {
        color: #667eea;
    }
    
    .day-badge {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        display: inline-block;
        margin-left: 0.75rem;
    }
    
    .timetable-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .timetable-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }
    
    .timetable-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    
    .timetable-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    
    .timetable-table tbody tr {
        transition: all 0.3s ease;
        background: white;
    }
    
    .timetable-table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .timetable-table tbody td {
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
        font-size: 0.95rem;
    }
    
    .time-badge {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #2d3748;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .subject-badge {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #a0aec0;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
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
        background: linear-gradient(90deg, #84fab0 0%, #8fd3f4 100%);
        border-radius: 10px;
    }
    
    .attendance-percent {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
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
        
        .timetable-table thead th,
        .timetable-table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
        }
    }
</style>

<div class="dashboard-header">
    <h2><i class="bi bi-person-circle me-2"></i>Student Dashboard</h2>
    <p>Welcome back, <?php echo e($student_name); ?>!</p>
    <span class="student-info-badge">
        <i class="bi bi-mortarboard-fill me-2"></i>
        Class <?php echo e($class); ?> - Section <?php echo e($section); ?>
    </span>
</div>

<div class="row g-4">
    <div class="col-lg-4 col-md-6">
        <div class="stat-card purple">
            <div class="stat-icon purple">
                <i class="bi bi-calendar-month-fill"></i>
            </div>
            <p class="stat-label">This Month</p>
            <h3 class="stat-number"><?php echo date('F Y'); ?></h3>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card green">
            <div class="stat-icon green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <p class="stat-label">Days Present</p>
            <h3 class="stat-number"><?php echo $present_count; ?></h3>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card red">
            <div class="stat-icon red">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <p class="stat-label">Days Absent</p>
            <h3 class="stat-number"><?php echo $absent_count; ?></h3>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="timetable-card">
            <h5>
                <i class="bi bi-calendar-week-fill"></i>Today's Timetable
                <span class="day-badge"><?php echo e($today); ?></span>
            </h5>
            <div class="table-responsive">
                <table class="table timetable-table">
                    <thead>
                    <tr>
                        <th><i class="bi bi-clock me-1"></i>Time Slot</th>
                        <th><i class="bi bi-book me-1"></i>Subject</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($today_timetable as $row): ?>
                        <tr>
                            <td><span class="time-badge"><?php echo e($row['time_slot']); ?></span></td>
                            <td><span class="subject-badge"><?php echo e($row['subject']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$today_timetable): ?>
                        <tr>
                            <td colspan="2" class="empty-state">
                                <i class="bi bi-calendar-x"></i>
                                <p class="mb-0">No classes scheduled today. Enjoy your day off!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="attendance-widget">
            <h5><i class="bi bi-graph-up me-2"></i>Attendance</h5>
            <div class="text-center mb-3">
                <p class="attendance-percent mb-0"><?php echo $attendance_percentage; ?>%</p>
                <p class="text-muted small">Attendance Rate</p>
            </div>
            <div class="progress progress-custom">
                <div class="progress-bar progress-bar-custom" role="progressbar" 
                     style="width: <?php echo $attendance_percentage; ?>%;" 
                     aria-valuenow="<?php echo $attendance_percentage; ?>" 
                     aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <small class="text-muted">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    <?php echo $present_count; ?> present
                </small>
                <small class="text-muted">
                    <i class="bi bi-x-circle-fill text-danger me-1"></i>
                    <?php echo $absent_count; ?> absent
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>