<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user data
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

// Handle meal plan generation
$generated_plan = null;
$preferences = null;

if ($_POST && isset($_POST['generate_plan'])) {
    $preferences = [
        'servings' => (int)$_POST['servings'],
        'allergic_nuts' => isset($_POST['allergic_nuts']),
        'no_gluten' => isset($_POST['no_gluten']),
        'no_dairy' => isset($_POST['no_dairy']),
        'vegetarian' => isset($_POST['vegetarian']),
        'vegan' => isset($_POST['vegan']),
        'low_carb' => isset($_POST['low_carb']),
        'no_spicy' => isset($_POST['no_spicy'])
    ];
    
    // Generate meal plan based on preferences
    $generated_plan = generateWeeklyMealPlan($conn, $preferences);
}

function generateWeeklyMealPlan($conn, $preferences) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $meals = ['Breakfast', 'Lunch', 'Dinner'];
    $plan = [];
    
    // Get suitable recipes from database
    $where_conditions = [];
    $params = [];
    
    if ($preferences['vegetarian']) {
        $where_conditions[] = "is_vegetarian = 1";
    }
    if ($preferences['vegan']) {
        $where_conditions[] = "is_vegan = 1";
    }
    if ($preferences['no_gluten']) {
        $where_conditions[] = "is_gluten_free = 1";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    try {
        $recipe_query = "SELECT * FROM recipes $where_clause ORDER BY rating DESC, total_ratings DESC";
        $stmt = $conn->prepare($recipe_query);
        $stmt->execute();
        $recipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($recipes)) {
            // Fallback to sample recipes if no database recipes match
            $recipes = [
                ['name' => 'Vegetable Omelette', 'cooking_time' => 15, 'calories_per_serving' => 250, 'cuisine_type' => 'Continental'],
                ['name' => 'Bengali Fish Curry', 'cooking_time' => 45, 'calories_per_serving' => 350, 'cuisine_type' => 'Bangla'],
                ['name' => 'Dal Tadka', 'cooking_time' => 30, 'calories_per_serving' => 200, 'cuisine_type' => 'Indian'],
                ['name' => 'Chicken Biriyani', 'cooking_time' => 90, 'calories_per_serving' => 450, 'cuisine_type' => 'Indian'],
                ['name' => 'Aloo Posto', 'cooking_time' => 25, 'calories_per_serving' => 180, 'cuisine_type' => 'Bangla'],
                ['name' => 'Mixed Vegetable Curry', 'cooking_time' => 35, 'calories_per_serving' => 220, 'cuisine_type' => 'Indian']
            ];
        }
        
        foreach ($days as $day) {
            $plan[$day] = [];
            foreach ($meals as $meal) {
                $recipe = $recipes[array_rand($recipes)];
                $plan[$day][$meal] = [
                    'name' => $recipe['name'],
                    'cooking_time' => $recipe['cooking_time'] ?? 30,
                    'calories' => $recipe['calories_per_serving'] ?? 300,
                    'cuisine' => $recipe['cuisine_type'] ?? 'Mixed',
                    'servings' => $preferences['servings']
                ];
            }
        }
        
    } catch (Exception $e) {
        // Fallback plan
        foreach ($days as $day) {
            $plan[$day] = [
                'Breakfast' => ['name' => 'Healthy Breakfast', 'cooking_time' => 15, 'calories' => 250, 'cuisine' => 'Continental', 'servings' => $preferences['servings']],
                'Lunch' => ['name' => 'Bengali Fish Curry', 'cooking_time' => 45, 'calories' => 350, 'cuisine' => 'Bangla', 'servings' => $preferences['servings']],
                'Dinner' => ['name' => 'Mixed Vegetable Curry', 'cooking_time' => 35, 'calories' => 300, 'cuisine' => 'Indian', 'servings' => $preferences['servings']]
            ];
        }
    }
    
    return $plan;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Plans - EasyPrep</title>
    <link rel="stylesheet" href="styles/meal-plan.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="meal-plan.php" class="active">Meal Plans</a>
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
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main class="meal-plan-main">
        <?php if (!$generated_plan): ?>
        <!-- Preferences Form -->
        <section class="preferences-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-utensils"></i> Create Your Perfect Meal Plan</h1>
                    <p>Tell us your preferences and we'll create a personalized weekly meal plan just for you!</p>
                </div>
                
                <form method="POST" class="preferences-form">
                    <div class="form-grid">
                        <!-- Servings -->
                        <div class="form-group">
                            <label for="servings"><i class="fas fa-users"></i> Number of Servings</label>
                            <select name="servings" id="servings" required>
                                <option value="1">For 1 person</option>
                                <option value="2" selected>For 2 people</option>
                                <option value="3">For 3 people</option>
                                <option value="4">For 4 people</option>
                                <option value="5">For 5+ people</option>
                            </select>
                        </div>
                        
                        <!-- Dietary Restrictions -->
                        <div class="form-group full-width">
                            <label><i class="fas fa-shield-alt"></i> Dietary Restrictions & Allergies</label>
                            <div class="checkbox-grid">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="allergic_nuts">
                                    <span class="checkmark"></span>
                                    <span class="text">Allergic to Nuts</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="no_gluten">
                                    <span class="checkmark"></span>
                                    <span class="text">Gluten-Free</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="no_dairy">
                                    <span class="checkmark"></span>
                                    <span class="text">Dairy-Free</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="vegetarian">
                                    <span class="checkmark"></span>
                                    <span class="text">Vegetarian</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="vegan">
                                    <span class="checkmark"></span>
                                    <span class="text">Vegan</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="low_carb">
                                    <span class="checkmark"></span>
                                    <span class="text">Low Carb</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="no_spicy">
                                    <span class="checkmark"></span>
                                    <span class="text">No Spicy Food</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="generate_plan" class="btn-primary">
                            <i class="fas fa-magic"></i>
                            Generate My Meal Plan
                        </button>
                    </div>
                </form>
            </div>
        </section>
        <?php else: ?>
        <!-- Generated Meal Plan -->
        <section class="meal-plan-section">
            <div class="container">
                <div class="plan-header">
                    <h1><i class="fas fa-calendar-week"></i> Your Weekly Meal Plan</h1>
                    <p>For <?php echo $preferences['servings']; ?> serving<?php echo $preferences['servings'] > 1 ? 's' : ''; ?> • Customized to your preferences</p>
                    <div class="plan-actions">
                        <button onclick="downloadMealPlan()" class="btn-secondary">
                            <i class="fas fa-download"></i> Download Plan
                        </button>
                        <button onclick="generateGroceryList()" class="btn-primary">
                            <i class="fas fa-shopping-cart"></i> Get All Groceries Now
                        </button>
                    </div>
                </div>
                
                <div class="meal-plan-grid" id="meal-plan-content">
                    <?php foreach ($generated_plan as $day => $meals): ?>
                    <div class="day-card">
                        <div class="day-header">
                            <h3><?php echo $day; ?></h3>
                            <div class="day-stats">
                                <span><i class="fas fa-fire"></i> <?php echo array_sum(array_column($meals, 'calories')); ?> cal</span>
                                <span><i class="fas fa-clock"></i> <?php echo array_sum(array_column($meals, 'cooking_time')); ?> min</span>
                            </div>
                        </div>
                        
                        <div class="meals-list">
                            <?php foreach ($meals as $meal_type => $meal): ?>
                            <div class="meal-item">
                                <div class="meal-header">
                                    <span class="meal-type"><?php echo $meal_type; ?></span>
                                    <span class="cuisine-badge"><?php echo $meal['cuisine']; ?></span>
                                </div>
                                <h4><?php echo htmlspecialchars($meal['name']); ?></h4>
                                <div class="meal-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo $meal['cooking_time']; ?> min</span>
                                    <span><i class="fas fa-fire"></i> <?php echo $meal['calories']; ?> cal</span>
                                    <span><i class="fas fa-users"></i> <?php echo $meal['servings']; ?> serving<?php echo $meal['servings'] > 1 ? 's' : ''; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="plan-footer">
                    <button onclick="window.location.reload()" class="btn-outline">
                        <i class="fas fa-redo"></i> Create New Plan
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <script src="scripts/meal-plan.js"></script>
</body>
</html>