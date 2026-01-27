<?php
$page_title = 'Timetable';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$message = '';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$fixed_slots = ['08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00', '12:00 - 13:00'];

// Currently selected class/section (for grid)
$selected_class = $_GET['class'] ?? $_POST['class'] ?? '';
$selected_section = $_GET['section'] ?? $_POST['section'] ?? '';

// Suggest existing classes/sections, subjects (for inputs)
$class_sections = $mysqli->query('SELECT DISTINCT class, section FROM students ORDER BY class, section')->fetch_all(MYSQLI_ASSOC);
$class_sections_timetable = $mysqli->query('SELECT DISTINCT class, section FROM timetable ORDER BY class, section')->fetch_all(MYSQLI_ASSOC);
$class_sections = array_merge($class_sections, $class_sections_timetable);

// Build unique class-section strings
$class_section_options = [];
foreach ($class_sections as $row) {
    $key = $row['class'] . '|' . $row['section'];
    $class_section_options[$key] = $row;
}

$subject_rows = $mysqli->query('SELECT DISTINCT subject FROM timetable ORDER BY subject')->fetch_all(MYSQLI_ASSOC);
$subjects = [];
foreach ($subject_rows as $row) {
    $subjects[$row['subject']] = true;
}

// Handle clear slot
if (isset($_GET['clear']) && $selected_class && $selected_section) {
    $day = $_GET['day'] ?? '';
    $slot = $_GET['slot'] ?? '';
    if (in_array($day, $days, true) && in_array($slot, $fixed_slots, true)) {
        $stmt = $mysqli->prepare('DELETE FROM timetable WHERE class=? AND section=? AND day=? AND time_slot=?');
        $stmt->bind_param('ssss', $selected_class, $selected_section, $day, $slot);
        $stmt->execute();
        $message = 'Slot cleared successfully.';
    }
}

// Handle save slot (one subject per cell, prevent duplicates)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_slot') {
    $selected_class = trim($_POST['class'] ?? '');
    $selected_section = trim($_POST['section'] ?? '');
    $day = $_POST['day'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';
    $subject = trim($_POST['subject'] ?? '');

    if ($selected_class && $selected_section && in_array($day, $days, true) && in_array($time_slot, $fixed_slots, true) && $subject) {
        // Check if a record already exists for this cell
        $stmt = $mysqli->prepare('SELECT id FROM timetable WHERE class=? AND section=? AND day=? AND time_slot=? LIMIT 1');
        $stmt->bind_param('ssss', $selected_class, $selected_section, $day, $time_slot);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $update = $mysqli->prepare('UPDATE timetable SET subject=? WHERE id=?');
            $update->bind_param('si', $subject, $existing['id']);
            $update->execute();
            $message = 'Timetable slot updated successfully.';
        } else {
            $insert = $mysqli->prepare('INSERT INTO timetable (class, section, day, time_slot, subject) VALUES (?, ?, ?, ?, ?)');
            $insert->bind_param('sssss', $selected_class, $selected_section, $day, $time_slot, $subject);
            $insert->execute();
            $message = 'Timetable slot added successfully.';
        }
    } else {
        $message = 'Please select class, section, day, time slot and subject.';
    }
}

