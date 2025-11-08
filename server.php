<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// ==========================
// EMAIL CONFIGURATION - FIXED!
// ==========================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'bationivan8@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'jzgk pqcq lyvt fczt'); // Your App Password
define('SMTP_FROM_EMAIL', 'bationivan8@gmail.com'); // Your Gmail address
define('SMTP_FROM_NAME', 'JHCSC Clinic');

// Function to send email using PHPMailer
function sendEmail($toEmail, $toName, $subject, $message) {
    require 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = 0; // Set to 2 for debugging, 0 for production

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // HTML Email Template
        // ... existing code ...

// HTML Email Template with Green Theme// ... existing code ...

// HTML Email Template with #2bb639 Green Theme
// ... rest of the PHP code ...


// ... rest of the PHP code ...
        
        $mail->Body = $htmlMessage;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
    }
}

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $student_id = $_POST['student_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check admin first
    $admin_sql = "SELECT * FROM admin_accounts WHERE admin_id = ?";
    $admin_stmt = $conn->prepare($admin_sql);
    $admin_stmt->bind_param("s", $student_id);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();

    if ($admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();
        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['role'] = 'admin';
            
            echo json_encode([
                'success' => true,
                'role' => 'admin',
                'user' => [
                    'id' => $admin['admin_id'],
                    'name' => $admin['name'],
                    'role' => 'admin'
                ]
            ]);
            exit;
        }
    }

    // Check student
    $student_sql = "SELECT * FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("s", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        if ($password === $student['password']) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['role'] = 'student';
            
            echo json_encode([
                'success' => true,
                'role' => 'student',
                'user' => [
                    'id' => $student['id'],
                    'name' => $student['name'],
                    'program' => $student['program'],
                    'block' => $student['block'],
                    'year' => $student['year'],
                    'email' => $student['email'],
                    'phone' => $student['phone']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
    }
    exit;
}

if ($action === 'signup') {
    $id = $_POST['student_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $program = $_POST['program'] ?? '';
    $block = $_POST['block'] ?? '';
    $year = $_POST['year'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if ID or email already exists
    $check_sql = "SELECT * FROM students WHERE id = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $id, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Student ID or Email already exists!']);
        exit;
    }

    // Insert new student
    $insert_sql = "INSERT INTO students (id, name, program, block, year, email, phone, password) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssssss", $id, $name, $program, $block, $year, $email, $phone, $password);

    if ($insert_stmt->execute()) {
        $_SESSION['student_id'] = $id;
        $_SESSION['student_name'] = $name;
        $_SESSION['role'] = 'student';
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!',
            'user' => [
                'id' => $id,
                'name' => $name,
                'program' => $program,
                'block' => $block,
                'year' => $year,
                'email' => $email,
                'phone' => $phone
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// Get all students for notifications
if ($action === 'get_students') {
    $sql = "SELECT id, name, email FROM students ORDER BY name ASC";
    $result = $conn->query($sql);
    $students = [];
    
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email']
        ];
    }
    
    echo json_encode(['success' => true, 'students' => $students]);
    exit;
}

// Events management
if ($action === 'get_events') {
    $sql = "SELECT * FROM events ORDER BY event_date DESC";
    $result = $conn->query($sql);
    $events = [];
    
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['event_id'],
            'title' => $row['event_name'],
            'date' => $row['event_date'],
            'capacity' => $row['slots_total'],
            'reserved' => $row['slots_total'] - $row['slots_available'],
            'description' => $row['event_description'] ?? ''
        ];
    }
    
    echo json_encode(['success' => true, 'events' => $events]);
    exit;
}

