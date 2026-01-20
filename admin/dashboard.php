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
?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Total Students</p>
                    <h3 class="mb-0"><?php echo $total_students; ?></h3>
                </div>
                <div class="ms-3 text-primary"><i class="bi bi-people-fill fs-1"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Classes & Sections</p>
                    <h3 class="mb-0"><?php echo $total_classes; ?></h3>
                </div>
                <div class="ms-3 text-success"><i class="bi bi-journal-bookmark-fill fs-1"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">Present Today</p>
                    <h3 class="mb-0"><?php echo $today_present; ?></h3>
                </div>
                <div class="ms-3 text-danger"><i class="bi bi-clipboard-check-fill fs-1"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 p-3">
    <h5 class="mb-3">Quick Actions</h5>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary" href="/saint-paul/admin/students.php">Manage Students</a>
        <a class="btn btn-outline-primary" href="/saint-paul/admin/timetable.php">Manage Timetable</a>
        <a class="btn btn-outline-success" href="/saint-paul/admin/attendance.php">Record Attendance</a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

