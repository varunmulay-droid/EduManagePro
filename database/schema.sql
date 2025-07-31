-- Student Management System Database Schema
-- MySQL/MariaDB Compatible

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS student_management 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE student_management;

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(10) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    profile_photo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_student_id (student_id),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form submissions table
CREATE TABLE form_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    form_type ENUM('case_record', 'hostel_registration', 'bonafide_certificate', 'admission_form') NOT NULL,
    form_data JSON NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_form (student_id, form_type),
    INDEX idx_submission_date (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_date (student_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_student_attendance (student_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fines table
CREATE TABLE fines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    fine_date DATE NOT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_fines (student_id),
    INDEX idx_fine_status (status),
    INDEX idx_fine_date (fine_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    user_type ENUM('admin', 'student') NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_activity (user_id, user_type),
    INDEX idx_activity_date (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certificates table (for generated certificates)
CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    certificate_type VARCHAR(50) NOT NULL,
    certificate_data JSON NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    certificate_path VARCHAR(255),
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_certificates (student_id),
    INDEX idx_certificate_type (certificate_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('school_name', 'संग्राम मुकबधीर विद्यालय', 'School name in Marathi'),
('school_name_english', 'Sangram Deaf & Dumb School', 'School name in English'),
('academic_year', '2024-2025', 'Current academic year'),
('max_students', '800', 'Maximum number of students allowed'),
('attendance_required_percentage', '75', 'Minimum attendance percentage required'),
('fine_late_fee', '10.00', 'Late fee amount per day'),
('certificate_validity_days', '90', 'Certificate validity period in days');

-- Sample data for testing (optional)
-- Insert a sample student
INSERT INTO students (student_id, full_name, email, phone, password, address, status) VALUES 
('STU001', 'राहुल शर्मा', 'rahul.sharma@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'मुंबई, महाराष्ट्र', 'active'),
('STU002', 'प्रिया पटेल', 'priya.patel@example.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'पुणे, महाराष्ट्र', 'active'),
('STU003', 'अमित कुमार', 'amit.kumar@example.com', '9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'नागपूर, महाराष्ट्र', 'active');

-- Sample attendance data
INSERT INTO attendance (student_id, attendance_date, status) VALUES
(1, '2024-01-15', 'present'),
(1, '2024-01-16', 'present'),
(1, '2024-01-17', 'absent'),
(2, '2024-01-15', 'present'),
(2, '2024-01-16', 'late'),
(2, '2024-01-17', 'present'),
(3, '2024-01-15', 'present'),
(3, '2024-01-16', 'present'),
(3, '2024-01-17', 'present');

-- Sample fines data
INSERT INTO fines (student_id, reason, description, amount, status, fine_date) VALUES
(1, 'Late Submission', 'Assignment submitted after deadline', 50.00, 'pending', '2024-01-10'),
(2, 'Library Book Fine', 'Book returned 5 days late', 25.00, 'paid', '2024-01-05'),
(3, 'Uniform Violation', 'Incorrect uniform on multiple occasions', 100.00, 'pending', '2024-01-12');

-- Create indexes for better performance
CREATE INDEX idx_students_created_at ON students(created_at);
CREATE INDEX idx_form_submissions_created_at ON form_submissions(submitted_at);
CREATE INDEX idx_attendance_status ON attendance(status);
CREATE INDEX idx_fines_amount ON fines(amount);

-- Create triggers for automatic logging
DELIMITER //

CREATE TRIGGER student_insert_log 
AFTER INSERT ON students 
FOR EACH ROW 
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, details) 
    VALUES (NEW.id, 'student', 'account_created', CONCAT('Student account created: ', NEW.student_id));
END//

CREATE TRIGGER student_update_log 
AFTER UPDATE ON students 
FOR EACH ROW 
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, details) 
    VALUES (NEW.id, 'student', 'profile_updated', CONCAT('Student profile updated: ', NEW.student_id));
END//

CREATE TRIGGER form_submission_log 
AFTER INSERT ON form_submissions 
FOR EACH ROW 
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, details) 
    VALUES (NEW.student_id, 'student', 'form_submitted', CONCAT('Form submitted: ', NEW.form_type));
END//

DELIMITER ;

-- Create views for common queries
CREATE VIEW student_summary AS
SELECT 
    s.id,
    s.student_id,
    s.full_name,
    s.email,
    s.phone,
    s.status,
    s.created_at,
    COUNT(DISTINCT a.id) as total_attendance_records,
    COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_days,
    COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_days,
    COALESCE(SUM(f.amount), 0) as total_fines,
    COUNT(DISTINCT fs.id) as total_forms_submitted
FROM students s
LEFT JOIN attendance a ON s.id = a.student_id
LEFT JOIN fines f ON s.id = f.student_id AND f.status = 'pending'
LEFT JOIN form_submissions fs ON s.id = fs.student_id
GROUP BY s.id;

CREATE VIEW monthly_attendance_summary AS
SELECT 
    YEAR(a.attendance_date) as year,
    MONTH(a.attendance_date) as month,
    COUNT(DISTINCT a.student_id) as total_students,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as total_present,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as total_absent,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as total_late,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(*)), 2) as attendance_percentage
FROM attendance a
GROUP BY YEAR(a.attendance_date), MONTH(a.attendance_date)
ORDER BY year DESC, month DESC;

-- Grant appropriate permissions (adjust as needed for your hosting environment)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON student_management.* TO 'your_db_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Final optimizations
OPTIMIZE TABLE students;
OPTIMIZE TABLE form_submissions;
OPTIMIZE TABLE attendance;
OPTIMIZE TABLE fines;
OPTIMIZE TABLE activity_logs;
