<?php
session_start();
include("connect.php");
// Load API key (ignored in git)
@require_once __DIR__ . '/config.php';

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
$force_external = isset($_GET['force_external']) ? (int)$_GET['force_external'] : 0;
$debug_external = isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
$insecure_ssl = isset($_GET['insecure_ssl']) ? (int)$_GET['insecure_ssl'] : 0; // DEV ONLY
$provider = isset($_GET['provider']) ? strtolower($_GET['provider']) : 'spoonacular';
$proxy = isset($_GET['proxy']) ? trim($_GET['proxy']) : '';

// Spoonacular fetch helper (used as fallback when DB has no results)
function fetch_spoonacular_recipes($search, $cuisine_type, $cooking_time, $dietary, $insecure_ssl = 0, &$debug_info = null, $proxy = '')
{
    if (!defined('SPOONACULAR_API_KEY') || empty(SPOONACULAR_API_KEY)) {
        return [];
    }

    $baseUrl = 'https://api.spoonacular.com/recipes/complexSearch';
    $params = [
        'apiKey' => SPOONACULAR_API_KEY,
        'number' => 12,
        'addRecipeInformation' => 'true'
    ];

    if (!empty($search)) {
        $params['query'] = $search;
    }

    // Map cuisine; treat Bangla/Bengali as Indian for API purposes
    if (!empty($cuisine_type)) {
        $normalizedCuisine = strtolower($cuisine_type);
        if (in_array($normalizedCuisine, ['bangla', 'bengali', 'bangladeshi'])) {
            $params['cuisine'] = 'Indian';
        } else {
            $params['cuisine'] = $cuisine_type;
        }
    }

    // Dietary mapping -> diet param
    if (!empty($dietary)) {
        if ($dietary === 'vegetarian') {
            $params['diet'] = 'vegetarian';
        } elseif ($dietary === 'vegan') {
            $params['diet'] = 'vegan';
        } elseif ($dietary === 'gluten_free') {
            $params['intolerances'] = 'gluten';
        }
    }

    // Cooking time mapping -> maxReadyTime
    if (!empty($cooking_time)) {
        if ($cooking_time === 'quick') {
            $params['maxReadyTime'] = 30;
        } elseif ($cooking_time === 'medium') {
            $params['maxReadyTime'] = 60;
        } elseif ($cooking_time === 'long') {
            $params['maxReadyTime'] = 120;
        }
    }

    $url = $baseUrl . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    // Prefer IPv4 to avoid some local DNS/IPv6 issues
    if (defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    if ($insecure_ssl) { // DEV ONLY
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    if (!empty($proxy)) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    if ($response === false) {
        if (is_array($debug_info)) {
            $debug_info['curl_error'] = $curlError;
            $debug_info['url'] = $url;
        }
        curl_close($ch);
        return [];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        if (is_array($debug_info)) {
            $debug_info['http_code'] = $httpCode;
            $debug_info['url'] = $url;
        }
        return [];
    }

    $data = json_decode($response, true);
    if (!isset($data['results']) || !is_array($data['results'])) {
        return [];
    }

    $external = [];
    foreach ($data['results'] as $item) {
        $external[] = [
            'name' => isset($item['title']) ? $item['title'] : 'Recipe',
            'description' => isset($item['summary']) ? strip_tags($item['summary']) : '',
            'cuisine_type' => isset($params['cuisine']) ? $params['cuisine'] : 'External',
            'cooking_time' => isset($item['readyInMinutes']) ? (int)$item['readyInMinutes'] : null,
            'servings' => isset($item['servings']) ? (int)$item['servings'] : null,
            'image_url' => isset($item['image']) ? $item['image'] : '',
            'source_url' => isset($item['sourceUrl']) ? $item['sourceUrl'] : (isset($item['spoonacularSourceUrl']) ? $item['spoonacularSourceUrl'] : ''),
        ];
    }

    return $external;
}

// Helper to safely truncate text without requiring mbstring
function truncate_text($text, $limit = 160)
{
    if (!is_string($text)) {
        return '';
    }
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...', 'UTF-8');
    }
    return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
}

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

// TheMealDB (keyless) external provider
function fetch_mealdb_recipes($search, &$debug_info = null, $proxy = '')
{
    $q = trim($search) !== '' ? $search : 'fish';
    $url = 'https://www.themealdb.com/api/json/v1/1/search.php?s=' . urlencode($q);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
    ]);
    if (defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    if (!empty($proxy)) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (is_array($debug_info)) {
        $debug_info['mealdb_url'] = $url;
        if ($curlError) $debug_info['mealdb_curl_error'] = $curlError;
        $debug_info['mealdb_http_code'] = $httpCode;
    }

    if ($response === false || $httpCode !== 200) {
        return [];
    }
    $data = json_decode($response, true);
    if (!isset($data['meals']) || !is_array($data['meals'])) {
        return [];
    }
    $external = [];
    foreach ($data['meals'] as $meal) {
        $external[] = [
            'name' => isset($meal['strMeal']) ? $meal['strMeal'] : 'Recipe',
            'description' => isset($meal['strInstructions']) ? truncate_text($meal['strInstructions'], 220) : '',
            'cuisine_type' => isset($meal['strArea']) ? $meal['strArea'] : 'External',
            'cooking_time' => null,
            'servings' => null,
            'image_url' => isset($meal['strMealThumb']) ? $meal['strMealThumb'] : '',
            'source_url' => '',
        ];
    }
    // Limit to 12
    return array_slice($external, 0, 12);
}

