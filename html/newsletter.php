<style>
    /* Newsletter Section */
.newsletter {
  width: 100vw; /* Full viewport width */
  margin-left: calc(-50vw + 50%); /* Center it back */
  padding: 81px 80px;
  background-color: #1a1a1a;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: 50px;
}

.newsletter-container {
  display: flex;
  width: 100%;
  max-width: 589px;
  flex-direction: column;
  align-items: center;
}

.newsletter-title {
  color: white;
  font-size: 2.5rem;
  font-weight: bold;
  text-align: center;
}

.newsletter-text {
  color: white;
  text-align: center;
  margin-top: 24px;
}

.newsletter-form {
  align-self: stretch;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-top: 32px;
}

.newsletter-input {
  border-radius: 4px;
  background-color: white;
  color: #999999;
  flex-grow: 1;
  flex-shrink: 0;
  flex-basis: 0;
  padding: 19px 24px;
  border: none;
  font-size: 1rem;
}

.newsletter-btn {
  border-radius: 4px;
  background-color: white;
  color: #1a1a1a;
  font-weight: 500;
  text-align: center;
  padding: 16px 32px;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s;
}

.newsletter-btn:hover {
  background-color: #f3f3f3;
}

.thank-you {
  align-self: stretch;
  margin-top: 32px;
  color: white;
  text-align: center;
}

.hidden {
  display: none;
}
</style>
<!-- filepath: z:\xampp\htdocs\fragrancefusion\includes\newsletter.php -->
<section class="newsletter">
    <div class="newsletter-container">
        <h2 class="newsletter-title">Subscribe to Our Newsletter</h2>
        <p class="newsletter-text">
            Stay updated with our latest collections and exclusive offers
        </p>
        <div id="newsletter-form-container">
            <form id="newsletter-form" class="newsletter-form">
                <input
                    type="email"
                    id="email"
                    placeholder="Enter your email"
                    required
                    class="newsletter-input"
                    aria-label="Email address"
                />
                <button type="submit" class="newsletter-btn">Subscribe Now</button>
            </form>
        </div>
        <div id="thank-you-message" class="thank-you hidden">
            Thank you for subscribing!
        </div>
    </div>
</section>