// Load timetable for selected class/section into a grid map
$grid = [];
if ($selected_class && $selected_section) {
    $stmt = $mysqli->prepare('SELECT day, time_slot, subject FROM timetable WHERE class=? AND section=?');
    $stmt->bind_param('ss', $selected_class, $selected_section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $grid[$row['day']][$row['time_slot']] = $row['subject'];
    }
}
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

    .selector-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }

    .selector-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .selector-card h5 i {
        color: #667eea;
    }

    .timetable-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .timetable-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0;
        font-size: 1.3rem;
    }

    .class-section-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-control,
    .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.65rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-load-timetable {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-load-timetable:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .time-slots-info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        border-radius: 12px;
        padding: 1.25rem;
        margin-top: 1.5rem;
    }

    .time-slots-info h6 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .time-slots-info ul {
        margin-bottom: 0;
        padding-left: 1.25rem;
    }

    .time-slots-info li {
        color: #4a5568;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
        font-weight: 500;
    }

    .timetable-grid-wrapper {
        overflow-x: auto;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .timetable-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .timetable-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        border: none;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .timetable-table thead th:first-child {
        border-radius: 12px 0 0 0;
        text-align: left;
        padding-left: 1.25rem;
    }

    .timetable-table thead th:last-child {
        border-radius: 0 12px 0 0;
    }

    .timetable-table tbody th {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 1rem 1.25rem;
        border: none;
        text-align: left;
        white-space: nowrap;
    }

    .timetable-table tbody td {
        background: white;
        padding: 1rem 0.75rem;
        border: 2px solid #e2e8f0;
        vertical-align: middle;
        text-align: center;
        min-width: 150px;
        transition: all 0.3s ease;
    }

    .timetable-table tbody td:hover {
        background: #f7fafc;
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .subject-pill {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
    }

    .btn-add-slot {
        background: white;
        color: #667eea;
        border: 2px dashed #667eea;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .btn-add-slot:hover {
        background: #667eea;
        color: white;
        border-style: solid;
        transform: translateY(-2px);
    }

    .btn-edit-slot {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        padding: 0.35rem 0.85rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-edit-slot:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    .btn-clear-slot {
        background: white;
        color: #f5576c;
        border: 2px solid #f5576c;
        padding: 0.35rem 0.85rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-clear-slot:hover {
        background: #f5576c;
        color: white;
        transform: translateY(-2px);
    }

    .empty-timetable {
        text-align: center;
        padding: 3rem;
        color: #a0aec0;
    }

    .empty-timetable i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
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

    .alert-info-custom {
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        color: #1e40af;
    }

    .alert-warning-custom {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #92400e;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
    }

    .modal-title {
        font-weight: 700;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        padding: 1.5rem 2rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-close {
        filter: brightness(0) invert(1);
    }

    .slot-info-badge {
        background: #f7fafc;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }

        .selector-card,
        .timetable-card {
            padding: 1.5rem;
        }

        .timetable-table thead th,
        .timetable-table tbody th,
        .timetable-table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
        }

        .subject-pill {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
    }
</style>

<div class="page-header">
    <h2><i class="bi bi-calendar-week-fill me-2"></i>Timetable Management</h2>
    <p>Create and manage class schedules and time slots</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-custom alert-success-custom">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="selector-card">
            <h5><i class="bi bi-funnel-fill"></i>Select Class</h5>
            <form method="get">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-bookmark me-1"></i>Class</label>
                    <select name="class" class="form-select" required>
                        <option value="">Choose class...</option>
                        <?php
                        $seenClasses = [];
                        foreach ($class_section_options as $opt):
                            if (in_array($opt['class'], $seenClasses, true)) {
                                continue;
                            }
                            $seenClasses[] = $opt['class'];
                            ?>
                            <option value="<?php echo e($opt['class']); ?>" <?php echo $selected_class === $opt['class'] ? 'selected' : ''; ?>>
                                Class <?php echo e($opt['class']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-grid-3x3-gap me-1"></i>Section</label>
                    <select name="section" class="form-select" required>
                        <option value="">Choose section...</option>
                        <?php
                        $seenSections = [];
                        foreach ($class_section_options as $opt):
                            if (in_array($opt['section'], $seenSections, true)) {
                                continue;
                            }
                            $seenSections[] = $opt['section'];
                            ?>
                            <option value="<?php echo e($opt['section']); ?>" <?php echo $selected_section === $opt['section'] ? 'selected' : ''; ?>>
                                Section <?php echo e($opt['section']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-grid">
                    <button class="btn btn-load-timetable" type="submit">
                        <i class="bi bi-arrow-right-circle me-2"></i>Load Timetable
                    </button>
                </div>
            </form>

            <div class="time-slots-info">
                <h6><i class="bi bi-clock-fill"></i>Time Slots</h6>
                <ul>
                    <?php foreach ($fixed_slots as $slot): ?>
                        <li><?php echo e($slot); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="timetable-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h5><i class="bi bi-table me-2"></i>Weekly Timetable</h5>
                <?php if ($selected_class && $selected_section): ?>
                    <span class="class-section-badge">
                        <i class="bi bi-mortarboard-fill"></i>
                        Class <?php echo e($selected_class); ?> - Section <?php echo e($selected_section); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!$selected_class || !$selected_section): ?>
                <div class="empty-timetable">
                    <i class="bi bi-calendar-x"></i>
                    <h5>No Timetable Selected</h5>
                    <p class="mb-0">Please select a class and section from the left panel to view and manage the timetable.
                    </p>
                </div>
            <?php else: ?>
                <div class="timetable-grid-wrapper">
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-calendar-day me-1"></i>Day</th>
                                <?php foreach ($fixed_slots as $slot): ?>
                                    <th><i class="bi bi-clock me-1"></i><?php echo e($slot); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days as $day): ?>
                                <tr>
                                    <th><?php echo e($day); ?></th>
                                    <?php foreach ($fixed_slots as $slot):
                                        $subject = $grid[$day][$slot] ?? '';
                                        ?>
                                        <td>
                                            <?php if ($subject): ?>
                                                <div class="subject-pill"><?php echo e($subject); ?></div>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button type="button" class="btn btn-edit-slot timetable-edit-btn"
                                                        data-day="<?php echo e($day); ?>" data-slot="<?php echo e($slot); ?>"
                                                        data-subject="<?php echo e($subject); ?>"
                                                        data-class="<?php echo e($selected_class); ?>"
                                                        data-section="<?php echo e($selected_section); ?>" data-bs-toggle="modal"
                                                        data-bs-target="#timetableSlotModal"><i class="bi bi-pencil-fill"></i>
                                                        Edit</button>
                                                    <a href="?class=<?php echo urlencode($selected_class); ?>&section=<?php echo urlencode($selected_section); ?>&day=<?php echo urlencode($day); ?>&slot=<?php echo urlencode($slot); ?>&clear=1"
                                                        class="btn btn-clear-slot" data-confirm="Clear this slot?"><i
                                                            class="bi bi-trash-fill"></i> Clear</a>
                                                </div>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-add-slot timetable-edit-btn"
                                                    data-day="<?php echo e($day); ?>" data-slot="<?php echo e($slot); ?>"
                                                    data-subject="" data-class="<?php echo e($selected_class); ?>"
                                                    data-section="<?php echo e($selected_section); ?>" data-bs-toggle="modal"
                                                    data-bs-target="#timetableSlotModal"><i class="bi bi-plus-circle me-1"></i>Add
                                                    Subject</button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Slot -->
<div class="modal fade" id="timetableSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="save_slot">
                <input type="hidden" name="class" id="tt-class">
                <input type="hidden" name="section" id="tt-section">
                <input type="hidden" name="day" id="tt-day">
                <input type="hidden" name="time_slot" id="tt-slot">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i>Timetable Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="slot-info-badge mb-3" id="tt-label">
                        <i class="bi bi-info-circle me-2"></i>Slot Information
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-book me-1"></i>Subject Name</label>
                        <input list="subjectOptions" type="text" name="subject" id="tt-subject" class="form-control"
                            placeholder="Enter or select subject" required>
                        <datalist id="subjectOptions">
                            <?php foreach (array_keys($subjects) as $subj): ?>
                                <option value="<?php echo e($subj); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-lightbulb me-1"></i>Type to search or add a new subject
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-load-timetable">
                        <i class="bi bi-check-circle me-1"></i>Save Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle edit/add button clicks
    document.querySelectorAll('.timetable-edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const day = this.dataset.day;
            const slot = this.dataset.slot;
            const subject = this.dataset.subject;
            const classVal = this.dataset.class;
            const section = this.dataset.section;

            document.getElementById('tt-class').value = classVal;
            document.getElementById('tt-section').value = section;
            document.getElementById('tt-day').value = day;
            document.getElementById('tt-slot').value = slot;
            document.getElementById('tt-subject').value = subject;
            document.getElementById('tt-label').innerHTML =
                `<i class="bi bi-info-circle me-2"></i><strong>${day}</strong> • <strong>${slot}</strong> • Class ${classVal}-${section}`;
        });
    });


</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>