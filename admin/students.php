<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$database = new Database();
$conn = $database->getConnection();

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $studentId = intval($_POST['student_id']);
                $query = "DELETE FROM students WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$studentId]);
                logActivity($_SESSION['user_id'], 'admin', 'delete_student', "Deleted student ID: $studentId");
                $_SESSION['success'] = 'Student deleted successfully';
                break;
                
            case 'update_status':
                $studentId = intval($_POST['student_id']);
                $status = sanitizeInput($_POST['status']);
                $query = "UPDATE students SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$status, $studentId]);
                logActivity($_SESSION['user_id'], 'admin', 'update_student_status', "Updated student ID: $studentId to $status");
                $_SESSION['success'] = 'Student status updated successfully';
                break;
        }
        header('Location: students.php');
        exit();
    }
}

// Get students with search and pagination
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereClause = '';
$params = [];

if ($search) {
    $whereClause = "WHERE full_name LIKE ? OR student_id LIKE ? OR email LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM students $whereClause";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalStudents = $stmt->fetch()['total'];
$totalPages = ceil($totalStudents / $perPage);

// Get students
$query = "SELECT * FROM students $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$stmt = $conn->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-user-shield me-2"></i>Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-dashboard me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="students.php"><i class="fas fa-users me-1"></i>Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="forms.php"><i class="fas fa-file-alt me-1"></i>Forms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Students Management</h5>
                    </div>
                    <div class="col-auto">
                        <form method="GET" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No students found</h5>
                        <p class="text-muted">No students match your search criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><strong><?php echo $student['student_id']; ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($student['profile_photo']): ?>
                                            <img src="../uploads/<?php echo $student['profile_photo']; ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                            <?php else: ?>
                                            <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($student['full_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $student['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($student['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($student['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" onclick="viewStudent(<?php echo $student['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="toggleStatus(<?php echo $student['id']; ?>, '<?php echo $student['status']; ?>')">
                                                <i class="fas fa-toggle-<?php echo $student['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Students pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetails">
                    <!-- Student details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Action Forms -->
    <form id="actionForm" method="POST" style="display: none;">
        <input type="hidden" name="action" id="actionType">
        <input type="hidden" name="student_id" id="studentId">
        <input type="hidden" name="status" id="newStatus">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewStudent(studentId) {
            // Load student details via AJAX (simplified for demo)
            document.getElementById('studentDetails').innerHTML = '<p>Loading student details...</p>';
            new bootstrap.Modal(document.getElementById('studentModal')).show();
        }

        function toggleStatus(studentId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this student?`)) {
                document.getElementById('actionType').value = 'update_status';
                document.getElementById('studentId').value = studentId;
                document.getElementById('newStatus').value = newStatus;
                document.getElementById('actionForm').submit();
            }
        }

        function deleteStudent(studentId, studentName) {
            if (confirm(`Are you sure you want to delete ${studentName}? This action cannot be undone.`)) {
                document.getElementById('actionType').value = 'delete';
                document.getElementById('studentId').value = studentId;
                document.getElementById('actionForm').submit();
            }
        }
    </script>
</body>
</html>
