<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user data using the email
$email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// If user not found, redirect to login
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Delivery - EasyPrep</title>
    <link rel="stylesheet" href="styles/recipes.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-inner">
            <div class="logo-section">
                <a href="index.php">
                    <img src="images/easyprep-logo.png" alt="EasyPrep Logo" class="logo">
                </a>
            </div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="recipes.php">Recipes</a>
                <a href="meal-plan.php">Meal Plans</a>
                <a href="grocery.php" class="active">Grocery Delivery</a>
            </nav>
            <div class="header-actions">
                <div class="user-menu">
                    <button class="user-menu-toggle">
                        <i class="fas fa-user user-avatar"></i>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="#"><i class="fas fa-user"></i> Profile</a>
                        <a href="#"><i class="fas fa-cog"></i> Settings</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main class="recipes-main">
        <!-- Hero Section -->
        <section class="recipes-hero">
            <div class="hero-content">
                <h1>Grocery Delivery</h1>
                <p>Order fresh ingredients and groceries directly to your door. Browse our selection of quality products or use your meal plan's shopping list.</p>
            </div>
        </section>

        <!-- Cart Summary -->
        <section class="search-filters">
            <div class="search-container">
                <div class="cart-summary">
                    <div class="cart-header">
                        <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
                        <div class="cart-total">
                            <span class="total-items">0 items</span>
                            <span class="total-price">$0.00</span>
                        </div>
                    </div>
                    <div class="cart-items" id="cart-items">
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Your cart is empty. Start adding items below!</p>
                        </div>
                    </div>
                    <div class="cart-actions">
                        <button class="btn-secondary" id="import-meal-plan">
                            <i class="fas fa-utensils"></i>
                            Import from Meal Plan
                        </button>
                        <button class="btn-primary" id="checkout-btn" disabled>
                            <i class="fas fa-credit-card"></i>
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="search-filters">
            <div class="search-container">
                <div class="category-tabs">
                    <button class="category-tab active" data-category="all">
                        <i class="fas fa-th"></i> All Items
                    </button>
                    <button class="category-tab" data-category="produce">
                        <i class="fas fa-apple-alt"></i> Produce
                    </button>
                    <button class="category-tab" data-category="meat">
                        <i class="fas fa-drumstick-bite"></i> Meat & Seafood
                    </button>
                    <button class="category-tab" data-category="dairy">
                        <i class="fas fa-cheese"></i> Dairy & Eggs
                    </button>
                    <button class="category-tab" data-category="pantry">
                        <i class="fas fa-jar"></i> Pantry
                    </button>
                    <button class="category-tab" data-category="frozen">
                        <i class="fas fa-snowflake"></i> Frozen
                    </button>
                </div>
            </div>
        </section>

        <!-- Grocery Items -->
        <section class="recipes-section">
            <div class="recipes-container">
                <div class="recipes-grid" id="grocery-grid">
                    <!-- Produce -->
                    <div class="recipe-card grocery-item" data-category="produce" data-item="bananas">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Bananas">
                            <div class="recipe-badges">
                                <span class="badge organic">Organic</span>
                            </div>
                        </div>
                        <div class="recipe-content">
                            <h3>Fresh Bananas</h3>
                            <p>Sweet and ripe bananas, per bunch</p>
                            <div class="grocery-meta">
                                <span class="price">$2.99</span>
                                <span class="unit">per bunch</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Fresh Bananas" data-price="2.99" data-unit="bunch">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="recipe-card grocery-item" data-category="produce" data-item="tomatoes">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1592924357228-91a4daadcfea?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Tomatoes">
                        </div>
                        <div class="recipe-content">
                            <h3>Roma Tomatoes</h3>
                            <p>Fresh, ripe tomatoes perfect for cooking</p>
                            <div class="grocery-meta">
                                <span class="price">$3.49</span>
                                <span class="unit">per lb</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Roma Tomatoes" data-price="3.49" data-unit="lb">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Meat -->
                    <div class="recipe-card grocery-item" data-category="meat" data-item="chicken">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1604503468506-a8da13d82791?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Chicken Breast">
                        </div>
                        <div class="recipe-content">
                            <h3>Chicken Breast</h3>
                            <p>Boneless, skinless chicken breast</p>
                            <div class="grocery-meta">
                                <span class="price">$7.99</span>
                                <span class="unit">per lb</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Chicken Breast" data-price="7.99" data-unit="lb">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="recipe-card grocery-item" data-category="meat" data-item="salmon">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Salmon Fillet">
                        </div>
                        <div class="recipe-content">
                            <h3>Atlantic Salmon</h3>
                            <p>Fresh salmon fillet, wild caught</p>
                            <div class="grocery-meta">
                                <span class="price">$12.99</span>
                                <span class="unit">per lb</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Atlantic Salmon" data-price="12.99" data-unit="lb">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Dairy -->
                    <div class="recipe-card grocery-item" data-category="dairy" data-item="milk">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1550583724-b2692b85b150?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Whole Milk">
                        </div>
                        <div class="recipe-content">
                            <h3>Whole Milk</h3>
                            <p>Fresh whole milk, 1 gallon</p>
                            <div class="grocery-meta">
                                <span class="price">$4.29</span>
                                <span class="unit">per gallon</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Whole Milk" data-price="4.29" data-unit="gallon">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="recipe-card grocery-item" data-category="dairy" data-item="eggs">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Eggs">
                        </div>
                        <div class="recipe-content">
                            <h3>Large Eggs</h3>
                            <p>Farm fresh large eggs, dozen</p>
                            <div class="grocery-meta">
                                <span class="price">$3.99</span>
                                <span class="unit">per dozen</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Large Eggs" data-price="3.99" data-unit="dozen">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pantry -->
                    <div class="recipe-card grocery-item" data-category="pantry" data-item="rice">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Basmati Rice">
                        </div>
                        <div class="recipe-content">
                            <h3>Basmati Rice</h3>
                            <p>Premium long grain basmati rice, 5lb bag</p>
                            <div class="grocery-meta">
                                <span class="price">$8.99</span>
                                <span class="unit">per 5lb bag</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Basmati Rice" data-price="8.99" data-unit="5lb bag">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="recipe-card grocery-item" data-category="pantry" data-item="pasta">
                        <div class="recipe-image">
                            <img src="https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Spaghetti">
                        </div>
                        <div class="recipe-content">
                            <h3>Spaghetti Pasta</h3>
                            <p>Italian spaghetti pasta, 1lb box</p>
                            <div class="grocery-meta">
                                <span class="price">$2.49</span>
                                <span class="unit">per box</span>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-primary add-to-cart" data-name="Spaghetti Pasta" data-price="2.49" data-unit="box">
                                    <i class="fas fa-plus"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
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
                <a href="meal-plan.php">Meal Plans</a>
                <a href="grocery.php">Grocery Delivery</a>
                <a href="recipes.php">Recipes</a>
                <a href="index.php#pricing">Pricing</a>
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
                <a href="index.php#about">About Us</a>
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
        // Cart functionality
        let cart = [];
        let cartTotal = 0;

        // Category filtering
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const category = this.dataset.category;
                const items = document.querySelectorAll('.grocery-item');

                items.forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const name = this.dataset.name;
                const price = parseFloat(this.dataset.price);
                const unit = this.dataset.unit;

                // Check if item already exists in cart
                const existingItem = cart.find(item => item.name === name);
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({
                        name: name,
                        price: price,
                        unit: unit,
                        quantity: 1
                    });
                }

                updateCartDisplay();
                this.innerHTML = '<i class="fas fa-check"></i> Added';
                this.classList.add('added');
                
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-plus"></i> Add to Cart';
                    this.classList.remove('added');
                }, 2000);
            });
        });

        function updateCartDisplay() {
            const cartItems = document.getElementById('cart-items');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            document.querySelector('.total-items').textContent = `${totalItems} items`;
            document.querySelector('.total-price').textContent = `$${cartTotal.toFixed(2)}`;

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Your cart is empty. Start adding items below!</p>
                    </div>
                `;
                document.getElementById('checkout-btn').disabled = true;
            } else {
                cartItems.innerHTML = cart.map(item => `
                    <div class="cart-item">
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            <p>$${item.price.toFixed(2)} per ${item.unit}</p>
                        </div>
                        <div class="item-controls">
                            <button class="qty-btn" onclick="changeQuantity('${item.name}', -1)">-</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="qty-btn" onclick="changeQuantity('${item.name}', 1)">+</button>
                            <button class="remove-btn" onclick="removeItem('${item.name}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="item-total">$${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                `).join('');
                document.getElementById('checkout-btn').disabled = false;
            }
        }

        function changeQuantity(itemName, change) {
            const item = cart.find(item => item.name === itemName);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.name !== itemName);
                }
                updateCartDisplay();
            }
        }

        function removeItem(itemName) {
            cart = cart.filter(item => item.name !== itemName);
            updateCartDisplay();
        }

        // Import from meal plan
        document.getElementById('import-meal-plan').addEventListener('click', function() {
            // This would integrate with the meal plan system
            alert('This feature will automatically add ingredients from your current meal plan to your cart.');
        });

        // Checkout
        document.getElementById('checkout-btn').addEventListener('click', function() {
            if (cart.length > 0) {
                alert(`Proceeding to checkout with ${cart.length} items totaling $${cartTotal.toFixed(2)}. This would normally redirect to a payment page.`);
            }
        });
    </script>
</body>
</html>
