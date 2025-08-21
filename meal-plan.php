<?php
session_start();
include("connect.php");
@require_once __DIR__ . '/config.php';

// Ensure user is authenticated
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

// Debug: Check what we got from the database
if ($user) {
    error_log("User loaded successfully. User data: " . print_r($user, true));
} else {
    error_log("No user found for email: " . $user_email);
}

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// User loaded; continue to rendering

// Handle meal plan generation
$generated_plan = null;
$preferences = [
    'servings' => 2,
    'protein_preference' => 'any',
    'cuisine_preference' => 'any',
    'cooking_time' => 'any',
    'spice_level' => 'any',
    'allergic_nuts' => false,
    'no_gluten' => false,
    'no_dairy' => false,
    'vegetarian' => false,
    'vegan' => false,
    'low_carb' => false,
    'no_spicy' => false,
    'low_sodium' => false,
    'high_protein' => false,
    'keto_friendly' => false
];
$error_message = '';

if ($_POST && isset($_POST['generate_plan'])) {
    // Get form data
    $servings = (int)($_POST['servings'] ?? 2);
    
    // Get all preferences
    $preferences = [
        'servings' => $servings,
        'protein_preference' => $_POST['protein_preference'] ?? 'any',
        'cuisine_preference' => $_POST['cuisine_preference'] ?? 'any',
        'cooking_time' => $_POST['cooking_time'] ?? 'any',
        'spice_level' => $_POST['spice_level'] ?? 'any',
        'allergic_nuts' => isset($_POST['allergic_nuts']),
        'no_gluten' => isset($_POST['no_gluten']),
        'no_dairy' => isset($_POST['no_dairy']),
        'vegetarian' => isset($_POST['vegetarian']),
        'vegan' => isset($_POST['vegan']),
        'low_carb' => isset($_POST['low_carb']),
        'no_spicy' => isset($_POST['no_spicy']),
        'low_sodium' => isset($_POST['low_sodium']),
        'high_protein' => isset($_POST['high_protein']),
        'keto_friendly' => isset($_POST['keto_friendly'])
    ];
    
    // Validate that at least one preference is selected
    $has_preferences = false;
    foreach ($preferences as $key => $value) {
        if ($key !== 'servings' && $key !== 'protein_preference' && $key !== 'cuisine_preference' && $key !== 'cooking_time' && $key !== 'spice_level') {
            if ($value === true) {
                $has_preferences = true;
                break;
            }
        }
    }
    
    // Also check if protein preference is selected
    if ($preferences['protein_preference'] !== 'any' || $preferences['cuisine_preference'] !== 'any' || $preferences['cooking_time'] !== 'any' || $preferences['spice_level'] !== 'any') {
        $has_preferences = true;
    }
    
    if (!$has_preferences) {
        $error_message = "Please select at least one preference or dietary restriction.";
    } else {
        // Generate meal plan
        $generated_plan = generateSpoonacularMealPlan($preferences);
        
        if (empty($generated_plan)) {
            $generated_plan = generateWeeklyMealPlan($conn, $preferences);
        }
        
        // Save to database if we have a plan
        if (!empty($generated_plan)) {
            // Ensure user has an ID before saving
            if (isset($user['id']) && $user['id']) {
                saveMealPlan($conn, $user['id'], $preferences, $generated_plan);
            } else {
                // Fallback: try to get user ID from email
                $id_query = "SELECT id FROM users WHERE email = ?";
                $id_stmt = $conn->prepare($id_query);
                $id_stmt->bind_param("s", $user_email);
                $id_stmt->execute();
                $id_result = $id_stmt->get_result();
                $user_id = $id_result->fetch_assoc();
                
                if ($user_id && isset($user_id['id'])) {
                    saveMealPlan($conn, $user_id['id'], $preferences, $generated_plan);
                } else {
                    // If still no ID, log error but don't break the page
                    error_log("Could not save meal plan: User ID not found for email: " . $user_email);
                }
            }
        }
    }
}

