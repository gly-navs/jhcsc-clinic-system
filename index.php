
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JHCSC Campus Clinic - Login</title>
  <link rel="icon" type="image/jpg" href = "jhlogo.png">
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

</head>
<body>
  <!-- Loading Screen -->
  <div class="loading-screen" id="loadingScreen">
      <div class="loader-container">
          <div class="animated-logo">
              <img src="jhlogo.png" alt="JHCSC Logo" />
          </div>
          <h2 class="loading-text">JHCSC CLINIC SYSTEM</h2>
          <div class="progress-bar">
              <div class="progress-fill"></div>
          </div>
          <div class="loading-dots">
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
          </div>
          <p class="loading-subtext">Your health journey begins here...</p>
      </div>
  </div>

  <div class="bg-shape-1"></div>
  <div class="bg-shape-2"></div>
  
  <div class="container">
    <!-- Left Side - Brand Section -->
    <div class="brand-section">
      <div class="logo-container">
        <div class="logo">
          <img src="jhlogo.png" alt="JHCSC Logo" />
        </div>
      </div>
      <div class="brand-text">
        <h1>Welcome to JHCSC Pagadian Clinic Campus!</h1>
        <h2>Clinic Event & Reservation System</h2>
        
        <ul class="brand-features">
          <li><i class="fas fa-calendar-check"></i> Easy Event Registration</li>
          <li><i class="fas fa-bell"></i> Instant Notifications</li>
          <li><i class="fas fa-clock"></i> 24/7 Access</li>
          <li><i class="fas fa-shield-alt"></i> Secure & Private</li>
        </ul>

        <!-- Facebook Connect Section -->
        <div class="social-connect">
          <p>Connect with us on</p>
          <a href="https://www.facebook.com/glylanz.navaluna.3" target="_blank" class="facebook-connect">
            <i class="fab fa-facebook-f"></i>
            Facebook
          </a>
        </div>
        
      </div>
    </div>

    <!-- Right Side - Form Section -->
    <div class="form-section">
      <div class="form-container">
        <!-- Form Toggle -->
        <div class="form-toggle">
          <button id="login-toggle" class="active">Login</button>
          <button id="signup-toggle">Sign Up</button>
          <div class="toggle-slider"></div>
        </div>

        <!-- Login Form -->
        <form id="login-form" class="form active" method="POST" action="server.php">
          <input type="hidden" name="action" value="login">
          <h2>Welcome Back</h2>
          <div class="input-group">
            <label for="login-id">Login ID</label>
            <input type="text" name="student_id" id="login-id" placeholder="e.g. 38743" required />
          </div>
          <div class="input-group password-group">
            <label for="login-password">Password</label>
            <div class="password-input-container">
              <input type="password" name="password" id="login-password" required />
              <span class="toggle-password" onclick="togglePassword('login-password', this)">
                <i class="fa-solid fa-eye-slash"></i>
              </span>
            </div>
          </div>
          <button type="submit" class="submit-btn">Login</button>
          <div class="form-footer">
            <p>Secure login for students and staff</p>
          </div>
        </form>

        <!-- Sign Up Form -->
        <form id="signup-form" class="form" method="POST" action="server.php">
          <input type="hidden" name="action" value="signup">
          <h2>Create Account</h2>

          <div class="input-group">
            <label for="signup-name">Full Name</label>
            <input type="text" name="name" id="signup-name" placeholder="Enter your full name" required />
          </div>

          <div class="input-group">
            <label for="signup-id">Student ID</label>
            <input type="text" name="student_id" id="signup-id" placeholder="e.g. 38743" required />
          </div>

          <div class="input-row-three">
            <div class="input-group">
              <label for="signup-program">Program</label>
              <input type="text" name="program" id="signup-program" placeholder="e.g. BSIT" required />
            </div>
            <div class="input-group">
              <label for="signup-block">Block</label>
              <input type="text" name="block" id="signup-block" placeholder="e.g. 3A" required />
            </div>
            <div class="input-group">
              <label for="signup-year">Year Level</label>
              <input type="text" name="year" id="signup-year" placeholder="e.g. 3rd Year" required />
            </div>
          </div>

          <div class="input-group">
            <label for="signup-email">Email</label>
            <input type="email" name="email" id="signup-email" placeholder="your.email@gmail.com" required />
          </div>

          <div class="input-group">
            <label for="signup-phone">Contact Number</label>
            <input type="tel" name="phone" id="signup-phone" placeholder="09XXXXXXXXX" required />
          </div>

          <div class="input-group password-group">
            <label for="signup-password">Password</label>
            <div class="password-input-container">
              <input type="password" name="password" id="signup-password" required />
              <span class="toggle-password" onclick="togglePassword('signup-password', this)">
                <i class="fa-solid fa-eye-slash"></i>
              </span>
            </div>
          </div>

          <div class="input-group password-group">
            <label for="signup-confirm-password">Confirm Password</label>
            <div class="password-input-container">
              <input type="password" id="signup-confirm-password" required />
              <span class="toggle-password" onclick="togglePassword('signup-confirm-password', this)">
                <i class="fa-solid fa-eye-slash"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="submit-btn">Create Account</button>
          <div class="form-footer">
            <p>By signing up, you agree to our terms and conditions</p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>