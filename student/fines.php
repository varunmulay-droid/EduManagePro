<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireStudent();

$database = new Database();
$conn = $database->getConnection();

// Get student's fines
$query = "SELECT * FROM fines WHERE student_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$fines = $stmt->fetchAll();

// Calculate totals
$totalAmount = 0;
$paidAmount = 0;
$pendingAmount = 0;

foreach ($fines as $fine) {
    $totalAmount += $fine['amount'];
    if ($fine['status'] === 'paid') {
        $paidAmount += $fine['amount'];
    } else {
        $pendingAmount += $fine['amount'];
    }
}

// Get student information
$student = getStudentInfo($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Details - Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-graduation-cap me-2"></i>Student Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-dashboard me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="forms.php"><i class="fas fa-file-alt me-1"></i>Forms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="fines.php"><i class="fas fa-money-bill me-1"></i>Fine Details</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php if ($student['profile_photo']): ?>
                            <img src="../uploads/<?php echo $student['profile_photo']; ?>" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                            <?php else: ?>
                            <i class="fas fa-user-circle me-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($student['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Fine Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Fines</h6>
                                <h3 class="mb-0">₹<?php echo number_format($totalAmount, 2); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-money-bill fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Paid Amount</h6>
                                <h3 class="mb-0">₹<?php echo number_format($paidAmount, 2); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pending Amount</h6>
                                <h3 class="mb-0">₹<?php echo number_format($pendingAmount, 2); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fine Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Fine Details</h5>
            </div>
            <div class="card-body">
                <?php if (empty($fines)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-money-bill fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No fines recorded</h5>
                        <p class="text-muted">You have no outstanding or historical fines.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reason</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Paid Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fines as $fine): ?>
                                <tr>
                                    <td><?php echo formatDate($fine['created_at']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($fine['reason']); ?></strong>
                                        <?php if ($fine['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($fine['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>₹<?php echo number_format($fine['amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $fine['status'] === 'paid' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($fine['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $fine['paid_at'] ? formatDate($fine['paid_at']) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($fine['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="markAsPaid(<?php echo $fine['id']; ?>)">
                                            <i class="fas fa-credit-card me-1"></i>Pay
                                        </button>
                                        <?php else: ?>
                                        <span class="text-muted">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsPaid(fineId) {
            if (confirm('Are you sure you want to mark this fine as paid?')) {
                // In a real system, this would integrate with a payment gateway
                alert('Payment integration would be implemented here. Please contact the admin office to pay fines.');
            }
        }
    </script>
</body>
</html>
