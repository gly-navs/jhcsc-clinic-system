// Admin Dashboard Script (MySQL Database Version with REAL EMAIL)

document.addEventListener("DOMContentLoaded", function () {
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

      if (target === "reservations") {
        renderReservations();
      }
      if (target === "events") {
        renderEvents();
      }
      if (target === "notifications") {
        populateNotificationForm();
      }
    });
  });

  // ==========================
  // Logout
  // ==========================
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      if (confirm("Are you sure you want to logout?")) {
        sessionStorage.removeItem("loggedInAdmin");
        window.location.href = "index.php";
      }
    });
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
      return { success: false, 'message': 'Network error occurred' };
    }
  }

  // ==========================
  // Date Validation Functions
  // ==========================
  function getCurrentDate() {
    const now = new Date();
    return now.toISOString().split('T')[0];
  }

  function isValidEventDate(selectedDate) {
    const today = new Date(getCurrentDate());
    const selected = new Date(selectedDate);
    return selected >= today;
  }

  function setDateRestrictions() {
    const eventDateInput = document.getElementById("event-date");
    if (!eventDateInput) return;

    eventDateInput.min = getCurrentDate();
    
    eventDateInput.addEventListener("change", function() {
      const selectedDate = this.value;
      if (selectedDate && !isValidEventDate(selectedDate)) {
        alert("⚠️ Event dates must be today or in the future. Please select a valid date.");
        this.value = "";
        this.focus();
      }
    });
  }

  // ==========================
  // Event Management
  // ==========================
  let editEventId = null;

  async function renderEvents() {
    const result = await apiCall('get_events');
    const eventsList = document.querySelector(".events-list");
    
    if (!eventsList) return;
    eventsList.innerHTML = "";

    if (!result.success || result.events.length === 0) {
      eventsList.innerHTML = "<p>No events created yet.</p>";
      return;
    }

    result.events.forEach(event => {
      const card = document.createElement("div");
      card.classList.add("event-card");
      
      const eventDate = new Date(event.date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const isPastEvent = eventDate < today;
      
      if (isPastEvent) {
        card.classList.add("past-event");
      }
      
      card.innerHTML = `
        <h4>${event.title || "No Title"}</h4>
        <p><strong>Date:</strong> 
          <span class="${isPastEvent ? 'past-date' : ''}">${event.date}</span>
          ${isPastEvent ? '<small class="past-warning">(Past Event)</small>' : ''}
        </p>
        <p><strong>Description:</strong> ${event.description || 'No description'}</p>
        <p><strong>Slots:</strong> ${event.reserved || 0}/${event.capacity}</p>
        <div class="event-actions">
          <button class="btn-edit" data-id="${event.id}" data-action="edit">Edit</button>
          <button class="btn-delete" data-id="${event.id}" data-action="delete">Delete</button>
        </div>
      `;
      eventsList.appendChild(card);
    });

    // Wire up edit/delete buttons
    document.querySelectorAll(".event-actions .btn-edit").forEach(b => {
      b.addEventListener("click", () => {
        const id = b.getAttribute("data-id");
        editEvent(id);
      });
    });
    
    document.querySelectorAll(".event-actions .btn-delete").forEach(b => {
      b.addEventListener("click", () => {
        const id = b.getAttribute("data-id");
        deleteEvent(id);
      });
    });
  }

  // Event Form Submission
  const eventForm = document.getElementById("event-form");
  if (eventForm) {
    eventForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const title = document.getElementById("event-title").value.trim();
      const date = document.getElementById("event-date").value.trim();
      const description = document.getElementById("event-desc").value.trim();
      const capacity = parseInt(document.getElementById("event-capacity").value.trim()) || 0;

      if (!title || !date || !description || capacity <= 0) {
        alert("❌ All fields including capacity are required!");
        return;
      }

      if (!isValidEventDate(date)) {
        alert("❌ Event dates must be today or in the future. Please select a valid date.");
        document.getElementById("event-date").focus();
        return;
      }

      const eventData = {
        title: title,
        date: date,
        description: description,
        capacity: capacity
      };

      if (editEventId) {
        eventData.event_id = editEventId;
      }

      const result = await apiCall('save_event', eventData);
      
      if (result.success) {
        alert("✅ " + result.message);
        renderEvents();
        eventForm.reset();
        closeModal();
        editEventId = null;
      } else {
        alert("❌ " + result.message);
      }
    });
  }

  // Edit Event
  async function editEvent(id) {
    const result = await apiCall('get_events');
    if (!result.success) {
      alert("❌ Error loading events");
      return;
    }

    const event = result.events.find(ev => ev.id == id);
    if (!event) {
      alert("❌ Event not found!");
      return;
    }
    
    editEventId = id;
    document.getElementById("event-title").value = event.title;
    document.getElementById("event-desc").value = event.description || '';
    document.getElementById("event-capacity").value = event.capacity;

    const eventDateInput = document.getElementById("event-date");
    const originalDate = event.date;
    
    const eventDate = new Date(originalDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const isPastEvent = eventDate < today;
    
    if (isPastEvent) {
      const proceed = confirm(`⚠️ This event is scheduled for ${originalDate} (past date). You can edit it, but please consider if it should be rescheduled to a future date. Continue editing?`);
      if (!proceed) {
        editEventId = null;
        return;
      }
    }
    
    eventDateInput.value = originalDate;
    eventDateInput.min = ""; // Remove restriction for editing
    
    document.getElementById("modal-title").textContent = "Edit Event";
    document.getElementById("event-modal").style.display = "block";
  }

  // Delete Event
  async function deleteEvent(id) {
    const result = await apiCall('get_events');
    if (!result.success) return;

    const eventToDelete = result.events.find(ev => ev.id == id);
    if (!eventToDelete) return;

    const eventDate = new Date(eventToDelete.date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const isPastEvent = eventDate < today;
    
    let confirmMessage = "Are you sure you want to delete this event?";
    if (isPastEvent) {
      confirmMessage = "⚠️ This is a past event. Deleting it will also remove any associated reservations. Are you sure?";
    }
    
    if (!confirm(confirmMessage)) return;

    const deleteResult = await apiCall('delete_event', { event_id: id });
    
    if (deleteResult.success) {
      alert("✅ " + deleteResult.message);
      renderEvents();
      renderReservations();
      populateNotificationForm();
    } else {
      alert("❌ " + deleteResult.message);
    }
  }

  // Make functions available globally
  window.editEvent = editEvent;
  window.deleteEvent = deleteEvent;

  // ==========================
  // Reservations Management
  // ==========================
  async function renderReservations() {
    const result = await apiCall('get_reservations');
    const tbody = document.getElementById("reservations-body");
    
    if (!tbody) return;
    tbody.innerHTML = "";

    if (!result.success || result.reservations.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6">No reservations yet.</td></tr>`;
      return;
    }

    result.reservations.forEach((res, index) => {
      const row = document.createElement("tr");
      const eventDate = new Date(res.eventDate);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const isPastEvent = eventDate < today;

      row.innerHTML = `
        <td>${res.studentName}</td>
        <td>${res.studentId}</td>
        <td>${res.eventName}</td>
        <td>
          <span class="${isPastEvent ? 'past-date' : ''}">${res.eventDate}</span>
          ${isPastEvent ? '<small class="past-warning">(Past)</small>' : ''}
        </td>
        <td><span class="status ${res.status.toLowerCase()}">${res.status}</span></td>
        <td class="action-cell">
          ${res.status === "Pending" ? `
            <button class="btn-primary btn-confirm" data-id="${res.id}">Confirm</button>
            <button class="btn-delete btn-reject" data-id="${res.id}">Reject</button>
          ` : res.status}
        </td>
      `;
      tbody.appendChild(row);
    });

    // Attach action listeners
    document.querySelectorAll(".btn-confirm").forEach(btn => {
      btn.addEventListener("click", async () => {
        const reservationId = btn.getAttribute("data-id");
        await updateReservationStatus(reservationId, "Confirmed");
      });
    });
    
    document.querySelectorAll(".btn-reject").forEach(btn => {
      btn.addEventListener("click", async () => {
        const reservationId = btn.getAttribute("data-id");
        await updateReservationStatus(reservationId, "Rejected");
      });
    });
  }

  async function updateReservationStatus(reservationId, status) {
    const result = await apiCall('update_reservation_status', {
      reservation_id: reservationId,
      status: status
    });

    if (result.success) {
      alert(`✅ Reservation ${status.toLowerCase()} successfully! ${result.message.includes('Email') ? 'Email notification sent.' : ''}`);
      renderReservations();
      renderEvents();
    } else {
      alert("❌ " + result.message);
    }
  }

  // ==========================
  // Notifications Management with REAL EMAIL
  // ==========================
  const recipientSelect = document.getElementById("recipientSelect");
  const notificationEventSelect = document.getElementById("notification-event");
  const notificationMessage = document.getElementById("notification-message");
  const notificationSubject = document.getElementById("notification-subject");

  async function populateNotificationForm() {
    const reservationsResult = await apiCall('get_reservations');
    const eventsResult = await apiCall('get_events');

    if (!reservationsResult.success || !eventsResult.success) {
      console.error('Error loading data for notifications');
      return;
    }

    // Get ALL students from database with their emails
    const studentsResult = await apiCall('get_students');
    const allStudents = studentsResult.success ? studentsResult.students : [];

    // Also get students from reservations as fallback
    const reservationStudents = reservationsResult.reservations
      .map(b => ({ id: b.studentId, name: b.studentName, email: b.studentEmail }))
      .reduce((acc, s) => {
        if (!acc.find(x => x.id === s.id)) acc.push(s);
        return acc;
      }, []);

    // Combine both lists, prioritizing database students
    const activeStudents = allStudents.length > 0 ? allStudents : reservationStudents;

    // Populate recipient select
    if (recipientSelect) {
        recipientSelect.innerHTML = "";
        const optAll = document.createElement("option");
        optAll.value = "all";
        optAll.textContent = "All Students";
        recipientSelect.appendChild(optAll);

        activeStudents.forEach(s => {
            // Only show students with email addresses
            if (s.email) {
                const o = document.createElement("option");
                o.value = s.id;
                o.textContent = `${s.name} (${s.id}) - ${s.email}`;
                o.setAttribute('data-email', s.email);
                recipientSelect.appendChild(o);
            }
        });

        // Show warning if no students with emails
        if (activeStudents.filter(s => s.email).length === 0) {
            const warningOpt = document.createElement("option");
            warningOpt.value = "";
            warningOpt.textContent = "No students with email addresses found";
            warningOpt.disabled = true;
            recipientSelect.appendChild(warningOpt);
        }
    }

    // Populate events list
    if (notificationEventSelect) {
        notificationEventSelect.innerHTML = "";
        const optNone = document.createElement("option");
        optNone.value = "";
        optNone.textContent = "-- None -- (General Notification)";
        notificationEventSelect.appendChild(optNone);

        eventsResult.events.forEach(ev => {
            const o = document.createElement("option");
            o.value = ev.id;
            o.textContent = `${ev.title} — ${ev.date}`;
            notificationEventSelect.appendChild(o);
        });
    }
  }

  // Function to send REAL EMAIL notification
  window.sendNotification = async function () {
    const to = recipientSelect.value;
    const eventId = notificationEventSelect.value || "";
    const subject = notificationSubject.value.trim() || "Notification from JHCSC Clinic";
    const message = notificationMessage.value.trim();
    
    if (!message) {
      alert("❌ Please type a message.");
      return;
    }

    if (!subject) {
      alert("❌ Please enter an email subject.");
      return;
    }

    if (to === "") {
      alert("❌ Please select a recipient.");
      return;
    }

    // Show loading state
    const sendBtn = document.querySelector('.btn-action.btn-primary');
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SENDING EMAIL...';
    sendBtn.disabled = true;

    try {
        // Send email via server
        const result = await apiCall('send_notification_email', {
            to_type: to === 'all' ? 'all' : 'specific',
            student_id: to === 'all' ? '' : to,
            subject: subject,
            message: message
        });

        if (result.success) {
            // Also save to local storage for in-app notifications
            const notes = getNotifications();
            const notificationId = Date.now().toString() + Math.random().toString(36).slice(2,6);
            
            notes.push({
                id: notificationId,
                to: to,
                title: "Email Notification Sent",
                message: `Subject: ${subject}\nMessage: ${message}`,
                date: new Date().toISOString(),
                read: false,
                eventId: eventId,
                emailSent: true,
                emailsSent: result.emails_sent || 1
            });

            setNotifications(notes);
            
            // Clear form
            notificationMessage.value = "";
            
            let successMessage = `✅ Email notification sent successfully to ${result.emails_sent || 1} student(s)!`;
            if (result.warnings) {
                successMessage += "\n\nSome warnings:\n" + result.warnings.join('\n');
            }
            alert(successMessage);
        } else {
            alert("❌ " + result.message);
        }
    } catch (error) {
        console.error('Error sending notification:', error);
        alert("❌ Error sending email notification. Please check your internet connection and try again.");
    } finally {``
        // Restore button state
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    }
  };

  // Keep existing localStorage notifications for demo
  function getNotifications() {
    return JSON.parse(localStorage.getItem("notifications")) || [];
  }

  function setNotifications(arr) {
    localStorage.setItem("notifications", JSON.stringify(arr));
  }

  // Function to delete a specific notification
  function deleteNotification(notificationId) {
    const notes = getNotifications();
    const updatedNotes = notes.filter(note => note.id !== notificationId);
    setNotifications(updatedNotes);
    alert("✅ Notification deleted successfully!");
  }

  // ==========================
  // Modal Functions
  // ==========================
  const modal = document.getElementById("event-modal");
  const openBtn = document.getElementById("create-event-btn");
  const closeBtn = document.getElementById("close-event-modal");

  function closeModal() {
    modal.style.display = "none";
    editEventId = null;
  }

  if (openBtn) {
    openBtn.addEventListener("click", function () {
      modal.style.display = "block";
      document.getElementById("modal-title").textContent = "Create New Event";
      eventForm.reset();
      
      const eventDateInput = document.getElementById("event-date");
      if (eventDateInput) {
        eventDateInput.value = getCurrentDate();
        eventDateInput.min = getCurrentDate();
      }
      
      editEventId = null;
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", closeModal);
  }

  window.onclick = function (e) {
    if (e.target == modal) closeModal();
  };

  // ==========================
  // Initialize
  // ==========================
  setDateRestrictions();
  renderEvents();
  renderReservations();
  populateNotificationForm();

  console.log("Admin Dashboard loaded with MySQL integration and REAL EMAIL functionality");
});