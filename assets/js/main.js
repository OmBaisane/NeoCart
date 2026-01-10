// assets/js/main.js - Optimized Version using Common.js

document.addEventListener("DOMContentLoaded", function() {
    // ===== FLOATING LABELS ENHANCEMENT =====
    initializeFloatingLabels();

    // ===== FORM VALIDATIONS =====
    initializeFormValidations();

    // ===== UI ENHANCEMENTS =====
    initializeUIEnhancements();

    // ===== PRODUCTS SEARCH & FILTERS =====
    initializeProductsSearch();

    // ===== READ MORE FUNCTIONALITY =====
    initializeReadMore();

    // ===== AJAX SEARCH FUNCTIONALITY =====
    initializeAjaxSearch();

    // ===== WISHLIST FUNCTIONALITY =====
    initializeWishlist();

    // ===== REVIEW FUNCTIONALITY =====
    initializeReviews();
});

// Floating Labels Enhancement
function initializeFloatingLabels() {
    const floatingInputs = document.querySelectorAll('.form-floating input, .form-floating textarea, .form-floating select');
    
    floatingInputs.forEach(input => {
        if (input.value.trim() !== '') {
            input.classList.add('has-value');
        }

        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.parentElement.classList.remove('focused');
            }
        });

        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
    });
}

// Products Search and Filters Functionality
function initializeProductsSearch() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortSelect = document.getElementById('sortSelect');
    const searchForm = document.getElementById('searchForm');
    
    if (!searchInput || !searchForm) return;
    
    let searchTimeout;
    
    // Real-time search with debouncing
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchForm.submit();
        }, 500);
    });
    
    // Auto-submit on filter changes
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            searchForm.submit();
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }
    
    // Price range auto-submit with debouncing
    const priceInputs = document.querySelectorAll('input[name="min_price"], input[name="max_price"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const minPrice = document.querySelector('input[name="min_price"]').value;
                const maxPrice = document.querySelector('input[name="max_price"]').value;
                
                if ((minPrice === '' || parseFloat(minPrice) >= 0) && 
                    (maxPrice === '' || parseFloat(maxPrice) >= 0)) {
                    searchForm.submit();
                }
            }, 800);
        });
    });
}

// Read More functionality for product descriptions
function initializeReadMore() {
    document.querySelectorAll('.btn-read-more').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const descElement = document.getElementById(`desc-${productId}`);
            const shortDesc = descElement.querySelector('.short-desc');
            const fullDesc = descElement.querySelector('.full-desc');
            
            if (shortDesc.style.display !== 'none') {
                // Show full description
                shortDesc.style.display = 'none';
                fullDesc.style.display = 'inline';
                this.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Read Less';
            } else {
                // Show short description
                shortDesc.style.display = 'inline';
                fullDesc.style.display = 'none';
                this.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Read More';
            }
        });
    });
}

// Form Validations
function initializeFormValidations() {
    // Delete Confirmation
    document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", e => {
            if (!confirm("Are you sure you want to delete this item?")) {
                e.preventDefault();
            }
        });
    });

    // Register Form Validation
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function(e) {
            if (!Common.validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }

    // Login Form Validation
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function(e) {
            if (!Common.validateLoginForm()) {
                e.preventDefault();
            }
        });
    }
}

// UI Enhancements
function initializeUIEnhancements() {
    // Navbar Active Link
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });

    // Image preview for file inputs
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                previewImage(file, this);
            }
        });
    });
}

// Image preview utility
function previewImage(file, inputElement) {
    const reader = new FileReader();
    reader.onload = function(e) {
        let preview = inputElement.parentNode.querySelector('.image-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview mt-2';
            inputElement.parentNode.appendChild(preview);
        }
        preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;" alt="Preview">`;
    };
    reader.readAsDataURL(file);
}

