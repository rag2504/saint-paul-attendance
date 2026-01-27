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
        $message = 'Attendance saved successfully.';
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
    
    .attendance-form-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }
    
    .attendance-form-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .attendance-form-card h5 i {
        color: #667eea;
    }
    
    .recent-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }
    
    .recent-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .recent-card h5 i {
        color: #f093fb;
    }
    
    .marking-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
        margin-top: 2rem;
    }
    
    .marking-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .marking-card h5 i {
        color: #4facfe;
    }
    
    .session-info-badge {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
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
        padding: 0.65rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-load-students {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-load-students:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .btn-save-attendance {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        border: none;
        color: #065f46;
        padding: 0.85rem 2rem;
        border-radius: 10px;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(132, 250, 176, 0.3);
        font-size: 1.05rem;
    }
    
    .btn-save-attendance:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(132, 250, 176, 0.4);
        color: #065f46;
    }
    
    .recent-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .recent-table thead th {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 0.85rem;
        border: none;
    }
    
    .recent-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    
    .recent-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    
    .recent-table tbody tr {
        transition: all 0.3s ease;
    }
    
    .recent-table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.01);
    }
    
    .recent-table tbody td {
        padding: 0.85rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.9rem;
        color: #4a5568;
    }
    
    .date-badge {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #2d3748;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .attendance-ratio {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #065f46;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .attendance-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .attendance-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }
    
    .attendance-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    
    .attendance-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    
    .attendance-table tbody tr {
        transition: all 0.3s ease;
        background: white;
    }
    
    .attendance-table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .attendance-table tbody td {
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
        font-size: 0.95rem;
    }
    
    .student-id-badge {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .student-name {
        color: #2d3748;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .btn-group .btn-check:checked + .btn-outline-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #065f46;
        border-color: transparent;
        font-weight: 700;
    }
    
    .btn-group .btn-check:checked + .btn-outline-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-color: transparent;
        font-weight: 700;
    }
    
    .btn-group .btn-outline-success {
        border: 2px solid #84fab0;
        color: #065f46;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s ease;
        border-radius: 8px 0 0 8px;
    }
    
    .btn-group .btn-outline-danger {
        border: 2px solid #f5576c;
        color: #f5576c;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s ease;
        border-radius: 0 8px 8px 0;
    }
    
    .btn-group .btn-outline-success:hover {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #065f46;
        border-color: transparent;
    }
    
    .btn-group .btn-outline-danger:hover {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-color: transparent;
    }
    
    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }
    
    .alert-success-custom {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #065f46;
    }
    
    .alert-warning-custom {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #92400e;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #a0aec0;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 0.75rem;
        opacity: 0.3;
    }
    
    .quick-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .quick-action-btn {
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        color: #4a5568;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .quick-action-btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
        .attendance-form-card, .recent-card, .marking-card {
            padding: 1.5rem;
        }
        
        .attendance-table thead th,
        .attendance-table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
        }
        
        .btn-group {
            width: 100%;
        }
        
        .btn-group .btn-outline-success,
        .btn-group .btn-outline-danger {
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
        }
    }
</style>

