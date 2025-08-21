<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Load user
$user_email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check if coming from meal plan
$from_meal_plan = isset($_GET['from_meal_plan']) && $_GET['from_meal_plan'] === 'true';
$meal_plan_id = $_GET['meal_plan_id'] ?? null;

// Get current meal plan if coming from meal planner
$current_meal_plan = null;
if ($from_meal_plan) {
    if ($meal_plan_id) {
        // Get specific meal plan
        $plan_query = "SELECT * FROM meal_plans WHERE id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1";
        $plan_stmt = $conn->prepare($plan_query);
        $plan_stmt->bind_param("ii", $meal_plan_id, $user['id']);
        $plan_stmt->execute();
        $plan_result = $plan_stmt->get_result();
        $current_meal_plan = $plan_result->fetch_assoc();
    } else {
        // Get latest meal plan
        $latest_query = "SELECT * FROM meal_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $latest_stmt = $conn->prepare($latest_query);
        $latest_stmt->bind_param("i", $user['id']);
        $latest_stmt->execute();
        $latest_result = $latest_stmt->get_result();
        $current_meal_plan = $latest_result->fetch_assoc();
    }
}

// Get recent meal plans for selector
$recent_plans_query = "SELECT id, name, created_at, servings FROM meal_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$recent_stmt = $conn->prepare($recent_plans_query);
$recent_stmt->bind_param("i", $user['id']);
$recent_stmt->execute();
$recent_plans = $recent_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyPrep Grocery Store</title>
    <link rel="stylesheet" href="styles/recipes.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .grocery-hero {
            background: linear-gradient(135deg, #fff6e0 0%, #f1c27d 50%, #e0ac69 100%);
            color: #5d4037;
            padding: 64px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .grocery-hero .grocery-container { position: relative; z-index: 2; }
        
        .grocery-hero h1 {
            font-size: 2.8rem;
            margin-bottom: 12px;
            font-weight: 800;
            letter-spacing: -1px;
            font-family: 'Nunito', 'Inter', 'Poppins', sans-serif;
            color: #5d4037;
        }
        
        .grocery-hero p {
            font-size: 1.25rem;
            opacity: 0.92;
            max-width: 720px;
            margin: 0 auto;
            line-height: 1.7;
            font-family: 'Nunito', 'Inter', 'Poppins', sans-serif;
            color: #8d5524;
        }
        
        .grocery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .grocery-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .grocery-sidebar {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .grocery-main {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .category-section {
            margin-bottom: 30px;
        }
        
        .category-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #5d4037;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .category-card {
            background: #f8f4f0;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #5d4037;
        }
        
        .category-card:hover {
            border-color: #8d5524;
            background: #fff6e0;
            transform: translateY(-3px);
        }
        
        .category-card i {
            font-size: 2rem;
            color: #8d5524;
            margin-bottom: 10px;
        }
        
        .category-card h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }
        
        .search-section {
            margin-bottom: 30px;
        }
        
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #8d5524;
        }
        
        .search-btn {
            background: #8d5524;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #5d4037;
            transform: translateY(-2px);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .product-card:hover {
            border-color: #8d5524;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(141, 85, 36, 0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f8f4f0;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #5d4037;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .product-category {
            color: #8d5524;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #5d4037;
            margin-bottom: 15px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .add-to-cart-btn {
            flex: 1;
            background: #8d5524;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-to-cart-btn:hover {
            background: #5d4037;
        }
        
        .quantity-input {
            width: 60px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .cart-summary {
            background: #f8f4f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .cart-summary h3 {
            color: #5d4037;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-total {
            font-weight: 700;
            color: #8d5524;
            font-size: 1.1rem;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #8d5524;
        }
        
        .checkout-btn {
            width: 100%;
            background: #e63946; /* strawberry red */
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .checkout-btn:hover {
            background: #d62839; /* darker strawberry red */
            transform: translateY(-2px);
        }
        
        .meal-plan-integration {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .meal-plan-integration h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .meal-plan-integration p {
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .meal-plan-btn {
            background: white;
            color: #4caf50;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .meal-plan-btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .receipt-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .receipt-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f1c27d;
            padding-bottom: 20px;
        }
        
        .receipt-header h2 {
            color: #4caf50;
            margin-bottom: 10px;
        }
        
        .receipt-items {
            margin-bottom: 25px;
        }
        
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .receipt-total {
            font-weight: 700;
            font-size: 1.2rem;
            color: #8d5524;
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f1c27d;
        }
        
        .receipt-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .close-receipt {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
        
        .close-receipt:hover {
            color: #333;
        }
        
        @media (max-width: 768px) {
            .grocery-grid {
                grid-template-columns: 1fr;
            }
            
            .grocery-hero h1 {
                font-size: 2rem;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
    </style>
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
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
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

    <!-- Hero Section -->
    <section class="grocery-hero">
        <div class="grocery-container">
            <h1>EasyPrep Grocery Store</h1>
            <p>Fresh ingredients, quality products, and smart shopping from your meal plans</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="grocery-container">
        <div class="grocery-grid">
            <!-- Sidebar -->
            <div class="grocery-sidebar">
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3><i class="fas fa-shopping-cart"></i> Your Cart</h3>
                    <div id="cart-items">
                        <p>Your cart is empty</p>
                    </div>
                    <div id="cart-summary-info" style="display: none;">
                        <div class="cart-item">
                            <span>Subtotal:</span>
                            <span id="cart-subtotal">$0.00</span>
                        </div>
                        <div class="cart-item">
                            <span>Tax (8.5%):</span>
                            <span id="cart-tax">$0.00</span>
                        </div>
                        <div class="cart-total">
                            <span>Total: </span>
                            <span id="cart-total">$0.00</span>
                        </div>
                        <button class="checkout-btn" onclick="checkout()">
                            <i class="fas fa-credit-card"></i> Checkout
                        </button>
                    </div>
                </div>

                <!-- Meal Plan Integration -->
                <?php if ($from_meal_plan && $current_meal_plan): ?>
                <div class="meal-plan-integration">
                    <h3><i class="fas fa-utensils"></i> Smart Shopping</h3>
                    <p>Order ingredients from your meal plan: <strong><?php echo htmlspecialchars($current_meal_plan['name']); ?></strong></p>
                    <button class="meal-plan-btn" onclick="orderFromMealPlan()">
                        <i class="fas fa-magic"></i> Order from Meal Plan
                    </button>
                </div>
                <?php endif; ?>

                <!-- Categories -->
                <div class="category-section">
                    <h3 class="category-title"><i class="fas fa-tags"></i> Categories</h3>
                    <div class="category-grid">
                        <a href="#" class="category-card" onclick="filterByCategory('all')">
                            <i class="fas fa-th-large"></i>
                            <h3>All Items</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('fruits')">
                            <i class="fas fa-apple-alt"></i>
                            <h3>Fruits</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('vegetables')">
                            <i class="fas fa-carrot"></i>
                            <h3>Vegetables</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('meat')">
                            <i class="fas fa-drumstick-bite"></i>
                            <h3>Meat & Fish</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('dairy')">
                            <i class="fas fa-cheese"></i>
                            <h3>Dairy</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('pantry')">
                            <i class="fas fa-wheat-awn"></i>
                            <h3>Pantry</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('beverages')">
                            <i class="fas fa-wine-glass"></i>
                            <h3>Beverages</h3>
                        </a>
                        <a href="#" class="category-card" onclick="filterByCategory('snacks')">
                            <i class="fas fa-cookie-bite"></i>
                            <h3>Snacks</h3>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grocery-main">
                <!-- Search Section -->
                <div class="search-section">
                    <div class="search-bar">
                        <input type="text" id="search-input" class="search-input" placeholder="Search for products...">
                        <button class="search-btn" onclick="searchProducts()">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div id="products-container">
                    <div class="products-grid" id="products-grid">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receipt-modal" class="receipt-modal">
        <div class="receipt-content">
            <button class="close-receipt" onclick="closeReceipt()">&times;</button>
            <div id="receipt-content">
                <!-- Receipt content will be generated here -->
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let cart = [];
        let products = [];
        let currentCategory = 'all';

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeUserMenu();
            loadProducts();
            updateCartDisplay();
        });

        // User menu functionality
        function initializeUserMenu() {
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('active');
                });
                
                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target)) {
                        userDropdown.classList.remove('active');
                    }
                });
            }
        }

        // Load products
        function loadProducts() {
            // Curated product database with high-quality images
            products = [
                // Fruits
                { id: 1, name: "Fresh Organic Apples", category: "fruits", price: 4.99, image: "https://images.unsplash.com/photo-1570913149827-d2ac84ab3f9a?w=400&h=300&fit=crop", unit: "1lb bag" },
                { id: 2, name: "Ripe Bananas", category: "fruits", price: 2.49, image: "https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&h=300&fit=crop", unit: "1 bunch" },
                { id: 3, name: "Sweet Strawberries", category: "fruits", price: 5.99, image: "https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=400&h=300&fit=crop", unit: "1lb container" },
                { id: 4, name: "Fresh Oranges", category: "fruits", price: 3.99, image: "https://images.unsplash.com/photo-1542444459-db63c7e56e0c?w=400&h=300&fit=crop", unit: "1lb bag" },
                
                // Vegetables
                { id: 5, name: "Organic Carrots", category: "vegetables", price: 2.99, image: "https://images.unsplash.com/photo-1447175008436-170170e7900e?w=400&h=300&fit=crop", unit: "1lb bag" },
                { id: 6, name: "Fresh Broccoli", category: "vegetables", price: 3.49, image: "https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400&h=300&fit=crop", unit: "1 head" },
                { id: 7, name: "Ripe Tomatoes", category: "vegetables", price: 4.49, image: "https://images.unsplash.com/photo-1556761175-4b46a572b786?w=400&h=300&fit=crop", unit: "1lb container" },
                { id: 8, name: "Fresh Spinach", category: "vegetables", price: 3.99, image: "https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=300&fit=crop", unit: "1 bag" },
                { id: 9, name: "Bell Peppers", category: "vegetables", price: 2.99, image: "https://images.unsplash.com/photo-1525609004558-c66e0fc133c1?w=400&h=300&fit=crop", unit: "3 pack" },
                { id: 10, name: "Fresh Onions", category: "vegetables", price: 2.49, image: "https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=400&h=300&fit=crop", unit: "1lb bag" },
                
                // Meat & Fish
                { id: 11, name: "Fresh Chicken Breast", category: "meat", price: 8.99, image: "https://images.unsplash.com/photo-1562967914-608f82629710?w=400&h=300&fit=crop", unit: "1lb" },
                { id: 12, name: "Atlantic Salmon", category: "meat", price: 12.99, image: "https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop", unit: "1lb" },
                { id: 13, name: "Ground Beef", category: "meat", price: 7.99, image: "https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=400&h=300&fit=crop", unit: "1lb" },
                { id: 14, name: "Fresh Pork Chops", category: "meat", price: 9.99, image: "https://images.unsplash.com/photo-1562967914-608f82629710?w=400&h=300&fit=crop", unit: "1lb" },
                
                // Dairy
                { id: 15, name: "Whole Milk", category: "dairy", price: 4.29, image: "https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&h=300&fit=crop", unit: "1 gallon" },
                { id: 16, name: "Sharp Cheddar Cheese", category: "dairy", price: 5.99, image: "https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=300&fit=crop", unit: "8oz block" },
                { id: 17, name: "Fresh Eggs", category: "dairy", price: 3.99, image: "https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=400&h=300&fit=crop", unit: "12 count" },
                { id: 18, name: "Greek Yogurt", category: "dairy", price: 4.99, image: "https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&h=300&fit=crop", unit: "32oz container" },
                
                // Pantry
                { id: 19, name: "Basmati Rice", category: "pantry", price: 6.99, image: "https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=300&fit=crop", unit: "2lb bag" },
                { id: 20, name: "Extra Virgin Olive Oil", category: "pantry", price: 8.99, image: "https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&h=300&fit=crop", unit: "16oz bottle" },
                { id: 21, name: "Whole Wheat Bread", category: "pantry", price: 3.99, image: "https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop", unit: "1 loaf" },
                { id: 22, name: "Pasta Spaghetti", category: "pantry", price: 2.99, image: "https://images.unsplash.com/photo-1551892374-ecf8754cf8b0?w=400&h=300&fit=crop", unit: "1lb box" },
                { id: 23, name: "Black Beans", category: "pantry", price: 1.99, image: "https://images.unsplash.com/photo-1543339494-bfcd4a7faa61?w=400&h=300&fit=crop", unit: "15oz can" },
                { id: 24, name: "Quinoa", category: "pantry", price: 7.99, image: "https://images.unsplash.com/photo-1505575972945-282f11aee3ee?w=400&h=300&fit=crop", unit: "1lb bag" },
                
                // Beverages
                { id: 25, name: "Fresh Orange Juice", category: "beverages", price: 4.99, image: "https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=400&h=300&fit=crop", unit: "64oz bottle" },
                { id: 26, name: "Sparkling Water", category: "beverages", price: 3.99, image: "https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&h=300&fit=crop", unit: "12 pack" },
                { id: 27, name: "Green Tea", category: "beverages", price: 5.99, image: "https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=400&h=300&fit=crop", unit: "40 tea bags" },
                
                // Snacks
                { id: 28, name: "Mixed Nuts", category: "snacks", price: 8.99, image: "https://images.unsplash.com/photo-1599599810769-bcde5a160d32?w=400&h=300&fit=crop", unit: "16oz container" },
                { id: 29, name: "Dark Chocolate", category: "snacks", price: 4.99, image: "https://images.unsplash.com/photo-1511381939415-e44015466834?w=400&h=300&fit=crop", unit: "3.5oz bar" },
                { id: 30, name: "Popcorn Kernels", category: "snacks", price: 3.99, image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop", unit: "1lb bag" }
            ];
            
            displayProducts(products);
        }

        // Display products
        function displayProducts(productsToShow) {
            const grid = document.getElementById('products-grid');
            grid.innerHTML = '';
            
            productsToShow.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='images/easyprep-logo.png'">
                    <div class="product-info">
                        <h3 class="product-name">${product.name}</h3>
                        <p class="product-category">${product.category.charAt(0).toUpperCase() + product.category.slice(1)} • ${product.unit}</p>
                        <div class="product-price">$${product.price.toFixed(2)}</div>
                        <div class="product-actions">
                            <input type="number" class="quantity-input" value="1" min="1" max="10">
                            <button class="add-to-cart-btn" onclick="addToCart(${product.id})">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                `;
                grid.appendChild(productCard);
            });
        }

        // Search products
        function searchProducts() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const filteredProducts = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                product.category.toLowerCase().includes(searchTerm)
            );
            displayProducts(filteredProducts);
        }

        // Filter by category
        function filterByCategory(category) {
            currentCategory = category;
            const filteredProducts = category === 'all' ? 
                products : 
                products.filter(product => product.category === category);
            displayProducts(filteredProducts);
        }

        // Add to cart
        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            const quantityInput = event.target.parentElement.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value);
            
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: quantity,
                    image: product.image
                });
            }
            
            quantityInput.value = 1;
            updateCartDisplay();
            
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.style.background = '#e63946';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '#8d5524';
            }, 1500);
        }

        // Update cart display
        function updateCartDisplay() {
            const cartItems = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary-info');
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p>Your cart is empty</p>';
                cartSummary.style.display = 'none';
                return;
            }
            
            let cartHTML = '';
            let subtotal = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                cartHTML += `
                    <div class="cart-item">
                        <span>${item.name} x${item.quantity}</span>
                        <span>$${itemTotal.toFixed(2)}</span>
                    </div>
                `;
            });
            
            cartItems.innerHTML = cartHTML;
            
            const tax = subtotal * 0.085;
            const total = subtotal + tax;
            
            document.getElementById('cart-subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('cart-tax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('cart-total').textContent = `$${total.toFixed(2)}`;
            cartSummary.style.display = 'block';
        }

        // Checkout
        function checkout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            generateReceipt();
        }

        // Generate receipt
        function generateReceipt() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.085;
            const total = subtotal + tax;
            
            const receiptHTML = `
                <div class="receipt-header">
                    <h2><i class=\"fas fa-check-circle\"></i> Order Confirmed!</h2>
                    <p>Thank you for your order from EasyPrep Grocery</p>
                </div>
                <div class="receipt-items">
                    ${cart.map(item => `
                        <div class="receipt-item">
                            <span>${item.name} x${item.quantity}</span>
                            <span>$${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
                <div class="receipt-total">
                    <div>Subtotal: $${subtotal.toFixed(2)}</div>
                    <div>Tax: $${tax.toFixed(2)}</div>
                    <div style="font-size: 1.4rem; margin-top: 10px;">Total: $${total.toFixed(2)}</div>
                </div>
                <div class="receipt-actions">
                    <button onclick="printReceipt()" class="btn-primary">Print Receipt</button>
                    <button onclick="closeReceipt()" class="btn-secondary">Close</button>
                </div>
            `;
            
            document.getElementById('receipt-content').innerHTML = receiptHTML;
            document.getElementById('receipt-modal').style.display = 'block';
            
            // Clear cart after order
            cart = [];
            updateCartDisplay();
        }

        // Close receipt
        function closeReceipt() {
            document.getElementById('receipt-modal').style.display = 'none';
        }

        // Print receipt
        function printReceipt() {
            window.print();
        }

        // Order from meal plan
        function orderFromMealPlan() {
            // This would integrate with your meal plan system
            alert('Ordering from meal plan... This feature integrates with your existing meal planner!');
        }

        // Search on Enter key
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    </script>
</body>
</html>
