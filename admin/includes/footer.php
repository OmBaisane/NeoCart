      </div> <!-- admin-content end -->
    </div> <!-- row end -->
  </div> <!-- container-fluid end -->
  
  <!-- Admin Footer -->
  <footer class="bg-dark text-center text-light py-3 mt-5">
    <div class="container">
      <p class="mb-0">
        <i class="fas fa-shopping-cart me-2"></i>
        &copy; <?php echo date("Y"); ?> NeoCart Admin Panel | 
        <small>Secure E-commerce Management System</small>
      </p>
      <small class="text-muted">
        Logged in as: <?php echo $_SESSION['admin_name'] ?? 'Administrator'; ?> | 
        Server Time: <?php echo date('Y-m-d H:i:s'); ?>
      </small>
    </div>
  </footer>

  <!-- CORRECT JAVASCRIPT PATHS -->
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  
  <!-- Auto-hide alerts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-hide alerts after 5 seconds
      setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
          if (!alert.classList.contains('alert-permanent')) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
          }
        });
      }, 5000);

      // Active link highlighting
      const currentPage = window.location.pathname.split('/').pop();
      document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage) {
          link.classList.add('active');
        }
      });
    });
  </script>
</body>
</html>