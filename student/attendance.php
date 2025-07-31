<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireStudent();

$database = new Database();
$conn = $database->getConnection();

// Get selected month and year (default to current)
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get attendance data for selected month
$query = "SELECT * FROM attendance 
          WHERE student_id = ? 
          AND MONTH(attendance_date) = ? 
          AND YEAR(attendance_date) = ? 
          ORDER BY attendance_date";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id'], $selectedMonth, $selectedYear]);
$attendance = $stmt->fetchAll();

// Create attendance array indexed by date
$attendanceByDate = [];
foreach ($attendance as $record) {
    $date = date('j', strtotime($record['attendance_date']));
    $attendanceByDate[$date] = $record['status'];
}

// Get statistics for selected month
$stats = getAttendanceStats($_SESSION['user_id'], $selectedMonth, $selectedYear);

// Calculate attendance percentage
$attendancePercentage = 0;
if ($stats['total_days'] > 0) {
    $attendancePercentage = ($stats['present_days'] / $stats['total_days']) * 100;
}

// Get number of days in selected month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$monthName = date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Student Portal</title>
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
                        <a class="nav-link active" href="attendance.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['full_name']; ?>
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
        <!-- Month Selection -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h3><i class="fas fa-calendar-check me-2"></i>Attendance - <?php echo $monthName; ?></h3>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <select name="month" class="form-select me-2">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i === $selectedMonth ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select me-2">
                        <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i === $selectedYear ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">View</button>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Present</h6>
                                <h3 class="mb-0"><?php echo $stats['present_days'] ?? 0; ?></h3>
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
                                <h3 class="mb-0"><?php echo $stats['absent_days'] ?? 0; ?></h3>
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
                                <h3 class="mb-0"><?php echo $stats['late_days'] ?? 0; ?></h3>
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
                                <h6 class="card-title">Percentage</h6>
                                <h3 class="mb-0"><?php echo number_format($attendancePercentage, 1); ?>%</h3>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Calendar -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Attendance Calendar</h5>
            </div>
            <div class="card-body">
                <div class="calendar-container">
                    <div class="row">
                        <?php
                        $firstDay = date('w', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
                        $currentDay = 1;
                        
                        // Week headers
                        $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        foreach ($weekDays as $day): ?>
                        <div class="col text-center font-weight-bold py-2">
                            <strong><?php echo $day; ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php for ($week = 0; $week < 6; $week++): ?>
                    <div class="row">
                        <?php for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++): ?>
                        <div class="col">
                            <?php
                            if (($week == 0 && $dayOfWeek < $firstDay) || $currentDay > $daysInMonth) {
                                echo '<div class="calendar-day empty"></div>';
                            } else {
                                $status = $attendanceByDate[$currentDay] ?? 'no-record';
                                $statusClass = '';
                                $statusIcon = '';
                                
                                switch ($status) {
                                    case 'present':
                                        $statusClass = 'bg-success text-white';
                                        $statusIcon = 'fas fa-check';
                                        break;
                                    case 'absent':
                                        $statusClass = 'bg-danger text-white';
                                        $statusIcon = 'fas fa-times';
                                        break;
                                    case 'late':
                                        $statusClass = 'bg-warning text-white';
                                        $statusIcon = 'fas fa-clock';
                                        break;
                                    default:
                                        $statusClass = 'bg-light';
                                        $statusIcon = '';
                                }
                                
                                echo "<div class='calendar-day $statusClass d-flex flex-column align-items-center justify-content-center'>";
                                echo "<div class='day-number'>$currentDay</div>";
                                if ($statusIcon) {
                                    echo "<i class='$statusIcon'></i>";
                                }
                                echo "</div>";
                                
                                $currentDay++;
                            }
                            ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php if ($currentDay > $daysInMonth) break; ?>
                    <?php endfor; ?>
                </div>
                
                <!-- Legend -->
                <div class="mt-4">
                    <h6>Legend:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success me-2"><i class="fas fa-check"></i></span>Present
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2"><i class="fas fa-times"></i></span>Absent
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning me-2"><i class="fas fa-clock"></i></span>Late
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-light text-dark me-2"></span>No Record
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
