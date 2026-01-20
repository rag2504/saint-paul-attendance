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

$today = date('l');
$timetable_today_stmt = $mysqli->prepare('SELECT time_slot, subject FROM timetable WHERE class = ? AND section = ? AND day = ? ORDER BY time_slot');
$timetable_today_stmt->bind_param('sss', $class, $section, $today);
$timetable_today_stmt->execute();
$today_timetable = $timetable_today_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <p class="text-muted mb-1">Welcome</p>
            <h4 class="mb-0"><?php echo e($student_name); ?></h4>
            <small class="text-muted">Class <?php echo e($class . ' - ' . $section); ?></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <p class="text-muted mb-1">Present (<?php echo date('M'); ?>)</p>
            <h3 class="mb-0 text-success"><?php echo $stats['present_count'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <p class="text-muted mb-1">Absent (<?php echo date('M'); ?>)</p>
            <h3 class="mb-0 text-danger"><?php echo $stats['absent_count'] ?? 0; ?></h3>
        </div>
    </div>
</div>

<div class="card mt-4 p-3">
    <h5 class="mb-3">Today's Timetable (<?php echo e($today); ?>)</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr><th>Time Slot</th><th>Subject</th></tr>
            </thead>
            <tbody>
            <?php foreach ($today_timetable as $row): ?>
                <tr>
                    <td><?php echo e($row['time_slot']); ?></td>
                    <td><?php echo e($row['subject']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$today_timetable): ?>
                <tr><td colspan="2" class="text-muted text-center">No classes scheduled today.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

