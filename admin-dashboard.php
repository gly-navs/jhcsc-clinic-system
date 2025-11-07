<?php
// ✅ Optional: Start a PHP session (for future use if needed)
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JHCSC Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="bg-shape-1"></div>
    <div class="bg-shape-2"></div>
    
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="jhlogo.png" alt="JHCSC Logo" />
            </div>
            <div class="header-text">
                <h1>JHCSC Pagadian Campus Clinic</h1>
                <h2>Clinic Staff Dashboard</h2>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <nav>
                <ul>
                    <li>
                        <a href="#" class="nav-link active" data-target="events">
                            <i class="fas fa-calendar-alt"></i>
                            Manage Events
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-target="reservations">
                            <i class="fas fa-list-check"></i>
                            View Reservations
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-target="notifications">
                            <i class="fas fa-bell"></i>
                            Send Notifications
                        </a>
                    </li>
                </ul>
            </nav>
            <button id="logout-btn" class="btn-action">
                <i class="fas fa-sign-out-alt"></i>
                LOGOUT
            </button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Events Section -->
            <div id="events" class="content-section active">
                <h2>Manage Events</h2>
                <button id="create-event-btn" class="btn-action btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    CREATE NEW EVENT
                </button>
                <!-- Events will be dynamically inserted here by admin-script.js -->
                <div class="events-list" id="events-list"></div>
            </div>

            <!-- Reservations Section -->
            <div id="reservations" class="content-section">
                <h2>View Reservations</h2>
                <div class="filters">
                    <select id="event-filter">
                        <option value="">All Events</option>
                    </select>
                    <input type="date" id="date-filter">
                </div>
                <div class="reservations-table-container">
                    <table class="reservations-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="reservations-body">
                            <!-- Filled by admin-script.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notifications Section -->
            <div id="notifications" class="content-section">
                <h2>Send Email Notifications</h2>
                <div class="notification-form">
                    <div class="input-group">
                        <label for="notification-type">Notification Type</label>
                        <select id="notification-type">
                            <option value="email">Email (Real Gmail)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="recipientSelect">Send To</label>
                        <select id="recipientSelect">
                            <option value="">Loading students...</option>
                        </select>
                        <small>Students without email addresses will not be shown</small>
                    </div>
                    <div class="input-group">
                        <label for="notification-event">Related Event (Optional)</label>
                        <select id="notification-event">
                            <option value="">Loading events...</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="notification-subject">Email Subject *</label>
                        <input type="text" id="notification-subject" placeholder="Enter email subject" value="Important Notification from JHCSC Clinic" required>
                    </div>
                    <div class="input-group">
                        <label for="notification-message">Email Message *</label>
                        <textarea id="notification-message" rows="6" placeholder="Type your email message here..." required></textarea>
                        <small>This message will be sent to the student's actual Gmail inbox</small>
                    </div>
                    <button class="btn-action btn-primary" onclick="sendNotification()">
                        <i class="fas fa-paper-plane"></i>
                        SEND EMAIL NOTIFICATION
                    </button>
                    
                    <div class="email-info">
                        <h4>📧 Email Information:</h4>
                        <p><strong>From:</strong> JHCSC Clinic (your-gmail@gmail.com)</p>
                        <p><strong>To:</strong> Selected student's registered email</p>
                        <p><strong>Delivery:</strong> Real Gmail delivery to inbox</p>
                    </div>
                </div>
            </div>

            <!-- Event Modal -->
            <div id="event-modal" class="modal">
                <div class="modal-content">
                    <span id="close-event-modal" class="close">&times;</span>
                    <h3 id="modal-title">Create New Event</h3>
                    <form id="event-form">
                        <div class="input-group">
                            <label for="event-title">Event Title</label>
                            <input type="text" id="event-title" required>
                        </div>
                        <div class="input-group">
                            <label for="event-date">Date</label>
                            <input type="date" id="event-date" required>
                        </div>
                        <div class="input-group">
                            <label for="event-desc">Description</label>
                            <textarea id="event-desc" rows="4" required></textarea>
                        </div>
                        <div class="input-group">
                            <label for="event-capacity">Slots Capacity</label>
                            <input type="number" id="event-capacity" min="1" value="10" required>
                        </div>
                        <button type="submit" class="btn-action btn-primary">
                            <i class="fas fa-save"></i>
                            SAVE EVENT
                        </button>
                    </form>
                </div>
            </div>
            <!-- End Event Modal -->
        </div>
    </div>

    <!-- Admin Script -->
    <script src="admin-script.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const adminSession = sessionStorage.getItem("loggedInAdmin");
        if (!adminSession) {
          alert("Access denied! Admins only.");
          window.location.href = "index.php"; // redirect if not logged in as admin
        }
      });
    </script>
</body>
</html>