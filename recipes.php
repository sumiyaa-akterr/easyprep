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

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cuisine_type = isset($_GET['cuisine_type']) ? $_GET['cuisine_type'] : '';
$cooking_time = isset($_GET['cooking_time']) ? $_GET['cooking_time'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$dietary = isset($_GET['dietary']) ? $_GET['dietary'] : '';

// Build the query conditions
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ? OR tags LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if (!empty($cuisine_type)) {
    $where_conditions[] = "cuisine_type = ?";
    $params[] = $cuisine_type;
    $param_types .= 's';
}

if (!empty($cooking_time)) {
    switch ($cooking_time) {
        case 'quick':
            $where_conditions[] = "cooking_time <= 30";
            break;
        case 'medium':
            $where_conditions[] = "cooking_time > 30 AND cooking_time <= 60";
            break;
        case 'long':
            $where_conditions[] = "cooking_time > 60";
            break;
    }
}

if (!empty($difficulty)) {
    $where_conditions[] = "difficulty = ?";
    $params[] = $difficulty;
    $param_types .= 's';
}

if (!empty($dietary)) {
    switch ($dietary) {
        case 'vegetarian':
            $where_conditions[] = "is_vegetarian = 1";
            break;
        case 'vegan':
            $where_conditions[] = "is_vegan = 1";
            break;
        case 'gluten_free':
            $where_conditions[] = "is_gluten_free = 1";
            break;
    }
}

// Build the final query with proper WHERE conditions
$query = "SELECT * FROM recipes";
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}
$query .= " ORDER BY created_at DESC";

// Execute the query
try {
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $recipes_result = $stmt->get_result();
} catch (Exception $e) {
    $recipes_result = null;
}