// ===== AJAX SEARCH FUNCTIONALITY =====
function initializeAjaxSearch() {
    const headerSearchInput = document.getElementById('headerSearchInput');
    
    if (!headerSearchInput) return;
    
    let searchTimeout;
    
    // Create search results dropdown
    const searchDropdown = document.createElement('div');
    searchDropdown.className = 'search-results-dropdown position-absolute w-100 bg-white shadow-lg rounded mt-1';
    searchDropdown.style.display = 'none';
    searchDropdown.style.zIndex = '1000';
    searchDropdown.style.maxHeight = '300px';
    searchDropdown.style.overflowY = 'auto';
    
    // Insert dropdown after search input
    if (headerSearchInput.parentNode) {
        headerSearchInput.parentNode.style.position = 'relative';
        headerSearchInput.parentNode.appendChild(searchDropdown);
    }
    
    // Real-time search with debouncing
    headerSearchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Hide dropdown if search term is too short
        if (searchTerm.length < 2) {
            searchDropdown.style.display = 'none';
            return;
        }
        
        // Show loading state
        searchDropdown.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';
        searchDropdown.style.display = 'block';
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            performAjaxSearch(searchTerm);
        }, 300);
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!headerSearchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.style.display = 'none';
        }
    });
    
    // Handle keyboard navigation
    headerSearchInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown' && searchDropdown.querySelector('.search-result-item')) {
            e.preventDefault();
            const firstResult = searchDropdown.querySelector('.search-result-item');
            if (firstResult) firstResult.focus();
        }
        
        if (e.key === 'Escape') {
            searchDropdown.style.display = 'none';
            this.blur();
        }
    });
}

// Perform AJAX search
function performAjaxSearch(searchTerm) {
    fetch(`/NeoCart/ajax/search.php?q=${encodeURIComponent(searchTerm)}&limit=5`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            displaySearchResults(data, searchTerm);
        })
        .catch(error => {
            console.error('Search error:', error);
            displaySearchError();
        });
}

// Display search results
function displaySearchResults(data, searchTerm) {
    const searchDropdown = document.querySelector('.search-results-dropdown');
    
    if (!data.success || data.results.length === 0) {
        searchDropdown.innerHTML = `
            <div class="p-3 text-center text-muted">
                <i class="fas fa-search me-2"></i>
                No products found for "<strong>${searchTerm}</strong>"
            </div>
        `;
        return;
    }
    
    let resultsHTML = '';
    
    data.results.forEach(product => {
        resultsHTML += `
            <a href="${product.url}" class="search-result-item d-block p-3 border-bottom text-decoration-none text-dark hover-bg-light">
                <div class="d-flex align-items-center">
                    <img src="${product.image}" 
                         alt="${product.name}" 
                         class="rounded me-3"
                         style="width: 40px; height: 40px; object-fit: cover;"
                         onerror="this.src='../assets/images/placeholder.jpg'">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${product.highlighted_name}</div>
                        <small class="text-muted">${product.highlighted_description}</small>
                        <div class="text-primary fw-bold mt-1">₹${product.price}</div>
                    </div>
                </div>
            </a>
        `;
    });
    
    // Add view all results link
    resultsHTML += `
        <div class="p-2 border-top bg-light">
            <a href="../pages/products.php?search=${encodeURIComponent(searchTerm)}" 
               class="btn btn-primary btn-sm w-100 text-center">
                <i class="fas fa-list me-1"></i>View All Results (${data.count})
            </a>
        </div>
    `;
    
    searchDropdown.innerHTML = resultsHTML;
    
    // Add hover effects
    searchDropdown.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

// Display search error
function displaySearchError() {
    const searchDropdown = document.querySelector('.search-results-dropdown');
    searchDropdown.innerHTML = `
        <div class="p-3 text-center text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Search temporarily unavailable
        </div>
    `;
}

// ===== WISHLIST FUNCTIONALITY =====
function initializeWishlist() {
    // Wishlist buttons
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            Common.toggleWishlist(productId, this);
        });
    });
}

// ===== REVIEW FUNCTIONALITY =====
function initializeReviews() {
    // Star rating
    document.querySelectorAll('.star-rating .fa-star').forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            const container = this.closest('.star-rating');
            
            // Update visual rating
            container.querySelectorAll('.fa-star').forEach(s => {
                if (s.dataset.rating <= rating) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
            
            // Set hidden input value
            const ratingInput = container.parentNode.querySelector('input[name="rating"]');
            if (ratingInput) {
                ratingInput.value = rating;
            }
        });
    });

    // Review form submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const productId = this.querySelector('input[name="product_id"]')?.value;
            const rating = this.querySelector('input[name="rating"]')?.value;
            const reviewText = this.querySelector('textarea[name="review_text"]')?.value;
            
            Common.submitReview(productId, rating, reviewText);
        });
    }
}