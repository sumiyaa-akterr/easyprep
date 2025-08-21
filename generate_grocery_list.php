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

// Handle grocery list generation from meal plan
if ($_POST && isset($_POST['meal_plan_data'])) {
    $meal_plan = json_decode($_POST['meal_plan_data'], true);
    $grocery_list = generateGroceryListFromMealPlan($meal_plan);
    
    // Save grocery list to database
    $grocery_list_id = saveGroceryList($conn, $user['id'], $grocery_list);
    
    // Redirect to grocery page with pre-filled list
    header("Location: grocery.php?list_id=" . $grocery_list_id);
    exit();
}

function generateGroceryListFromMealPlan($meal_plan) {
    // Base ingredients calculation for common Bengali/Indian recipes
    $base_ingredients = [
        'Rice' => ['quantity' => 2, 'unit' => 'kg', 'price' => 150, 'category' => 'Grains'],
        'Oil' => ['quantity' => 1, 'unit' => 'L', 'price' => 120, 'category' => 'Cooking'],
        'Onions' => ['quantity' => 1, 'unit' => 'kg', 'price' => 80, 'category' => 'Vegetables'],
        'Garlic' => ['quantity' => 250, 'unit' => 'g', 'price' => 60, 'category' => 'Vegetables'],
        'Ginger' => ['quantity' => 200, 'unit' => 'g', 'price' => 50, 'category' => 'Vegetables'],
        'Tomatoes' => ['quantity' => 1, 'unit' => 'kg', 'price' => 100, 'category' => 'Vegetables'],
        'Potatoes' => ['quantity' => 1, 'unit' => 'kg', 'price' => 40, 'category' => 'Vegetables'],
        'Turmeric Powder' => ['quantity' => 100, 'unit' => 'g', 'price' => 30, 'category' => 'Spices'],
        'Red Chili Powder' => ['quantity' => 100, 'unit' => 'g', 'price' => 40, 'category' => 'Spices'],
        'Cumin Powder' => ['quantity' => 50, 'unit' => 'g', 'price' => 35, 'category' => 'Spices'],
        'Coriander Powder' => ['quantity' => 50, 'unit' => 'g', 'price' => 30, 'category' => 'Spices'],
        'Salt' => ['quantity' => 1, 'unit' => 'kg', 'price' => 25, 'category' => 'Condiments'],
        'Lentils (Dal)' => ['quantity' => 1, 'unit' => 'kg', 'price' => 120, 'category' => 'Pulses']
    ];
    
    // Calculate servings multiplier
    $total_servings = 0;
    foreach ($meal_plan as $day => $meals) {
        foreach ($meals as $meal) {
            $total_servings += $meal['servings'];
        }
    }
    
    $multiplier = max(1, $total_servings / 21); // 21 = 7 days × 3 meals
    
    // Add protein based on meal types
    $protein_items = [
        'Chicken' => ['quantity' => 1, 'unit' => 'kg', 'price' => 300, 'category' => 'Meat'],
        'Fish' => ['quantity' => 1, 'unit' => 'kg', 'price' => 250, 'category' => 'Seafood'],
        'Eggs' => ['quantity' => 12, 'unit' => 'pieces', 'price' => 90, 'category' => 'Dairy'],
        'Paneer' => ['quantity' => 500, 'unit' => 'g', 'price' => 150, 'category' => 'Dairy']
    ];
    
    // Add vegetables
    $vegetable_items = [
        'Mixed Vegetables' => ['quantity' => 2, 'unit' => 'kg', 'price' => 200, 'category' => 'Vegetables'],
        'Green Chilies' => ['quantity' => 100, 'unit' => 'g', 'price' => 20, 'category' => 'Vegetables'],
        'Coriander Leaves' => ['quantity' => 100, 'unit' => 'g', 'price' => 15, 'category' => 'Herbs']
    ];
    
    // Combine all ingredients
    $grocery_list = array_merge($base_ingredients, $protein_items, $vegetable_items);
    
    // Apply multiplier and format for database
    $formatted_list = [];
    foreach ($grocery_list as $item_name => $details) {
        $adjusted_quantity = $details['quantity'] * $multiplier;
        $formatted_list[] = [
            'name' => $item_name,
            'quantity' => $adjusted_quantity,
            'unit' => $details['unit'],
            'price' => $details['price'],
            'category' => $details['category'],
            'selected' => true
        ];
    }
    
    return $formatted_list;
}

function saveGroceryList($conn, $user_id, $grocery_items) {
    try {
        // Create grocery_lists table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS grocery_lists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            meal_plan_id INT NULL,
            name VARCHAR(255) NOT NULL DEFAULT 'Grocery List',
            items JSON NOT NULL,
            total_items INT DEFAULT 0,
            total_cost DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('pending', 'ordered', 'delivered') DEFAULT 'pending',
            delivery_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($create_table);
        
        // Calculate totals
        $total_items = count($grocery_items);
        $total_cost = array_sum(array_column($grocery_items, 'price'));
        
        // Insert grocery list
        $insert_query = "INSERT INTO grocery_lists (user_id, name, items, total_items, total_cost) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $items_json = json_encode($grocery_items);
        $list_name = "Weekly Meal Plan Groceries - " . date('M d, Y');
        
        $stmt->bind_param("isiid", $user_id, $list_name, $items_json, $total_items, $total_cost);
        $stmt->execute();
        
        return $conn->insert_id;
        
    } catch (Exception $e) {
        error_log("Error saving grocery list: " . $e->getMessage());
        return false;
    }
}

// If accessed directly without POST data, redirect to meal planning
if (!$_POST) {
    header("Location: meal-plan.php");
    exit();
}
?>






