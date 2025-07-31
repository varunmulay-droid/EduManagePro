<?php
session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: student/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="landing-page">
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-primary text-white">
                <div class="text-center">
                    <i class="fas fa-graduation-cap fa-5x mb-4"></i>
                    <h1 class="display-4 fw-bold mb-3">Student Management System</h1>
                    <p class="lead mb-4">संग्राम मुकबधीर विद्यालय</p>
                    <p class="fs-5">Comprehensive student portal with attendance tracking, form management, and administrative controls</p>
                </div>
            </div>
            
            <!-- Right Side - Login Options -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="login-container">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold mb-3">Welcome</h2>
                        <p class="text-muted">Choose your login type to continue</p>
                    </div>
                    
                    <!-- Login Type Selection -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card login-card" onclick="showLoginForm('student')">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                                    <h4 class="card-title">Student Login</h4>
                                    <p class="card-text text-muted">Access your dashboard, forms, and attendance</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card login-card" onclick="showLoginForm('admin')">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-shield fa-3x text-success mb-3"></i>
                                    <h4 class="card-title">Admin Login</h4>
                                    <p class="card-text text-muted">Manage students and system settings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student Login Form -->
                    <div id="studentLoginForm" class="login-form-container" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Login</h5>
                            </div>
                            <div class="card-body">
                                <form action="auth/login.php" method="POST">
                                    <input type="hidden" name="user_type" value="student">
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">Student ID</label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" placeholder="STU001" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="student_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="student_password" name="password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Login</button>
                                    </div>
                                </form>
                                <div class="text-center mt-3">
                                    <a href="#" onclick="showRegisterForm()">Don't have an account? Register here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Admin Login Form -->
                    <div id="adminLoginForm" class="login-form-container" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Admin Login</h5>
                            </div>
                            <div class="card-body">
                                <form action="auth/login.php" method="POST">
                                    <input type="hidden" name="user_type" value="admin">
                                    <div class="mb-3">
                                        <label for="admin_username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="admin_username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="admin_password" name="password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success">Login</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student Registration Form -->
                    <div id="registerForm" class="login-form-container" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Student Registration</h5>
                            </div>
                            <div class="card-body">
                                <form action="auth/register.php" method="POST">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-info">Register</button>
                                    </div>
                                </form>
                                <div class="text-center mt-3">
                                    <a href="#" onclick="showLoginForm('student')">Already have an account? Login here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button class="btn btn-outline-secondary" onclick="resetForms()">
                            <i class="fas fa-arrow-left me-2"></i>Back to Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function showLoginForm(type) {
            resetForms();
            if (type === 'student') {
                document.getElementById('studentLoginForm').style.display = 'block';
            } else if (type === 'admin') {
                document.getElementById('adminLoginForm').style.display = 'block';
            }
        }
        
        function showRegisterForm() {
            resetForms();
            document.getElementById('registerForm').style.display = 'block';
        }
        
        function resetForms() {
            document.getElementById('studentLoginForm').style.display = 'none';
            document.getElementById('adminLoginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'none';
        }
    </script>
</body>
</html>
