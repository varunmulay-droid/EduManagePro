/**
 * Student Management System - Main JavaScript
 */

// Global Variables
let currentUser = null;
let notifications = [];

// Document Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    initializeFormValidation();
    initializeFileUploads();
    initializeNotifications();
});

/**
 * Initialize Application
 */
function initializeApp() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Initialize dark mode toggle if available
    initializeDarkMode();
}

/**
 * Form Validation
 */
function initializeFormValidation() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Custom validation for specific forms
    initializeCaseRecordValidation();
    initializeHostelFormValidation();
    initializeBonafideValidation();
    initializeAdmissionValidation();
}

/**
 * Case Record Form Validation
 */
function initializeCaseRecordValidation() {
    const form = document.getElementById('caseRecordForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const weight = parseFloat(document.getElementById('weight').value);
        const height = parseFloat(document.getElementById('height').value);
        
        if (weight && height) {
            const bmi = weight / ((height / 100) ** 2);
            if (bmi < 10 || bmi > 50) {
                e.preventDefault();
                showAlert('Please check weight and height values. BMI seems unusual.', 'warning');
            }
        }
    });
}

/**
 * Hostel Form Validation
 */
function initializeHostelFormValidation() {
    const form = document.getElementById('hostelForm');
    if (!form) return;

    // Age validation
    const dobInput = form.querySelector('input[name="dob"]');
    const ageYearsInput = form.querySelector('input[name="age_years"]');
    
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
            
            if (ageYearsInput) {
                ageYearsInput.value = age;
            }
        });
    }
}

/**
 * Bonafide Certificate Validation
 */
function initializeBonafideValidation() {
    const form = document.getElementById('bonafideForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const academicYear = form.querySelector('input[name="academic_year"]').value;
        const currentYear = new Date().getFullYear();
        
        if (academicYear) {
            const yearMatch = academicYear.match(/(\d{4})/);
            if (yearMatch) {
                const year = parseInt(yearMatch[1]);
                if (year < currentYear - 5 || year > currentYear + 1) {
                    e.preventDefault();
                    showAlert('Please check the academic year.', 'warning');
                }
            }
        }
    });
}

/**
 * Admission Form Validation
 */
function initializeAdmissionValidation() {
    const form = document.getElementById('admissionForm');
    if (!form) return;

    // Aadhar number validation
    const aadharInput = form.querySelector('input[name="aadhar_number"]');
    if (aadharInput) {
        aadharInput.addEventListener('input', function() {
            const value = this.value.replace(/\D/g, '');
            this.value = value.substring(0, 12);
            
            if (value.length === 12) {
                if (!validateAadhar(value)) {
                    this.setCustomValidity('Invalid Aadhar number');
                } else {
                    this.setCustomValidity('');
                }
            }
        });
    }

    // Mobile number validation
    const mobileInput = form.querySelector('input[name="mobile_number"]');
    if (mobileInput) {
        mobileInput.addEventListener('input', function() {
            const value = this.value.replace(/\D/g, '');
            this.value = value.substring(0, 10);
            
            if (value.length === 10 && !value.startsWith('0')) {
                this.setCustomValidity('');
            } else if (value.length === 10) {
                this.setCustomValidity('Mobile number should not start with 0');
            }
        });
    }
}

/**
 * File Upload Handlers
 */
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                validateFileUpload(file, input);
            }
        });
    });
}

/**
 * Validate File Upload
 */
function validateFileUpload(file, input) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    
    if (file.size > maxSize) {
        showAlert('File size should not exceed 5MB', 'danger');
        input.value = '';
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('Only JPG, PNG, and PDF files are allowed', 'danger');
        input.value = '';
        return false;
    }
    
    return true;
}

/**
 * AJAX File Upload
 */
function uploadFile(file, type = 'document') {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        
        fetch('../api/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(new Error(data.message));
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

/**
 * Notification System
 */
function initializeNotifications() {
    // Check for session messages and display them
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('success')) {
        showAlert(decodeURIComponent(urlParams.get('success')), 'success');
    }
    
    if (urlParams.get('error')) {
        showAlert(decodeURIComponent(urlParams.get('error')), 'danger');
    }
}

/**
 * Show Alert
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type} alert-dismissible fade show`;
    alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertElement);
    
    // Auto dismiss
    if (duration > 0) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertElement);
            bsAlert.close();
        }, duration);
    }
}

/**
 * Create Alert Container
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * Utility Functions
 */

// Validate Aadhar Number (basic validation)
function validateAadhar(aadhar) {
    if (aadhar.length !== 12) return false;
    
    // Basic Luhn algorithm check (simplified)
    let sum = 0;
    for (let i = 0; i < 11; i++) {
        sum += parseInt(aadhar[i]);
    }
    
    return (sum % 10) === parseInt(aadhar[11]);
}

// Format Indian Phone Number
function formatPhoneNumber(phone) {
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.length === 10) {
        return cleaned.replace(/(\d{5})(\d{5})/, '$1-$2');
    }
    return phone;
}

// Format Date to Indian Format
function formatDateIndian(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN');
}

// Calculate Age from Date of Birth
function calculateAge(dob) {
    const today = new Date();
    const birthDate = new Date(dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Debounce Function
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Dark Mode Toggle
function initializeDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (!darkModeToggle) return;
    
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
    }
    
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark);
    });
}

// Print Function
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link rel="stylesheet" href="../assets/css/style.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Export Functions for Global Use
window.showAlert = showAlert;
window.uploadFile = uploadFile;
window.printElement = printElement;
window.validateAadhar = validateAadhar;
window.formatPhoneNumber = formatPhoneNumber;
window.formatDateIndian = formatDateIndian;
window.calculateAge = calculateAge;
