<?php
// Start the session at the very beginning
session_start();

// Include the database connection
require_once __DIR__ . '/../config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Fragrance Fusion</title>
    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/aboutus.css">
  </head>
  <body>

    <?php include __DIR__ . '/header.php'; ?>


    <div class="container">
      <main>
        <section class="hero">
          <h1 class="hero-title">Our Story</h1>
          <p class="hero-text">
            Founded in 2025, Fragrance Fusion emerged from a passion for creating
            unique, memorable scents that tell stories and capture moments.
          </p>
        </section>

        <section class="stats">
          <div class="stats-container">
            <div class="stat-card">
              <div class="stat-number">50+</div>
              <div class="stat-label">Unique Fragrances</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">25</div>
              <div class="stat-label">Countries</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">100%</div>
              <div class="stat-label">Sustainable</div>
            </div>
          </div>
        </section>

        <section class="team">
            <h2 class="team-title">Meet Our Team</h2>
          
            <div class="team-row first-row">
              <div class="team-col-left">
                <div class="team-members-row">
                  <div class="team-member">
                    <img
                      src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/915225df3180b816f34ae9698cbd744a9455ceff?placeholderIfAbsent=true"
                      alt="Gerald Elli Ramos - Founder & Master Perfumer"
                      class="team-img"
                    />
                    <h3 class="member-name">Gerald Elli Ramos</h3>
                    <p class="member-title">Founder & Master Perfumer</p>
                    <p class="member-desc">
                      With 15 years of experience in fragrance creation, Gerald
                      leads our creative vision.
                    </p>
                  </div>
                  <div class="team-member">
                    <img
                      src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/ac103aa61414a58363e5f863302459d5bc1e907a?placeholderIfAbsent=true"
                      alt="Clyde Salvador - Creative Director"
                      class="team-img"
                    />
                    <h3 class="member-name">Clyde Salvador</h3>
                    <p class="member-title">Creative Director</p>
                    <p class="member-desc">
                      Bringing artistic direction to every collection we create.
                    </p>
                  </div>
                </div>
              </div>
              <div class="team-col-right">
                <div class="team-member">
                  <img
                    src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/f6079def7921f89905799913cf649d6b5aec24f1?placeholderIfAbsent=true"
                    alt="Joshua Santos - Head of Product Development"
                    class="team-img"
                  />
                  <h3 class="member-name">Joshua Santos</h3>
                  <p class="member-title">Head of Product Development</p>
                  <p class="member-desc">
                    Ensuring each fragrance meets our exceptional standards.
                  </p>
                </div>
              </div>
            </div>
          
            <div class="team-row second-row">
              <div class="team-member">
                <img
                  src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/605002cc6c2d269b3faa9f2e81d1d3aa260656c5?placeholderIfAbsent=true"
                  alt="Sean Soliven - Chief Innovation Officer"
                  class="team-img"
                />
                <h3 class="member-name">Sean Soliven</h3>
                <p class="member-title">Chief Innovation Officer</p>
                <p class="member-desc">
                  Leading our research and development initiatives.
                </p>
              </div>
              <div class="team-member">
                <img
                  src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/d0eac87e7cdcc2f5db7cc8231f6ff8bfa794fc62?placeholderIfAbsent=true"
                  alt="Jude Andres - Global Marketing Director"
                  class="team-img"
                />
                <h3 class="member-name">Jude Andres</h3>
                <p class="member-title">Global Marketing Director</p>
                <p class="member-desc">
                  Crafting our brand story across international markets.
                </p>
              </div>
              <div class="team-member">
                <img
                  src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/3b4402d8fc4c46ace54c10d72c06f8de99310c7d?placeholderIfAbsent=true"
                  alt="James Rabena - Sustainability Director"
                  class="team-img"
                />
                <h3 class="member-name">James Rabena</h3>
                <p class="member-title">Sustainability Director</p>
                <p class="member-desc">
                  Championing our environmental initiatives.
                </p>
              </div>
            </div>
          </section>
    </div>
    </main>

        <!-- footer to pre -->
        <?php include __DIR__ . '/newsletter.php'; ?>   
        <!-- footer to pre -->
        <?php include __DIR__ . '/footer.php'; ?>   

    <script src="/js/aboutus.js"></script>
  </body>
</html>

