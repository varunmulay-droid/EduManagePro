<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullName = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $password = sanitizeInput($_POST['password']);
        $confirmPassword = sanitizeInput($_POST['confirm_password']);
        
        // Validation
        if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        if ($password !== $confirmPassword) {
            throw new Exception('Passwords do not match');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        $database = new Database();
        $conn = $database->getConnection();
        
        // Check if email already exists
        $query = "SELECT id FROM students WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        // Generate student ID
        $studentId = generateStudentId();
        
        // Hash password
        $hashedPassword = hashPassword($password);
        
        // Insert student
        $query = "INSERT INTO students (student_id, full_name, email, phone, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([$studentId, $fullName, $email, $phone, $hashedPassword]);
        
        logActivity($conn->lastInsertId(), 'student', 'register', 'Student registered with ID: ' . $studentId);
        
        $_SESSION['success'] = "Registration successful! Your Student ID is: $studentId";
        header('Location: ../index.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../index.php');
        exit();
    }
}

header('Location: ../index.php');
exit();
?>
