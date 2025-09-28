<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/FormValidator.php';

// Now include the header which will have access to the session
require_once __DIR__ . '/includes/header.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $validator = new FormValidator($_POST);
        $validator->addRule('name', 'Name', ['required', ['min', 2], ['max', 100]])
                 ->addRule('email', 'Email', ['required', 'email'])
                 ->addRule('subject', 'Subject', ['required', ['min', 5], ['max', 200]])
                 ->addRule('message', 'Message', ['required', ['min', 20], ['max', 1000]]);

        if ($validator->validate()) {
            try {
                $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    FormValidator::sanitize($_POST['name']),
                    FormValidator::sanitize($_POST['email']),
                    FormValidator::sanitize($_POST['subject']),
                    FormValidator::sanitize($_POST['message'])
                ]);
                $success = 'Thank you for your message. We will get back to you soon!';
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
                $error = 'An error occurred. Please try again later.';
            }
        } else {
            $error = $validator->getFirstError();
        }
    }
}
?>

<!-- Page Header -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted mb-0">
                    Have questions? We'd love to hear from you. Send us a message
                    and we'll respond as soon as possible.
                </p>
            </div>
            <div class="col-lg-6 text-center">
                <img src="/pns_store/assets/images/logo/pns_logo.png" alt="Palawan National School Logo" class="img-fluid d-none d-lg-block mx-auto" style="max-height: 180px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Contact Information -->
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="position-sticky" style="top: 2rem;">
                <!-- School Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <h5 class="fw-bold">Palawan National School</h5>
                        <p class="text-muted mb-0">
                            Your trusted source for quality school supplies and merchandise.
                        </p>
                    </div>
                </div>

                <!-- Contact Methods -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Contact Information</h5>
                        
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bi bi-geo-alt-fill text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-semibold mb-1">Location</h6>
                                <p class="text-muted small mb-0">Palawan National School<br>Puerto Princesa City, Palawan</p>
                            </div>
                        </div>

                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bi bi-envelope-fill text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-semibold mb-1">Email</h6>
                                <p class="text-muted small mb-0">store@pns.edu.ph<br>support@pns.edu.ph</p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bi bi-clock-fill text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-semibold mb-1">Office Hours</h6>
                                <p class="text-muted small mb-0">Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday & Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Connect With Us</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-light border rounded-circle" style="width: 40px; height: 40px;">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="btn btn-light border rounded-circle" style="width: 40px; height: 40px;">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-light border rounded-circle" style="width: 40px; height: 40px;">
                                <i class="bi bi-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form and FAQs -->
        <div class="col-lg-8">
            <!-- Contact Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white p-3 rounded-circle me-3">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Visit Us</h5>
                                    <p class="mb-0">Palawan National School<br>Puerto Princesa City, Palawan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white p-3 rounded-circle me-3">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Email Us</h5>
                                    <p class="mb-0">store@pns.edu.ph<br>support@pns.edu.ph</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-4" style="font-size: 1.5rem;">Send us a Message</h4>
                    <form id="contactForm" action="send_message.php" method="POST" class="needs-validation" novalidate>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label mb-2" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Your Name</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text" 
                                              style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">
                                            <i class="bi bi-person text-slate-600" style="color: #475569;"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-lg" 
                                               id="name" name="name" required 
                                               style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none; 
                                                      font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                                                      box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                                                      transition: all 0.2s ease-in-out;"
                                               onFocus="this.style.backgroundColor='#ffffff'; 
                                                       this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';
                                                       this.previousElementSibling.style.backgroundColor='#ffffff';"
                                               onBlur="this.style.backgroundColor='#f8fafc';
                                                      this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';
                                                      this.previousElementSibling.style.backgroundColor='#f8fafc';">
                                    </div>
                                    <div class="invalid-feedback" style="font-size: 0.9rem;">Please enter your name</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label mb-2" style="font-size: 1rem; font-weight: 600; color: #2c3e50;">Email Address</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text" 
                                              style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">
                                            <i class="bi bi-envelope text-slate-600" style="color: #475569;"></i>
                                        </span>
                                        <input type="email" class="form-control form-control-lg" 
                                               id="email" name="email" required 
                                               style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none;
                                                      font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                                                      box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                                                      transition: all 0.2s ease-in-out;"
                                               onFocus="this.style.backgroundColor='#ffffff';
                                                       this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';
                                                       this.previousElementSibling.style.backgroundColor='#ffffff';"
                                               onBlur="this.style.backgroundColor='#f8fafc';
                                                      this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';
                                                      this.previousElementSibling.style.backgroundColor='#f8fafc';">
                                    </div>
                                    <div class="invalid-feedback" style="font-size: 0.9rem; color: #dc2626;">Please enter a valid email address</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="subject" class="form-label mb-2" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Subject</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text" 
                                              style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">
                                            <i class="bi bi-chat-left-text text-slate-600" style="color: #475569;"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-lg" 
                                               id="subject" name="subject" required 
                                               style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none;
                                                      font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                                                      box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                                                      transition: all 0.2s ease-in-out;"
                                               onFocus="this.style.backgroundColor='#ffffff';
                                                       this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';
                                                       this.previousElementSibling.style.backgroundColor='#ffffff';"
                                               onBlur="this.style.backgroundColor='#f8fafc';
                                                      this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';
                                                      this.previousElementSibling.style.backgroundColor='#f8fafc';">
                                    </div>
                                    <div class="invalid-feedback" style="font-size: 0.9rem;">Please enter a subject</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="message" class="form-label mb-2" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Message</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text align-items-start" 
                                              style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;
                                                     padding-top: 0.75rem;">
                                            <i class="bi bi-pencil text-slate-600" style="color: #475569;"></i>
                                        </span>
                                        <textarea class="form-control form-control-lg" 
                                                  id="message" name="message" rows="8" required 
                                                  style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none;
                                                         font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                                                         resize: vertical; min-height: 200px; line-height: 1.7;
                                                         box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                                                         transition: all 0.2s ease-in-out;"
                                                  placeholder="Write your message here..."
                                                  onFocus="this.style.backgroundColor='#ffffff';
                                                          this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';
                                                          this.previousElementSibling.style.backgroundColor='#ffffff';"
                                                  onBlur="this.style.backgroundColor='#f8fafc';
                                                         this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';
                                                         this.previousElementSibling.style.backgroundColor='#f8fafc';"></textarea>
                                    </div>
                                    <div class="invalid-feedback" style="font-size: 0.9rem; color: #dc2626;">Please enter your message</div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-5 py-3" 
                                        style="font-size: 1rem; font-weight: 600;">
                                    <i class="bi bi-send-fill me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- FAQs -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Frequently Asked Questions</h4>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-cart me-2 text-success"></i>
                                    How do I place an order?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-3">
                                    Browse our products, add items to your cart, and proceed to checkout. You'll need to be logged in to complete your purchase.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="bi bi-box me-2 text-success"></i>
                                    Where can I pick up my orders?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-3">
                                    All orders can be picked up at the administration office during school hours. You'll receive a notification when your order is ready.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="bi bi-percent me-2 text-success"></i>
                                    How do student discounts work?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-3">
                                    Student discounts are automatically applied when you log in with your student account. Make sure your account is verified to access all discounts.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and submission
(function () {
    'use strict';
    
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            
            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            // Get form data
            const formData = new FormData(form);
            
            // Disable submit button and show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

            // Remove any existing alerts
            const existingAlerts = form.parentNode.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            // Send form data using fetch
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Create alert element
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                // Insert alert before form
                form.parentNode.insertBefore(alertDiv, form);

                // Scroll to alert
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // If successful, reset form
                if (data.success) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Create error alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    An error occurred while sending your message. Please try again later.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                form.parentNode.insertBefore(alertDiv, form);
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>