if ($action === 'save_event') {
    $event_id = $_POST['event_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $date = $_POST['date'] ?? '';
    $description = $_POST['description'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;

    if ($event_id) {
        // Update existing event
        $sql = "UPDATE events SET event_name = ?, event_date = ?, slots_total = ?, event_description = ? WHERE event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $title, $date, $capacity, $description, $event_id);
    } else {
        // Create new event
        $sql = "INSERT INTO events (event_name, event_date, slots_total, slots_available, event_description) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiss", $title, $date, $capacity, $capacity, $description);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event saved successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving event: ' . $conn->error]);
    }
    exit;
}

if ($action === 'delete_event') {
    $event_id = $_POST['event_id'] ?? '';
    
    $sql = "DELETE FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $conn->error]);
    }
    exit;
}

// Reservations management
if ($action === 'get_reservations') {
    $sql = "SELECT r.*, s.name as student_name, s.email as student_email, e.event_name, e.event_date 
            FROM reservations r 
            JOIN students s ON r.student_id = s.id 
            JOIN events e ON r.event_id = e.event_id 
            ORDER BY r.reserved_at DESC";
    $result = $conn->query($sql);
    $reservations = [];
    
    while ($row = $result->fetch_assoc()) {
        $reservations[] = [
            'id' => $row['reservation_id'],
            'studentId' => $row['student_id'],
            'studentName' => $row['student_name'],
            'studentEmail' => $row['student_email'],
            'eventId' => $row['event_id'],
            'eventName' => $row['event_name'],
            'eventDate' => $row['event_date'],
            'status' => $row['status'],
            'createdAt' => $row['reserved_at']
        ];
    }
    
    echo json_encode(['success' => true, 'reservations' => $reservations]);
    exit;
}

if ($action === 'make_reservation') {
    $student_id = $_SESSION['student_id'] ?? '';
    $event_id = $_POST['event_id'] ?? '';
    
    if (!$student_id) {
        echo json_encode(['success' => false, 'message' => 'Please login first.']);
        exit;
    }

    // Check if already reserved
    $check_sql = "SELECT * FROM reservations WHERE student_id = ? AND event_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $student_id, $event_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You already booked this event.']);
        exit;
    }

    // Check available slots
    $event_sql = "SELECT slots_available FROM events WHERE event_id = ?";
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    $event = $event_result->fetch_assoc();

    if ($event['slots_available'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'No slots available.']);
        exit;
    }

    // Get event details
    $event_info_sql = "SELECT event_name, event_date FROM events WHERE event_id = ?";
    $event_info_stmt = $conn->prepare($event_info_sql);
    $event_info_stmt->bind_param("i", $event_id);
    $event_info_stmt->execute();
    $event_info_result = $event_info_stmt->get_result();
    $event_info = $event_info_result->fetch_assoc();

    // Get student name and email
    $student_sql = "SELECT name, email FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("s", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $student = $student_result->fetch_assoc();

    // Create reservation
    $insert_sql = "INSERT INTO reservations (student_id, student_name, event_id, event_name, event_date, status) 
                   VALUES (?, ?, ?, ?, ?, 'Pending')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssiss", $student_id, $student['name'], $event_id, $event_info['event_name'], $event_info['event_date']);

    if ($insert_stmt->execute()) {
        // Update available slots
        $update_sql = "UPDATE events SET slots_available = slots_available - 1 WHERE event_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $event_id);
        $update_stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Booking submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error making reservation: ' . $conn->error]);
    }
    exit;
}

