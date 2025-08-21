<?php
// Simple script to create the meal_plans and grocery_lists tables
include("connect.php");

echo "<h2>Creating Database Tables...</h2>";

// Create meal_plans table
$meal_plans_sql = "CREATE TABLE IF NOT EXISTS meal_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'My Meal Plan',
    servings INT NOT NULL DEFAULT 2,
    dietary_restrictions JSON,
    week_start_date DATE NOT NULL,
    meal_data JSON NOT NULL,
    total_calories INT DEFAULT 0,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($meal_plans_sql) === TRUE) {
    echo "<p style='color: green;'>✅ meal_plans table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating meal_plans table: " . $conn->error . "</p>";
}

// Create grocery_lists table
$grocery_lists_sql = "CREATE TABLE IF NOT EXISTS grocery_lists (
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (meal_plan_id) REFERENCES meal_plans(id) ON DELETE SET NULL
)";

if ($conn->query($grocery_lists_sql) === TRUE) {
    echo "<p style='color: green;'>✅ grocery_lists table created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating grocery_lists table: " . $conn->error . "</p>";
}

// Check if tables exist
$result = $conn->query("SHOW TABLES LIKE 'meal_plans'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ meal_plans table exists in database</p>";
} else {
    echo "<p style='color: red;'>❌ meal_plans table NOT found in database</p>";
}

$result = $conn->query("SHOW TABLES LIKE 'grocery_lists'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ grocery_lists table exists in database</p>";
} else {
    echo "<p style='color: red;'>❌ grocery_lists table NOT found in database</p>";
}

echo "<hr>";
echo "<p><a href='meal-plan.php'>← Back to Meal Plans</a></p>";
echo "<p><strong>Note:</strong> You can delete this file (create_tables.php) after running it successfully.</p>";
?>




