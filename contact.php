<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container">
  <h1 class="mt-4 magictime spaceInRight">Contact NeoCart</h1>
  
  <div class="row">
    <div class="col-md-8">
      <?php
      $sent = false;
      $errors = [];

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $name = htmlspecialchars(trim($_POST['name']));
          $email = htmlspecialchars(trim($_POST['email']));
          $subject = htmlspecialchars(trim($_POST['subject']));
          $message = htmlspecialchars(trim($_POST['message']));

          // 🔹 Enhanced validation
          if (!preg_match("/^[A-Za-z\s]{3,50}$/", $name)) {
              $errors[] = "Name must be 3–50 characters and letters only.";
          }

          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $errors[] = "Please enter a valid email address.";
          }

          if (empty($subject) || strlen($subject) < 5) {
              $errors[] = "Subject must be at least 5 characters.";
          }

          if (strlen($message) < 15) {
              $errors[] = "Message should be at least 15 characters.";
          }

          if (empty($errors)) {
              // 📧 Email functionality (optional)
              $to = "admin@neocart.com";
              $headers = "From: $email\r\n";
              $headers .= "Reply-To: $email\r\n";
              $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
              
              // mail($to, "NeoCart Contact: $subject", $email_body, $headers);
              $sent = true;
          }
      }
      ?>

      <?php if ($sent): ?>
        <div class="alert alert-success magictime spaceInRight">
          <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-3 fs-4"></i>
            <div>
              <h5 class="mb-1">Thank You, <strong><?php echo $name; ?></strong>!</h5>
              <p class="mb-0">Your message has been received. Our team will get back to you within 24 hours.</p>
            </div>
          </div>
          <div class="mt-3">
            <a href="index.php" class="btn btn-success me-2">Continue Shopping</a>
            <a href="contact.php" class="btn btn-outline-primary">Send Another Message</a>
          </div>
        </div>
      <?php else: ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger magictime spaceInDown">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following issues:</h6>
            <ul class="mb-0 mt-2">
              <?php foreach ($errors as $err): ?>
                <li><?php echo $err; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="mb-5 magictime spaceInUp" style="max-width:680px;" id="contactForm">
          <div class="row">
            <div class="col-md-6 mb-3 form-floating">
              <input name="name" id="contactName" class="form-control" 
                     placeholder=" " required 
                     pattern="[A-Za-z\s]{3,50}" 
                     title="3–50 letters only">
              <label for="contactName" class="form-label">Full Name *</label>
            </div>
            <div class="col-md-6 mb-3 form-floating">
              <input type="email" name="email" id="contactEmail" class="form-control" 
                     placeholder=" " required>
              <label for="contactEmail" class="form-label">Email Address *</label>
            </div>
          </div>
          
          <div class="mb-3 form-floating">
            <select name="subject" id="contactSubject" class="form-select" required>
              <option value=""></option>
              <option value="General Inquiry">General Inquiry</option>
              <option value="Order Support">Order Support</option>
              <option value="Technical Issue">Technical Issue</option>
              <option value="Feature Request">Feature Request</option>
              <option value="Partnership">Partnership</option>
              <option value="Other">Other</option>
            </select>
            <label for="contactSubject" class="form-label">Subject *</label>
          </div>
          
          <div class="mb-3 form-floating">
            <textarea name="message" id="contactMessage" class="form-control" 
                      placeholder=" " required minlength="15" style="height: 120px"></textarea>
            <label for="contactMessage" class="form-label">Message *</label>
            <div class="form-text">Minimum 15 characters required</div>
          </div>
          
          <button type="submit" class="btn btn-primary px-4 magictime perspectiveDownReturn">
            <i class="fas fa-paper-plane me-2"></i>Send Message
          </button>
        </form>

      <?php endif; ?>
    </div>
    
    <div class="col-md-4">
      <div class="card bg-light">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Contact Information</h5>
          <div class="mt-3">
            <p><i class="fas fa-envelope me-2 text-primary"></i><strong>Email:</strong><br>support@neocart.com</p>
            <p><i class="fas fa-clock me-2 text-primary"></i><strong>Response Time:</strong><br>Within 24 hours</p>
            <p><i class="fas fa-headset me-2 text-primary"></i><strong>Support Hours:</strong><br>Mon-Fri: 9AM-6PM</p>
          </div>
          
          <hr>
          
          <h6><i class="fas fa-question-circle me-2"></i>Quick Help</h6>
          <div class="small">
            <p><strong>Order Issues?</strong><br>Include your Order ID for faster resolution.</p>
            <p><strong>Technical Problems?</strong><br>Describe the steps to reproduce the issue.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/main.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>