<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireStudent();

$database = new Database();
$conn = $database->getConnection();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullName = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        
        // Handle profile photo upload
        $profilePhoto = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $profilePhoto = uploadFile($_FILES['profile_photo'], ['jpg', 'jpeg', 'png']);
        }
        
        // Build update query
        $updateFields = ['full_name = ?', 'email = ?', 'phone = ?', 'address = ?'];
        $params = [$fullName, $email, $phone, $address];
        
        if ($profilePhoto) {
            $updateFields[] = 'profile_photo = ?';
            $params[] = $profilePhoto;
        }
        
        $params[] = $_SESSION['user_id'];
        
        $query = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        
        logActivity($_SESSION['user_id'], 'student', 'update_profile', 'Profile updated');
        $_SESSION['success'] = 'Profile updated successfully';
        
        // Update session name if changed
        if ($fullName !== $_SESSION['full_name']) {
            $_SESSION['full_name'] = $fullName;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: profile.php');
    exit();
}

// Get student information
$student = getStudentInfo($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Student Portal</title>
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
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="profile-photo-container mb-3">
                            <?php if ($student['profile_photo']): ?>
                            <img src="../uploads/<?php echo $student['profile_photo']; ?>" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-4x text-white"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                        <p class="text-muted"><?php echo $student['student_id']; ?></p>
                        <span class="badge bg-<?php echo $student['status'] === 'active' ? 'success' : 'secondary'; ?> fs-6">
                            <?php echo ucfirst($student['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="text" class="form-control" id="student_id" value="<?php echo $student['student_id']; ?>" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                                <div class="form-text">Upload a new profile photo (JPG, PNG). Maximum size: 5MB</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Registration Date</label>
                                    <input type="text" class="form-control" value="<?php echo formatDate($student['created_at']); ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Updated</label>
                                    <input type="text" class="form-control" value="<?php echo formatDate($student['updated_at'] ?? $student['created_at']); ?>" disabled>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
