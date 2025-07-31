<?php
require_once __DIR__ . '/../config/database.php';

function sanitizeInput($data) {
    if ($data === null) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateStudentId() {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get the highest existing student ID number
    $query = "SELECT student_id FROM students ORDER BY CAST(SUBSTRING(student_id, 4) AS UNSIGNED) DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        $lastId = intval(substr($result['student_id'], 3));
        $nextId = $lastId + 1;
    } else {
        $nextId = MIN_STUDENT_ID;
    }
    
    if ($nextId > MAX_STUDENT_ID) {
        throw new Exception("Maximum student capacity reached");
    }
    
    return 'STU' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header('Location: ../index.php');
        exit();
    }
}

function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSize = MAX_FILE_SIZE) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds maximum allowed size');
    }
    
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('File type not allowed');
    }
    
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return $fileName;
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function getStudentInfo($studentId) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$studentId]);
    
    return $stmt->fetch();
}

function getAttendanceStats($studentId, $month = null, $year = null) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $whereClause = "WHERE student_id = ?";
    $params = [$studentId];
    
    if ($month && $year) {
        $whereClause .= " AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?";
        $params[] = $month;
        $params[] = $year;
    }
    
    $query = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
              FROM attendance $whereClause";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetch();
}

function logActivity($userId, $userType, $action, $details = null) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "INSERT INTO activity_logs (user_id, user_type, action, details, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId, $userType, $action, $details]);
}
?>
