<style>
/* Footer styles */
.footer {
  background-color: #ffffff; /* Match the background color of the page */
  width: 100%; /* Ensure the footer spans the full width */
  padding: 60px 40px;
  margin: 0; /* Remove any default margins */
  margin-top: 10px; /* Add spacing above the footer */
}

.footer-content {
  display: flex;
  gap: 20px;
  max-width: 1280px; /* Optional: limit the content width */
  margin: 0 auto; /* Center the content inside the footer */
}

.footer-column {
  width: 100%;
  display: flex;
  gap: 20px;
}

.footer-section {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.footer-heading {
  color: #111111;
  font-size: 1.25rem;
  font-weight: bold;
  line-height: 1.4;
}

.footer-text {
  color: #444444;
  font-size: 1rem;
  line-height: 1.5;
  margin-top: 27px;
}

.footer-nav {
  display: flex;
  flex-direction: column;
}

.footer-link {
  color: #444444;
  margin-top: 25px;
  transition: color 0.3s;
}

.footer-link:hover {
  color: black;
}

.social-links {
  display: flex;
  gap: 24px;
  margin-top: 25px;
}

.social-icon {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

.bar-before-footer {
  background-color: #f3f3f3; /* Light gray background for blending */
  padding: 20px 0; /* Add vertical padding */
  text-align: center; /* Center-align the content */
  font-size: 1rem;
  color: #444444;
  border-top: 1px solid #ddd; /* Optional: subtle border for separation */
  border-bottom: 1px solid #ddd; /* Optional: subtle border for separation */
}

.bar-before-footer p {
  margin: 0; /* Remove default margins */
}

.copyright {
  text-align: center;
  color: #444444;
  font-size: 0.875rem;
  margin-top: 40px;
}

.bar-before-footer {
    margin-top: 40px; /* Add spacing above the bar */
    background-color: #f3f3f3; /* Light gray background for blending */
    padding: 20px 0; /* Add vertical padding */
    text-align: center; /* Center-align the content */
    font-size: 1rem;
    color: #444444;
    border-top: 1px solid #ddd; /* Optional: subtle border for separation */
    border-bottom: 1px solid #ddd; /* Optional: subtle border for separation */
}

.bar-before-footer p {
    margin: 0; /* Remove default margins */
}
</style>
<!-- filepath: z:\xampp\htdocs\fragrancefusion\includes\footer.php -->

<!-- Bar Before Footer -->
<div class="bar-before-footer">
    <p>Join our community and stay updated with the latest trends in fragrances!</p>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-column">
            <div class="footer-section">
                <h3 class="footer-heading">About Us</h3>
                <p class="footer-text">
                    Crafting unique fragrances that tell stories and create memories.
                </p>
            </div>
            <div class="footer-section">
                <h3 class="footer-heading">Quick Links</h3>
                <nav class="footer-nav">
                    <a href="/fragrancefusion/html/collections.php" class="footer-link">Collections</a>
                    <a href="/fragrancefusion/html/ContactForm.php" class="footer-link">Contact Us</a>
                </nav>
            </div>
        </div>
        <div class="footer-column">
            <div class="footer-section">
                <h3 class="footer-heading">Customer Care</h3>
                <nav class="footer-nav">
                    <a href="#" class="footer-link">Returns</a>
                    <a href="/fragrancefusion/html/FAQ.php" class="footer-link">FAQ</a>
                </nav>
            </div>
            <div class="footer-section">
                <h3 class="footer-heading">Follow Us</h3>
                <div class="social-links">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="social-link">
                        <img src="/fragrancefusion/assets/facebook.png" alt="Facebook" class="social-icon">
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="social-link">
                        <img src="/fragrancefusion/assets/instagram.png" alt="Instagram" class="social-icon">
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>