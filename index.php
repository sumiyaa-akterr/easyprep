<?php
session_start();
include("connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyPrep: Personalized Meal Planning & Grocery Delivery</title>
    <link rel="stylesheet" href="styles/homepage.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body id="top">
    <header>
        <div class="header-inner">
            <div class="logo-section">
                <a href="#top">
                    <img src="images/easyprep-logo.png" alt="EasyPrep Logo" class="logo">
                </a>
            </div>
            <nav>
                <a href="#how-it-works">How it Works</a>
                <a href="#meal-plans">Meal Plans</a>
                <a href="#grocery-delivery">Grocery Delivery</a>
                <a href="#recipes">Recipes</a>
                <a href="#pricing">Pricing</a>
                <a href="#about">About</a>
            </nav>
            <div class="header-actions">
                <a href="login.php" class="login-btn">Log in</a>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-star"></i>
                    <span>Join 10,000+ families who love EasyPrep</span>
                </div>
                <h1>Stop wondering what to cook.<br>Start enjoying delicious meals.</h1>
                <p>Get personalized weekly meal plans that fit your lifestyle. Whether you're cooking for one or feeding a family, we'll create the perfect plan with shopping lists and easy recipes.</p>
                <div class="hero-cta">
                    <a href="login.php" class="cta-primary">
                        <i class="fas fa-utensils"></i>
                        Start My Meal Plan
                    </a>
                </div>
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Personalized to your preferences</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Weekly grocery lists included</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Same-day grocery delivery</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Fresh ingredients and meals">
                </div>
                <!-- Floating cards removed for a cleaner look -->
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works" id="how-it-works">
            <div class="section-header">
                <h2>How EasyPrep Works</h2>
                <p>Three simple steps to stress-free meal planning</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="step-number">1</div>
                    <h3>Tell Us About You</h3>
                    <p>Share your dietary preferences, allergies, family size, and cooking skill level. We'll customize everything just for you.</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="step-number">2</div>
                    <h3>Get Your Weekly Plan</h3>
                    <p>Receive a complete meal plan with recipes, cooking instructions, and a smart grocery list that combines all ingredients.</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="step-number">3</div>
                    <h3>Shop or Order Delivery</h3>
                    <p>Print your grocery list and shop yourself, or order everything through EasyPrep for same-day delivery to your door.</p>
                </div>
            </div>
        </section>

        <!-- Meal Plans Section -->
        <section class="meal-plans" id="meal-plans">
            <div class="section-header">
                <h2>Perfect for Every Lifestyle</h2>
                <p>Whether you're cooking for one or feeding a family, we have the perfect plan</p>
            </div>
            <div class="plans-grid">
                <div class="plan-card">
                    <div class="plan-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Single Person</h3>
                    <p>Perfect for busy professionals who want to eat healthy without the hassle of meal planning.</p>
                    <ul>
                        <li>5-7 meals per week</li>
                        <li>Single-serving recipes</li>
                        <li>Quick & easy prep</li>
                        <li>Minimal waste</li>
                    </ul>
                    <a href="login.php" class="plan-btn">Choose Plan</a>
                </div>
                <div class="plan-card featured">
                    <div class="popular-badge">Most Popular</div>
                    <div class="plan-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Family Plan</h3>
                    <p>Delicious meals that everyone will love, with portion sizes perfect for families of 2-4 people.</p>
                    <ul>
                        <li>5-7 meals per week</li>
                        <li>Family-friendly recipes</li>
                        <li>Kid-approved options</li>
                        <li>Budget-friendly</li>
                    </ul>
                    <a href="login.php" class="plan-btn">Choose Plan</a>
                </div>
                <div class="plan-card">
                    <div class="plan-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Special Diets</h3>
                    <p>Customized plans for vegetarian, vegan, gluten-free, keto, and other dietary requirements.</p>
                    <ul>
                        <li>Diet-specific recipes</li>
                        <li>Nutritional guidance</li>
                        <li>Ingredient substitutions</li>
                        <li>Health-focused meals</li>
                    </ul>
                    <a href="login.php" class="plan-btn">Choose Plan</a>
                </div>
            </div>
        </section>

        <!-- Grocery Delivery Section -->
        <section class="grocery-delivery" id="grocery-delivery">
            <div class="delivery-content">
                <div class="delivery-text">
                    <h2>Grocery Delivery That Actually Saves Time</h2>
                    <p>Skip the store and get fresh, quality ingredients delivered to your door in as little as 2 hours.</p>
                    <div class="delivery-features">
                        <div class="delivery-feature">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Same-Day Delivery</h4>
                                <p>Order by 2 PM, get delivery by 6 PM</p>
                            </div>
                        </div>
                        <div class="delivery-feature">
                            <i class="fas fa-leaf"></i>
                            <div>
                                <h4>Fresh Ingredients</h4>
                                <p>Hand-picked produce and quality meats</p>
                            </div>
                        </div>
                        <div class="delivery-feature">
                            <i class="fas fa-receipt"></i>
                            <div>
                                <h4>No Hidden Fees</h4>
                                <p>Free delivery on orders over $50</p>
                            </div>
                        </div>
                    </div>
                    <a href="login.php" class="delivery-btn">
                        <i class="fas fa-shopping-cart"></i>
                        Order Groceries Now
                    </a>
                </div>
                <div class="delivery-image">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Fresh groceries delivery">
                </div>
            </div>
        </section>

        <!-- Featured Recipes Section -->
        <section class="featured-recipes" id="recipes">
            <div class="section-header">
                <h2>This Week's Trending Recipes</h2>
                <p>Discover new favorites that our community loves</p>
            </div>
            <div class="recipes-grid">
                <a href="login.php" class="recipe-card">
                    <div class="recipe-image">
                        <img src="https://i.pinimg.com/736x/cc/a8/b0/cca8b07c7ff0e2c451a8607b35e8009a.jpg" alt="Grilled Salmon">
                        <div class="recipe-badge">Popular</div>
                    </div>
                    <div class="recipe-info">
                        <h3>Grilled Salmon with Roasted Vegetables</h3>
                        <div class="recipe-meta">
                            <span><i class="fas fa-clock"></i> 25 min</span>
                            <span><i class="fas fa-users"></i> 2 servings</span>
                            <span><i class="fas fa-fire"></i> 450 cal</span>
                        </div>
                        <p>Fresh salmon fillet grilled to perfection with colorful roasted vegetables and lemon herb butter.</p>
                    </div>
                </a>
                <a href="login.php" class="recipe-card">
                    <div class="recipe-image">
                        <img src="https://i.pinimg.com/1200x/29/c8/b6/29c8b6a5e8108362026d93cb487eca38.jpg" alt="Creamy Pasta">
                        <div class="recipe-badge">Vegetarian</div>
                    </div>
                    <div class="recipe-info">
                        <h3>Creamy Mushroom Pasta</h3>
                        <div class="recipe-meta">
                            <span><i class="fas fa-clock"></i> 20 min</span>
                            <span><i class="fas fa-users"></i> 2 servings</span>
                            <span><i class="fas fa-fire"></i> 520 cal</span>
                        </div>
                        <p>Rich and creamy pasta with wild mushrooms, garlic, and fresh herbs - a vegetarian delight.</p>
                    </div>
                </a>
                <a href="login.php" class="recipe-card">
                    <div class="recipe-image">
                        <img src="https://i.pinimg.com/736x/cd/1f/18/cd1f185753d535325b3d69c1e8fc71ba.jpg" alt="Asian Rice Bowl">
                        <div class="recipe-badge">Quick</div>
                    </div>
                    <div class="recipe-info">
                        <h3>Teriyaki Chicken Rice Bowl</h3>
                        <div class="recipe-meta">
                            <span><i class="fas fa-clock"></i> 30 min</span>
                            <span><i class="fas fa-users"></i> 2 servings</span>
                            <span><i class="fas fa-fire"></i> 580 cal</span>
                        </div>
                        <p>Asian-inspired bowl with tender teriyaki chicken, fluffy rice, and crisp vegetables.</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="pricing" id="pricing">
            <div class="section-header">
                <h2>Simple, Transparent Pricing</h2>
                <p>Choose the plan that works best for you and your family</p>
            </div>
            <div class="pricing-cards">
                <div class="pricing-card">
                    <h3>Starter Plan</h3>
                    <div class="price">
                        <span class="amount">$9.99</span>
                        <span class="per-meal">per week</span>
                    </div>
                    <ul>
                        <li><i class="fas fa-check"></i> 5 meals per week</li>
                        <li><i class="fas fa-check"></i> Personalized recipes</li>
                        <li><i class="fas fa-check"></i> Grocery lists</li>
                        <li><i class="fas fa-check"></i> Basic support</li>
                    </ul>
                    <a href="login.php" class="pricing-btn">Start Free Trial</a>
                </div>
                <div class="pricing-card featured">
                    <div class="popular-badge">Most Popular</div>
                    <h3>Family Plan</h3>
                    <div class="price">
                        <span class="amount">$14.99</span>
                        <span class="per-meal">per week</span>
                    </div>
                    <ul>
                        <li><i class="fas fa-check"></i> 7 meals per week</li>
                        <li><i class="fas fa-check"></i> Family-sized portions</li>
                        <li><i class="fas fa-check"></i> Smart grocery lists</li>
                        <li><i class="fas fa-check"></i> Priority support</li>
                        <li><i class="fas fa-check"></i> Recipe customization</li>
                    </ul>
                    <a href="login.php" class="pricing-btn">Start Free Trial</a>
                </div>
                <div class="pricing-card">
                    <h3>Premium Plan</h3>
                    <div class="price">
                        <span class="amount">$19.99</span>
                        <span class="per-meal">per week</span>
                    </div>
                    <ul>
                        <li><i class="fas fa-check"></i> Unlimited meals</li>
                        <li><i class="fas fa-check"></i> Special diet support</li>
                        <li><i class="fas fa-check"></i> Grocery delivery</li>
                        <li><i class="fas fa-check"></i> 24/7 support</li>
                        <li><i class="fas fa-check"></i> Advanced customization</li>
                    </ul>
                    <a href="login.php" class="pricing-btn">Start Free Trial</a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about" id="about">
            <div class="section-header">
                <h2>About EasyPrep</h2>
                <p>Transforming the way families plan, cook, and enjoy meals together</p>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <div class="about-story">
                        <h3>Our Story</h3>
                        <p>Founded in 2020, EasyPrep was born from a simple frustration: the endless cycle of "what's for dinner?" that plagues busy families everywhere. Our founders, a group of food enthusiasts and tech professionals, decided to solve this problem once and for all.</p>
                        <p>What started as a small meal planning app has grown into a comprehensive platform that helps thousands of families save time, reduce food waste, and enjoy delicious, healthy meals together.</p>
                    </div>
                    <div class="about-mission">
                        <h3>Our Mission</h3>
                        <p>We believe that good food brings people together. Our mission is to eliminate the stress of meal planning while making healthy, delicious eating accessible to everyone, regardless of their cooking skills or dietary restrictions.</p>
                    </div>
                </div>
                <div class="about-cards">
                    <div class="about-card">
                        <i class="fas fa-heart"></i>
                        <h4>Quality First</h4>
                        <p>We partner with local farmers and suppliers to ensure the freshest, highest-quality ingredients for your meals.</p>
                    </div>
                    <div class="about-card">
                        <i class="fas fa-users"></i>
                        <h4>Community Driven</h4>
                        <p>Building a community of food lovers who share recipes, tips, and culinary adventures together.</p>
                    </div>
                    <div class="about-card">
                        <i class="fas fa-leaf"></i>
                        <h4>Sustainability</h4>
                        <p>Committed to reducing food waste and promoting sustainable eating practices for a better future.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <h2>Ready to transform your meal planning?</h2>
                <p>Join thousands of families who have made cooking enjoyable again.</p>
                <div class="cta-buttons">
                    <a href="login.php" class="cta-primary">
                        <i class="fas fa-rocket"></i>
                        Start Your Free Trial
                    </a>
                </div>
                <p class="cta-note">14-day free trial • Cancel anytime • No commitment required</p>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="images/easyprep-logo.png" alt="EasyPrep Logo" class="footer-logo-img">
                    <span>EasyPrep</span>
                </div>
                <p>Making meal planning simple, delicious, and stress-free for everyone.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Product</h4>
                <a href="#">Meal Plans</a>
                <a href="#">Grocery Delivery</a>
                <a href="#">Recipes</a>
                <a href="#">Pricing</a>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <a href="#">Help Center</a>
                <a href="#">Contact Us</a>
                <a href="#">FAQ</a>
                <a href="#">Live Chat</a>
            </div>
            <div class="footer-section">
                <h4>Company</h4>
                <a href="#">About Us</a>
                <a href="#">Careers</a>
                <a href="#">Blog</a>
                <a href="#">Press</a>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
                <a href="#">Refund Policy</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 EasyPrep. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('nav').classList.toggle('active');
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
