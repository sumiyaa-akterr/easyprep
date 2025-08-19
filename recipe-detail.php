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

// Get recipe ID from URL
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recipe_name = isset($_GET['name']) ? $_GET['name'] : '';

// Sample Bengali recipes data for hardcoded recipes
$sample_recipes = [
    'Shorshe Ilish' => [
        'id' => 'br1',
        'name' => 'Shorshe Ilish',
        'description' => 'Traditional Bengali hilsa fish cooked in mustard seed paste with green chilies and turmeric - the king of Bengali fish dishes',
        'cuisine_type' => 'Bangla',
        'cooking_time' => 30,
        'servings' => 4,
        'difficulty' => 'Medium',
        'calories_per_serving' => 320,
        'image_url' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'ingredients' => 'Hilsa fish (1 kg), Mustard seeds (3 tbsp), Green chilies (4-5), Turmeric (1 tsp), Mustard oil (3 tbsp), Salt to taste, Water (1 cup)',
        'instructions' => '1. Clean and cut hilsa into pieces\n2. Marinate with turmeric and salt\n3. Grind mustard seeds with green chilies and little water\n4. Heat mustard oil and lightly fry fish\n5. Add mustard paste and simmer for 10 minutes\n6. Serve hot with steamed rice',
        'is_vegetarian' => 0,
        'is_vegan' => 0,
        'is_gluten_free' => 1,
        'tags' => 'fish,traditional,mustard,spicy,bengali special'
    ]
];

// Try to get recipe from database first
$recipe = null;
if ($recipe_id > 0) {
    try {
        $recipe_query = "SELECT * FROM recipes WHERE id = ?";
        $stmt = $conn->prepare($recipe_query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $recipe_result = $stmt->get_result();
        $recipe = $recipe_result->fetch_assoc();
    } catch (Exception $e) {
        $recipe = null;
    }
}

// If not found in database and name is provided, try sample recipes
if (!$recipe && !empty($recipe_name) && isset($sample_recipes[$recipe_name])) {
    $recipe = $sample_recipes[$recipe_name];
}

// If still no recipe found, redirect back to recipes
if (!$recipe) {
    header("Location: recipes.php");
    exit();
}

// Convert database format to display format if needed
if (is_string($recipe['ingredients'])) {
    $recipe['ingredients'] = explode(', ', $recipe['ingredients']);
}
if (is_string($recipe['instructions'])) {
    $recipe['instructions'] = explode('\n', str_replace('\\n', '\n', $recipe['instructions']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Details - EasyPrep</title>
    <link rel="stylesheet" href="styles/recipe-detail.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        </div>
    </header>

    <main style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <!-- Recipe Header -->
        <div style="display: flex; gap: 2rem; margin-bottom: 2rem; align-items: center;">
            <a href="recipes.php" style="color: #ff8a50; text-decoration: none; font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Back to Recipes
            </a>
        </div>

        <!-- Recipe Hero -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem; align-items: center;">
            <div>
                <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" 
                     style="width: 100%; height: 400px; object-fit: cover; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
            </div>
            <div>
                <h1 style="font-size: 2.5rem; color: #6b4423; margin-bottom: 1rem;"><?php echo htmlspecialchars($recipe['name']); ?></h1>
                <p style="color: #8d5524; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem;"><?php echo htmlspecialchars($recipe['description']); ?></p>
                
                <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b4423;">
                        <i class="fas fa-clock" style="color: #ff8a50;"></i>
                        <span><?php echo $recipe['cooking_time']; ?> min</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b4423;">
                        <i class="fas fa-users" style="color: #ff8a50;"></i>
                        <span><?php echo $recipe['servings']; ?> servings</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b4423;">
                        <i class="fas fa-fire" style="color: #ff8a50;"></i>
                        <span><?php echo $recipe['calories_per_serving']; ?> cal</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b4423;">
                        <i class="fas fa-signal" style="color: #ff8a50;"></i>
                        <span><?php echo $recipe['difficulty']; ?></span>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button onclick="alert('Recipe saved!')" style="background: #ff8a50; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-heart"></i> Save Recipe
                    </button>
                    <button onclick="alert('Added to meal plan!')" style="background: transparent; color: #6b4423; border: 2px solid #6b4423; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-plus"></i> Add to Meal Plan
                    </button>
                </div>
            </div>
        </div>

        <!-- Recipe Content -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- Ingredients -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(141, 85, 36, 0.08);">
                <h2 style="color: #6b4423; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-list"></i> Ingredients
                </h2>
                <div style="space-y: 0.75rem;">
                    <?php foreach ($recipe['ingredients'] as $ingredient): ?>
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                            <input type="checkbox" style="accent-color: #ff8a50;">
                            <span style="color: #6b4423;"><?php echo htmlspecialchars($ingredient); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button onclick="alert('Ingredients added to grocery list!')" style="background: #f8f4f0; color: #6b4423; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-shopping-cart"></i> Add to Grocery List
                </button>
            </div>

            <!-- Instructions -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(141, 85, 36, 0.08);">
                <h2 style="color: #6b4423; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-utensils"></i> Instructions
                </h2>
                <div>
                    <?php foreach ($recipe['instructions'] as $index => $instruction): ?>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start;">
                            <div style="background: linear-gradient(135deg, #ff8a50, #ff7043); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                <?php echo $index + 1; ?>
                            </div>
                            <div style="color: #6b4423; line-height: 1.6; flex: 1;">
                                <?php echo htmlspecialchars(trim($instruction)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.querySelector('.user-menu-toggle').addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('active');
        });
    </script>
</body>
</html>
