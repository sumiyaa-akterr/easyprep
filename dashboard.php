<?php
session_start();
include("connect.php");
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

// Get user's meal plans - robust and direct query (works without mysqlnd get_result)
try {
	$meal_plans_result = null;
	$check_table = $conn->query("SHOW TABLES LIKE 'meal_plans'");
	if ($check_table && $check_table->num_rows > 0 && isset($user['id'])) {
		$userId = (int)$user['id'];
		$sql = "SELECT id, name, servings, week_start_date, total_calories, created_at FROM meal_plans WHERE user_id = {$userId} ORDER BY id DESC LIMIT 5";
		$meal_plans_result = $conn->query($sql);
	}
} catch (Exception $e) {
	$meal_plans_result = null;
}

// Get featured recipes - prioritize popular Bengali recipes
try {
	// First check if table exists
	$check_table = $conn->query("SHOW TABLES LIKE 'recipes'");
	if ($check_table->num_rows > 0) {
		$recipes_query = "SELECT id, name, description, cuisine_type, cooking_time, servings, difficulty, 
					  calories_per_serving, rating, total_ratings, image_url, is_vegetarian, is_vegan, is_gluten_free 
					  FROM recipes 
					  ORDER BY rating DESC, total_ratings DESC, created_at DESC 
					  LIMIT 6";
		$stmt = $conn->prepare($recipes_query);
		$stmt->execute();
		$recipes_result = $stmt->get_result();
		
		// If no recipes in database, show sample Bengali recipes instead
		if (!$recipes_result || $recipes_result->num_rows == 0) {
			$recipes_result = null;
		}
	} else {
		$recipes_result = null;
	}
} catch (Exception $e) {
	$recipes_result = null;
}

// Get grocery lists - only if table exists
try {
	// First check if table exists
	$check_table = $conn->query("SHOW TABLES LIKE 'grocery_lists'");
	if ($check_table->num_rows > 0) {
		$grocery_query = "SELECT * FROM grocery_lists WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
		$stmt = $conn->prepare($grocery_query);
		$stmt->bind_param("i", $user['id']);
		$stmt->execute();
		$grocery_result = $stmt->get_result();
	} else {
		$grocery_result = null;
	}
} catch (Exception $e) {
	$grocery_result = null;
}

// External featured recipes (API)
function dash_fetch_spoonacular($search = '', $cuisine_type = '', $cooking_time = '', $dietary = '', $insecure_ssl = 0, $proxy = '') {
    if (!defined('SPOONACULAR_API_KEY') || empty(SPOONACULAR_API_KEY)) { return []; }
    $baseUrl = 'https://api.spoonacular.com/recipes/complexSearch';
    $params = [
        'apiKey' => SPOONACULAR_API_KEY,
        'number' => 6,
        'addRecipeInformation' => 'true'
    ];
    if (!empty($search)) { $params['query'] = $search; }
    if (!empty($cuisine_type)) {
        $normalized = strtolower($cuisine_type);
        $params['cuisine'] = in_array($normalized, ['bangla','bengali','bangladeshi']) ? 'Indian' : $cuisine_type;
    }
    if (!empty($dietary)) {
        if ($dietary === 'vegetarian') { $params['diet'] = 'vegetarian'; }
        elseif ($dietary === 'vegan') { $params['diet'] = 'vegan'; }
        elseif ($dietary === 'gluten_free') { $params['intolerances'] = 'gluten'; }
    }
    if (!empty($cooking_time)) {
        if ($cooking_time === 'quick') { $params['maxReadyTime'] = 30; }
        elseif ($cooking_time === 'medium') { $params['maxReadyTime'] = 60; }
        elseif ($cooking_time === 'long') { $params['maxReadyTime'] = 120; }
    }
    $url = $baseUrl . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    if (defined('CURL_IPRESOLVE_V4')) { curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); }
    if ($insecure_ssl) { curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); }
    if (!empty($proxy)) { curl_setopt($ch, CURLOPT_PROXY, $proxy); }
    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || $http !== 200) { return []; }
    $data = json_decode($response, true);
    if (!isset($data['results']) || !is_array($data['results'])) { return []; }
    $out = [];
    foreach ($data['results'] as $item) {
        $out[] = [
            'name' => $item['title'] ?? 'Recipe',
            'description' => isset($item['summary']) ? strip_tags($item['summary']) : '',
            'cuisine_type' => $cuisine_type ?: 'External',
            'cooking_time' => isset($item['readyInMinutes']) ? (int)$item['readyInMinutes'] : null,
            'servings' => isset($item['servings']) ? (int)$item['servings'] : null,
            'image_url' => $item['image'] ?? '',
            'source_url' => $item['sourceUrl'] ?? ($item['spoonacularSourceUrl'] ?? ''),
        ];
    }
    return $out;
}

