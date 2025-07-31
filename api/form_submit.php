<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    $formType = sanitizeInput($_POST['form_type']);
    $studentId = intval($_POST['student_id']);
    
    // Verify student authorization
    if (!isStudent() || $_SESSION['user_id'] !== $studentId) {
        throw new Exception('Unauthorized access');
    }

    // Handle file uploads
    $uploadedFiles = [];
    foreach ($_FILES as $fieldName => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            try {
                $fileName = uploadFile($file);
                $uploadedFiles[$fieldName] = $fileName;
            } catch (Exception $e) {
                // Log upload error but continue with form submission
                error_log("File upload error for $fieldName: " . $e->getMessage());
            }
        }
    }

    // Prepare form data
    $formData = $_POST;
    unset($formData['form_type'], $formData['student_id']);
    
    // Add uploaded file references to form data
    foreach ($uploadedFiles as $fieldName => $fileName) {
        $formData[$fieldName] = $fileName;
    }

    // Insert form submission
    $query = "INSERT INTO form_submissions (student_id, form_type, form_data, submitted_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        $studentId,
        $formType,
        json_encode($formData)
    ]);
    
    $submissionId = $conn->lastInsertId();
    
    // Log activity
    logActivity($studentId, 'student', 'form_submission', "Submitted $formType form");

    $response = [
        'success' => true,
        'message' => 'Form submitted successfully',
        'submission_id' => $submissionId
    ];

    // Special handling for bonafide certificate generation
    if ($formType === 'bonafide_certificate') {
        $response['certificate_url'] = '../forms/bonafide_generate.php?submission_id=' . $submissionId;
    }

    // Return to forms page with success message
    $_SESSION['success'] = 'Form submitted successfully!';
    
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ../student/forms.php');
    } else {
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log("Form submission error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
