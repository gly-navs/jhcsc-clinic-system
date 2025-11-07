

<?php
include 'server.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['login_id'];
    $password = $_POST['login_password'];

    // Check if admin
    $admin_sql = "SELECT * FROM admin_accounts WHERE admin_id='$id' AND password='$password'";
    $admin_result = $conn->query($admin_sql);

    if ($admin_result->num_rows > 0) {
        $_SESSION['role'] = "admin";
        echo "<script>alert('Welcome Clinic Staff!'); window.location.href='admin-dashboard.php';</script>";
        exit;
    }

    // Check if student
    $student_sql = "SELECT * FROM students WHERE id='$id' AND password='$password'";
    $student_result = $conn->query($student_sql);

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $_SESSION['id'] = $student['id'];
        $_SESSION['name'] = $student['name'];
        $_SESSION['role'] = "student";
        echo "<script>alert('Welcome, " . $student['name'] . "!'); window.location.href='student-dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Invalid ID or Password!'); window.location.href='index.php';</script>";
        exit;
    }
}
?>

this is script.js

// Email validation function
function validateEmail(email) {
    return email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);
}

// ✅ Password Toggle with Smooth Fade Animation
function togglePassword(inputId, iconSpan) {
  const input = document.getElementById(inputId);
  const icon = iconSpan.querySelector('i');

  // Fade out
  icon.style.opacity = '0';

  setTimeout(() => {
    // Toggle input type
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    }

    // Fade back in
    icon.style.opacity = '1';
  }, 150);
}

// Loading screen functionality
window.addEventListener('load', function() {
    const loadingScreen = document.getElementById('loadingScreen');
    
    // Simulate loading process
    setTimeout(() => {
        loadingScreen.classList.add('fade-out');
        
        // Remove loading screen from DOM after fade out
        setTimeout(() => {
            loadingScreen.remove();
        }, 500);
    }, 2500); // Adjust this time as needed (2500ms = 2.5 seconds)
});

// Fallback in case page takes longer to load
setTimeout(() => {
    const loadingScreen = document.getElementById('loadingScreen');
    if (loadingScreen && !loadingScreen.classList.contains('fade-out')) {
        loadingScreen.classList.add('fade-out');
        setTimeout(() => {
            loadingScreen.remove();
        }, 500);
    }
}, 5000); // Maximum 5 seconds loading time

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const loginToggle = document.getElementById('login-toggle');
    const signupToggle = document.getElementById('signup-toggle');
    const brandSection = document.querySelector('.brand-section');
    const formSection = document.querySelector('.form-section');
    const container = document.querySelector('.container');

    // Form Toggle Functionality
    loginToggle.addEventListener('click', function() {
        switchForm('login');
    });

    signupToggle.addEventListener('click', function() {
        switchForm('signup');
    });

    function switchForm(formType) {
        if (formType === 'login') {
            // Add swapping class for animation
            container.classList.remove('swapped');
            
            // Show/hide forms
            loginForm.classList.add('active');
            signupForm.classList.remove('active');
            loginToggle.classList.add('active');
            signupToggle.classList.remove('active');
        } else {
            // Add swapping class for animation
            container.classList.add('swapped');
            
            // Show/hide forms
            signupForm.classList.add('active');
            loginForm.classList.remove('active');
            signupToggle.classList.add('active');
            loginToggle.classList.remove('active');
        }
    }

    // ✅ Sign Up Form Handler - FIXED DATABASE CONNECTION
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('signup-name').value.trim();
        const studentId = document.getElementById('signup-id').value.trim();
        const program = document.getElementById('signup-program').value.trim();
        const block = document.getElementById('signup-block').value.trim();
        const year = document.getElementById('signup-year').value.trim();
        const email = document.getElementById('signup-email').value.trim();
        const phone = document.getElementById('signup-phone').value.trim();
        const password = document.getElementById('signup-password').value;
        const confirmPassword = document.getElementById('signup-confirm-password').value;

        // Prevent using staff ID
        if (studentId === "12345") {
            alert('This ID is reserved for staff. Please use a different Student ID.');
            return;
        }

        // Validation
        if (!name || !studentId || !program || !block || !year || !email || !phone || !password) {
            alert('Please fill in all fields.');
            return;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            return;
        }

        if (!validateEmail(email)) {
            alert('Please enter a valid email address.');
            return;
        }

        // Submit to server - FIXED: Using proper database connection
        const formData = new FormData();
        formData.append('action', 'signup');
        formData.append('student_id', studentId);
        formData.append('name', name);
        formData.append('program', program);
        formData.append('block', block);
        formData.append('year', year);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('password', password);

        fetch('server.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Save user data to sessionStorage
                sessionStorage.setItem('currentStudent', JSON.stringify(data.user));
                alert('Sign up successful! You are now logged in.');
                window.location.href = 'student-dashboard.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during signup. Please try again.');
        });
    });

    // Login Form Handler - FIXED DATABASE CONNECTION
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const studentId = document.getElementById('login-id').value.trim();
        const password = document.getElementById('login-password').value.trim();

        if (!studentId || !password) {
            alert('Please enter your ID and Password.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('student_id', studentId);
        formData.append('password', password);

        fetch('server.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.role === 'admin') {
                    sessionStorage.setItem('loggedInAdmin', JSON.stringify(data.user));
                    alert('Welcome, Clinic Staff!');
                    window.location.href = 'admin-dashboard.php';
                } else {
                    sessionStorage.setItem('currentStudent', JSON.stringify(data.user));
                    alert('Login successful! Welcome, ' + data.user.name);
                    window.location.href = 'student-dashboard.php';
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during login. Please try again.');
        });
    });
});
