<?php
$page_title = 'My Attendance';
require_once __DIR__ . '/../includes/header.php';
requireStudent();

$student_id = $_SESSION['user_id'];
$selected_month = $_GET['month'] ?? date('Y-m');
$start_date = date('Y-m-01', strtotime($selected_month));
$end_date = date('Y-m-t', strtotime($selected_month));

$stmt = $mysqli->prepare('SELECT attendance_date, time_slot, status FROM attendance WHERE student_id = ? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date DESC, time_slot DESC');
$stmt->bind_param('sss', $student_id, $start_date, $end_date);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$summary = ['present' => 0, 'absent' => 0];
foreach ($records as $r) {
    if ($r['status'] === 'Present') {
        $summary['present']++;
    } else {
        $summary['absent']++;
    }
}
$total_days = $summary['present'] + $summary['absent'];
$percentage = $total_days ? round($summary['present'] / $total_days * 100, 1) : 0;
?>

<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .page-header h2 {
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 2rem;
    }
    
    .page-header p {
        opacity: 0.95;
        margin-bottom: 0;
        font-size: 1.05rem;
    }
    
    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }
    
    .filter-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-card h5 i {
        color: #667eea;
    }
    
    .form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.65rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.65rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
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
    
    .stat-card.green::before {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }
    
    .stat-card.red::before {
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
        margin-bottom: 1rem;
    }
    
    .stat-icon.green {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: white;
    }
    
    .stat-icon.red {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .stat-icon.blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
    
    .records-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }
    
    .records-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .records-card h5 i {
        color: #667eea;
    }
    
    .records-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .records-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }
    
    .records-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    
    .records-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    
    .records-table tbody tr {
        transition: all 0.3s ease;
        background: white;
    }
    
    .records-table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .records-table tbody td {
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
        font-size: 0.95rem;
        vertical-align: middle;
    }
    
    .date-badge {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #2d3748;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
    }
    
    .time-badge {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #2d3748;
        padding: 0.4rem 0.9rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .status-badge {
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge.present {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(132, 250, 176, 0.3);
    }
    
    .status-badge.absent {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(240, 147, 251, 0.3);
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
    
    .progress-widget {
        background: #f7fafc;
        padding: 1.5rem;
        border-radius: 12px;
        margin-top: 1.5rem;
    }
    
    .progress-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .progress-custom {
        height: 16px;
        border-radius: 10px;
        background: #e2e8f0;
    }
    
    .progress-bar-custom {
        border-radius: 10px;
        transition: width 0.6s ease;
    }
    
    .progress-bar-custom.high {
        background: linear-gradient(90deg, #84fab0 0%, #8fd3f4 100%);
    }
    
    .progress-bar-custom.medium {
        background: linear-gradient(90deg, #ffecd2 0%, #fcb69f 100%);
    }
    
    .progress-bar-custom.low {
        background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
        .filter-card, .records-card {
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .records-table thead th,
        .records-table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
        }
    }
</style>

<div class="page-header">
    <h2><i class="bi bi-clipboard-data me-2"></i>My Attendance</h2>
    <p>Track your attendance records and statistics</p>
</div>

<div class="filter-card">
    <h5><i class="bi bi-funnel-fill"></i>Filter Records</h5>
    <form class="row g-3 align-items-end">
        <div class="col-md-8 col-lg-9">
            <label class="form-label"><i class="bi bi-calendar-month me-1"></i>Select Month</label>
            <input type="month" name="month" value="<?php echo e($selected_month); ?>" class="form-control" required>
        </div>
        <div class="col-md-4 col-lg-3">
            <button class="btn btn-filter w-100" type="submit">
                <i class="bi bi-search me-2"></i>Filter
            </button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="stat-card green">
            <div class="stat-icon green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <p class="stat-label">Days Present</p>
            <h3 class="stat-number"><?php echo $summary['present']; ?></h3>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card red">
            <div class="stat-icon red">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <p class="stat-label">Days Absent</p>
            <h3 class="stat-number"><?php echo $summary['absent']; ?></h3>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card blue">
            <div class="stat-icon blue">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <p class="stat-label">Attendance Rate</p>
            <h3 class="stat-number"><?php echo $percentage; ?>%</h3>
        </div>
    </div>
</div>

<div class="records-card">
    <h5><i class="bi bi-list-ul"></i>Attendance Records for <?php echo date('F Y', strtotime($selected_month)); ?></h5>
    
    <div class="progress-widget">
        <div class="progress-label">
            <span><i class="bi bi-bar-chart-fill me-2"></i>Overall Attendance</span>
            <span><?php echo $percentage; ?>%</span>
        </div>
        <div class="progress progress-custom">
            <div class="progress-bar progress-bar-custom <?php echo $percentage >= 75 ? 'high' : ($percentage >= 50 ? 'medium' : 'low'); ?>" 
                 role="progressbar" 
                 style="width: <?php echo $percentage; ?>%;" 
                 aria-valuenow="<?php echo $percentage; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
    </div>
    
    <div class="table-responsive mt-4">
        <table class="table records-table">
            <thead>
            <tr>
                <th><i class="bi bi-calendar-day me-1"></i>Date</th>
                <th><i class="bi bi-clock me-1"></i>Time Slot</th>
                <th><i class="bi bi-clipboard-check me-1"></i>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><span class="date-badge"><?php echo date('M d, Y', strtotime($r['attendance_date'])); ?></span></td>
                    <td><span class="time-badge"><?php echo e($r['time_slot']); ?></span></td>
                    <td>
                        <span class="status-badge <?php echo $r['status'] === 'Present' ? 'present' : 'absent'; ?>">
                            <i class="bi bi-<?php echo $r['status'] === 'Present' ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i>
                            <?php echo e($r['status']); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?>
                <tr>
                    <td colspan="3" class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0">No attendance records found for this month.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>