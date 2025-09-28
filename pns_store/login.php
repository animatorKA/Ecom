<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

// Only check login if we're not processing a login form
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isLoggedIn()) {
    error_log('Already logged in, redirecting based on role');
    
    // Get stored redirect URL if exists
    if (!empty($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        error_log('Redirecting to stored URL: ' . $redirect);
        header('Location: ' . $redirect);
        exit;
    }
    
    // Default redirects if no stored redirect
    if ($_SESSION['role'] === 'admin') {
        header('Location: /pns_store/admin/dashboard.php');
    } else if ($_SESSION['role'] === 'org') {
        header('Location: /pns_store/org/dashboard.php');
    } else {
        header('Location: /pns_store/');
    }
    exit;
}

// Initialize error variable
$err = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $err = 'All fields are required';
    } else {
        // Sanitize inputs
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Invalid email format';
        } else {
            // Query user
            $stmt = $pdo->prepare("SELECT user_id, password_hash, role, org_id, name, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            
            if (!$u) {
                $err = 'Invalid email address';
            } elseif ($u['status'] === 'suspended') {
                $err = 'Your account has been suspended. Please contact support.';
            } elseif ($u['status'] === 'inactive') {
                $err = 'Your account is inactive. Please verify your email.';
            } elseif (!password_verify($password, $u['password_hash'])) {
                $err = 'Invalid password';
            } else {
                // Create the user session
                createUserSession($u);
                
                // Debug session state
                error_log('Login successful for user: ' . $u['user_id'] . ', Role: ' . $u['role']);
                error_log('Session data: ' . print_r($_SESSION, true));
                
                // Handle redirect after login
                if (!empty($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    error_log('Redirecting to stored URL: ' . $redirect);
                    header('Location: ' . $redirect);
                    exit;
                }

                // Default redirects if no stored redirect
                switch ($u['role']) {
                    case 'admin':
                        header('Location: /pns_store/admin/dashboard.php');
                        break;
                    case 'org':
                        header('Location: /pns_store/org/dashboard.php');
                        break;
                    default:
                        header('Location: /pns_store/');
                }
                exit;
                // redirect() already calls exit
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h4>Login</h4>
      <?php if ($err): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($err) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" 
                   name="email" 
                   id="email" 
                   class="form-control" 
                   placeholder="Enter your email"
                   required
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   autocomplete="email">
          </div>
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" 
                   name="password" 
                   id="password" 
                   class="form-control" 
                   placeholder="Enter your password"
                   required
                   autocomplete="current-password">
          </div>
          <div class="invalid-feedback">Please enter your password.</div>
        </div>

        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
      </form>

      <script>
      // Client-side form validation
      (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
      })()
      </script>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