<div class="page-header">
    <h2><i class="bi bi-clipboard-check-fill me-2"></i>Attendance Management</h2>
    <p>Track and record student attendance for classes</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-custom <?php echo strpos($message, 'successfully') !== false ? 'alert-success-custom' : 'alert-warning-custom'; ?>">
        <i class="bi <?php echo strpos($message, 'successfully') !== false ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i><?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="attendance-form-card">
            <h5><i class="bi bi-pencil-square"></i>Mark Attendance</h5>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-bookmark me-1"></i>Class</label>
                    <select name="class" class="form-select" required>
                        <option value="">Select class...</option>
                        <?php foreach (array_keys($classes) as $c): ?>
                            <option value="<?php echo e($c); ?>" <?php echo $selected_class === $c ? 'selected' : ''; ?>>
                                Class <?php echo e($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-grid-3x3-gap me-1"></i>Section</label>
                    <select name="section" class="form-select" required>
                        <option value="">Select section...</option>
                        <?php foreach (array_keys($sections) as $s): ?>
                            <option value="<?php echo e($s); ?>" <?php echo $selected_section === $s ? 'selected' : ''; ?>>
                                Section <?php echo e($s); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="<?php echo e($selected_date); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-clock me-1"></i>Time Slot</label>
                    <select name="time_slot" class="form-select" required>
                        <option value="">Select slot...</option>
                        <?php foreach (array_keys($slot_options) as $slot): ?>
                            <option value="<?php echo e($slot); ?>" <?php echo $selected_slot === $slot ? 'selected' : ''; ?>>
                                <?php echo e($slot); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-load-students w-100" name="action" value="load" type="submit">
                        <i class="bi bi-people-fill me-2"></i>Load Students
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="recent-card">
            <h5><i class="bi bi-clock-history"></i>Recent Attendance</h5>
            <div class="table-responsive">
                <table class="table recent-table">
                    <thead>
                    <tr>
                        <th><i class="bi bi-calendar-day me-1"></i>Date</th>
                        <th><i class="bi bi-clock me-1"></i>Slot</th>
                        <th><i class="bi bi-book me-1"></i>Class</th>
                        <th><i class="bi bi-graph-up me-1"></i>Ratio</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><span class="date-badge"><?php echo e(date('M d', strtotime($r['attendance_date']))); ?></span></td>
                            <td><strong><?php echo e($r['time_slot']); ?></strong></td>
                            <td><strong><?php echo e($r['class'] . '-' . $r['section']); ?></strong></td>
                            <td><span class="attendance-ratio"><?php echo e($r['present'] . '/' . $r['total']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recent): ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p class="mb-0">No attendance records yet</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($loaded_students): ?>
    <div class="marking-card">
        <h5><i class="bi bi-clipboard-data"></i>Mark Attendance</h5>
        <div class="session-info-badge">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Class <?php echo e($selected_class); ?>-<?php echo e($selected_section); ?></strong> • 
            <?php echo e(date('F d, Y', strtotime($selected_date))); ?> • 
            <?php echo e($selected_slot); ?>
        </div>
        
        <form method="post">
            <input type="hidden" name="class" value="<?php echo e($selected_class); ?>">
            <input type="hidden" name="section" value="<?php echo e($selected_section); ?>">
            <input type="hidden" name="attendance_date" value="<?php echo e($selected_date); ?>">
            <input type="hidden" name="time_slot" value="<?php echo e($selected_slot); ?>">
            <input type="hidden" name="action" value="save">
            
            <div class="quick-actions mb-4">
                <button type="button" class="quick-action-btn" onclick="markAll('Present')">
                    <i class="bi bi-check-all me-1"></i>Mark All Present
                </button>
                <button type="button" class="quick-action-btn" onclick="markAll('Absent')">
                    <i class="bi bi-x-circle me-1"></i>Mark All Absent
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table attendance-table">
                    <thead>
                    <tr>
                        <th><i class="bi bi-hash me-1"></i>Student ID</th>
                        <th><i class="bi bi-person me-1"></i>Student Name</th>
                        <th class="text-center"><i class="bi bi-clipboard-check me-1"></i>Attendance Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($loaded_students as $stu): ?>
                        <tr>
                            <td><span class="student-id-badge"><?php echo e($stu['student_id']); ?></span></td>
                            <td><span class="student-name"><?php echo e($stu['name']); ?></span></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($stu['student_id']); ?>]" id="p-<?php echo e($stu['student_id']); ?>" value="Present" checked>
                                    <label class="btn btn-outline-success" for="p-<?php echo e($stu['student_id']); ?>">
                                        <i class="bi bi-check-circle-fill me-1"></i>Present
                                    </label>
                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($stu['student_id']); ?>]" id="a-<?php echo e($stu['student_id']); ?>" value="Absent">
                                    <label class="btn btn-outline-danger" for="a-<?php echo e($stu['student_id']); ?>">
                                        <i class="bi bi-x-circle-fill me-1"></i>Absent
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid mt-4">
                <button class="btn btn-save-attendance" type="submit">
                    <i class="bi bi-save-fill me-2"></i>Save Attendance Record
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<script>
    function markAll(status) {
        const radios = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
        radios.forEach(radio => {
            radio.checked = true;
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>