// Spoonacular API functions
function generateSpoonacularMealPlan($preferences) {
    if (!defined('SPOONACULAR_API_KEY')) {
        return null;
    }
    
    $api_key = SPOONACULAR_API_KEY;
    $url = "https://api.spoonacular.com/mealplanner/generate?timeFrame=week&apiKey={$api_key}";
    
    // Add preferences to API call
    if ($preferences['servings'] > 0) {
        $url .= "&targetCalories=" . ($preferences['servings'] * 600);
    }
    
    if ($preferences['vegetarian']) {
        $url .= "&diet=vegetarian";
    } elseif ($preferences['vegan']) {
        $url .= "&diet=vegan";
    }
    
    if ($preferences['no_gluten']) {
        $url .= "&intolerances=gluten";
    }
    
    if ($preferences['no_dairy']) {
        $url .= "&intolerances=dairy";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['week'])) {
            $meal_plan = [];
            foreach ($data['week'] as $day => $meals) {
                $day_meals = [];
                foreach ($meals as $meal_type => $meal) {
                    if (isset($meal['id'])) {
                        $recipe_info = fetchSpoonacularRecipeInformation($meal['id']);
                        if ($recipe_info) {
                            $day_meals[] = $recipe_info;
                        }
                    }
                }
                if (!empty($day_meals)) {
                    $meal_plan[ucfirst($day)] = $day_meals;
                }
            }
            return $meal_plan;
        }
    }
    
    return null;
}

function fetchSpoonacularRecipeInformation($recipe_id) {
    if (!defined('SPOONACULAR_API_KEY')) {
        return null;
    }
    
    $api_key = SPOONACULAR_API_KEY;
    $url = "https://api.spoonacular.com/recipes/{$recipe_id}/information?apiKey={$api_key}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            return [
                'name' => $data['title'] ?? 'Recipe',
                'calories' => $data['nutrition']?->nutrients[0]?->amount ?? 300,
                'cooking_time' => $data['readyInMinutes'] ?? 30,
                'image' => $data['image'] ?? '',
                'instructions' => $data['instructions'] ?? '',
                'ingredients' => array_map(function($ingredient) {
                    return $ingredient['original'] ?? '';
                }, $data['extendedIngredients'] ?? [])
            ];
        }
    }
    
    return null;
}

// Local meal plan generator (fallback)
function generateWeeklyMealPlan($conn, $preferences) {
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $meal_plan = [];
    
    foreach ($days as $day) {
        $meals = [];
        
        // Generate 3 meals per day
        $meal_types = ['Breakfast', 'Lunch', 'Dinner'];
        foreach ($meal_types as $meal_type) {
            $meals[] = [
                'name' => generateMealName($meal_type, $preferences),
                'calories' => rand(300, 800),
                'cooking_time' => rand(15, 60),
                'image' => '',
                'instructions' => 'Cook according to your preferences.',
                'ingredients' => generateIngredients($preferences)
            ];
        }
        
        $meal_plan[$day] = $meals;
    }
    
    return $meal_plan;
}

function generateMealName($meal_type, $preferences) {
    $proteins = ['Chicken', 'Fish', 'Beef', 'Pork', 'Tofu', 'Eggs'];
    $styles = ['Grilled', 'Baked', 'Stir-fried', 'Roasted', 'Steamed'];
    $sides = ['with Rice', 'with Vegetables', 'with Salad', 'with Pasta'];
    
    $protein = $preferences['protein_preference'] !== 'any' ? ucfirst($preferences['protein_preference']) : $proteins[array_rand($proteins)];
    $style = $styles[array_rand($styles)];
    $side = $sides[array_rand($sides)];
    
    return "{$style} {$protein} {$side}";
}

