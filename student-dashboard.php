<?php
// You can optionally add PHP session or authentication logic here later if needed.
// For now, this keeps your original HTML structure unchanged.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - JHCSC Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="student-style.css">
</head>
<body>
    <div class="bg-shape-1"></div>
    <div class="bg-shape-2"></div>
    
    <!-- HEADER -->
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="jhlogo.png" alt="JHCSC Logo" />
            </div>
            <div class="header-text">
                <h1>JHCSC Pagadian Campus Clinic</h1>
                <h2>Student Dashboard</h2>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <nav>
                <ul>
                    <li>
                        <a href="#" class="nav-link" data-target="profile">
                            <span class="nav-text">Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link active" data-target="events">
                            <span class="nav-text">View Events</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-target="reservations">
                            <span class="nav-text">My Reservations</span>
                        </a>
                    </li>
                    <li class="nav-item-notifications">
                        <a href="#" class="nav-link" data-target="notifications">
                            <span class="nav-text">Notifications</span>
                            <span id="notification-badge" class="notification-badge">0</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <button id="logout-btn" class="btn-action">
                <i class="fas fa-sign-out-alt"></i>
                LOGOUT
            </button>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- PROFILE SECTION -->
            <div id="profile" class="content-section">
                <h2>My Profile</h2>
                <div id="profile-info">
                    <p><strong>Student ID:</strong> <span id="profile-id"></span></p>
                    <p><strong>Name:</strong> <span id="profile-name"></span></p>
                    <p><strong>Program:</strong> <span id="profile-program"></span></p>
                    <p><strong>Block:</strong> <span id="profile-block"></span></p>
                    <p><strong>Year Level:</strong> <span id="profile-year"></span></p>
                    <p><strong>Email:</strong> <span id="profile-email"></span></p>
                    <p><strong>Contact Number:</strong> <span id="profile-number"></span></p>
                    <!-- Health Information -->
                    <p><strong>Weight:</strong> <span id="profile-weight">Not set</span></p>
                    <p><strong>Height:</strong> <span id="profile-height">Not set</span></p>
                    <p><strong>Blood Type:</strong> <span id="profile-bloodtype">Not set</span></p>
                </div>
                <button id="edit-profile-btn">Edit Profile</button>
                <div id="profile-message" style="margin-top: 10px;"></div>
            </div>

            <!-- EVENTS SECTION -->
            <div id="events" class="content-section active">
                <h2>Available Events</h2>
                <div class="events-header">
                    <button id="view-all-events-btn" class="btn-view-all">
                        <i class="fas fa-calendar-alt"></i>
                        VIEW ALL EVENTS
                    </button>
                    <div class="events-count">
                        <span id="events-count">0 events available</span>
                    </div>
                </div>
                <div class="events-list"></div>
            </div>

            <!-- RESERVATIONS SECTION -->
            <div id="reservations" class="content-section">
                <h2>My Reservations</h2>
                <div class="reservations-list" id="reservationsList">
                    <p>No reservations yet.</p>
                </div>
            </div>

            <!-- NOTIFICATIONS SECTION -->
            <div id="notifications" class="content-section">
                <h2>Notifications</h2>
                <div class="notifications-list" id="notificationsList">
                    <p>No notifications yet.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- BOOKING MODAL -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeBooking">&times;</span>
            <h3>Confirm Booking</h3>
            <p>Are you sure you want to book this event?</p>
            <div class="modal-actions">
                <button id="confirmYes">Yes</button>
                <button id="confirmNo">No</button>
            </div>
        </div>
    </div>

    <!-- CANCEL MODAL -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCancel">&times;</span>
            <h3>Cancel Reservation</h3>
            <p>Are you sure you want to cancel this reservation?</p>
            <div class="modal-actions">
                <button id="cancelYes">Yes</button>
                <button id="cancelNo">No</button>
            </div>
        </div>
    </div>

    <!-- ALL EVENTS MODAL -->
    <div id="allEventsModal" class="modal">
        <div class="modal-content large-modal">
            <span class="close" id="closeAllEvents">&times;</span>
            <h3>All Events</h3>
            <div class="modal-filters">
                <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                <select id="event-sort">
                    <option value="date-desc">Date: Newest First</option>
                    <option value="date-asc">Date: Oldest First</option>
                    <option value="title-asc">Title: A-Z</option>
                    <option value="title-desc">Title: Z-A</option>
                </select>
            </div>
            <div class="all-events-container" id="all-events-container">
                <!-- All events will be loaded here -->
            </div>
            <div class="modal-actions">
                <button id="close-all-events-btn" class="btn-action">Close</button>
            </div>
        </div>
    </div>

    <!-- LOGOUT MODAL -->
    <div id="logout-modal" class="logout-modal">
        <div class="logout-modal-content">
            <h3>Are you sure you want to log out?</h3>
            <div class="logout-actions">
                <button id="confirm-logout" class="btn-yes">Yes</button>
                <button id="cancel-logout" class="btn-no">No</button>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="student-script.js"></script>
</body>
</html>