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
    $message = 'Student removed successfully.';
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
            $message = "Student created successfully with ID: {$student_id}";
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
            $message = 'Student updated successfully.';
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
    
    .add-student-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }
    
    .add-student-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .add-student-card h5 i {
        color: #667eea;
    }
    
    .students-list-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
    }
    
    .students-list-card h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0;
        font-size: 1.4rem;
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
    
    .btn-primary-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .filter-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .search-filter-section {
        background: #f7fafc;
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    
    .table-custom {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-custom thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }
    
    .table-custom thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    
    .table-custom thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    
    .table-custom tbody tr {
        transition: all 0.3s ease;
        background: white;
    }
    
    .table-custom tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .table-custom tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
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
    
    .class-badge {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
        padding: 0.3rem 0.7rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .btn-edit-custom {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    
    .btn-edit-custom:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-delete-custom {
        background: white;
        color: #f5576c;
        border: 2px solid #f5576c;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    
    .btn-delete-custom:hover {
        background: #f5576c;
        color: white;
        transform: translateY(-2px);
    }
    
    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .alert-success-custom {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #065f46;
    }
    
    .alert-info-custom {
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        color: #1e40af;
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
    
    .pagination-info {
        color: #718096;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .btn-pagination {
        border: 2px solid #e2e8f0;
        color: #4a5568;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-pagination:hover {
        border-color: #667eea;
        color: #667eea;
        background: #f7fafc;
    }
    
    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #a0aec0;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
        .add-student-card, .students-list-card {
            padding: 1.5rem;
        }
        
        .table-custom {
            font-size: 0.85rem;
        }
        
        .table-custom thead th,
        .table-custom tbody td {
            padding: 0.75rem 0.5rem;
        }
    }
</style>

<div class="page-header">
    <h2><i class="bi bi-people-fill me-2"></i>Student Management</h2>
    <p>Add, edit, and manage student records</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-custom alert-success-custom mb-4">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="add-student-card">
            <h5><i class="bi bi-person-plus-fill"></i>Add New Student</h5>
            <form method="post">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person me-1"></i>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter student name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-bookmark me-1"></i>Class</label>
                    <input type="text" name="class" class="form-control" placeholder="e.g. 10" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-grid-3x3-gap me-1"></i>Section</label>
                    <input type="text" name="section" class="form-control" placeholder="e.g. A" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope me-1"></i>Email (Optional)</label>
                    <input type="email" name="email" class="form-control" placeholder="student@example.com">
                </div>
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-key me-1"></i>Password</label>
                    <input type="text" name="password" class="form-control" placeholder="Set initial password" required>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary-custom" type="submit">
                        <i class="bi bi-plus-circle me-2"></i>Create Student
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="students-list-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h5><i class="bi bi-list-ul me-2"></i>All Students</h5>
                <span class="filter-badge">
                    <i class="bi bi-funnel-fill"></i>
                    Search & Filter
                </span>
            </div>

            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input id="studentSearch" type="text" class="form-control" placeholder="🔍 Search by ID or Name...">
                    </div>
                    <div class="col-md-3">
                        <select id="filterClass" class="form-select">
                            <option value="">All Classes</option>
                            <?php foreach (array_keys($class_options) as $c): ?>
                                <option value="<?php echo e($c); ?>">Class <?php echo e($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterSection" class="form-select">
                            <option value="">All Sections</option>
                            <?php foreach (array_keys($section_options) as $sec): ?>
                                <option value="<?php echo e($sec); ?>">Section <?php echo e($sec); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom" id="studentsTable">
                    <thead>
                    <tr>
                        <th><i class="bi bi-hash me-1"></i>Student ID</th>
                        <th><i class="bi bi-person me-1"></i>Name</th>
                        <th><i class="bi bi-book me-1"></i>Class</th>
                        <th><i class="bi bi-grid me-1"></i>Section</th>
                        <th><i class="bi bi-envelope me-1"></i>Email</th>
                        <th class="text-end"><i class="bi bi-gear me-1"></i>Actions</th>
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
                            <td><span class="student-id-badge"><?php echo e($s['student_id']); ?></span></td>
                            <td><strong><?php echo e($s['name']); ?></strong></td>
                            <td><span class="class-badge"><?php echo e($s['class']); ?></span></td>
                            <td><strong><?php echo e($s['section']); ?></strong></td>
                            <td><?php echo e($s['email'] ?: '—'); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-edit-custom btn-sm student-edit-btn me-1"
                                        data-bs-toggle="modal" data-bs-target="#editStudentModal"
                                        data-id="<?php echo e($s['student_id']); ?>"
                                        data-name="<?php echo e($s['name']); ?>"
                                        data-class="<?php echo e($s['class']); ?>"
                                        data-section="<?php echo e($s['section']); ?>"
                                        data-email="<?php echo e($s['email']); ?>">
                                    <i class="bi bi-pencil-fill me-1"></i>Edit
                                </button>
                                <a href="?delete=<?php echo e($s['student_id']); ?>" class="btn btn-delete-custom btn-sm" data-confirm="Delete this student?">
                                    <i class="bi bi-trash-fill me-1"></i>Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$students): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p class="mb-0">No students added yet. Start by adding your first student!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <div class="pagination-info" id="studentsCount"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-pagination btn-sm" id="studentsPrev">
                        <i class="bi bi-chevron-left me-1"></i>Previous
                    </button>
                    <button type="button" class="btn btn-pagination btn-sm" id="studentsNext">
                        Next<i class="bi bi-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="student_id" id="edit-student-id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-hash me-1"></i>Student ID</label>
                            <input type="text" class="form-control" id="edit-student-id-display" readonly style="background: #f7fafc;">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-person me-1"></i>Full Name</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-bookmark me-1"></i>Class</label>
                            <input type="text" name="class" id="edit-class" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-grid-3x3-gap me-1"></i>Section</label>
                            <input type="text" name="section" id="edit-section" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
                            <input type="email" name="email" id="edit-email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-key me-1"></i>New Password</label>
                            <input type="text" name="new_password" class="form-control" placeholder="Leave blank to keep current password">
                            <small class="text-muted">Only fill this if you want to change the password</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Edit button handler
    document.querySelectorAll('.student-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-student-id').value = this.dataset.id;
            document.getElementById('edit-student-id-display').value = this.dataset.id;
            document.getElementById('edit-name').value = this.dataset.name;
            document.getElementById('edit-class').value = this.dataset.class;
            document.getElementById('edit-section').value = this.dataset.section;
            document.getElementById('edit-email').value = this.dataset.email;
        });
    });
    
    // Delete confirmation
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>