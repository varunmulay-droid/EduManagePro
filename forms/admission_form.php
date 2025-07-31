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
    <title>शाळा प्रवेश अर्ज</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Noto Sans Devanagari', sans-serif;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
            font-size: 28px;
            color: #2c3e50;
            position: relative;
        }
        
        .form-title .form-no {
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            padding: 5px;
            font-size: 14px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #34495e;
            border-bottom: 2px solid #ced4da;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <a href="../student/forms.php" class="btn btn-secondary back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Forms
    </a>

    <div class="container mt-5 mb-5">
        <div class="bg-white p-4 shadow rounded">
            <div class="form-title">
                शाळा प्रवेश अर्ज
                <div class="form-no">
                    <label for="application_no">अ.नं</label>
                    <input type="text" class="form-control" value="<?php echo $student['student_id']; ?>" readonly />
                </div>
            </div>
            
            <div class="alert alert-info">
                <strong>Student Information:</strong><br>
                Name: <?php echo htmlspecialchars($student['full_name']); ?><br>
                Student ID: <?php echo $student['student_id']; ?><br>
                Email: <?php echo htmlspecialchars($student['email']); ?>
            </div>

            <form id="admissionForm" action="../api/form_submit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="admission_form">
                <input type="hidden" name="student_id" value="<?php echo $_SESSION['user_id']; ?>">

                <!-- विद्यार्थी आणि शाळेची माहिती -->
                <div class="section-title">विद्यार्थी आणि शाळेची माहिती</div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">शाळेचे नाव</label>
                        <input type="text" class="form-control" name="school_name" value="संग्राम मुकबधीर विद्यालय" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">विद्यार्थी सतत ID</label>
                        <input type="text" class="form-control" name="continuous_student_id" value="<?php echo $student['student_id']; ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">U-DISE+ पोर्टल SDMS - STUDENT PEN</label>
                        <input type="text" class="form-control" name="udise_pen">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label">प्रवेश इयत्ता</label>
                        <input type="text" class="form-control" name="admission_class" required>
                    </div>
                </div>

                <!-- Personal IDs and Dates -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">जन्माचा रजिस्टर नं.</label>
                        <input type="text" class="form-control" name="birth_register_no">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">आधार कार्ड नं.</label>
                        <input type="text" class="form-control" name="aadhar_number" pattern="[0-9]{12}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">जन्मतारीख</label>
                        <input type="date" class="form-control" name="date_of_birth" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">शाळेत प्रवेश तारीख</label>
                        <input type="date" class="form-control" name="admission_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <!-- Gender and Photos -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label d-block">लिंग</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" required>
                            <label class="form-check-label" for="male">मुलगा</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="female" required>
                            <label class="form-check-label" for="female">मुलगी</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="student_photo" class="form-label">विद्यार्थ्याचे फोटो</label>
                        <input type="file" class="form-control" id="student_photo" name="student_photo" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <label for="parent_photo" class="form-label">पालकांचे फोटो</label>
                        <input type="file" class="form-control" id="parent_photo" name="parent_photo" accept="image/*">
                    </div>
                </div>

                <!-- Student Name -->
                <div class="section-title">विद्यार्थ्याचे नाव</div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">प्रथम नाव (मराठी)</label>
                        <input type="text" class="form-control" name="first_name_marathi" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">आडनाव (मराठी)</label>
                        <input type="text" class="form-control" name="last_name_marathi" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">वडिलांचे नाव</label>
                        <input type="text" class="form-control" name="father_name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">आईचे नाव</label>
                        <input type="text" class="form-control" name="mother_name" required>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">जन्मतारीख अक्षरी</label>
                        <input type="text" class="form-control" name="birth_date_words" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">धर्म</label>
                        <input type="text" class="form-control" name="religion" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">जात</label>
                        <input type="text" class="form-control" name="caste" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">पोटजात</label>
                        <input type="text" class="form-control" name="sub_caste">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">जात प्रमाण</label>
                        <input type="text" class="form-control" name="caste_certificate">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">अल्पसंख्याक</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="minority" id="minorityYes" value="yes">
                            <label class="form-check-label" for="minorityYes">आहे</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="minority" id="minorityNo" value="no">
                            <label class="form-check-label" for="minorityNo">नाही</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">राष्ट्रत्व</label>
                        <input type="text" class="form-control" name="nationality" value="भारतीय" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">मातृभाषा</label>
                        <input type="text" class="form-control" name="mother_tongue" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">मोबाईल क्रमांक</label>
                        <input type="tel" class="form-control" name="mobile_number" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                    </div>
                </div>

                <!-- BPL and Disability -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">BPL स्थिती</label>
                        <div class="d-md-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="bpl_status" id="bplYes" value="yes">
                                <label class="form-check-label" for="bplYes">आहे</label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="bpl_status" id="bplNo" value="no">
                                <label class="form-check-label" for="bplNo">नाही</label>
                            </div>
                            <input type="text" class="form-control mt-2 mt-md-0" name="bpl_number" placeholder="BPL क्रमांक">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">दिव्यांग स्थिती</label>
                        <div class="d-md-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="disability_status" id="disabilityYes" value="yes">
                                <label class="form-check-label" for="disabilityYes">आहे</label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="disability_status" id="disabilityNo" value="no">
                                <label class="form-check-label" for="disabilityNo">नाही</label>
                            </div>
                            <input type="text" class="form-control mt-2 mt-md-0" name="disability_type" placeholder="दिव्यांग प्रकार">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">पालकांचे संपूर्ण नाव</label>
                    <input type="text" class="form-control" name="parent_full_name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">पत्ता</label>
                    <textarea class="form-control" name="address" rows="2" required><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmationCheck" name="confirmation" required>
                        <label class="form-check-label" for="confirmationCheck">
                            वरील नमूद केलेली सर्व माहिती बरोबर असून मी ती स्वतः दिली आहे. तसेच रजिस्टर नंबर व त्या नंतर देण्यात आलेली माहिती खरीखुरी आहे.
                        </label>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>जमा करा
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
