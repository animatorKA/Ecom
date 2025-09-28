<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/FormValidator.php';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create validator instance
    $validator = new FormValidator($_POST);
    
    // Add validation rules
    $validator->addRule('name', 'Full Name', ['required', ['min', 2], ['max', 100]])
             ->addRule('email', 'Email Address', ['required', 'email'])
             ->addRule('password', 'Password', ['required', ['min', 8], 'password']);
             
    // Validate form
    if ($validator->validate()) {
        $name = FormValidator::sanitize($_POST['name']);
        $email = FormValidator::sanitize($_POST['email']);
        $password = $_POST['password'];
        
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $err = 'Email address is already registered';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, status) VALUES (?, ?, ?, 'active')");
                $stmt->execute([$name, $email, $hash]);
                
                // Redirect to login
                header('Location: /pns_store/login.php');
                exit;
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $err = 'An error occurred during registration. Please try again.';
        }
    } else {
        $err = $validator->getFirstError();
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h4>Register</h4>
      <?php if($err): ?><div class="alert alert-danger"><?=$err?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3"><input name="name" required class="form-control" placeholder="Full name"></div>
        <div class="mb-3"><input name="email" required class="form-control" placeholder="Email"></div>
        <div class="mb-3"><input name="password" type="password" required class="form-control" placeholder="Password"></div>
        <button class="btn btn-success">Register</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
