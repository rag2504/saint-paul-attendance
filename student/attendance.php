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
<div class="card p-3 mb-3">
    <form class="row g-2">
        <div class="col-md-4">
            <label class="form-label">Month</label>
            <input type="month" name="month" value="<?php echo e($selected_month); ?>" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card p-3">
            <p class="text-muted mb-1">Present</p>
            <h3 class="mb-0 text-success"><?php echo $summary['present']; ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <p class="text-muted mb-1">Absent</p>
            <h3 class="mb-0 text-danger"><?php echo $summary['absent']; ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <p class="text-muted mb-1">Attendance %</p>
            <h3 class="mb-0"><?php echo $percentage; ?>%</h3>
        </div>
    </div>
</div>

<div class="card p-3">
    <h5 class="mb-3">Attendance Records</h5>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr><th>Date</th><th>Time Slot</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?php echo e($r['attendance_date']); ?></td>
                    <td><?php echo e($r['time_slot']); ?></td>
                    <td class="<?php echo $r['status'] === 'Present' ? 'status-present' : 'status-absent'; ?>">
                        <?php echo e($r['status']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?>
                <tr><td colspan="3" class="text-center text-muted">No attendance recorded for this month.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

