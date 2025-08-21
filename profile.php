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

// Get user's meal plans count
$meal_plans_count = 0;
try {
    $check_table = $conn->query("SHOW TABLES LIKE 'meal_plans'");
    if ($check_table->num_rows > 0) {
        $count_query = "SELECT COUNT(*) as count FROM meal_plans WHERE user_id = ?";
        $stmt = $conn->prepare($count_query);
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $meal_plans_count = $count_result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    $meal_plans_count = 0;
}

// Get user's grocery lists count
$grocery_lists_count = 0;
try {
    $check_table = $conn->query("SHOW TABLES LIKE 'grocery_lists'");
    if ($check_table->num_rows > 0) {
        $count_query = "SELECT COUNT(*) as count FROM grocery_lists WHERE user_id = ?";
        $stmt = $conn->prepare($count_query);
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $grocery_lists_count = $count_result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    $grocery_lists_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EasyPrep</title>
    <link rel="stylesheet" href="styles/dashboard.css">
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
                        <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
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
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h1>My Profile</h1>
                    <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
                    <p class="profile-member-since">Member since <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?></p>
                </div>
            </div>

            <!-- Profile Stats -->
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $meal_plans_count; ?></div>
                        <div class="stat-label">Meal Plans Created</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $grocery_lists_count; ?></div>
                        <div class="stat-label">Grocery Lists</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">∞</div>
                        <div class="stat-label">Recipes Available</div>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <div class="detail-section">
                    <h2><i class="fas fa-info-circle"></i> Account Information</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Email Address</label>
                            <div class="detail-value"><?php echo htmlspecialchars($email); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Account Type</label>
                            <div class="detail-value">Free Member</div>
                        </div>
                        <div class="detail-item">
                            <label>Member Since</label>
                            <div class="detail-value"><?php echo date('F j, Y', strtotime($user['created_at'] ?? 'now')); ?></div>
                        </div>
                        <div class="detail-item">
                            <label>Last Login</label>
                            <div class="detail-value"><?php echo date('F j, Y g:i A'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2><i class="fas fa-chart-line"></i> Activity Summary</h2>
                    <div class="activity-summary">
                        <div class="activity-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>You've created <?php echo $meal_plans_count; ?> meal plan<?php echo $meal_plans_count != 1 ? 's' : ''; ?> so far</span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-shopping-basket"></i>
                            <span>You've generated <?php echo $grocery_lists_count; ?> grocery list<?php echo $grocery_lists_count != 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-star"></i>
                            <span>You have access to unlimited recipes</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2><i class="fas fa-cog"></i> Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="meal-plan.php" class="action-btn">
                            <i class="fas fa-plus"></i>
                            Create New Meal Plan
                        </a>
                        <a href="recipes.php" class="action-btn">
                            <i class="fas fa-search"></i>
                            Browse Recipes
                        </a>
                        <a href="grocery.php" class="action-btn">
                            <i class="fas fa-shopping-cart"></i>
                            Order Groceries
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="scripts/dashboard.js"></script>
</body>
</html>
