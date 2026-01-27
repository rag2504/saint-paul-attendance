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

$current_day = date('l');
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
    
    .class-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 10px;
        display: inline-block;
        margin-top: 0.5rem;
        font-weight: 600;
    }
    
    .timetable-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
    }
    
    .timetable-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .timetable-title {
        font-weight: 700;
        color: #2d3748;
        font-size: 1.4rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .timetable-title i {
        color: #667eea;
    }
    
    .current-day-badge {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
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
        font-size: 0.95rem;
        padding: 1rem 1.25rem;
        border: none;
        text-align: left;
        white-space: nowrap;
        position: relative;
    }
    
    .timetable-table tbody th.current-day {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
    }
    
    .timetable-table tbody th.current-day::after {
        content: '• Today';
        position: absolute;
        right: 1rem;
        font-size: 0.75rem;
        opacity: 0.9;
    }
    
    .timetable-table tbody td {
        background: white;
        padding: 1.25rem 0.75rem;
        border: 2px solid #e2e8f0;
        vertical-align: middle;
        text-align: center;
        min-width: 120px;
        transition: all 0.3s ease;
    }
    
    .timetable-table tbody tr.current-day-row td {
        background: #f0f9ff;
        border-color: #4facfe;
    }
    
    .timetable-table tbody td:hover {
        background: #f7fafc;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 5;
    }
    
    .subject-pill {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.6rem 1.25rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    .subject-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }
    
    .empty-slot {
        color: #cbd5e0;
        font-size: 1.5rem;
        font-weight: 300;
    }
    
    .legend {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding: 1.5rem;
        background: #f7fafc;
        border-radius: 12px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .legend-color {
        width: 40px;
        height: 20px;
        border-radius: 6px;
    }
    
    .legend-color.day {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .legend-color.today {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .legend-color.subject {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .legend-label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
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
            padding: 0.5rem 1rem;
        }
        
        .legend {
            gap: 1rem;
        }
    }
</style>

<div class="page-header">
    <h2><i class="bi bi-calendar-week-fill me-2"></i>My Timetable</h2>
    <p>View your weekly class schedule</p>
    <span class="class-badge">
        <i class="bi bi-mortarboard-fill me-2"></i>
        Class <?php echo e($class); ?> - Section <?php echo e($section); ?>
    </span>
</div>

<div class="timetable-card">
    <div class="timetable-header">
        <h5 class="timetable-title">
            <i class="bi bi-table"></i>
            Weekly Schedule
        </h5>
        <span class="current-day-badge">
            <i class="bi bi-calendar-check-fill"></i>
            Today: <?php echo e($current_day); ?>
        </span>
    </div>
    
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
                <tr class="<?php echo $day === $current_day ? 'current-day-row' : ''; ?>">
                    <th class="<?php echo $day === $current_day ? 'current-day' : ''; ?>">
                        <?php echo e($day); ?>
                    </th>
                    <?php foreach ($fixed_slots as $slot): 
                        $subject = $grid[$day][$slot] ?? '';
                        ?>
                        <td>
                            <?php if ($subject): ?>
                                <span class="subject-pill"><?php echo e($subject); ?></span>
                            <?php else: ?>
                                <span class="empty-slot">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color day"></div>
            <span class="legend-label">Regular Day</span>
        </div>
        <div class="legend-item">
            <div class="legend-color today"></div>
            <span class="legend-label">Today's Schedule</span>
        </div>
        <div class="legend-item">
            <div class="legend-color subject"></div>
            <span class="legend-label">Subject Period</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>