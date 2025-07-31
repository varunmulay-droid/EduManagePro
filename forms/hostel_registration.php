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
    <title>वसतिगृह प्रवेश अर्ज</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Devanagari', sans-serif;
            background-color: #f8f9fa;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .rules {
            margin-top: 30px;
            border-top: 2px solid #000;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <a href="../student/forms.php" class="btn btn-secondary back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Forms
    </a>

    <div class="container bg-white p-4 shadow rounded mt-5">
        <div class="text-center mb-4">
            <h2>वसतिगृह प्रवेश अर्ज</h2>
            <p class="text-muted">Student: <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo $student['student_id']; ?>)</p>
        </div>

        <form id="hostelForm" action="../api/form_submit.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="hostel_registration">
            <input type="hidden" name="student_id" value="<?php echo $_SESSION['user_id']; ?>">

            <div class="section">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="hostel_name" class="form-label">वसतिगृहाचे नाव</label>
                        <input type="text" class="form-control" name="hostel_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="hostel_address" class="form-label">वसतिगृहाचा पत्ता</label>
                        <input type="text" class="form-control" name="hostel_address" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="parent_name" class="form-label">अर्जदार (पालकांचे नाव)</label>
                        <input type="text" class="form-control" name="parent_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="parent_address" class="form-label">पालकांचा पत्ता</label>
                        <input type="text" class="form-control" name="parent_address" required>
                    </div>
                </div>
            </div>

            <h3 class="mt-4 mb-3">* मुलाची / मुलीची माहिती *</h3>

            <div class="section">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="student_full_name" class="form-label">१) संपूर्ण नाव</label>
                        <input type="text" class="form-control" name="student_full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">३) फोन नं.</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="student_address" class="form-label">२) संपूर्ण पत्ता</label>
                    <textarea class="form-control" name="student_address" rows="2" required><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="caste" class="form-label">४) जात व पोटजात</label>
                        <input type="text" class="form-control" name="caste" required>
                    </div>
                    <div class="col-md-6">
                        <label for="dob" class="form-label">६) जन्म तारीख</label>
                        <input type="date" class="form-control" name="dob" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="birth_village" class="form-label">५) जन्म ठिकाण - मु.</label>
                        <input type="text" class="form-control" name="birth_village" required>
                    </div>
                    <div class="col-md-4">
                        <label for="birth_taluka" class="form-label">ता.</label>
                        <input type="text" class="form-control" name="birth_taluka" required>
                    </div>
                    <div class="col-md-4">
                        <label for="birth_district" class="form-label">जि.</label>
                        <input type="text" class="form-control" name="birth_district" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="age_years" class="form-label">७) वय - वर्षे</label>
                        <input type="number" class="form-control" name="age_years" required>
                    </div>
                    <div class="col-md-6">
                        <label for="age_months" class="form-label">महिने</label>
                        <input type="number" class="form-control" name="age_months" max="11">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="education" class="form-label">८) शिक्षण</label>
                        <input type="text" class="form-control" name="education" required>
                    </div>
                    <div class="col-md-6">
                        <label for="annual_income" class="form-label">१०) पालकांचे वार्षिक उत्पन्न</label>
                        <input type="number" class="form-control" name="annual_income" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="previous_school" class="form-label">९) पूर्वी शिकत असलेल्या शाळेचे नाव व पत्ता</label>
                    <textarea class="form-control" name="previous_school" rows="2" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="exam_results" class="form-label">११) मागील तीन वर्षांच्या परीक्षांचा निकाल</label>
                    <textarea class="form-control" name="exam_results" rows="3" required></textarea>
                </div>
            </div>

            <h3 class="mt-4 mb-3">* पालकांनी भरून द्यावयाचे करार-पत्र *</h3>

            <div class="section">
                <div class="mb-3">
                    <label for="guardian_name" class="form-label">पालक/पालिकाचे नाव</label>
                    <input type="text" class="form-control" name="guardian_name" required>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="parent_agreement" name="parent_agreement" required>
                        <label class="form-check-label" for="parent_agreement">
                            मी प्रमाणित करतो की वरील सर्व माहिती खरी आहे व वसतिगृहाच्या नियमांचे पालन करण्यास सहमत आहे.
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="parent_signature" class="form-label">पालकांची सही (फाइल अपलोड करा)</label>
                    <input type="file" class="form-control" name="parent_signature" accept="image/*">
                </div>
            </div>

            <h3 class="mt-4 mb-3">* विद्यार्थ्याने भरून द्यावयाचे करार-पत्र *</h3>

            <div class="section">
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="student_agreement" name="student_agreement" required>
                        <label class="form-check-label" for="student_agreement">
                            मी प्रमाणित करतो की वसतिगृहाच्या नियम व अटी मला मान्य आहेत व त्यांचे पालन करण्यास सहमत आहे.
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="student_signature" class="form-label">विद्यार्थ्याची सही (फाइल अपलोड करा)</label>
                    <input type="file" class="form-control" name="student_signature" accept="image/*">
                </div>
            </div>

            <div class="rules">
                <h3>* वसतिगृहाचे नियम *</h3>
                <ol>
                    <li>संस्थेच्या परवानगीशिवाय अनुपस्थित राहता येणार नाही.</li>
                    <li>संस्थेच्या संमतीशिवाय वसतिगृहात प्रवेश मिळणार नाही.</li>
                    <li>वसतिगृह नियमांचे काटेकोर पालन करणे बंधनकारक आहे.</li>
                    <li>शांतता, स्वच्छता, आणि शिस्त राखावी लागेल.</li>
                    <li>वसतिगृह प्रवेशानंतर अन्य संस्थेत प्रवेश घेतल्यास प्रवेश रद्द होईल.</li>
                    <li>स्वतंत्रपणे राहण्यास परवानगी नाही.</li>
                    <li>सर्व परीक्षा उपस्थित राहणे बंधनकारक आहे.</li>
                    <li>खोली व्यवस्थापनाच्या सूचनेनुसार बदली जाईल.</li>
                    <li>वर्तन सुसंगत ठेवणे आवश्यक आहे.</li>
                    <li>प्रशासनाच्या नविन नियमांचे पालन करावे लागेल.</li>
                    <li>अनधिकृत उपकरणांचा वापर टाळावा.</li>
                </ol>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Submit Application
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
