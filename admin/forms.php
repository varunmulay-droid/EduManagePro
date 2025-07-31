<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$database = new Database();
$conn = $database->getConnection();

// Handle form view request
$viewFormId = isset($_GET['view']) ? intval($_GET['view']) : null;
$formDetails = null;

if ($viewFormId) {
    $query = "SELECT fs.*, s.full_name, s.student_id, s.email 
              FROM form_submissions fs 
              JOIN students s ON fs.student_id = s.id 
              WHERE fs.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$viewFormId]);
    $formDetails = $stmt->fetch();
}

// Get form submissions with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filterType = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$whereClause = '';
$params = [];

if ($filterType) {
    $whereClause = "WHERE fs.form_type = ?";
    $params = [$filterType];
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM form_submissions fs $whereClause";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalForms = $stmt->fetch()['total'];
$totalPages = ceil($totalForms / $perPage);

// Get form submissions
$query = "SELECT fs.*, s.full_name, s.student_id 
          FROM form_submissions fs 
          JOIN students s ON fs.student_id = s.id 
          $whereClause 
          ORDER BY fs.submitted_at DESC 
          LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$stmt = $conn->prepare($query);
$stmt->execute($params);
$forms = $stmt->fetchAll();

// Get form types for filter
$query = "SELECT DISTINCT form_type FROM form_submissions ORDER BY form_type";
$stmt = $conn->prepare($query);
$stmt->execute();
$formTypes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forms - Admin Panel</title>
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
                        <a class="nav-link" href="students.php"><i class="fas fa-users me-1"></i>Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="forms.php"><i class="fas fa-file-alt me-1"></i>Forms</a>
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
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Form Submissions</h5>
                    </div>
                    <div class="col-auto">
                        <select class="form-select" onchange="filterForms(this.value)">
                            <option value="">All Form Types</option>
                            <?php foreach ($formTypes as $type): ?>
                            <option value="<?php echo $type['form_type']; ?>" <?php echo $filterType === $type['form_type'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $type['form_type'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($forms)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No form submissions found</h5>
                        <p class="text-muted">No forms have been submitted yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Form Type</th>
                                    <th>Submitted At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($forms as $form): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($form['full_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo $form['student_id']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo ucfirst(str_replace('_', ' ', $form['form_type'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($form['submitted_at'], 'd/m/Y H:i'); ?></td>
                                    <td>
                                        <span class="badge bg-success">Submitted</span>
                                    </td>
                                    <td>
                                        <a href="?view=<?php echo $form['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Forms pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&type=<?php echo urlencode($filterType); ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo urlencode($filterType); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&type=<?php echo urlencode($filterType); ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Details Modal -->
        <?php if ($formDetails): ?>
        <div class="modal fade show" id="formModal" tabindex="-1" style="display: block;" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <?php echo ucfirst(str_replace('_', ' ', $formDetails['form_type'])); ?> - 
                            <?php echo htmlspecialchars($formDetails['full_name']); ?>
                        </h5>
                        <a href="forms.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Student ID:</strong> <?php echo $formDetails['student_id']; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Submitted:</strong> <?php echo formatDate($formDetails['submitted_at'], 'd/m/Y H:i'); ?>
                            </div>
                        </div>
                        
                        <div class="form-data">
                            <h6>Form Data:</h6>
                            <?php 
                            $formData = json_decode($formDetails['form_data'], true);
                            if ($formData): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <?php foreach ($formData as $key => $value): ?>
                                        <tr>
                                            <td><strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong></td>
                                            <td><?php echo is_array($value) ? implode(', ', $value) : htmlspecialchars($value); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No form data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="forms.php" class="btn btn-secondary">Close</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterForms(type) {
            window.location.href = `forms.php?type=${encodeURIComponent(type)}`;
        }
    </script>
</body>
</html>