// Always fetch external API results (shown before local results)
$external_recipes = [];
$external_debug = [];
if (true) {
    if ($provider === 'mealdb') {
        $external_recipes = fetch_mealdb_recipes($search, $external_debug, $proxy);
    } else {
        if (defined('SPOONACULAR_API_KEY') && !empty(SPOONACULAR_API_KEY)) {
            $external_recipes = fetch_spoonacular_recipes($search, $cuisine_type, $cooking_time, $dietary, $insecure_ssl, $external_debug, $proxy);
            // Fallback to MealDB if Spoonacular returns nothing
            if (empty($external_recipes)) {
                $external_recipes = fetch_mealdb_recipes($search, $external_debug, $proxy);
            }
        } else {
            // No Spoonacular key; use MealDB as fallback
            $external_recipes = fetch_mealdb_recipes($search, $external_debug, $proxy);
        }
    }
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

// Curated image resolver for known Bengali recipes and common dishes (uses stable Unsplash photo URLs)
function get_curated_recipe_image(string $recipeName, ?string $existingUrl = '', ?string $cuisine = null): string {
    $name = strtolower(trim($recipeName));
    $map = [
        // Bengali staples with stable Unsplash photo URLs
        'aloo bhorta' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80',
        'aloo vorta' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80',
        'bhuna khichuri' => 'https://images.unsplash.com/photo-1617093727343-374698b1aaf9?auto=format&fit=crop&w=800&q=80',
        'shorshe ilish' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
        'bhapa ilish' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
        'rui macher jhol' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
        'katla macher kalia' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
        'chingri malai curry' => 'https://images.unsplash.com/photo-1565299507177-b0ac66763828?auto=format&fit=crop&w=800&q=80',
        'masoor dal' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=800&q=80',
        'chana dal' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=800&q=80',
        'aloo posto' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=800&q=80',
        'shukto' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=800&q=80',
        'kala bhuna' => 'https://images.unsplash.com/photo-1574484284002-952d92456975?auto=format&fit=crop&w=800&q=80',
        'mughlai paratha' => 'https://images.unsplash.com/photo-1618040996337-56904b7850b9?auto=format&fit=crop&w=800&q=80',
        'mishti doi' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=800&q=80',
        // Common
        'butter chicken' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?auto=format&fit=crop&w=800&q=80',
        'biryani' => 'https://images.unsplash.com/photo-1604908554049-10e2b524a77a?auto=format&fit=crop&w=800&q=80',
        'palak paneer' => 'https://images.unsplash.com/photo-1625948695672-13e8ebefb563?auto=format&fit=crop&w=800&q=80',
        'pad thai' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?auto=format&fit=crop&w=800&q=80',
        'tom yum' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80',
        'green curry' => 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80',
        'margherita pizza' => 'https://images.unsplash.com/photo-1548365328-9f547fb09541?auto=format&fit=crop&w=800&q=80',
        'spaghetti carbonara' => 'https://images.unsplash.com/photo-1523983254930-47e9c17937e1?auto=format&fit=crop&w=800&q=80',
        'risotto' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80'
    ];

    foreach ($map as $key => $url) {
        if (strpos($name, $key) !== false) {
            return $url;
        }
    }

    // Prefer existing DB URL if present
    if (!empty($existingUrl)) {
        return $existingUrl;
    }
    // Cuisine-specific fallback to avoid random mismatches
    if (!empty($cuisine) && strtolower($cuisine) === 'bangla') {
        return 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80';
    }
    // Neutral fallback
    return 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?auto=format&fit=crop&w=800&q=80';
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
                <?php if ($force_external): ?>
                <div style="background: #fff3e0; border: 1px solid #ffe0b2; color: #6b4423; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-plug"></i>
                    <strong>External test mode ON</strong>
                    <span style="opacity:0.8;">(force_external=1)</span>
                </div>
                <?php endif; ?>
                <?php if ($debug_external): ?>
                <div style="background: #f9fbe7; border: 1px solid #e6ee9c; color: #6b4423; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-bug"></i>
                    <strong>Debug mode ON</strong>
                    <span style="opacity:0.8;">(debug=1)</span>
                </div>
                <?php endif; ?>
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
                <?php if (!empty($external_recipes)): ?>
                    <div class="no-results" style="margin-bottom: 1rem;">
                        <i class="fas fa-globe"></i>
                        <h3>External results</h3>
                        <p>Additional matches from <?php echo htmlspecialchars($provider === 'mealdb' ? 'TheMealDB' : 'Spoonacular'); ?>.</p>
                    </div>
                    <div class="recipes-grid">
                        <?php foreach ($external_recipes as $ext): ?>
                            <div class="recipe-card">
                                <div class="recipe-image">
                                    <img src="<?php echo htmlspecialchars($ext['image_url']); ?>" alt="<?php echo htmlspecialchars($ext['name']); ?>">
                                    <div class="recipe-badges">
                                        <span class="badge">External</span>
                                        <?php if (!empty($ext['cuisine_type'])): ?>
                                            <span class="badge"><?php echo htmlspecialchars($ext['cuisine_type']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="recipe-content">
                                    <div class="recipe-header">
                                        <h3><?php echo htmlspecialchars($ext['name']); ?></h3>
                                        <span class="cuisine-type"><?php echo htmlspecialchars($ext['cuisine_type']); ?></span>
                                    </div>
                                    <p class="recipe-description"><?php echo htmlspecialchars(truncate_text($ext['description'], 160)); ?></p>
                                    <div class="recipe-meta">
                                        <?php if (!empty($ext['cooking_time'])): ?>
                                            <span class="meta-item">
                                                <i class="fas fa-clock"></i>
                                                <?php echo (int)$ext['cooking_time']; ?> min
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($ext['servings'])): ?>
                                            <span class="meta-item">
                                                <i class="fas fa-users"></i>
                                                <?php echo (int)$ext['servings']; ?> servings
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="recipe-actions">
                                        <?php if (!empty($ext['source_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($ext['source_url']); ?>" target="_blank" rel="noopener" class="btn-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                                View Recipe
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn-secondary save-recipe"
                                            data-external="1"
                                            data-provider="<?php echo htmlspecialchars($provider); ?>"
                                            data-external-id="<?php echo htmlspecialchars(md5(($ext['name'] ?? '') . '|' . ($ext['image_url'] ?? '') . '|' . ($ext['source_url'] ?? ''))); ?>"
                                            data-name="<?php echo htmlspecialchars($ext['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($ext['description']); ?>"
                                            data-cuisine="<?php echo htmlspecialchars($ext['cuisine_type']); ?>"
                                            data-cooking-time="<?php echo htmlspecialchars((string)$ext['cooking_time']); ?>"
                                            data-servings="<?php echo htmlspecialchars((string)$ext['servings']); ?>"
                                            data-image-url="<?php echo htmlspecialchars($ext['image_url']); ?>">
                                            <i class="fas fa-heart"></i> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($recipes_result && $recipes_result->num_rows > 0): ?>
                    <div class="recipes-grid">
                        <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                            <div class="recipe-card">
                                <div class="recipe-image">
                                    <img src="<?php echo htmlspecialchars(get_curated_recipe_image($recipe['name'], $recipe['image_url'] ?? '', $recipe['cuisine_type'] ?? null)); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?auto=format&fit=crop&w=800&q=80';">
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
                <?php endif; ?>

                <?php if ((!$recipes_result || $recipes_result->num_rows === 0) && empty($external_recipes)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No recipes found</h3>
                        <p>Try adjusting your search criteria or browse all recipes.</p>
                        <?php if ($force_external): ?>
                            <p style="margin-top: 0.5rem; color: #a0522d;">External API returned no results.</p>
                        <?php endif; ?>
                        <?php if ($debug_external): ?>
                            <pre style="text-align: left; max-width: 100%; overflow: auto; background: #fffdf8; padding: 0.75rem; border-radius: 8px; border: 1px solid #f0e6da; color: #6b4423;">
<?php echo htmlspecialchars(print_r($external_debug, true)); ?>
                            </pre>
                        <?php endif; ?>
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