if ($action === 'update_reservation_status') {
    $reservation_id = $_POST['reservation_id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    // Get reservation details including student email
    $get_sql = "SELECT r.*, s.email as student_email, s.name as student_name 
                FROM reservations r 
                JOIN students s ON r.student_id = s.id 
                WHERE r.reservation_id = ?";
    $get_stmt = $conn->prepare($get_sql);
    $get_stmt->bind_param("i", $reservation_id);
    $get_stmt->execute();
    $get_result = $get_stmt->get_result();
    $reservation = $get_result->fetch_assoc();

    if (!$reservation) {
        echo json_encode(['success' => false, 'message' => 'Reservation not found.']);
        exit;
    }

    // Update reservation status
    $sql = "UPDATE reservations SET status = ? WHERE reservation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $reservation_id);
    
    if ($stmt->execute()) {
        // Send email notification to student
        $emailSubject = "Reservation Update - JHCSC Clinic";
        $emailMessage = "Dear {$reservation['student_name']},\n\nYour reservation status has been updated:\n\nEvent: {$reservation['event_name']}\nDate: {$reservation['event_date']}\nNew Status: {$status}\n\nThank you for using JHCSC Clinic services.";
        
        $emailResult = sendEmail($reservation['student_email'], $reservation['student_name'], $emailSubject, $emailMessage);

        echo json_encode([
            'success' => true, 
            'message' => 'Reservation updated successfully! ' . ($emailResult['success'] ? 'Email notification sent.' : 'But email failed: ' . $emailResult['message'])
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating reservation: ' . $conn->error]);
    }
    exit;
}

if ($action === 'cancel_reservation') {
    $reservation_id = $_POST['reservation_id'] ?? '';
    $student_id = $_SESSION['student_id'] ?? '';
    
    // Get event_id and student email before deleting
    $get_sql = "SELECT r.*, s.email as student_email, s.name as student_name 
                FROM reservations r 
                JOIN students s ON r.student_id = s.id 
                WHERE r.reservation_id = ? AND r.student_id = ?";
    $get_stmt = $conn->prepare($get_sql);
    $get_stmt->bind_param("is", $reservation_id, $student_id);
    $get_stmt->execute();
    $get_result = $get_stmt->get_result();
    $reservation = $get_result->fetch_assoc();

    if (!$reservation) {
        echo json_encode(['success' => false, 'message' => 'Reservation not found.']);
        exit;
    }

    // Delete reservation
    $delete_sql = "DELETE FROM reservations WHERE reservation_id = ? AND student_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("is", $reservation_id, $student_id);

    if ($delete_stmt->execute()) {
        // Restore available slot
        $update_sql = "UPDATE events SET slots_available = slots_available + 1 WHERE event_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $reservation['event_id']);
        $update_stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error cancelling reservation: ' . $conn->error]);
    }
    exit;
}

// ==========================
// ADMIN SEND NOTIFICATION EMAIL - FIXED!
// ==========================
if ($action === 'send_notification_email') {
    $to_type = $_POST['to_type'] ?? ''; // 'all' or 'specific'
    $student_id = $_POST['student_id'] ?? '';
    $subject = $_POST['subject'] ?? 'Notification from JHCSC Clinic';
    $message = $_POST['message'] ?? '';

    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
        exit;
    }

    $emailsSent = 0;
    $errors = [];

    if ($to_type === 'all') {
        // Send to all students
        $sql = "SELECT email, name FROM students WHERE email IS NOT NULL AND email != ''";
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No students with email addresses found.']);
            exit;
        }
        
        while ($student = $result->fetch_assoc()) {
            $emailResult = sendEmail($student['email'], $student['name'], $subject, $message);
            if ($emailResult['success']) {
                $emailsSent++;
            } else {
                $errors[] = "Failed to send to {$student['email']}: {$emailResult['message']}";
            }
            
            // Add small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }
    } else {
        // Send to specific student
        $sql = "SELECT email, name FROM students WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();

        if ($student) {
            $emailResult = sendEmail($student['email'], $student['name'], $subject, $message);
            if ($emailResult['success']) {
                $emailsSent++;
            } else {
                $errors[] = $emailResult['message'];
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found.']);
            exit;
        }
    }

    if ($emailsSent > 0) {
        $response = [
            'success' => true, 
            'message' => "Email notification sent to {$emailsSent} student(s) successfully!",
            'emails_sent' => $emailsSent
        ];
        if (!empty($errors)) {
            $response['warnings'] = $errors;
        }
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send any emails. Errors: ' . implode(', ', $errors)]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>