function generateIngredients($preferences) {
    $ingredients = [];
    
    // Protein ingredients
    if ($preferences['protein_preference'] !== 'any') {
        $protein = strtolower($preferences['protein_preference']);
        $ingredients[] = [
            'name' => $protein,
            'amount' => 1,
            'unit' => 'lb',
            'estimated_price' => getIngredientPrice($protein)
        ];
    } else {
        $proteins = ['chicken', 'beef', 'fish', 'pork'];
        $protein = $proteins[array_rand($proteins)];
        $ingredients[] = [
            'name' => $protein,
            'amount' => 1,
            'unit' => 'lb',
            'estimated_price' => getIngredientPrice($protein)
        ];
    }
    
    // Staple ingredients
    $staples = [
        ['name' => 'rice', 'amount' => 2, 'unit' => 'cups', 'estimated_price' => 2.99],
        ['name' => 'olive oil', 'amount' => 0.25, 'unit' => 'cup', 'estimated_price' => 3.99],
        ['name' => 'onions', 'amount' => 2, 'unit' => 'pieces', 'estimated_price' => 2.99],
        ['name' => 'garlic', 'amount' => 4, 'unit' => 'cloves', 'estimated_price' => 1.99],
        ['name' => 'tomatoes', 'amount' => 4, 'unit' => 'pieces', 'estimated_price' => 3.99],
        ['name' => 'bell peppers', 'amount' => 2, 'unit' => 'pieces', 'estimated_price' => 2.99],
        ['name' => 'spinach', 'amount' => 1, 'unit' => 'bag', 'estimated_price' => 4.49],
        ['name' => 'potatoes', 'amount' => 4, 'unit' => 'pieces', 'estimated_price' => 4.99],
        ['name' => 'carrots', 'amount' => 6, 'unit' => 'pieces', 'estimated_price' => 2.49],
        ['name' => 'eggs', 'amount' => 6, 'unit' => 'pieces', 'estimated_price' => 2.99],
        ['name' => 'milk', 'amount' => 1, 'unit' => 'gallon', 'estimated_price' => 4.29],
        ['name' => 'cheese', 'amount' => 1, 'unit' => '8oz', 'estimated_price' => 5.99],
        ['name' => 'bread', 'amount' => 1, 'unit' => 'loaf', 'estimated_price' => 3.99],
        ['name' => 'flour', 'amount' => 2, 'unit' => 'cups', 'estimated_price' => 1.99],
        ['name' => 'sugar', 'amount' => 1, 'unit' => 'cup', 'estimated_price' => 1.99],
        ['name' => 'salt', 'amount' => 1, 'unit' => 'tsp', 'estimated_price' => 0.99],
        ['name' => 'black pepper', 'amount' => 1, 'unit' => 'tsp', 'estimated_price' => 0.99],
        ['name' => 'herbs', 'amount' => 1, 'unit' => 'bunch', 'estimated_price' => 2.99]
    ];
    
    // Add random staples based on preferences
    $num_staples = rand(8, 12);
    $selected_staples = array_rand($staples, $num_staples);
    if (!is_array($selected_staples)) {
        $selected_staples = [$selected_staples];
    }
    
    foreach ($selected_staples as $index) {
        $ingredients[] = $staples[$index];
    }
    
    return $ingredients;
}

function getIngredientPrice($ingredient_name) {
    $price_map = [
        'chicken' => 7.99,
        'beef' => 8.99,
        'pork' => 6.99,
        'fish' => 12.99,
        'lamb' => 9.99,
        'turkey' => 7.49,
        'duck' => 11.99,
        'veal' => 13.99
    ];
    
    return $price_map[strtolower($ingredient_name)] ?? 5.99;
}

