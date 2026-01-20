<?php
$page_title = 'Timetable';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$message = '';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$fixed_slots = ['08:00 - 09:00','09:00 - 10:00','10:00 - 11:00','11:00 - 12:00','12:00 - 13:00'];

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
        $message = 'Slot cleared.';
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
            $message = 'Timetable slot updated.';
        } else {
            $insert = $mysqli->prepare('INSERT INTO timetable (class, section, day, time_slot, subject) VALUES (?, ?, ?, ?, ?)');
            $insert->bind_param('sssss', $selected_class, $selected_section, $day, $time_slot, $subject);
            $insert->execute();
            $message = 'Timetable slot added.';
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
<div class="row g-3">
    <div class="col-lg-3">
        <div class="card p-3">
            <h5 class="mb-3">Select Class &amp; Section</h5>
            <?php if ($message): ?>
                <div class="alert alert-info py-2"><?php echo e($message); ?></div>
            <?php endif; ?>
            <form method="get" class="mb-3">
                <div class="mb-2">
                    <label class="form-label">Class</label>
                    <select name="class" class="form-select" required>
                        <option value="">Choose...</option>
                        <?php
                        $seenClasses = [];
                        foreach ($class_section_options as $opt):
                            if (in_array($opt['class'], $seenClasses, true)) {
                                continue;
                            }
                            $seenClasses[] = $opt['class'];
                            ?>
                            <option value="<?php echo e($opt['class']); ?>" <?php echo $selected_class === $opt['class'] ? 'selected' : ''; ?>>
                                <?php echo e($opt['class']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Section</label>
                    <select name="section" class="form-select" required>
                        <option value="">Choose...</option>
                        <?php
                        $seenSections = [];
                        foreach ($class_section_options as $opt):
                            if (in_array($opt['section'], $seenSections, true)) {
                                continue;
                            }
                            $seenSections[] = $opt['section'];
                            ?>
                            <option value="<?php echo e($opt['section']); ?>" <?php echo $selected_section === $opt['section'] ? 'selected' : ''; ?>>
                                <?php echo e($opt['section']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="submit">Load Timetable</button>
                </div>
            </form>
            <div class="muted-box small">
                Fixed time slots:
                <ul class="mb-0 ps-3">
                    <?php foreach ($fixed_slots as $slot): ?>
                        <li><?php echo e($slot); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">Timetable Grid <?php if ($selected_class && $selected_section) echo ' - Class '.e($selected_class).' / Section '.e($selected_section); ?></h5>
                <span class="badge bg-light text-dark">Classic grid view (Days × Time Slots)</span>
            </div>
            <?php if (!$selected_class || !$selected_section): ?>
                <p class="text-muted mb-0">Choose a class and section on the left to view and edit timetable.</p>
            <?php else: ?>
                <div class="table-responsive timetable-grid">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 10%;">Day</th>
                            <?php foreach ($fixed_slots as $slot): ?>
                                <th><?php echo e($slot); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($days as $day): ?>
                            <tr>
                                <th class="text-start"><?php echo e($day); ?></th>
                                <?php foreach ($fixed_slots as $slot): 
                                    $subject = $grid[$day][$slot] ?? '';
                                    ?>
                                    <td>
                                        <?php if ($subject): ?>
                                            <div class="fw-semibold"><?php echo e($subject); ?></div>
                                            <div class="mt-1">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary timetable-edit-btn"
                                                    data-day="<?php echo e($day); ?>"
                                                    data-slot="<?php echo e($slot); ?>"
                                                    data-subject="<?php echo e($subject); ?>"
                                                    data-class="<?php echo e($selected_class); ?>"
                                                    data-section="<?php echo e($selected_section); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#timetableSlotModal"
                                                >Edit</button>
                                                <a href="?class=<?php echo urlencode($selected_class); ?>&section=<?php echo urlencode($selected_section); ?>&day=<?php echo urlencode($day); ?>&slot=<?php echo urlencode($slot); ?>&clear=1" class="btn btn-sm btn-outline-danger ms-1" data-confirm="Clear this slot?">Clear</a>
                                            </div>
                                        <?php else: ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary timetable-edit-btn"
                                                data-day="<?php echo e($day); ?>"
                                                data-slot="<?php echo e($slot); ?>"
                                                data-subject=""
                                                data-class="<?php echo e($selected_class); ?>"
                                                data-section="<?php echo e($selected_section); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#timetableSlotModal"
                                            >Add</button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="save_slot">
                <input type="hidden" name="class" id="tt-class">
                <input type="hidden" name="section" id="tt-section">
                <input type="hidden" name="day" id="tt-day">
                <input type="hidden" name="time_slot" id="tt-slot">
                <div class="modal-header">
                    <h5 class="modal-title">Timetable Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2" id="tt-label"></p>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input list="subjectOptions" type="text" name="subject" id="tt-subject" class="form-control" required>
                        <datalist id="subjectOptions">
                            <?php foreach (array_keys($subjects) as $subj): ?>
                                <option value="<?php echo e($subj); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

