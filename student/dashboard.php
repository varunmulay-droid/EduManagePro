<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireStudent();

$database = new Database();
$conn = $database->getConnection();

// Get student information
$student = getStudentInfo($_SESSION['user_id']);

// Get attendance statistics for current month
$attendanceStats = getAttendanceStats($_SESSION['user_id'], date('n'), date('Y'));

// Get recent form submissions
$query = "SELECT * FROM form_submissions WHERE student_id = ? ORDER BY submitted_at DESC LIMIT 3";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$recentForms = $stmt->fetchAll();

// Get fine details
$query = "SELECT * FROM fines WHERE student_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$fines = $stmt->fetchAll();
$totalFines = array_sum(array_column($fines, 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-dashboard me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="forms.php"><i class="fas fa-file-alt me-1"></i>Forms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fines.php"><i class="fas fa-money-bill me-1"></i>Fine Details</a>
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

    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="mb-1">Welcome back, <?php echo htmlspecialchars($student['full_name']); ?>!</h4>
                                <p class="mb-0">Student ID: <?php echo $student['student_id']; ?></p>
                            </div>
                            <div class="col-auto">
                                <div class="welcome-date">
                                    <i class="fas fa-calendar me-2"></i><?php echo date('d F Y'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Present Days</h6>
                                <h3 class="mb-0"><?php echo $attendanceStats['present_days'] ?? 0; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Absent Days</h6>
                                <h3 class="mb-0"><?php echo $attendanceStats['absent_days'] ?? 0; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Late Days</h6>
                                <h3 class="mb-0"><?php echo $attendanceStats['late_days'] ?? 0; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Fines</h6>
                                <h3 class="mb-0">₹<?php echo number_format($totalFines, 2); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-money-bill fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="forms.php" class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-2"></i>Fill Forms
                            </a>
                            <a href="attendance.php" class="btn btn-outline-success">
                                <i class="fas fa-calendar-check me-2"></i>View Attendance
                            </a>
                            <a href="profile.php" class="btn btn-outline-info">
                                <i class="fas fa-user me-2"></i>Update Profile
                            </a>
                            <a href="fines.php" class="btn btn-outline-warning">
                                <i class="fas fa-money-bill me-2"></i>View Fines
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Form Submissions -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent Forms</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentForms)): ?>
                            <p class="text-muted text-center">No forms submitted yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentForms as $form): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $form['form_type'])); ?></strong><br>
                                    <small class="text-muted"><?php echo formatDate($form['submitted_at']); ?></small>
                                </div>
                                <span class="badge bg-success">Submitted</span>
                            </div>
                            <?php if (!end($recentForms) !== $form): ?>
                            <hr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Fines -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Recent Fines</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($fines)): ?>
                            <p class="text-muted text-center">No fines recorded.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($fines, 0, 3) as $fine): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($fine['reason']); ?></strong><br>
                                    <small class="text-muted"><?php echo formatDate($fine['created_at']); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $fine['status'] === 'paid' ? 'success' : 'danger'; ?>">
                                    ₹<?php echo number_format($fine['amount'], 2); ?>
                                </span>
                            </div>
                            <?php if (end(array_slice($fines, 0, 3)) !== $fine): ?>
                            <hr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
