<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = sanitizeInput($_POST['login_type'] ?? $_POST['user_type'] ?? '');
    
    if ($userType === 'admin') {
        $username = sanitizeInput($_POST['username']);
        $password = sanitizeInput($_POST['password']);
        
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['user_id'] = 'admin';
            $_SESSION['user_type'] = 'admin';
            $_SESSION['username'] = $username;
            
            logActivity('admin', 'admin', 'login', 'Admin logged in');
            
            header('Location: ../admin/dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Invalid admin credentials';
        }
    } elseif ($userType === 'student') {
        $studentId = sanitizeInput($_POST['student_id']);
        $password = sanitizeInput($_POST['password']);
        
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        
        if ($student && verifyPassword($password, $student['password'])) {
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['full_name'] = $student['full_name'];
            
            logActivity($student['id'], 'student', 'login', 'Student logged in');
            
            header('Location: ../student/dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Invalid student credentials';
        }
    }
}

header('Location: ../index.php');
exit();
?>
