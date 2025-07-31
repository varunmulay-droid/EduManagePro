<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['file'];
    
    // Determine allowed file types based on upload type
    $uploadType = $_POST['type'] ?? 'document';
    $allowedTypes = [];
    
    switch ($uploadType) {
        case 'image':
        case 'photo':
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            break;
        case 'document':
            $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            break;
        case 'signature':
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            break;
        default:
            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    }

    $fileName = uploadFile($file, $allowedTypes);
    
    // Log the upload
    logActivity($_SESSION['user_id'], $_SESSION['user_type'], 'file_upload', "Uploaded file: $fileName");

    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'filename' => $fileName,
        'url' => '../uploads/' . $fileName
    ]);

} catch (Exception $e) {
    error_log("File upload error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
