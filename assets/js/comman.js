// assets/js/common.js - Preloader COMPLETELY Removed

const Common = {
    // ===== UTILITY FUNCTIONS =====
    showNotification: function(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        
        document.body.insertBefore(alertDiv, document.body.firstChild);
        
        setTimeout(() => {
            if (alertDiv.parentNode) alertDiv.remove();
        }, 5000);
    },

    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    setButtonLoading: function(button, isLoading) {
        if (isLoading) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            button.disabled = true;
        } else {
            button.innerHTML = button.dataset.originalText;
            button.disabled = false;
        }
    },

    isUserLoggedIn: function() {
        return document.body.classList.contains('user-logged-in');
    },

    // 🗑️ PRELOADER FUNCTION COMPLETELY REMOVED

    // ===== WISHLIST =====
    toggleWishlist: function(productId, button) {
        if (!this.isUserLoggedIn()) {
            this.showNotification('Please login to add to wishlist', 'warning');
            return;
        }

        const isActive = button.classList.contains('active');
        const action = isActive ? 'remove' : 'add';
        
        this.setButtonLoading(button, true);
        
        fetch('../ajax/wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=${action}&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            this.setButtonLoading(button, false);
            if (data.success) {
                button.classList.toggle('active', action === 'add');
                this.showNotification(data.message, 'success');
            } else {
                this.showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            this.setButtonLoading(button, false);
            this.showNotification('Network error', 'error');
        });
    },

    // ===== FORM VALIDATIONS =====
    validateRegisterForm: function() {
        const name = document.getElementById("regName")?.value.trim() || "";
        const email = document.getElementById("regEmail")?.value.trim() || "";
        const password = document.getElementById("regPassword")?.value.trim() || "";
        const confirmPassword = document.getElementById("confirmPassword")?.value.trim() || "";

        if (!/^[A-Za-z\s]{3,50}$/.test(name)) {
            this.showNotification("Name must be 3-50 characters", "error");
            return false;
        }
        if (!this.validateEmail(email)) {
            this.showNotification("Invalid email address", "error");
            return false;
        }
        if (password.length < 6) {
            this.showNotification("Password must be 6+ characters", "error");
            return false;
        }
        if (password !== confirmPassword) {
            this.showNotification("Passwords don't match", "error");
            return false;
        }
        return true;
    },

    // ===== INITIALIZATION =====
    initialize: function() {
        // 🗑️ PRELOADER INITIALIZATION REMOVED
        // Only initialize essential functions if needed
        console.log('Common functions initialized');
    }
};

// Initialize 
document.addEventListener("DOMContentLoaded", function() {
    Common.initialize();
});

