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
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>केस रेकॉर्ड फॉर्म - विभाग १३ ते १६</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Noto Sans Devanagari', sans-serif;
        }

        .form-section-title {
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <a href="../student/forms.php" class="btn btn-secondary back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Forms
    </a>
    
    <div class="container bg-white p-4 shadow rounded mt-5">
        <div class="text-center mb-4">
            <h2>केस रेकॉर्ड फॉर्म</h2>
            <p class="text-muted">Student: <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo $student['student_id']; ?>)</p>
        </div>

        <form id="caseRecordForm" action="../api/form_submit.php" method="post">
            <input type="hidden" name="form_type" value="case_record">
            <input type="hidden" name="student_id" value="<?php echo $_SESSION['user_id']; ?>">
            
            <!-- विभाग १३ -->
            <h5 class="form-section-title">विभाग १३: शारिरीक तपासणी</h5>

            <div class="mb-3">
                <label for="generalExamination" class="form-label">१३.१ सर्वसाधारण तपासणी</label>
                <textarea class="form-control" id="generalExamination" name="general_examination" rows="2" required></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="weight" class="form-label">१३.२ वजन (किलोमध्ये)</label>
                    <input type="number" class="form-control" id="weight" name="weight" step="0.1" required>
                </div>
                <div class="col-md-6">
                    <label for="height" class="form-label">उंची (से.मी.)</label>
                    <input type="number" class="form-control" id="height" name="height" step="0.1" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="physicalDisabilities" class="form-label">१३.३ शारिरीक उपनग्नत्र असल्यास थोडक्यात वर्णन करा</label>
                <textarea class="form-control" id="physicalDisabilities" name="physical_disabilities" rows="2"></textarea>
            </div>

            <div class="mb-3">
                <label for="blindDeaf" class="form-label">१३.४ अंधत्व / कर्णबधिरत्व</label>
                <textarea class="form-control" id="blindDeaf" name="blind_deaf" rows="2"></textarea>
            </div>

            <!-- विभाग १४ -->
            <h5 class="form-section-title">विभाग १४: मतीमंद / मूकबधिर व्यक्तीची मुलाखत आणि निरीक्षणाचे मुद्दे</h5>
            <div class="mb-3">
                <textarea class="form-control" id="interviewObservations" name="interview_observations" rows="3" required></textarea>
            </div>

            <!-- विभाग १५ -->
            <h5 class="form-section-title">विभाग १५: तात्पुरते निदान</h5>
            <div class="mb-3">
                <textarea class="form-control" id="provisionalDiagnosis" name="provisional_diagnosis" rows="3" required></textarea>
            </div>

            <!-- विभाग १६ -->
            <h5 class="form-section-title">विभाग १६: व्यवस्थापनाचा आराखडा / कार्यश्रमाचा आराखडा</h5>
            <div class="mb-3">
                <textarea class="form-control" id="managementPlan" name="management_plan" rows="3" required></textarea>
            </div>

            <!-- तारीख आणि सही -->
            <div class="row mb-4 mt-4">
                <div class="col-md-6">
                    <label for="examination_date" class="form-label">तारीख</label>
                    <input type="date" class="form-control" id="examination_date" name="examination_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="doctor_signature" class="form-label">डॉक्टर/समुपदेशकाची सही</label>
                    <input type="text" class="form-control" id="doctor_signature" name="doctor_signature" placeholder="डॉक्टर/समुपदेशकाची सही" required>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>सबमिट करा
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
