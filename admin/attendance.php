<?php
$page_title = 'Attendance';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$message = '';
$selected_class = $_POST['class'] ?? '';
$selected_section = $_POST['section'] ?? '';
$selected_date = $_POST['attendance_date'] ?? date('Y-m-d');
$selected_slot = $_POST['time_slot'] ?? '';
$loaded_students = [];

// DB-backed dropdown options (classes/sections from existing data)
$class_sections = $mysqli->query('SELECT DISTINCT class, section FROM students ORDER BY class, section')->fetch_all(MYSQLI_ASSOC);
$class_sections_tt = $mysqli->query('SELECT DISTINCT class, section FROM timetable ORDER BY class, section')->fetch_all(MYSQLI_ASSOC);
$class_sections = array_merge($class_sections, $class_sections_tt);
$class_section_options = [];
foreach ($class_sections as $row) {
    $key = $row['class'] . '|' . $row['section'];
    $class_section_options[$key] = $row;
}
$classes = [];
$sections = [];
foreach ($class_section_options as $opt) {
    $classes[$opt['class']] = true;
    $sections[$opt['section']] = true;
}

// Time slots: if class+section selected, pull from timetable for that class/section
$fixed_slots = ['08:00 - 09:00','09:00 - 10:00','10:00 - 11:00','11:00 - 12:00','12:00 - 13:00'];
$slot_options = [];
if ($selected_class && $selected_section) {
    $stmt = $mysqli->prepare('SELECT DISTINCT time_slot FROM timetable WHERE class = ? AND section = ? ORDER BY time_slot');
    $stmt->bind_param('ss', $selected_class, $selected_section);
    $stmt->execute();
    $slot_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($slot_rows as $r) {
        $slot_options[$r['time_slot']] = true;
    }
}
if (!$slot_options) {
    foreach ($fixed_slots as $s) {
        $slot_options[$s] = true;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'save') {
    $selected_class = $_POST['class'];
    $selected_section = $_POST['section'];
    $selected_date = $_POST['attendance_date'];
    $selected_slot = $_POST['time_slot'];
    $attendance = $_POST['attendance'] ?? [];

    if ($selected_class && $selected_section && $selected_date && $selected_slot) {
        $stmt = $mysqli->prepare('INSERT INTO attendance (student_id, class, section, attendance_date, time_slot, status) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
        foreach ($attendance as $student_id => $status) {
            $status = $status === 'Present' ? 'Present' : 'Absent';
            $stmt->bind_param('ssssss', $student_id, $selected_class, $selected_section, $selected_date, $selected_slot, $status);
            $stmt->execute();
        }
        $message = 'Attendance saved.';
    } else {
        $message = 'Please select class, section, date and time slot.';
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'load' && $selected_class && $selected_section) {
    $stmt = $mysqli->prepare('SELECT student_id, name FROM students WHERE class = ? AND section = ? ORDER BY name');
    $stmt->bind_param('ss', $selected_class, $selected_section);
    $stmt->execute();
    $loaded_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$recent = $mysqli->query('SELECT attendance_date, time_slot, class, section, COUNT(*) AS total, SUM(status="Present") AS present FROM attendance GROUP BY attendance_date, time_slot, class, section ORDER BY attendance_date DESC, time_slot DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC);
?>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Mark Attendance</h5>
            <?php if ($message): ?>
                <div class="alert alert-info py-2"><?php echo e($message); ?></div>
            <?php endif; ?>
            <form method="post" class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Class</label>
                    <select name="class" class="form-select" required>
                        <option value="">Choose...</option>
                        <?php foreach (array_keys($classes) as $c): ?>
                            <option value="<?php echo e($c); ?>" <?php echo $selected_class === $c ? 'selected' : ''; ?>>
                                <?php echo e($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Section</label>
                    <select name="section" class="form-select" required>
                        <option value="">Choose...</option>
                        <?php foreach (array_keys($sections) as $s): ?>
                            <option value="<?php echo e($s); ?>" <?php echo $selected_section === $s ? 'selected' : ''; ?>>
                                <?php echo e($s); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="<?php echo e($selected_date); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Time Slot</label>
                    <select name="time_slot" class="form-select" required>
                        <option value="">Choose...</option>
                        <?php foreach (array_keys($slot_options) as $slot): ?>
                            <option value="<?php echo e($slot); ?>" <?php echo $selected_slot === $slot ? 'selected' : ''; ?>>
                                <?php echo e($slot); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-primary" name="action" value="load" type="submit">Load Students</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <h5 class="mb-3">Recent Attendance</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Date</th><th>Slot</th><th>Class</th><th>Present/Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><?php echo e($r['attendance_date']); ?></td>
                            <td><?php echo e($r['time_slot']); ?></td>
                            <td><?php echo e($r['class'] . '-' . $r['section']); ?></td>
                            <td><?php echo e($r['present'] . ' / ' . $r['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recent): ?>
                        <tr><td colspan="4" class="text-center text-muted">No records yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($loaded_students): ?>
    <div class="card mt-3 p-3">
        <h5 class="mb-3">Mark for <?php echo e($selected_class . ' - ' . $selected_section . ' (' . $selected_date . ' ' . $selected_slot . ')'); ?></h5>
        <form method="post">
            <input type="hidden" name="class" value="<?php echo e($selected_class); ?>">
            <input type="hidden" name="section" value="<?php echo e($selected_section); ?>">
            <input type="hidden" name="attendance_date" value="<?php echo e($selected_date); ?>">
            <input type="hidden" name="time_slot" value="<?php echo e($selected_slot); ?>">
            <input type="hidden" name="action" value="save">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($loaded_students as $stu): ?>
                        <tr>
                            <td><?php echo e($stu['student_id']); ?></td>
                            <td><?php echo e($stu['name']); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($stu['student_id']); ?>]" id="p-<?php echo e($stu['student_id']); ?>" value="Present" checked>
                                    <label class="btn btn-outline-success btn-sm" for="p-<?php echo e($stu['student_id']); ?>">Present</label>
                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($stu['student_id']); ?>]" id="a-<?php echo e($stu['student_id']); ?>" value="Absent">
                                    <label class="btn btn-outline-danger btn-sm" for="a-<?php echo e($stu['student_id']); ?>">Absent</label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid">
                <button class="btn btn-primary" type="submit">Save Attendance</button>
            </div>
        </form>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

