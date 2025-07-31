<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [];

// Total students
$query = "SELECT COUNT(*) as total FROM students";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['total_students'] = $stmt->fetch()['total'];

// Students registered this month
$query = "SELECT COUNT(*) as total FROM students WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['new_students'] = $stmt->fetch()['total'];

// Total form submissions
$query = "SELECT COUNT(*) as total FROM form_submissions";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['form_submissions'] = $stmt->fetch()['total'];

// Today's attendance
$query = "SELECT COUNT(*) as total FROM attendance WHERE DATE(attendance_date) = CURRENT_DATE()";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['todays_attendance'] = $stmt->fetch()['total'];

// Recent activities
$query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->execute();
$recent_activities = $stmt->fetchAll();

// Recent form submissions
$query = "SELECT fs.*, s.full_name, s.student_id 
          FROM form_submissions fs 
          JOIN students s ON fs.student_id = s.id 
          ORDER BY fs.submitted_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$recent_forms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Management System</title>
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
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-dashboard me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php"><i class="fas fa-users me-1"></i>Students</a>
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Students</h6>
                                <h3 class="mb-0"><?php echo $stats['total_students']; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">New This Month</h6>
                                <h3 class="mb-0"><?php echo $stats['new_students']; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-plus fa-2x"></i>
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
                                <h6 class="card-title">Form Submissions</h6>
                                <h3 class="mb-0"><?php echo $stats['form_submissions']; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-file-alt fa-2x"></i>
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
                                <h6 class="card-title">Today's Attendance</h6>
                                <h3 class="mb-0"><?php echo $stats['todays_attendance']; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Form Submissions -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent Form Submissions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_forms)): ?>
                            <p class="text-muted">No form submissions yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Form Type</th>
                                            <th>Submitted</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_forms as $form): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($form['full_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo $form['student_id']; ?></small>
                                            </td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $form['form_type'])); ?></td>
                                            <td><?php echo formatDate($form['submitted_at'], 'd/m/Y H:i'); ?></td>
                                            <td>
                                                <a href="forms.php?view=<?php echo $form['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
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

            <!-- Recent Activities -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted">No recent activities.</p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-content">
                                        <small class="text-muted"><?php echo formatDate($activity['created_at'], 'd/m/Y H:i'); ?></small>
                                        <p class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></p>
                                        <?php if ($activity['details']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($activity['details']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
