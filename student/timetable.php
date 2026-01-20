<?php
$page_title = 'My Timetable';
require_once __DIR__ . '/../includes/header.php';
requireStudent();

$class = $_SESSION['user_class'];
$section = $_SESSION['user_section'];

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$fixed_slots = ['08:00 - 09:00','09:00 - 10:00','10:00 - 11:00','11:00 - 12:00','12:00 - 13:00'];

// Build grid for this student's class/section
$grid = [];
$stmt = $mysqli->prepare('SELECT day, time_slot, subject FROM timetable WHERE class = ? AND section = ?');
$stmt->bind_param('ss', $class, $section);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $grid[$row['day']][$row['time_slot']] = $row['subject'];
}
?>
<div class="card p-3">
    <h5 class="mb-3">Timetable - Class <?php echo e($class . ' - ' . $section); ?></h5>
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
                                <span class="fw-semibold"><?php echo e($subject); ?></span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

