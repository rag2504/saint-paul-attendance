<?php
$page_title = 'Manage Students';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$message = '';

function generateStudentId(mysqli $mysqli): string
{
    do {
        $candidate = 'SP' . mt_rand(10000, 99999);
        $stmt = $mysqli->prepare('SELECT 1 FROM students WHERE student_id = ?');
        $stmt->bind_param('s', $candidate);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
    } while ($exists);

    return $candidate;
}

if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $stmt = $mysqli->prepare('DELETE FROM students WHERE student_id = ?');
    $stmt->bind_param('s', $del_id);
    $stmt->execute();
    $message = 'Student removed.';
}

// Create / Edit student handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $class = trim($_POST['class'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name && $class && $section && $password) {
            $student_id = generateStudentId($mysqli);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare('INSERT INTO students (student_id, name, class, section, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $student_id, $name, $class, $section, $email, $hash);
            $stmt->execute();
            $message = "Student created with ID: {$student_id}";
        } else {
            $message = 'Please fill all required fields.';
        }
    }

    if ($action === 'edit') {
        $student_id = trim($_POST['student_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $class = trim($_POST['class'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if ($student_id && $name && $class && $section) {
            if ($new_password !== '') {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare('UPDATE students SET name=?, class=?, section=?, email=?, password_hash=? WHERE student_id=?');
                $stmt->bind_param('ssssss', $name, $class, $section, $email, $hash, $student_id);
                $stmt->execute();
            } else {
                $stmt = $mysqli->prepare('UPDATE students SET name=?, class=?, section=?, email=? WHERE student_id=?');
                $stmt->bind_param('sssss', $name, $class, $section, $email, $student_id);
                $stmt->execute();
            }
            $message = 'Student updated.';
        } else {
            $message = 'Please fill required fields for edit.';
        }
    }
}

$students = $mysqli->query('SELECT student_id, name, class, section, email, created_at FROM students ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);

// Dropdown filter options
$class_options = [];
$section_options = [];
foreach ($students as $s) {
    $class_options[$s['class']] = true;
    $section_options[$s['section']] = true;
}
?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3">
            <h5 class="mb-3">Add Student</h5>
            <?php if ($message): ?>
                <div class="alert alert-info py-2"><?php echo e($message); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="create">
                <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Class</label>
                    <input type="text" name="class" class="form-control" placeholder="e.g. 10" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Section</label>
                    <input type="text" name="section" class="form-control" placeholder="A" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Email (optional)</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Set Password</label>
                    <input type="text" name="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="submit">Create Student</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h5 class="mb-0">Students</h5>
                <span class="badge bg-light text-dark">Search • Filter • Edit</span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <input id="studentSearch" type="text" class="form-control" placeholder="Search by Student ID or Name...">
                </div>
                <div class="col-md-3">
                    <select id="filterClass" class="form-select">
                        <option value="">All Classes</option>
                        <?php foreach (array_keys($class_options) as $c): ?>
                            <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterSection" class="form-select">
                        <option value="">All Sections</option>
                        <?php foreach (array_keys($section_options) as $sec): ?>
                            <option value="<?php echo e($sec); ?>"><?php echo e($sec); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle" id="studentsTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Email</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr class="student-row"
                            data-id="<?php echo e($s['student_id']); ?>"
                            data-name="<?php echo e($s['name']); ?>"
                            data-class="<?php echo e($s['class']); ?>"
                            data-section="<?php echo e($s['section']); ?>"
                            data-email="<?php echo e($s['email']); ?>"
                        >
                            <td><?php echo e($s['student_id']); ?></td>
                            <td><?php echo e($s['name']); ?></td>
                            <td><?php echo e($s['class']); ?></td>
                            <td><?php echo e($s['section']); ?></td>
                            <td><?php echo e($s['email']); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary student-edit-btn"
                                        data-bs-toggle="modal" data-bs-target="#editStudentModal"
                                        data-id="<?php echo e($s['student_id']); ?>"
                                        data-name="<?php echo e($s['name']); ?>"
                                        data-class="<?php echo e($s['class']); ?>"
                                        data-section="<?php echo e($s['section']); ?>"
                                        data-email="<?php echo e($s['email']); ?>">
                                    Edit
                                </button>
                                <a href="?delete=<?php echo e($s['student_id']); ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this student?">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$students): ?>
                        <tr><td colspan="6" class="text-center text-muted">No students yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                <div class="text-muted small" id="studentsCount"></div>
                <div class="btn-group" role="group" aria-label="Pagination">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="studentsPrev">Prev</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="studentsNext">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="student_id" id="edit-student-id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="edit-student-id-display" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <input type="text" name="class" id="edit-class" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" id="edit-section" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" name="email" id="edit-email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">New Password (leave blank to keep unchanged)</label>
                            <input type="text" name="new_password" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