// Get unique cuisine types for filter
$cuisine_query = "SELECT DISTINCT cuisine_type FROM recipes ORDER BY cuisine_type";
$cuisine_result = $conn->query($cuisine_query);
$cuisine_types = [];
if ($cuisine_result) {
    while ($row = $cuisine_result->fetch_assoc()) {
        $cuisine_types[] = $row['cuisine_type'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes - EasyPrep</title>
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
                <a href="recipes.php" class="active">Recipes</a>
                <a href="meal-plan.php">Meal Plans</a>
                <a href="grocery.php">Grocery Delivery</a>
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
                <h1>Discover Authentic Bengali Recipes</h1>
                <p>Explore our collection of traditional Bengali cuisine. From classic fish curries to aromatic dal recipes, find your next favorite meal from the heart of Bengal.</p>
            </div>
        </section>

        <!-- Search and Filters -->
        <section class="search-filters">
            <div class="search-container">
                <form method="GET" class="search-form">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search recipes..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filters-row">
                        <select name="cuisine_type" class="filter-select">
                            <option value="">All Cuisines</option>
                            <?php foreach ($cuisine_types as $cuisine): ?>
                                <option value="<?php echo $cuisine; ?>" <?php echo $cuisine_type === $cuisine ? 'selected' : ''; ?>>
                                    <?php echo $cuisine; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="cooking_time" class="filter-select">
                            <option value="">Any Time</option>
                            <option value="quick" <?php echo $cooking_time === 'quick' ? 'selected' : ''; ?>>Quick (≤30 min)</option>
                            <option value="medium" <?php echo $cooking_time === 'medium' ? 'selected' : ''; ?>>Medium (31-60 min)</option>
                            <option value="long" <?php echo $cooking_time === 'long' ? 'selected' : ''; ?>>Long (>60 min)</option>
                        </select>
                        
                        <select name="difficulty" class="filter-select">
                            <option value="">Any Difficulty</option>
                            <option value="Easy" <?php echo $difficulty === 'Easy' ? 'selected' : ''; ?>>Easy</option>
                            <option value="Medium" <?php echo $difficulty === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="Hard" <?php echo $difficulty === 'Hard' ? 'selected' : ''; ?>>Hard</option>
                        </select>
                        
                        <select name="dietary" class="filter-select">
                            <option value="">Any Diet</option>
                            <option value="vegetarian" <?php echo $dietary === 'vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                            <option value="vegan" <?php echo $dietary === 'vegan' ? 'selected' : ''; ?>>Vegan</option>
                            <option value="gluten_free" <?php echo $dietary === 'gluten_free' ? 'selected' : ''; ?>>Gluten Free</option>
                        </select>
                        
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        
                        <?php if (!empty($search) || !empty($cuisine_type) || !empty($cooking_time) || !empty($difficulty) || !empty($dietary)): ?>
                            <a href="recipes.php" class="clear-btn">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <!-- Recipes Grid -->
        <section class="recipes-section">
            <div class="recipes-container">
                <?php if ($recipes_result && $recipes_result->num_rows > 0): ?>
                    <div class="recipes-grid">
                        <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                            <div class="recipe-card">
                                <div class="recipe-image">
                                    <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                                    <div class="recipe-badges">
                                        <?php if ($recipe['cuisine_type'] === 'Bangla'): ?>
                                            <span class="badge bengali">Bengali</span>
                                        <?php else: ?>
                                            <span class="badge"><?php echo htmlspecialchars($recipe['cuisine_type']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($recipe['is_vegetarian']): ?>
                                            <span class="badge vegetarian">Vegetarian</span>
                                        <?php endif; ?>
                                        <?php if ($recipe['is_vegan']): ?>
                                            <span class="badge vegan">Vegan</span>
                                        <?php endif; ?>
                                        <?php if ($recipe['is_gluten_free']): ?>
                                            <span class="badge gluten-free">Gluten Free</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="recipe-content">
                                    <div class="recipe-header">
                                        <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
                                        <span class="cuisine-type"><?php echo htmlspecialchars($recipe['cuisine_type']); ?></span>
                                    </div>
                                    <p class="recipe-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
                                    <div class="recipe-meta">
                                        <span class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <?php echo $recipe['cooking_time']; ?> min
                                        </span>
                                        <span class="meta-item">
                                            <i class="fas fa-users"></i>
                                            <?php echo $recipe['servings']; ?> servings
                                        </span>
                                        <span class="meta-item">
                                            <i class="fas fa-fire"></i>
                                            <?php echo $recipe['calories_per_serving']; ?> cal
                                        </span>
                                        <span class="meta-item difficulty-<?php echo strtolower($recipe['difficulty']); ?>">
                                            <i class="fas fa-signal"></i>
                                            <?php echo $recipe['difficulty']; ?>
                                        </span>
                                    </div>
                                    <div class="recipe-actions">
                                        <a href="recipe-detail.php?id=<?php echo $recipe['id']; ?>" class="btn-primary">
                                            <i class="fas fa-eye"></i>
                                            View Recipe
                                        </a>
                                        <button class="btn-secondary save-recipe" data-recipe-id="<?php echo $recipe['id']; ?>">
                                            <i class="fas fa-heart"></i>
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No recipes found</h3>
                        <p>Try adjusting your search criteria or browse all recipes.</p>
                        <a href="recipes.php" class="btn-primary">Browse All Recipes</a>
                    </div>
                <?php endif; ?>
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

    <script src="scripts/recipes.js"></script>
    <script>
        // Save recipe functionality
        document.querySelectorAll('.save-recipe').forEach(button => {
            button.addEventListener('click', function() {
                const recipeId = this.dataset.recipeId;
                
                // Simulate saving recipe
                this.innerHTML = '<i class="fas fa-check"></i> Saved';
                this.classList.add('saved');
                
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-heart"></i> Save';
                    this.classList.remove('saved');
                }, 3000);
            });
        });

        // User menu toggle
        document.querySelector('.user-menu-toggle').addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.querySelector('.user-dropdown').classList.remove('active');
            }
        });
    </script>
</body>
</html>
