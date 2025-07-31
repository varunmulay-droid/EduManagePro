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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>प्रतिनिधान प्रमाणपत्र फॉर्म</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Devanagari', sans-serif;
            background: linear-gradient(to right, #f4f4f4, #e0eafc);
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border: 2px solid #3498db;
        }
    </style>
</head>

<body>
    <a href="../student/forms.php" class="btn btn-secondary back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Forms
    </a>

    <div class="container mt-5">
        <div class="school-name">शाळेचे नाव: संग्राम मुकबधीर विद्यालय</div>

        <h1 class="text-center mb-4">प्रतिनिधान प्रमाणपत्र फॉर्म</h1>

        <div class="form-container">
            <div class="alert alert-info">
                <strong>Student Information:</strong><br>
                Name: <?php echo htmlspecialchars($student['full_name']); ?><br>
                Student ID: <?php echo $student['student_id']; ?><br>
                Email: <?php echo htmlspecialchars($student['email']); ?>
            </div>

            <form id="bonafideForm" action="../api/form_submit.php" method="POST">
                <input type="hidden" name="form_type" value="bonafide_certificate">
                <input type="hidden" name="student_id" value="<?php echo $_SESSION['user_id']; ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="academic_year" class="form-label">शैक्षणिक वर्ष</label>
                        <input type="text" class="form-control" name="academic_year" placeholder="2023-2024" required>
                    </div>
                    <div class="col-md-6">
                        <label for="class_standard" class="form-label">इयत्ता</label>
                        <input type="text" class="form-control" name="class_standard" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="division" class="form-label">तुकडी</label>
                        <input type="text" class="form-control" name="division" required>
                    </div>
                    <div class="col-md-6">
                        <label for="conduct" class="form-label">वर्तन</label>
                        <select class="form-control" name="conduct" required>
                            <option value="">निवडा</option>
                            <option value="उत्कृष्ट">उत्कृष्ट</option>
                            <option value="चांगले">चांगले</option>
                            <option value="संतोषजनक">संतोषजनक</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="caste" class="form-label">जात</label>
                        <input type="text" class="form-control" name="caste" required>
                    </div>
                    <div class="col-md-6">
                        <label for="date_of_birth" class="form-label">जन्मतारीख</label>
                        <input type="date" class="form-control" name="date_of_birth" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="birth_place" class="form-label">जन्मस्थान</label>
                        <input type="text" class="form-control" name="birth_place" required>
                    </div>
                    <div class="col-md-6">
                        <label for="school_place" class="form-label">शाळेचे स्थळ</label>
                        <input type="text" class="form-control" name="school_place" value="संग्राम मुकबधीर विद्यालय" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label">प्रमाणपत्राचा उद्देश</label>
                    <textarea class="form-control" name="purpose" rows="2" placeholder="प्रमाणपत्र कशासाठी हवे आहे ते नमूद करा" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="additional_info" class="form-label">अतिरिक्त माहिती (वैकल्पिक)</label>
                    <textarea class="form-control" name="additional_info" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="declaration" name="declaration" required>
                        <label class="form-check-label" for="declaration">
                            मी प्रमाणित करतो की वरील सर्व माहिती खरी आहे.
                        </label>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-certificate me-2"></i>दाखला तयार करा
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
