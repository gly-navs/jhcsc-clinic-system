// Student Dashboard Script (MySQL Database Version)

document.addEventListener("DOMContentLoaded", function () {
  // ==========================
  // Notification Badge Management (Local Storage)
  // ==========================
  function updateNotificationBadge() {
    const notifications = getNotifications();
    const currentStudent = JSON.parse(sessionStorage.getItem("currentStudent"));

    if (!currentStudent) return;

    const relevantNotifications = notifications.filter(n =>
      (n.to === "all" || n.to === currentStudent.id) && !n.read
    );

    const badge = document.getElementById("notification-badge");
    const count = relevantNotifications.length;

    if (badge) {
      if (count > 0) {
        badge.textContent = count > 99 ? "99+" : count;
        badge.setAttribute("data-count", count);
        badge.classList.add("show");
      } else {
        badge.classList.remove("show");
        badge.textContent = "0";
      }
    }

    return count;
  }

  function markAllNotificationsAsRead() {
    const notifications = getNotifications();
    const currentStudent = JSON.parse(sessionStorage.getItem("currentStudent"));

    if (!currentStudent) return;

    const updatedNotifications = notifications.map(n => {
      if ((n.to === "all" || n.to === currentStudent.id) && !n.read) {
        return { ...n, read: true, readAt: new Date().toISOString() };
      }
      return n;
    });

    setNotifications(updatedNotifications);
  }

  // ==========================
  // Local Storage Notifications (Keep for demo)
  // ==========================
  function getNotifications() {
    return JSON.parse(localStorage.getItem("notifications")) || [];
  }

  function setNotifications(arr) {
    localStorage.setItem("notifications", JSON.stringify(arr));
    updateNotificationBadge();
  }

  // Function to delete a specific notification
  function deleteNotification(notificationId) {
    const notes = getNotifications();
    const updatedNotes = notes.filter(note => note.id !== notificationId);
    setNotifications(updatedNotes);
    renderNotifications(); // Re-render the notifications list
    alert("✅ Notification deleted successfully!");
  }

  // ==========================
  // API Functions
  // ==========================
  async function apiCall(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    for (const key in data) {
      formData.append(key, data[key]);
    }

    try {
      const response = await fetch('server.php', {
        method: 'POST',
        body: formData
      });
      return await response.json();
    } catch (error) {
      console.error('API Error:', error);
      return { success: false, message: 'Network error occurred' };
    }
  }

  // ==========================
  // Navigation
  // ==========================
  const navLinks = document.querySelectorAll(".nav-link");
  const contentSections = document.querySelectorAll(".content-section");

  navLinks.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      navLinks.forEach(l => l.classList.remove("active"));
      contentSections.forEach(s => s.classList.remove("active"));

      this.classList.add("active");
      const target = this.getAttribute("data-target");
      document.getElementById(target).classList.add("active");

      if (target === "events") {
        renderStudentEvents();
      }
      if (target === "reservations") {
        renderMyReservations();
      }
      if (target === "profile") {
        loadProfile();
      }
      if (target === "notifications") {
        renderNotifications();
        markAllNotificationsAsRead();
      }
    });
  });

  // ==========================
  // Ensure student is logged in
  // ==========================
  const currentStudent = JSON.parse(sessionStorage.getItem("currentStudent"));
  if (!currentStudent) {
    window.location.href = "index.php";
    return;
  }

  // ==========================
  // Enhanced Render Notifications with Delete Button
  // ==========================
  function renderNotifications() {
    const container = document.getElementById("notificationsList");
    if (!container) return;

    const notes = getNotifications();
    const relevant = notes.filter(n => n.to === "all" || n.to === currentStudent.id);

    container.innerHTML = "";
    if (relevant.length === 0) {
      container.innerHTML = "<p>No notifications yet.</p>";
      return;
    }

    relevant.sort((a, b) => {
      const aRead = a.read ? 1 : 0;
      const bRead = b.read ? 1 : 0;
      if (aRead !== bRead) return aRead - bRead;
      return new Date(b.date) - new Date(a.date);
    });

    relevant.forEach(n => {
      const card = document.createElement("div");
      card.classList.add("notification-card");

      if (!n.read) {
        card.classList.add("unread");
      }

      if (n.title && n.title.toLowerCase().includes("cancel")) {
        card.classList.add("cancelled");
      }

      card.innerHTML = `
        <div class="notification-header">
          <h4>${n.title || "Notification"}</h4>
          <small>${new Date(n.date).toLocaleString()}</small>
        </div>
        <p>${n.message}</p>
        <div class="notification-actions">
          <button class="btn-delete-notification" data-id="${n.id}">
            <i class="fas fa-trash"></i> Delete
          </button>
        </div>
      `;

      // Mark as read when clicked
      card.addEventListener("click", (e) => {
        // Don't mark as read if delete button is clicked
        if (!e.target.closest('.btn-delete-notification') && !n.read) {
          const updatedNotes = notes.map(note =>
            note.id === n.id ? { ...note, read: true, readAt: new Date().toISOString() } : note
          );
          setNotifications(updatedNotes);
        }
      });

      // Add delete functionality
      const deleteBtn = card.querySelector('.btn-delete-notification');
      deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent marking as read when deleting
        if (confirm("Are you sure you want to delete this notification?")) {
          deleteNotification(n.id);
        }
      });

      container.appendChild(card);
    });
  }

  // ==========================
  // Events Management
  // ==========================
  async function renderStudentEvents() {
    const container = document.querySelector(".events-list");
    if (!container) return;

    const result = await apiCall('get_events');
    const reservationsResult = await apiCall('get_reservations');

    container.innerHTML = "";

    if (!result.success || result.events.length === 0) {
      container.innerHTML = "<p>No events available at the moment.</p>";
      document.getElementById("events-count").textContent = "0 events available";
      return;
    }

    // Update events count
    document.getElementById("events-count").textContent = `${result.events.length} events available`;

    const myReservations = reservationsResult.success ? 
      reservationsResult.reservations.filter(b => b.studentId === currentStudent.id) : [];

    // Show only 3 events in main view
    const eventsToShow = result.events.slice(0, 3);

    eventsToShow.forEach(ev => {
      const alreadyBooked = myReservations.some(b => b.eventId == ev.id);

      const card = document.createElement("div");
      card.classList.add("event-card");
      card.setAttribute("data-event", ev.id);
      card.innerHTML = `
        <h3>${ev.title}</h3>
        <p><strong>Date:</strong> ${ev.date}</p>
        <p><strong>Description:</strong> ${ev.description || 'No description'}</p>
        <p><strong>Slots:</strong> ${ev.reserved || 0}/${ev.capacity}</p>
        <div class="event-actions-student"></div>
      `;
      const actions = card.querySelector(".event-actions-student");

      if (alreadyBooked) {
        actions.innerHTML = `<button class="btn-book" disabled>Already Booked</button>`;
      } else if ((ev.reserved || 0) >= ev.capacity) {
        actions.innerHTML = `<button class="btn-book" disabled>No slots</button>`;
      } else {
        const b = document.createElement("button");
        b.classList.add("btn-book");
        b.textContent = "Book";
        b.addEventListener("click", () => openBookingModal(ev.id));
        actions.appendChild(b);
      }

      container.appendChild(card);
    });

    // Show "View More" message if there are more events
    if (result.events.length > 3) {
      const viewMoreCard = document.createElement("div");
      viewMoreCard.classList.add("event-card", "view-more-card");
      viewMoreCard.innerHTML = `
        <h3>More Events Available</h3>
        <p>There are ${result.events.length - 3} more events to view</p>
        <p>Click "VIEW ALL EVENTS" button to see all available events</p>
      `;
      container.appendChild(viewMoreCard);
    }
  }

  // ==========================
  // View All Events Modal
  // ==========================
  const allEventsModal = document.getElementById("allEventsModal");
  const closeAllEvents = document.getElementById("closeAllEvents");
  const closeAllEventsBtn = document.getElementById("close-all-events-btn");
  const viewAllEventsBtn = document.getElementById("view-all-events-btn");
  const eventSearch = document.getElementById("event-search");
  const eventSort = document.getElementById("event-sort");

  function openAllEventsModal() {
    if (allEventsModal) {
      allEventsModal.style.display = "flex";
      loadAllEvents();
    }
  }

  function closeAllEventsModal() {
    if (allEventsModal) {
      allEventsModal.style.display = "none";
    }
  }

  async function loadAllEvents() {
    const container = document.getElementById("all-events-container");
    if (!container) return;

    const result = await apiCall('get_events');
    const reservationsResult = await apiCall('get_reservations');

    if (!result.success || result.events.length === 0) {
      container.innerHTML = "<p>No events available at the moment.</p>";
      return;
    }

    const myReservations = reservationsResult.success ? 
      reservationsResult.reservations.filter(b => b.studentId === currentStudent.id) : [];

    let events = result.events;

    // Apply search filter
    const searchTerm = eventSearch.value.toLowerCase();
    if (searchTerm) {
      events = events.filter(ev => 
        ev.title.toLowerCase().includes(searchTerm) || 
        (ev.description && ev.description.toLowerCase().includes(searchTerm))
      );
    }

    // Apply sorting
    const sortValue = eventSort.value;
    events.sort((a, b) => {
      switch (sortValue) {
        case 'date-asc':
          return new Date(a.date) - new Date(b.date);
        case 'date-desc':
          return new Date(b.date) - new Date(a.date);
        case 'title-asc':
          return a.title.localeCompare(b.title);
        case 'title-desc':
          return b.title.localeCompare(a.title);
        default:
          return new Date(b.date) - new Date(a.date);
      }
    });

    container.innerHTML = "";

    if (events.length === 0) {
      container.innerHTML = "<p>No events match your search criteria.</p>";
      return;
    }

    events.forEach(ev => {
      const alreadyBooked = myReservations.some(b => b.eventId == ev.id);
      const eventDate = new Date(ev.date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const isPastEvent = eventDate < today;

      const card = document.createElement("div");
      card.classList.add("event-card", "all-event-card");
      if (isPastEvent) {
        card.classList.add("past-event");
      }
      
      card.setAttribute("data-event", ev.id);
      card.innerHTML = `
        <div class="event-header">
          <h3>${ev.title}</h3>
          ${isPastEvent ? '<span class="past-badge">Past Event</span>' : ''}
        </div>
        <p><strong>Date:</strong> ${ev.date}</p>
        <p><strong>Description:</strong> ${ev.description || 'No description'}</p>
        <p><strong>Available Slots:</strong> ${Math.max(0, ev.capacity - (ev.reserved || 0))}/${ev.capacity}</p>
        <div class="event-actions-student"></div>
      `;
      const actions = card.querySelector(".event-actions-student");

      if (isPastEvent) {
        actions.innerHTML = `<button class="btn-book" disabled>Event Ended</button>`;
      } else if (alreadyBooked) {
        actions.innerHTML = `<button class="btn-book" disabled>Already Booked</button>`;
      } else if ((ev.reserved || 0) >= ev.capacity) {
        actions.innerHTML = `<button class="btn-book" disabled>Fully Booked</button>`;
      } else {
        const b = document.createElement("button");
        b.classList.add("btn-book");
        b.textContent = "Book This Event";
        b.addEventListener("click", () => {
          closeAllEventsModal();
          openBookingModal(ev.id);
        });
        actions.appendChild(b);
      }

      container.appendChild(card);
    });
  }

  // Event listeners for All Events Modal
  if (viewAllEventsBtn) {
    viewAllEventsBtn.addEventListener("click", openAllEventsModal);
  }

  if (closeAllEvents) {
    closeAllEvents.addEventListener("click", closeAllEventsModal);
  }

  if (closeAllEventsBtn) {
    closeAllEventsBtn.addEventListener("click", closeAllEventsModal);
  }

  if (eventSearch) {
    eventSearch.addEventListener("input", loadAllEvents);
  }

  if (eventSort) {
    eventSort.addEventListener("change", loadAllEvents);
  }

  // ==========================
  // Booking Modal
  // ==========================
  const bookingModal = document.getElementById("bookingModal");
  const closeBooking = document.getElementById("closeBooking");
  const confirmYes = document.getElementById("confirmYes");
  const confirmNo = document.getElementById("confirmNo");
  let selectedEventId = null;

  function openBookingModal(eventId) {
    selectedEventId = eventId;
    if (bookingModal) bookingModal.style.display = "flex";
  }

  closeBooking?.addEventListener("click", () => bookingModal.style.display = "none");
  confirmNo?.addEventListener("click", () => bookingModal.style.display = "none");

  confirmYes?.addEventListener("click", async () => {
    if (!selectedEventId) return;

    const result = await apiCall('make_reservation', { event_id: selectedEventId });

    if (result.success) {
      // Create local notification
      const notifications = getNotifications();
      notifications.push({
        id: Date.now().toString() + Math.random().toString(36).slice(2,6),
        to: currentStudent.id,
        title: "Booking Submitted",
        message: `Your booking has been submitted. Status: Pending.`,
        date: new Date().toISOString(),
        read: false,
        eventId: selectedEventId
      });
      setNotifications(notifications);

      bookingModal.style.display = "none";
      selectedEventId = null;

      renderStudentEvents();
      renderMyReservations();
      alert("✅ " + result.message);
    } else {
      alert("❌ " + result.message);
    }
  });

  // ==========================
  // Reservations Management
  // ==========================
  async function renderMyReservations() {
    const container = document.getElementById("reservationsList");
    if (!container) return;

    const result = await apiCall('get_reservations');
    
    container.innerHTML = "";
    
    if (!result.success || result.reservations.length === 0) {
      container.innerHTML = "<p>No reservations yet.</p>";
      return;
    }

    const myReservations = result.reservations.filter(b => b.studentId === currentStudent.id);

    if (myReservations.length === 0) {
      container.innerHTML = "<p>No reservations yet.</p>";
      return;
    }

    myReservations.forEach(bk => {
      const card = document.createElement("div");
      card.classList.add("reservation-card");
      card.innerHTML = `
        <h3>${bk.eventName}</h3>
        <p><strong>Date:</strong> ${bk.eventDate}</p>
        <p><strong>Status:</strong> <span class="status-${bk.status.toLowerCase()}">${bk.status}</span></p>
        <div class="reservation-actions"></div>
      `;
      const actions = card.querySelector(".reservation-actions");

      if (bk.status === "Pending" || bk.status === "Confirmed") {
        const cancelBtn = document.createElement("button");
        cancelBtn.classList.add("btn-cancel");
        cancelBtn.textContent = "Cancel Reservation";
        cancelBtn.addEventListener("click", () => openCancelModal(bk.id));
        actions.appendChild(cancelBtn);
      }

      container.appendChild(card);
    });
  }

  // ==========================
  // Cancel Modal
  // ==========================
  const cancelModal = document.getElementById("cancelModal");
  const closeCancel = document.getElementById("closeCancel");
  const cancelYes = document.getElementById("cancelYes");
  const cancelNo = document.getElementById("cancelNo");
  let selectedBookingIdToCancel = null;

  function openCancelModal(bookingId) {
    selectedBookingIdToCancel = bookingId;
    if (cancelModal) cancelModal.style.display = "flex";
  }

  closeCancel?.addEventListener("click", () => cancelModal.style.display = "none");
  cancelNo?.addEventListener("click", () => cancelModal.style.display = "none");

  cancelYes?.addEventListener("click", async () => {
    if (!selectedBookingIdToCancel) return;

    const result = await apiCall('cancel_reservation', { 
      reservation_id: selectedBookingIdToCancel 
    });

    if (result.success) {
      // Create local notification
      const notifications = getNotifications();
      notifications.push({
        id: Date.now().toString() + Math.random().toString(36).slice(2,6),
        to: currentStudent.id,
        title: "Reservation Cancelled",
        message: `You successfully cancelled your reservation.`,
        date: new Date().toISOString(),
        read: false,
        type: "cancellation"
      });
      setNotifications(notifications);

      cancelModal.style.display = "none";
      selectedBookingIdToCancel = null;

      renderStudentEvents();
      renderMyReservations();
      alert("✅ " + result.message);
    } else {
      alert("❌ " + result.message);
    }
  });

  // ==========================
  // Profile Management
  // ==========================
  function loadProfile() {
    // Display current student data from sessionStorage
    document.getElementById("profile-id").textContent = currentStudent.id;
    document.getElementById("profile-name").textContent = currentStudent.name;
    document.getElementById("profile-program").textContent = currentStudent.program;
    document.getElementById("profile-block").textContent = currentStudent.block;
    document.getElementById("profile-year").textContent = currentStudent.year;
    document.getElementById("profile-email").textContent = currentStudent.email;
    document.getElementById("profile-number").textContent = currentStudent.phone;
    
    // Load health information
    document.getElementById("profile-weight").textContent = currentStudent.weight || "Not set";
    document.getElementById("profile-height").textContent = currentStudent.height || "Not set";
    document.getElementById("profile-bloodtype").textContent = currentStudent.bloodtype || "Not set";
  }

  const editProfileBtn = document.getElementById("edit-profile-btn");
  editProfileBtn?.addEventListener("click", function () {
    const btn = this;
    const infoDiv = document.getElementById("profile-info");

    if (btn.textContent === "Edit Profile") {
      infoDiv.innerHTML = `
        <p><strong>Student ID:</strong> <input type="text" id="edit-id" value="${currentStudent.id}" readonly></p>
        <p><strong>Name:</strong> <input type="text" id="edit-name" value="${currentStudent.name}"></p>
        <p><strong>Program:</strong> <input type="text" id="edit-program" value="${currentStudent.program}"></p>
        <p><strong>Block:</strong> <input type="text" id="edit-block" value="${currentStudent.block}"></p>
        <p><strong>Year Level:</strong> <input type="text" id="edit-year" value="${currentStudent.year}"></p>
        <p><strong>Email:</strong> <input type="email" id="edit-email" value="${currentStudent.email}"></p>
        <p><strong>Contact Number:</strong> <input type="text" id="edit-number" value="${currentStudent.phone}"></p>
        <!-- Health Information Inputs -->
        <p><strong>Weight (kg):</strong> <input type="number" id="edit-weight" value="${currentStudent.weight || ''}" placeholder="Enter weight in kg" min="30" max="200"></p>
        <p><strong>Height (cm):</strong> <input type="number" id="edit-height" value="${currentStudent.height || ''}" placeholder="Enter height in cm" min="100" max="250"></p>
        <p><strong>Blood Type:</strong> 
          <select id="edit-bloodtype">
            <option value="">Select Blood Type</option>
            <option value="A+" ${currentStudent.bloodtype === 'A+' ? 'selected' : ''}>A+</option>
            <option value="A-" ${currentStudent.bloodtype === 'A-' ? 'selected' : ''}>A-</option>
            <option value="B+" ${currentStudent.bloodtype === 'B+' ? 'selected' : ''}>B+</option>
            <option value="B-" ${currentStudent.bloodtype === 'B-' ? 'selected' : ''}>B-</option>
            <option value="AB+" ${currentStudent.bloodtype === 'AB+' ? 'selected' : ''}>AB+</option>
            <option value="AB-" ${currentStudent.bloodtype === 'AB-' ? 'selected' : ''}>AB-</option>
            <option value="O+" ${currentStudent.bloodtype === 'O+' ? 'selected' : ''}>O+</option>
            <option value="O-" ${currentStudent.bloodtype === 'O-' ? 'selected' : ''}>O-</option>
          </select>
        </p>
      `;
      btn.textContent = "Save Profile";
    } else {
      // For demo purposes, we'll just update sessionStorage
      // In a real app, you'd send this to the server
      const updatedProfile = {
        id: document.getElementById("edit-id").value,
        name: document.getElementById("edit-name").value,
        program: document.getElementById("edit-program").value,
        block: document.getElementById("edit-block").value,
        year: document.getElementById("edit-year").value,
        email: document.getElementById("edit-email").value,
        phone: document.getElementById("edit-number").value,
        weight: document.getElementById("edit-weight").value,
        height: document.getElementById("edit-height").value,
        bloodtype: document.getElementById("edit-bloodtype").value
      };

      sessionStorage.setItem('currentStudent', JSON.stringify(updatedProfile));

      document.getElementById("profile-info").innerHTML = `
        <p><strong>Student ID:</strong> <span id="profile-id">${updatedProfile.id}</span></p>
        <p><strong>Name:</strong> <span id="profile-name">${updatedProfile.name}</span></p>
        <p><strong>Program:</strong> <span id="profile-program">${updatedProfile.program}</span></p>
        <p><strong>Block:</strong> <span id="profile-block">${updatedProfile.block}</span></p>
        <p><strong>Year Level:</strong> <span id="profile-year">${updatedProfile.year}</span></p>
        <p><strong>Email:</strong> <span id="profile-email">${updatedProfile.email}</span></p>
        <p><strong>Contact Number:</strong> <span id="profile-number">${updatedProfile.phone}</span></p>
        <!-- Health Information Display -->
        <p><strong>Weight:</strong> <span id="profile-weight">${updatedProfile.weight || "Not set"}</span></p>
        <p><strong>Height:</strong> <span id="profile-height">${updatedProfile.height || "Not set"}</span></p>
        <p><strong>Blood Type:</strong> <span id="profile-bloodtype">${updatedProfile.bloodtype || "Not set"}</span></p>
      `;
      btn.textContent = "Edit Profile";
      
      alert("Profile updated! (Note: Changes are saved in browser session only for this demo)");
    }
  });

  // ==========================
  // Logout
  // ==========================
  const logoutBtn = document.getElementById("logout-btn");
  const logoutModal = document.getElementById("logout-modal");
  const confirmLogout = document.getElementById("confirm-logout");
  const cancelLogout = document.getElementById("cancel-logout");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", function() {
      if (logoutModal) {
        logoutModal.style.display = "flex";
      }
    });
  }

  if (cancelLogout) {
    cancelLogout.addEventListener("click", function() {
      if (logoutModal) {
        logoutModal.style.display = "none";
      }
    });
  }

  if (confirmLogout) {
    confirmLogout.addEventListener("click", function() {
      sessionStorage.removeItem("currentStudent");
      window.location.href = "index.php";
    });
  }

  // ==========================
  // Initial Setup
  // ==========================
  if (getNotifications().length === 0) {
    setNotifications([]);
  }

  loadProfile();
  renderStudentEvents();
  renderMyReservations();
  renderNotifications();
  updateNotificationBadge();

  console.log("Student Dashboard loaded with MySQL integration and View All Events feature");
});