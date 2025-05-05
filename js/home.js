// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.querySelector('.simple-navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Active nav link highlighting
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    const emailInput = document.querySelector('.newsletter .form-control');
    const subscribeButton = document.querySelector('.newsletter .btn-purple');
    
    if (subscribeButton && emailInput) {
        subscribeButton.addEventListener('click', function(e) {
            const email = emailInput.value.trim();
            
            if (email && validateEmail(email)) {
                // In a real app, you would send this to your server
                alert('Thank you for subscribing! You will receive our latest updates soon.');
                emailInput.value = '';
            } else {
                alert('Please enter a valid email address.');
            }
        });
    }

    // Product category hover effects using event delegation
    const perfumeCategoriesContainer = document.querySelector('.perfume-categories');
    if (perfumeCategoriesContainer) {
        // Using event delegation for better performance
        perfumeCategoriesContainer.addEventListener('mouseover', function(e) {
            const category = e.target.closest('.perfume-category');
            if (category) {
                category.style.transform = 'translateY(-10px)';
                category.style.transition = 'transform 0.3s ease';
            }
        });
        
        perfumeCategoriesContainer.addEventListener('mouseout', function(e) {
            const category = e.target.closest('.perfume-category');
            if (category) {
                category.style.transform = 'translateY(0)';
            }
        });
    }

    // CTA button animation
    const ctaButton = document.querySelector('.cta-button');
    if (ctaButton) {
        ctaButton.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        ctaButton.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    }

    // Footer year update
    const footer = document.querySelector('footer');
    if (footer) {
        const existingCopyright = footer.querySelector('.text-center.mt-4');
        if (!existingCopyright) {
            const yearSpan = document.createElement('span');
            yearSpan.textContent = new Date().getFullYear();
            const copyright = document.createElement('div');
            copyright.className = 'text-center mt-4';
            copyright.innerHTML = `&copy; ${yearSpan.textContent} Fragrance Fusion. All rights reserved.`;
            footer.appendChild(copyright);
        }
    }

    // Helper function to validate email
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Mobile menu close on click - with safe Bootstrap check
    const mobileNavLinks = document.querySelectorAll('.navbar-nav .nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                // Check if Bootstrap is available
                if (typeof bootstrap !== 'undefined') {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                        toggle: false
                    });
                    bsCollapse.hide();
                } else {
                    // Fallback if Bootstrap JS isn't loaded
                    navbarCollapse.classList.remove('show');
                }
            }
        });
    });
    
    // Login status handling - check if user is logged in
    function updateAuthButtons() {
        const logoutButton = document.querySelector('.logout-button');
        const loginButton = document.querySelector('.login-button');
        const signupButton = document.querySelector('.signup-button');
        
        // If this function is called after an AJAX login/logout
        // you can update the buttons visibility here
        if (logoutButton && loginButton && signupButton) {
            // Example: This would be replaced by your actual logic
            const isLoggedIn = logoutButton && window.getComputedStyle(logoutButton).display !== 'none';
            console.log("User login status:", isLoggedIn ? "Logged in" : "Logged out");
        }
    }
    
    // Call once on page load
    updateAuthButtons();
});