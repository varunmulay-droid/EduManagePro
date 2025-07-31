-- Student Management System Database Schema
-- PostgreSQL Compatible

-- Create database (if not exists)
-- CREATE DATABASE student_management;

-- Connect to database
-- \c student_management;

-- Enable UUID extension for better ID generation
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id SERIAL PRIMARY KEY,
    student_id VARCHAR(10) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    profile_photo VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for students table
CREATE INDEX IF NOT EXISTS idx_student_id ON students(student_id);
CREATE INDEX IF NOT EXISTS idx_email ON students(email);
CREATE INDEX IF NOT EXISTS idx_status ON students(status);
CREATE INDEX IF NOT EXISTS idx_students_created_at ON students(created_at);

-- Form submissions table
CREATE TABLE IF NOT EXISTS form_submissions (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    form_type VARCHAR(50) NOT NULL CHECK (form_type IN ('case_record', 'hostel_registration', 'bonafide_certificate', 'admission_form')),
    form_data JSONB NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create indexes for form_submissions table
CREATE INDEX IF NOT EXISTS idx_student_form ON form_submissions(student_id, form_type);
CREATE INDEX IF NOT EXISTS idx_submission_date ON form_submissions(submitted_at);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('present', 'absent', 'late')),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE (student_id, attendance_date)
);

-- Create indexes for attendance table
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(attendance_date);
CREATE INDEX IF NOT EXISTS idx_student_attendance ON attendance(student_id, attendance_date);
CREATE INDEX IF NOT EXISTS idx_attendance_status ON attendance(status);

-- Fines table
CREATE TABLE IF NOT EXISTS fines (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    reason VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'paid')),
    fine_date DATE NOT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create indexes for fines table
CREATE INDEX IF NOT EXISTS idx_student_fines ON fines(student_id);
CREATE INDEX IF NOT EXISTS idx_fine_status ON fines(status);
CREATE INDEX IF NOT EXISTS idx_fine_date ON fines(fine_date);
CREATE INDEX IF NOT EXISTS idx_fines_amount ON fines(amount);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_type VARCHAR(20) NOT NULL CHECK (user_type IN ('admin', 'student')),
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for activity_logs table
CREATE INDEX IF NOT EXISTS idx_user_activity ON activity_logs(user_id, user_type);
CREATE INDEX IF NOT EXISTS idx_activity_date ON activity_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_action ON activity_logs(action);

-- Certificates table (for generated certificates)
CREATE TABLE IF NOT EXISTS certificates (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    certificate_type VARCHAR(50) NOT NULL,
    certificate_data JSONB NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    certificate_path VARCHAR(255),
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create indexes for certificates table
CREATE INDEX IF NOT EXISTS idx_student_certificates ON certificates(student_id);
CREATE INDEX IF NOT EXISTS idx_certificate_type ON certificates(certificate_type);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for system_settings table
CREATE INDEX IF NOT EXISTS idx_setting_key ON system_settings(setting_key);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('school_name', 'संग्राम मुकबधीर विद्यालय', 'School name in Marathi'),
('school_name_english', 'Sangram Deaf & Dumb School', 'School name in English'),
('academic_year', '2024-2025', 'Current academic year'),
('max_students', '800', 'Maximum number of students allowed'),
('attendance_required_percentage', '75', 'Minimum attendance percentage required'),
('fine_late_fee', '10.00', 'Late fee amount per day'),
('certificate_validity_days', '90', 'Certificate validity period in days')
ON CONFLICT (setting_key) DO NOTHING;

-- Sample data for testing (optional)
-- Insert sample students
INSERT INTO students (student_id, full_name, email, phone, password, address, status) VALUES 
('STU001', 'राहुल शर्मा', 'rahul.sharma@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'मुंबई, महाराष्ट्र', 'active'),
('STU002', 'प्रिया पटेल', 'priya.patel@example.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'पुणे, महाराष्ट्र', 'active'),
('STU003', 'अमित कुमार', 'amit.kumar@example.com', '9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'नागपूर, महाराष्ट्र', 'active')
ON CONFLICT (student_id) DO NOTHING;

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
(3, '2024-01-17', 'present')
ON CONFLICT (student_id, attendance_date) DO NOTHING;

-- Sample fines data
INSERT INTO fines (student_id, reason, description, amount, status, fine_date) VALUES
(1, 'Late Submission', 'Assignment submitted after deadline', 50.00, 'pending', '2024-01-10'),
(2, 'Library Book Fine', 'Book returned 5 days late', 25.00, 'paid', '2024-01-05'),
(3, 'Uniform Violation', 'Incorrect uniform on multiple occasions', 100.00, 'pending', '2024-01-12');

-- Create function to automatically update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updating updated_at timestamps
CREATE TRIGGER update_students_updated_at BEFORE UPDATE ON students 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_attendance_updated_at BEFORE UPDATE ON attendance 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_fines_updated_at BEFORE UPDATE ON fines 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_system_settings_updated_at BEFORE UPDATE ON system_settings 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Create functions for automatic logging (PostgreSQL style)
CREATE OR REPLACE FUNCTION log_student_insert()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, details) 
    VALUES (NEW.id::text, 'student', 'account_created', 'Student account created: ' || NEW.student_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION log_student_update()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, details) 
    VALUES (NEW.id::text, 'student', 'profile_updated', 'Student profile updated: ' || NEW.student_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION log_form_submission()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, details) 
    VALUES (NEW.student_id::text, 'student', 'form_submitted', 'Form submitted: ' || NEW.form_type);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers for logging
CREATE TRIGGER student_insert_log 
AFTER INSERT ON students 
FOR EACH ROW 
EXECUTE FUNCTION log_student_insert();

CREATE TRIGGER student_update_log 
AFTER UPDATE ON students 
FOR EACH ROW 
EXECUTE FUNCTION log_student_update();

CREATE TRIGGER form_submission_log 
AFTER INSERT ON form_submissions 
FOR EACH ROW 
EXECUTE FUNCTION log_form_submission();

-- Create views for common queries
CREATE OR REPLACE VIEW student_summary AS
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
GROUP BY s.id, s.student_id, s.full_name, s.email, s.phone, s.status, s.created_at;

CREATE OR REPLACE VIEW monthly_attendance_summary AS
SELECT 
    EXTRACT(YEAR FROM a.attendance_date) as year,
    EXTRACT(MONTH FROM a.attendance_date) as month,
    COUNT(DISTINCT a.student_id) as total_students,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as total_present,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as total_absent,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as total_late,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(*))::numeric, 2) as attendance_percentage
FROM attendance a
GROUP BY EXTRACT(YEAR FROM a.attendance_date), EXTRACT(MONTH FROM a.attendance_date)
ORDER BY year DESC, month DESC;

-- Optimize tables
VACUUM ANALYZE students;
VACUUM ANALYZE form_submissions;
VACUUM ANALYZE attendance;
VACUUM ANALYZE fines;
VACUUM ANALYZE activity_logs;