function saveMealPlan($conn, $user_id, $preferences, $meal_plan) {
    try {
        $check_table = $conn->query("SHOW TABLES LIKE 'meal_plans'");
        if ($check_table && $check_table->num_rows > 0) {
            // Compute week start (Monday) and total calories
            $week_start_date = date('Y-m-d', strtotime('monday this week'));
            $plan_name = "Weekly Plan - " . date('M j, Y', strtotime($week_start_date));
            $total_calories = 0;
            if (is_array($meal_plan)) {
                foreach ($meal_plan as $dayMeals) {
                    if (is_array($dayMeals)) {
                        foreach ($dayMeals as $meal) {
                            $total_calories += (int)($meal['calories'] ?? 0);
                        }
                    }
                }
            }

            $dietary_json = json_encode($preferences);
            $meal_data_json = json_encode($meal_plan);
            $servings = (int)($preferences['servings'] ?? 2);

            // Align with schema (name, week_start_date, totals)
            $sql = "INSERT INTO meal_plans (user_id, name, servings, dietary_restrictions, week_start_date, meal_data, total_calories, total_cost, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, 1)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("isisssi", $user_id, $plan_name, $servings, $dietary_json, $week_start_date, $meal_data_json, $total_calories);
                if (!$stmt->execute()) {
                    error_log('saveMealPlan execute error: ' . $stmt->error);
                }
            } else {
                error_log('saveMealPlan prepare error: ' . $conn->error);
            }
        }
    } catch (Exception $e) {
        // Silently fail if table doesn't exist or schema mismatch
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planning - EasyPrep</title>
    <link rel="stylesheet" href="styles/meal-plan.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
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
        <section class="preferences-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-utensils"></i> Create Your Perfect Meal Plan</h1>
                    <p>Tell us your preferences and we'll create a personalized weekly meal plan just for you!</p>
                </div>
                
                <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="preferences-form" id="mealPlanForm">
                    <div class="form-grid">
                        <!-- Basic Preferences -->
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
                        
                        <div class="form-group">
                            <label for="protein_preference"><i class="fas fa-drumstick-bite"></i> Protein Preference</label>
                            <select name="protein_preference" id="protein_preference">
                                <option value="any">Any Protein</option>
                                <option value="chicken">Chicken</option>
                                <option value="fish">Fish</option>
                                <option value="beef">Beef</option>
                                <option value="pork">Pork</option>
                                <option value="tofu">Tofu</option>
                                <option value="eggs">Eggs</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cuisine_preference"><i class="fas fa-globe"></i> Cuisine Style</label>
                            <select name="cuisine_preference" id="cuisine_preference">
                                <option value="any">Any Cuisine</option>
                                <option value="italian">Italian</option>
                                <option value="mexican">Mexican</option>
                                <option value="asian">Asian</option>
                                <option value="indian">Indian</option>
                                <option value="mediterranean">Mediterranean</option>
                                <option value="american">American</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cooking_time"><i class="fas fa-clock"></i> Cooking Time</label>
                            <select name="cooking_time" id="cooking_time">
                                <option value="any">Any Time</option>
                                <option value="quick">Quick (15-30 min)</option>
                                <option value="medium">Medium (30-45 min)</option>
                                <option value="slow">Slow (45+ min)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="spice_level"><i class="fas fa-fire"></i> Spice Level</label>
                            <select name="spice_level" id="spice_level">
                                <option value="any">Any Level</option>
                                <option value="mild">Mild</option>
                                <option value="medium">Medium</option>
                                <option value="hot">Hot</option>
                            </select>
                        </div>
                        
                        <!-- Dietary Restrictions & Allergies -->
                        <div class="form-group full-width">
                            <label><i class="fas fa-shield-alt"></i> Dietary Restrictions & Allergies</label>
                            <div class="checkbox-grid">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="allergic_nuts" id="allergic_nuts">
                                    <span class="checkmark"></span>
                                    <span class="text">Allergic to Nuts</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="no_gluten" id="no_gluten">
                                    <span class="checkmark"></span>
                                    <span class="text">Gluten-Free</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="no_dairy" id="no_dairy">
                                    <span class="checkmark"></span>
                                    <span class="text">Dairy-Free</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="vegetarian" id="vegetarian">
                                    <span class="checkmark"></span>
                                    <span class="text">Vegetarian</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="vegan" id="vegan">
                                    <span class="checkmark"></span>
                                    <span class="text">Vegan</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="low_carb" id="low_carb">
                                    <span class="checkmark"></span>
                                    <span class="text">Low Carb</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="no_spicy" id="no_spicy">
                                    <span class="checkmark"></span>
                                    <span class="text">No Spicy Food</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="low_sodium" id="low_sodium">
                                    <span class="checkmark"></span>
                                    <span class="text">Low Sodium</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="high_protein" id="high_protein">
                                    <span class="checkmark"></span>
                                    <span class="text">High Protein</span>
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="keto_friendly" id="keto_friendly">
                                    <span class="checkmark"></span>
                                    <span class="text">Keto Friendly</span>
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
        <section class="meal-plan-section">
            <div class="container">
                <div class="plan-header">
                    <h1><i class="fas fa-receipt"></i> Your Weekly Meal Receipt</h1>
                    <p>For <?php echo (int)$preferences['servings']; ?> serving<?php echo ((int)$preferences['servings'] > 1 ? 's' : ''); ?> • Generated on <?php echo date('M j, Y'); ?></p>
                    <div class="plan-actions">
                        <button onclick="downloadMealPlan()" class="btn-secondary"><i class="fas fa-download"></i> Download Receipt (PDF)</button>
                        <button onclick="showGroceryList()" class="btn-primary"><i class="fas fa-shopping-cart"></i> Get Grocery</button>
                    </div>
                </div>
                
                <!-- Receipt Container -->
                <div class="receipt-container">
                    <div class="receipt-paper" id="receipt-paper">
                        <!-- Receipt Header -->
                        <div class="receipt-header">
                            <div class="receipt-title">
                                <h2>EASYPREP</h2>
                                <p>Weekly Meal Plan Receipt</p>
                            </div>
                            <div class="receipt-meta">
                                <p>Date: <?php echo date('M j, Y'); ?></p>
                                <p>Servings: <?php echo (int)$preferences['servings']; ?></p>
                                <p>Plan ID: #<?php echo strtoupper(substr(md5(time()), 0, 8)); ?></p>
                            </div>
                        </div>
                        
                        <!-- Receipt Content -->
                        <div class="receipt-content">
                            <?php 
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $currentDay = 0;
                            foreach ($days as $day): 
                                if (isset($generated_plan[$day])):
                                    $meals = $generated_plan[$day];
                                    $dayCalories = array_sum(array_map(fn($m)=> (int)$m['calories'], $meals));
                                    $dayTime = array_sum(array_map(fn($m)=> (int)$m['cooking_time'], $meals));
                            ?>
                            <div class="day-receipt <?php echo $currentDay === 0 ? 'active' : ''; ?>" data-day="<?php echo $currentDay; ?>">
                                <div class="day-separator">
                                    <span class="day-name"><?php echo strtoupper($day); ?></span>
                                    <span class="day-date"><?php echo date('M j', strtotime("+$currentDay days")); ?></span>
                                </div>
                                
                                <div class="meals-receipt">
                                    <?php foreach ($meals as $meal): ?>
                                    <div class="meal-receipt-item">
                                        <div class="meal-name"><?php echo htmlspecialchars($meal['name']); ?></div>
                                        <div class="meal-details">
                                            <span class="meal-calories"><?php echo $meal['calories']; ?> cal</span>
                                            <span class="meal-time"><?php echo $meal['cooking_time']; ?> min</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="day-summary">
                                    <div class="summary-item">
                                        <span class="summary-label">TOTAL CALORIES:</span>
                                        <span class="summary-value"><?php echo $dayCalories; ?> cal</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">COOKING TIME:</span>
                                        <span class="summary-value"><?php echo $dayTime; ?> min</span>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                $currentDay++;
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        
                        <!-- Receipt Footer -->
                        <div class="receipt-footer">
                            <div class="footer-separator">--------------------------------</div>
                            <div class="footer-stats">
                                <div class="stat-item">
                                    <span class="stat-label">WEEKLY TOTAL:</span>
                                    <span class="stat-value"><?php echo array_sum(array_map(fn($day) => array_sum(array_map(fn($m) => (int)$m['calories'], $day)), $generated_plan)); ?> calories</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">AVG DAILY:</span>
                                    <span class="stat-value"><?php echo round(array_sum(array_map(fn($day) => array_sum(array_map(fn($m) => (int)$m['calories'], $day)), $generated_plan)) / 7); ?> calories</span>
                                </div>
                            </div>
                            <div class="footer-message">
                                <p>Thank you for using EasyPrep!</p>
                                <p>Your personalized meal plan is ready.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Dots -->
                    <div class="receipt-nav">
                        <?php for ($i = 0; $i < $currentDay; $i++): ?>
                        <button class="nav-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-day="<?php echo $i; ?>"></button>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Navigation Arrows -->
                    <button class="nav-arrow nav-prev" onclick="previousDay()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-arrow nav-next" onclick="nextDay()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="plan-footer">
                    <button onclick="createNewPlan()" class="btn-outline"><i class="fas fa-redo"></i> Create New Plan</button>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <script>
        // Form validation and checkbox functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up form...');
            const form = document.getElementById('mealPlanForm');
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            
            console.log('Found checkboxes:', checkboxes.length);
            
            // Add event listeners to checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    console.log('Checkbox changed:', this.name, '=', this.checked);
                    updateCheckboxStyle(this);
                });
            });
            
            // Form submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateForm()) {
                        e.preventDefault();
                        alert('Please select at least one preference or dietary restriction.');
                    }
                });
            }
            
            // Initialize user menu
            initializeUserMenu();
            
            // Initialize navigation dots
            initializeDayNavigation();
        });
        
        function updateCheckboxStyle(checkbox) {
            const item = checkbox.closest('.checkbox-item');
            if (item) {
                if (checkbox.checked) {
                    item.classList.add('checked');
                } else {
                    item.classList.remove('checked');
                }
            }
        }
        
        function validateForm() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            const proteinPref = document.getElementById('protein_preference').value;
            const cuisinePref = document.getElementById('cuisine_preference').value;
            const cookingTime = document.getElementById('cooking_time').value;
            const spiceLevel = document.getElementById('spice_level').value;
            
            // Check if any checkbox is checked
            if (checkboxes.length > 0) {
                return true;
            }
            
            // Check if any preference is selected
            if (proteinPref !== 'any' || cuisinePref !== 'any' || cookingTime !== 'any' || spiceLevel !== 'any') {
                return true;
            }
            
            return false;
        }
        
        function testForm() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            const proteinPref = document.getElementById('protein_preference').value;
            const cuisinePref = document.getElementById('cuisine_preference').value;
            
            console.log('Checked checkboxes:', Array.from(checkboxes).map(cb => cb.name));
            console.log('Protein preference:', proteinPref);
            console.log('Cuisine preference:', cuisinePref);
            console.log('Form valid:', validateForm());
        }
        
        // Receipt navigation functions
        function previousDay() {
            const currentDay = document.querySelector('.day-receipt.active');
            const prevDay = currentDay.previousElementSibling;
            if (prevDay && prevDay.classList.contains('day-receipt')) {
                currentDay.classList.remove('active');
                prevDay.classList.add('active');
                updateNavigation();
            }
        }
        
        function nextDay() {
            const currentDay = document.querySelector('.day-receipt.active');
            const nextDay = currentDay.nextElementSibling;
            if (nextDay && nextDay.classList.contains('day-receipt')) {
                currentDay.classList.remove('active');
                nextDay.classList.add('active');
                updateNavigation();
            }
        }
        
        function updateNavigation() {
            const activeDay = document.querySelector('.day-receipt.active');
            const dayIndex = activeDay.dataset.day;
            
            // Update dots
            document.querySelectorAll('.nav-dot').forEach((dot, index) => {
                dot.classList.toggle('active', index == dayIndex);
            });
        }
        
        // User menu functionality
        function initializeUserMenu() {
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            const userDropdown = document.querySelector('.user-dropdown');

            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target)) {
                        userDropdown.classList.remove('active');
                    }
                });
            }
        }
        
        function downloadMealPlan() {
            const paper = document.getElementById('receipt-paper');
            if (!paper) { alert('Nothing to download'); return; }

            // Prepare a printable clone
            const clone = paper.cloneNode(true);
            // Remove interactive hints
            clone.style.cursor = 'default';
            // Open new window and write minimal HTML
            const printWindow = window.open('', '_blank');
            if (!printWindow) { window.print(); return; }
            printWindow.document.write(`
                <html>
                <head>
                    <title>EasyPrep Meal Plan</title>
                    <link rel="stylesheet" href="styles/meal-plan.css">
                    <style>@media print { body { margin: 0 } }</style>
                </head>
                <body>
                    <div class="receipt-container">
                        ${clone.outerHTML}
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            // Give the new window a moment to render, then trigger print
            printWindow.focus();
            printWindow.print();
            // Optional: save as PDF can be chosen in the print dialog
        }
        
        function showGroceryList() {
            const mealPlanData = <?php echo json_encode($generated_plan ?? null); ?>;
            const preferences = <?php echo json_encode($preferences ?? null); ?>;

            if (mealPlanData) {
                const groceryList = generateGroceryListFromPlan(mealPlanData, preferences);
                displayGroceryList(groceryList);
            } else {
                alert('No meal plan found. Please generate a meal plan first.');
            }
        }
        
        function generateGroceryListFromPlan(mealPlan, preferences) {
            const groceryList = [];
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            for (let i = 0; i < days.length; i++) {
                const day = days[i];
                if (mealPlan[day]) {
                    for (const meal of mealPlan[day]) {
                        for (const ingredient of meal.ingredients) {
                            let item = groceryList.find(g => g.name === ingredient.name);
                            if (item) {
                                item.quantity += ingredient.amount;
                            } else {
                                item = {
                                    name: ingredient.name,
                                    quantity: ingredient.amount,
                                    unit: ingredient.unit,
                                    estimated_price: ingredient.estimated_price
                                };
                                groceryList.push(item);
                            }
                        }
                    }
                }
            }

            // Sort by name for easier readability
            groceryList.sort((a, b) => a.name.localeCompare(b.name));

            return groceryList;
        }
        
        function displayGroceryList(groceryList) {
            const totalCost = groceryList.reduce((sum, item) => sum + item.estimated_price * item.quantity, 0);
            
            // Store the original meal plan content
            const originalReceipt = document.getElementById('receipt-paper').innerHTML;
            
            const groceryReceipt = document.createElement('div');
            groceryReceipt.classList.add('receipt-paper', 'grocery-receipt');
            groceryReceipt.innerHTML = `
                <div class="receipt-header">
                    <div class="receipt-title">
                        <h2>EASYPREP Grocery List</h2>
                        <p>Your personalized grocery list for the week</p>
                    </div>
                    <div class="receipt-meta">
                        <p>Date: ${new Date().toLocaleDateString()}</p>
                        <p>Plan ID: #${Math.random().toString(36).substring(2, 8).toUpperCase()}</p>
                    </div>
                </div>
                <div class="receipt-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Estimated Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${groceryList.map(item => `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.quantity}</td>
                                    <td>${item.unit}</td>
                                    <td>$${item.estimated_price.toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="receipt-footer">
                    <div class="footer-separator">--------------------------------</div>
                    <div class="footer-stats">
                        <div class="stat-item">
                            <span class="stat-label">TOTAL ESTIMATED COST:</span>
                            <span class="stat-value">$${totalCost.toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="footer-message">
                        <p>Thank you for using EasyPrep!</p>
                        <p>Your grocery list is ready.</p>
                    </div>
                    <div class="grocery-actions">
                        <button onclick="confirmGroceryOrder(${JSON.stringify(groceryList).replace(/"/g, '&quot;')})" class="btn-primary">
                            <i class="fas fa-check"></i> Confirm Order
                        </button>
                        <button onclick="showMealPlan()" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Meal Plan
                        </button>
                    </div>
                </div>
            `;
            
            // Store original content and show grocery list
            document.getElementById('receipt-paper').setAttribute('data-original-content', originalReceipt);
            document.getElementById('receipt-paper').innerHTML = groceryReceipt.innerHTML;
            document.getElementById('receipt-paper').classList.add('grocery-receipt');
            
            // Remove clickable behavior for grocery list (it's not a meal plan)
            document.getElementById('receipt-paper').style.cursor = 'default';
            document.getElementById('receipt-paper').removeEventListener('click', window.receiptClickHandler);
        }
        
        function confirmGroceryOrder(groceryList) {
            const totalCost = groceryList.reduce((sum, item) => sum + item.estimated_price * item.quantity, 0);
            const orderId = Math.random().toString(36).substring(2, 8).toUpperCase();
            
            const confirmationReceipt = document.createElement('div');
            confirmationReceipt.classList.add('receipt-paper', 'confirmation-receipt');
            confirmationReceipt.innerHTML = `
                <div class="receipt-header">
                    <div class="receipt-title">
                        <h2>ORDER CONFIRMED!</h2>
                        <p>Your grocery order has been placed successfully</p>
                    </div>
                    <div class="receipt-meta">
                        <p>Order Date: ${new Date().toLocaleDateString()}</p>
                        <p>Order Time: ${new Date().toLocaleTimeString()}</p>
                        <p>Order ID: #${orderId}</p>
                    </div>
                </div>
                <div class="receipt-content">
                    <div class="confirmation-message">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #6B8F71; margin-bottom: 20px;"></i>
                        <h3>Thank you for your order!</h3>
                        <p>Your grocery list has been confirmed and will be processed.</p>
                        <p><strong>Total Items:</strong> ${groceryList.length}</p>
                        <p><strong>Total Cost:</strong> $${totalCost.toFixed(2)}</p>
                    </div>
                </div>
                <div class="receipt-footer">
                    <div class="footer-message">
                        <p>You will receive a confirmation email shortly.</p>
                        <p>Happy cooking!</p>
                    </div>
                    <div class="grocery-actions">
                        <button onclick="showMealPlan()" class="btn-primary">
                            <i class="fas fa-home"></i> Back to Meal Plan
                        </button>
                    </div>
                </div>
            `;
            document.getElementById('receipt-paper').innerHTML = confirmationReceipt.innerHTML;
            document.getElementById('receipt-paper').classList.remove('grocery-receipt');
            document.getElementById('receipt-paper').classList.add('confirmation-receipt');
        }
        
        function showMealPlan() {
            // Restore the original meal plan content
            const originalContent = document.getElementById('receipt-paper').getAttribute('data-original-content');
            if (originalContent) {
                document.getElementById('receipt-paper').innerHTML = originalContent;
                document.getElementById('receipt-paper').classList.remove('grocery-receipt', 'confirmation-receipt');
                
                // Restore clickable behavior for meal plan
                document.getElementById('receipt-paper').style.cursor = 'pointer';
                if (window.receiptClickHandler) {
                    document.getElementById('receipt-paper').addEventListener('click', window.receiptClickHandler);
                }
                
                // Re-initialize day navigation after restoring content
                setTimeout(() => {
                    initializeDayNavigation();
                }, 100);
            } else {
                // Fallback: reload the page if no original content stored
                window.location.reload();
            }
        }

        function createNewPlan() {
            // Clear form data and reload the page
            window.location.href = 'meal-plan.php';
        }
        
        function initializeDayNavigation() {
            const navDots = document.querySelectorAll('.nav-dot');
            const dayReceipts = document.querySelectorAll('.day-receipt');
            const receiptPaper = document.getElementById('receipt-paper');
            
            // Store the click handler reference so we can remove it later
            window.receiptClickHandler = function(e) {
                // Don't trigger if clicking on buttons or navigation dots
                if (e.target.tagName === 'BUTTON' || e.target.closest('.nav-dot') || e.target.closest('.grocery-actions')) {
                    return;
                }
                
                // Find current active day
                const currentActiveDay = document.querySelector('.day-receipt.active');
                if (!currentActiveDay) return;
                
                // Find next day
                const allDays = Array.from(dayReceipts);
                const currentIndex = allDays.indexOf(currentActiveDay);
                const nextIndex = (currentIndex + 1) % allDays.length;
                const nextDay = allDays[nextIndex];
                
                // Switch to next day
                currentActiveDay.classList.remove('active');
                nextDay.classList.add('active');
                
                // Update navigation dots
                navDots.forEach(d => d.classList.remove('active'));
                const nextDot = document.querySelector(`[data-day="${nextDay.dataset.day}"]`);
                if (nextDot) {
                    nextDot.classList.add('active');
                }
            };
            
            // Add click event to the entire receipt area for day cycling
            if (receiptPaper) {
                receiptPaper.addEventListener('click', window.receiptClickHandler);
            }
            
            // Keep the original dot navigation as well
            navDots.forEach(dot => {
                dot.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent triggering the receipt click
                    const dayIndex = this.dataset.day;
                    
                    // Remove active class from all dots and receipts
                    navDots.forEach(d => d.classList.remove('active'));
                    dayReceipts.forEach(day => day.classList.remove('active'));
                    
                    // Add active class to clicked dot and corresponding receipt
                    this.classList.add('active');
                    const targetReceipt = document.querySelector(`[data-day="${dayIndex}"]`);
                    if (targetReceipt) {
                        targetReceipt.classList.add('active');
                    }
                });
            });
            
            // Set first day as active by default
            if (navDots.length > 0) {
                navDots[0].click();
            }
        }
    </script>
</body>
</html>