function dash_fetch_mealdb($search = '', $proxy = '') {
    $q = trim($search) !== '' ? $search : 'fish';
    $url = 'https://www.themealdb.com/api/json/v1/1/search.php?s=' . urlencode($q);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12]);
    if (defined('CURL_IPRESOLVE_V4')) { curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); }
    if (!empty($proxy)) { curl_setopt($ch, CURLOPT_PROXY, $proxy); }
    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || $http !== 200) { return []; }
    $data = json_decode($response, true);
    if (!isset($data['meals']) || !is_array($data['meals'])) { return []; }
    $out = [];
    foreach ($data['meals'] as $meal) {
        $out[] = [
            'name' => $meal['strMeal'] ?? 'Recipe',
            'description' => isset($meal['strInstructions']) ? substr(trim($meal['strInstructions']), 0, 160) : '',
            'cuisine_type' => $meal['strArea'] ?? 'External',
            'cooking_time' => null,
            'servings' => null,
            'image_url' => $meal['strMealThumb'] ?? '',
            'source_url' => '',
        ];
    }
    return array_slice($out, 0, 6);
}

$dashboard_external_recipes = [];
try {
    // Always attempt to fetch a small set of external featured recipes
    $dashboard_external_recipes = dash_fetch_spoonacular('', '', '', '', 0, '');
    if (empty($dashboard_external_recipes)) {
        $dashboard_external_recipes = dash_fetch_mealdb('', '');
    }
} catch (Exception $e) {
    $dashboard_external_recipes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard - EasyPrep</title>
	<link rel="stylesheet" href="styles/dashboard.css">
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
				<a href="dashboard.php" class="active">Dashboard</a>
				<a href="recipes.php">Recipes</a>
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

	<main class="dashboard-main">
		<!-- Welcome Section -->
		<section class="welcome-section">
			<div class="welcome-content">
				<div class="welcome-text">
					<h1>Welcome back!</h1>
					<p>Ready to plan your next delicious meal? Here's what's happening with your EasyPrep account.</p>
				</div>
				<div class="welcome-actions">
					<a href="meal-plan.php" class="btn-primary">
						<i class="fas fa-plus"></i>
						Create New Meal Plan
					</a>
					<a href="grocery.php" class="btn-secondary">
						<i class="fas fa-shopping-cart"></i>
						Order Groceries
					</a>
				</div>
			</div>
			<div class="welcome-decoration">


			</div>
		</section>

		<!-- Stats Overview -->
		<section class="stats-section">
			<div class="stats-grid">
				<div class="stat-card">
					<div class="stat-icon">
						<i class="fas fa-utensils"></i>
					</div>
					<div class="stat-content">
						<h3>2</h3>
						<p>Active Meal Plans</p>
					</div>
					<div class="stat-decoration">
						<img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Meal planning" class="stat-image">
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon">
						<i class="fas fa-shopping-basket"></i>
					</div>
					<div class="stat-content">
						<h3>3</h3>
						<p>Grocery Lists</p>
					</div>
					<div class="stat-decoration">
						<img src="https://images.unsplash.com/photo-1588964895597-cfccd6e2dbf9?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Grocery shopping" class="stat-image">
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon">
						<i class="fas fa-heart"></i>
					</div>
					<div class="stat-content">
						<h3>5</h3>
						<p>Saved Recipes</p>
					</div>
					<div class="stat-decoration">
						<img src="https://images.unsplash.com/photo-1506368249639-73a05d6f6488?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Saved recipes" class="stat-image">
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon">
						<i class="fas fa-calendar-check"></i>
					</div>
					<div class="stat-content">
						<h3>0</h3>
						<p>Meals Planned</p>
					</div>
					<div class="stat-decoration">
						<img src="https://images.unsplash.com/photo-1547592180-85f173990554?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Meals planned" class="stat-image">
					</div>
				</div>
			</div>
		</section>

		<!-- Main Content Grid -->
		<section class="dashboard-content">
			<div class="content-grid">
				<!-- Recent Meal Plans -->
				<div class="content-card meal-plans-card">
					<div class="card-header">
						<h2><i class="fas fa-utensils"></i> Recent Meal Plans</h2>
						<a href="meal-plan.php" class="view-all">View All</a>
					</div>
					<div class="card-content">
						<?php if ($meal_plans_result && $meal_plans_result->num_rows > 0): ?>
							<?php while ($meal_plan = $meal_plans_result->fetch_assoc()): ?>
								<div class="meal-plan-item">
									<div class="meal-plan-info">
										<h4><?php echo htmlspecialchars($meal_plan['name'] ?? 'Meal Plan'); ?></h4>
										<p>Week start: <?php echo isset($meal_plan['week_start_date']) ? date('M j, Y', strtotime($meal_plan['week_start_date'])) : 'N/A'; ?> • Servings: <?php echo (int)($meal_plan['servings'] ?? 2); ?> • Calories: <?php echo (int)($meal_plan['total_calories'] ?? 0); ?></p>
										<span class="meal-plan-date">Saved on <?php echo date('M j, Y', strtotime($meal_plan['created_at'] ?? 'now')); ?></span>
									</div>
									<div class="meal-plan-actions">
										<a class="btn-small" href="meal-plan.php?from_meal_plan=true&meal_plan_id=<?php echo (int)$meal_plan['id']; ?>" title="View"><i class="fas fa-eye"></i></a>
										<a class="btn-small" href="meal-plan.php" title="Create New"><i class="fas fa-plus"></i></a>
									</div>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<!-- Show dummy meal plans for demonstration -->
							<div class="meal-plan-item">
								<div class="meal-plan-info">
									<h4>Weekly Bengali Feast</h4>
									<p>Week start: Dec 16, 2024 • Servings: 4 • Calories: 2400</p>
									<span class="meal-plan-date">Saved on Dec 15, 2024</span>
								</div>
								<div class="meal-plan-actions">
									<a class="btn-small" href="meal-plan.php" title="View"><i class="fas fa-eye"></i></a>
									<a class="btn-small" href="meal-plan.php" title="Create New"><i class="fas fa-plus"></i></a>
								</div>
							</div>
							<div class="meal-plan-item">
								<div class="meal-plan-info">
									<h4>Quick Weeknight Dinners</h4>
									<p>Week start: Dec 9, 2024 • Servings: 2 • Calories: 1800</p>
									<span class="meal-plan-date">Saved on Dec 8, 2024</span>
								</div>
								<div class="meal-plan-actions">
									<a class="btn-small" href="meal-plan.php" title="View"><i class="fas fa-eye"></i></a>
									<a class="btn-small" href="meal-plan.php" title="Create New"><i class="fas fa-plus"></i></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="content-card quick-actions-card">
					<div class="card-header">
						<h2><i class="fas fa-bolt"></i> Quick Actions</h2>
					</div>
					<div class="card-content">
						<div class="quick-actions-grid">
							<a href="meal-plan.php" class="quick-action">
								<div class="quick-action-icon">
									<i class="fas fa-plus"></i>
								</div>
								<span>New Meal Plan</span>
							</a>
							<a href="grocery.php" class="quick-action">
								<div class="quick-action-icon">
									<i class="fas fa-shopping-cart"></i>
								</div>
								<span>Order Groceries</span>
							</a>
							<a href="recipes.php" class="quick-action">
								<div class="quick-action-icon">
									<i class="fas fa-search"></i>
								</div>
								<span>Find Recipes</span>
							</a>
							<a href="meal-plan.php" class="quick-action">
								<div class="quick-action-icon">
									<i class="fas fa-calendar"></i>
								</div>
								<span>Schedule Meals</span>
							</a>
							<a href="#" class="quick-action">
								<div class="quick-action-icon">
									<i class="fas fa-heart"></i>
								</div>
								<span>Favorites</span>
							</a>
							<a href="#" class="quick-action">
								<div class="quick-action-icon">
									<i class="fas fa-cog"></i>
								</div>
								<span>Settings</span>
							</a>
						</div>
					</div>
				</div>

				<!-- Featured Recipes -->
				<div class="content-card recipes-card">
					<div class="card-header">
						<h2><i class="fas fa-book-open"></i> Featured Recipes</h2>
						<a href="recipes.php" class="view-all">View All</a>
					</div>
					<div class="card-content">
						<?php 
						$recipe_images = [
							'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
							'https://images.unsplash.com/photo-1495521821757-a1efb6729352?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
							'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
							'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
							'https://images.unsplash.com/photo-1490645935967-10de6ba17061?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
							'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'
						];

						// Prefer external recipes fetched above
						if (!empty($dashboard_external_recipes)):
						?>
							<div class="recipes-grid">
								<?php foreach ($dashboard_external_recipes as $ext): ?>
									<div class="recipe-item" <?php if (!empty($ext['source_url'])): ?>onclick="window.open('<?php echo htmlspecialchars($ext['source_url']); ?>','_blank')"<?php endif; ?>>
										<div class="recipe-image">
											<img src="<?php echo htmlspecialchars($ext['image_url']); ?>" alt="<?php echo htmlspecialchars($ext['name']); ?>">
											<div class="recipe-badges">
												<span class="cuisine-badge"><?php echo htmlspecialchars($ext['cuisine_type']); ?></span>
											</div>
										</div>
										<div class="recipe-info">
											<h4><?php echo htmlspecialchars($ext['name']); ?></h4>
											<p><?php echo htmlspecialchars(substr($ext['description'], 0, 60)) . '...'; ?></p>
											<div class="recipe-meta">
												<?php if (!empty($ext['cooking_time'])): ?>
													<span><i class="fas fa-clock"></i> <?php echo (int)$ext['cooking_time']; ?> min</span>
												<?php endif; ?>
												<?php if (!empty($ext['servings'])): ?>
													<span><i class="fas fa-users"></i> <?php echo (int)$ext['servings']; ?> servings</span>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php elseif ($recipes_result && $recipes_result->num_rows > 0):
						?>
							<div class="recipes-grid">
								<?php 
								$image_index = 0;
								while ($recipe = $recipes_result->fetch_assoc()): 
									$current_image = $recipe_images[$image_index % count($recipe_images)];
									$image_index++;
								?>
									<div class="recipe-item" onclick="window.location.href='recipe-detail.php?id=<?php echo $recipe['id']; ?>'">
										<div class="recipe-image">
											<img src="<?php echo $recipe['image_url'] ?? $current_image; ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
											<div class="recipe-badges">
												<span class="cuisine-badge"><?php echo $recipe['cuisine_type']; ?></span>
												<?php if ($recipe['is_vegetarian']): ?>
													<span class="diet-badge veg">Veg</span>
												<?php endif; ?>
											</div>
										</div>
										<div class="recipe-info">
											<h4><?php echo htmlspecialchars($recipe['name']); ?></h4>
											<p><?php echo htmlspecialchars(substr($recipe['description'], 0, 60)) . '...'; ?></p>
											<div class="recipe-meta">
												<span><i class="fas fa-clock"></i> <?php echo $recipe['cooking_time']; ?> min</span>
												<span><i class="fas fa-users"></i> <?php echo $recipe['servings']; ?> servings</span>
												<span><i class="fas fa-signal"></i> <?php echo $recipe['difficulty']; ?></span>
											</div>
											<div class="recipe-rating">
												<?php if ($recipe['rating'] > 0): ?>
													<span class="rating">
														<i class="fas fa-star"></i> <?php echo number_format($recipe['rating'], 1); ?>
														<small>(<?php echo $recipe['total_ratings']; ?>)</small>
													</span>
												<?php else: ?>
													<span class="rating">
														<i class="fas fa-star-o"></i> New Recipe
													</span>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endwhile; ?>
							</div>
						<?php else: 
							// Show sample Bengali recipes if database is empty
							$sample_recipes = [
								['name' => 'Hilsa Fish Curry', 'description' => 'Traditional Bengali hilsa curry with mustard and spices', 'time' => '45'],
								['name' => 'Chingri Malai Curry', 'description' => 'Creamy prawn curry with coconut milk', 'time' => '35'],
								['name' => 'Dal Tadka', 'description' => 'Spiced yellow lentils with aromatic tempering', 'time' => '25'],
								['name' => 'Aloo Posto', 'description' => 'Potatoes cooked in poppy seed paste', 'time' => '30'],
								['name' => 'Rui Macher Jhol', 'description' => 'Bengali fish curry with vegetables', 'time' => '40'],
								['name' => 'Chicken Kosha', 'description' => 'Slow-cooked spicy Bengali chicken curry', 'time' => '60']
							];
						?>
							<div class="recipes-grid">
								<?php foreach ($sample_recipes as $index => $recipe): ?>
									<div class="recipe-item">
										<div class="recipe-image">
											<img src="<?php echo $recipe_images[$index % count($recipe_images)]; ?>" alt="Recipe">
										</div>
										<div class="recipe-info">
											<h4><?php echo htmlspecialchars($recipe['name']); ?></h4>
											<p><?php echo htmlspecialchars($recipe['description']); ?></p>
											<div class="recipe-meta">
												<span><i class="fas fa-clock"></i> <?php echo $recipe['time']; ?> min</span>
												<span><i class="fas fa-users"></i> 4 servings</span>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Grocery Lists -->
				<div class="content-card grocery-card">
					<div class="card-header">
						<h2><i class="fas fa-shopping-basket"></i> Recent Grocery Lists</h2>
						<a href="grocery.php" class="view-all">View All</a>
					</div>
					<div class="card-content">
						<?php if ($grocery_result && $grocery_result->num_rows > 0): ?>
							<div class="grocery-list-grid">
								<?php while ($grocery = $grocery_result->fetch_assoc()): ?>
									<div class="grocery-item">
										<div class="grocery-info">
											<h4><?php echo htmlspecialchars($grocery['name'] ?? 'Grocery List'); ?></h4>
											<p><?php echo htmlspecialchars($grocery['description'] ?? 'Weekly groceries'); ?></p>
											<span class="grocery-date"><?php echo date('M j, Y', strtotime($grocery['created_at'] ?? 'now')); ?></span>
										</div>
										<div class="grocery-actions">
											<button class="btn-small"><i class="fas fa-eye"></i></button>
											<button class="btn-small"><i class="fas fa-shopping-cart"></i></button>
										</div>
									</div>
								<?php endwhile; ?>
							</div>
						<?php else: ?>
							<div class="empty-state">
								<i class="fas fa-shopping-basket"></i>
								<h3>No grocery lists yet</h3>
								<p>Create your first grocery list!</p>
								<a href="grocery.php" class="btn-primary">Create List</a>
							</div>
						<?php endif; ?>
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

	<script src="scripts/dashboard.js"></script>
</body>
</html>

