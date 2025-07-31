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

// Get student's form submissions
$query = "SELECT * FROM form_submissions WHERE student_id = ? ORDER BY submitted_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forms - Student Portal</title>
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
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-dashboard me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="forms.php"><i class="fas fa-file-alt me-1"></i>Forms</a>
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

    <div class="container py-4">
        <div class="row">
            <!-- Available Forms -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Available Forms / उपलब्ध फॉर्म</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card form-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-notes-medical fa-3x text-primary mb-3"></i>
                                        <h6 class="card-title">केस रेकॉर्ड फॉर्म</h6>
                                        <p class="card-text text-muted">Medical examination form</p>
                                        <a href="../forms/case_record_form.php" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Fill Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card form-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-home fa-3x text-success mb-3"></i>
                                        <h6 class="card-title">वसतिगृह प्रवेश अर्ज</h6>
                                        <p class="card-text text-muted">Hostel registration form</p>
                                        <a href="../forms/hostel_registration.php" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit me-1"></i>Fill Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card form-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-certificate fa-3x text-info mb-3"></i>
                                        <h6 class="card-title">प्रतिनिधान प्रमाणपत्र</h6>
                                        <p class="card-text text-muted">Bonafide certificate form</p>
                                        <a href="../forms/bonafide_certificate.php" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit me-1"></i>Fill Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card form-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user-graduate fa-3x text-warning mb-3"></i>
                                        <h6 class="card-title">शाळा प्रवेश अर्ज</h6>
                                        <p class="card-text text-muted">School admission form</p>
                                        <a href="../forms/admission_form.php" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i>Fill Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Submissions History -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Submission History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($submissions)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No forms submitted yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="submission-list">
                                <?php foreach ($submissions as $submission): ?>
                                <div class="submission-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $submission['form_type'])); ?></h6>
                                            <small class="text-muted"><?php echo formatDate($submission['submitted_at'], 'd/m/Y H:i'); ?></small>
                                        </div>
                                        <span class="badge bg-success">Submitted</span>
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
    <script src="../assets/js/main.js"></script>
</body>
</html>
