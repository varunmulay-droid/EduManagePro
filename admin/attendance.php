<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$database = new Database();
$conn = $database->getConnection();

// Handle attendance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_attendance':
                $date = sanitizeInput($_POST['attendance_date']);
                $attendanceData = $_POST['attendance'] ?? [];
                
                foreach ($attendanceData as $studentId => $status) {
                    // Check if attendance already exists for this date
                    $checkQuery = "SELECT id FROM attendance WHERE student_id = ? AND DATE(attendance_date) = ?";
                    $checkStmt = $conn->prepare($checkQuery);
                    $checkStmt->execute([$studentId, $date]);
                    
                    if ($checkStmt->fetch()) {
                        // Update existing attendance
                        $updateQuery = "UPDATE attendance SET status = ?, updated_at = NOW() WHERE student_id = ? AND DATE(attendance_date) = ?";
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->execute([$status, $studentId, $date]);
                    } else {
                        // Insert new attendance
                        $insertQuery = "INSERT INTO attendance (student_id, attendance_date, status, created_at) VALUES (?, ?, ?, NOW())";
                        $insertStmt = $conn->prepare($insertQuery);
                        $insertStmt->execute([$studentId, $date, $status]);
                    }
                }
                
                logActivity($_SESSION['user_id'], 'admin', 'mark_attendance', "Marked attendance for date: $date");
                $_SESSION['success'] = 'Attendance marked successfully';
                break;
        }
        header('Location: attendance.php');
        exit();
    }
}

// Get selected date (default to today)
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get students for attendance marking
$studentsQuery = "SELECT id, student_id, full_name, status FROM students WHERE status = 'active' ORDER BY student_id";
$studentsStmt = $conn->prepare($studentsQuery);
$studentsStmt->execute();
$students = $studentsStmt->fetchAll();

// Get existing attendance for selected date
$attendanceQuery = "SELECT student_id, status FROM attendance WHERE DATE(attendance_date) = ?";
$attendanceStmt = $conn->prepare($attendanceQuery);
$attendanceStmt->execute([$selectedDate]);
$existingAttendance = [];
while ($row = $attendanceStmt->fetch()) {
    $existingAttendance[$row['student_id']] = $row['status'];
}

// Get attendance statistics for current month
$currentMonth = date('Y-m');
$statsQuery = "SELECT 
                   COUNT(DISTINCT student_id) as total_students,
                   COUNT(CASE WHEN status = 'present' THEN 1 END) as total_present,
                   COUNT(CASE WHEN status = 'absent' THEN 1 END) as total_absent,
                   COUNT(CASE WHEN status = 'late' THEN 1 END) as total_late
               FROM attendance 
               WHERE DATE_FORMAT(attendance_date, '%Y-%m') = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->execute([$currentMonth]);
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Admin Panel</title>
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
                        <a class="nav-link" href="forms.php"><i class="fas fa-file-alt me-1"></i>Forms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="attendance.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Students</h6>
                                <h3 class="mb-0"><?php echo $stats['total_students'] ?? 0; ?></h3>
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
                                <h6 class="card-title">Present</h6>
                                <h3 class="mb-0"><?php echo $stats['total_present'] ?? 0; ?></h3>
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
                                <h6 class="card-title">Absent</h6>
                                <h3 class="mb-0"><?php echo $stats['total_absent'] ?? 0; ?></h3>
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
                                <h6 class="card-title">Late</h6>
                                <h3 class="mb-0"><?php echo $stats['total_late'] ?? 0; ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Mark Attendance</h5>
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" id="attendanceDate" value="<?php echo $selectedDate; ?>" onchange="changeDate()">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No active students found</h5>
                        <p class="text-muted">There are no active students to mark attendance for.</p>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="mark_attendance">
                        <input type="hidden" name="attendance_date" value="<?php echo $selectedDate; ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Late</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <?php $currentStatus = $existingAttendance[$student['id']] ?? 'present'; ?>
                                    <tr>
                                        <td><strong><?php echo $student['student_id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" <?php echo $currentStatus === 'present' ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" <?php echo $currentStatus === 'absent' ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" <?php echo $currentStatus === 'late' ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Attendance
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeDate() {
            const date = document.getElementById('attendanceDate').value;
            window.location.href = `attendance.php?date=${date}`;
        }
    </script>
</body>
</html>
