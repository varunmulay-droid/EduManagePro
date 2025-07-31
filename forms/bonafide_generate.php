<?php
require_once '../includes/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if form data is provided
if (!isset($_GET['submission_id'])) {
    header('Location: ../student/forms.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get form submission data
$query = "SELECT fs.*, s.full_name, s.student_id 
          FROM form_submissions fs 
          JOIN students s ON fs.student_id = s.id 
          WHERE fs.id = ? AND fs.form_type = 'bonafide_certificate'";
$stmt = $conn->prepare($query);
$stmt->execute([$_GET['submission_id']]);
$submission = $stmt->fetch();

if (!$submission) {
    header('Location: ../student/forms.php');
    exit();
}

$formData = json_decode($submission['form_data'], true);
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>प्रतिनिधान प्रमाणपत्र</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Devanagari', sans-serif;
            background-color: #f4f9ff;
            padding: 50px;
            margin: 0;
        }

        .certificate-box {
            max-width: 850px;
            margin: auto;
            padding: 40px;
            border: 4px double #2c3e50;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .school-name {
            position: absolute;
            top: 15px;
            left: 25px;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

        .title {
            text-align: center;
            font-size: 28px;
            margin-bottom: 30px;
            margin-top: 40px;
            color: #1a237e;
            text-decoration: underline;
        }

        .certificate-box p {
            font-size: 18px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 12px;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding: 0 30px;
            font-weight: bold;
            font-size: 18px;
        }

        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }

        @media print {
            .print-btn { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>

<body>
    <button class="print-btn btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print Certificate
    </button>

    <div class="certificate-box">
        <!-- School Name in Top-Left Corner -->
        <p class="school-name">शाळेचे नाव: संग्राम मुकबधीर विद्यालय</p>

        <!-- Main Heading -->
        <h1 class="title">प्रतिनिधान प्रमाणपत्र (Bonafide Certificate)</h1>

        <!-- Certificate Content -->
        <p>हा दाखला <strong><?php echo htmlspecialchars($submission['full_name']); ?></strong> या विद्यार्थ्याने आमच्या शाळेत <strong><?php echo htmlspecialchars($formData['academic_year'] ?? ''); ?></strong> या शैक्षणिक वर्षात <strong><?php echo htmlspecialchars($formData['class_standard'] ?? ''); ?></strong> इयत्तेत, <strong><?php echo htmlspecialchars($formData['division'] ?? ''); ?></strong> तुकडीत प्रवेश घेतल्याचा पुरावा म्हणून देण्यात येत आहे.</p>

        <p>त्याचे वर्तन <strong><?php echo htmlspecialchars($formData['conduct'] ?? ''); ?></strong> असून तो/ती <strong><?php echo htmlspecialchars($formData['caste'] ?? ''); ?></strong> या जातीचा आहे/आहे.</p>

        <p>त्याची जन्मतारीख <strong><?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?></strong> असून जन्मस्थान <strong><?php echo htmlspecialchars($formData['birth_place'] ?? ''); ?></strong> आहे.</p>

        <p>वरील माहिती आमच्या शाळेच्या नोंदीप्रमाणे खरी असून ती शाळेच्या रेकॉर्डमध्ये अस्तित्वात आहे.</p>

        <p>हा दाखला <strong><?php echo htmlspecialchars($formData['school_place'] ?? ''); ?></strong> येथे देण्यात आला आहे. विद्यार्थी ID: <strong><?php echo $submission['student_id']; ?></strong></p>

        <?php if (!empty($formData['purpose'])): ?>
        <p><strong>उद्देश:</strong> <?php echo htmlspecialchars($formData['purpose']); ?></p>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <div>
                <p>तारीख: <?php echo formatDate($submission['submitted_at'], 'd/m/Y'); ?></p>
            </div>
            <div style="text-align: center;">
                <p>मुख्याध्यापक</p>
                <div style="margin-top: 20px;">
                    <p>शाळेचा शिक्का</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
