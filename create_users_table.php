<?php
// Script to check and create users table if needed
include("connect.php");

echo "<h2>Checking Users Table...</h2>";

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ users table exists</p>";
    
    // Check the structure
    $structure = $conn->query("DESCRIBE users");
    echo "<h3>Users Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<p style='color: red;'>❌ users table NOT found. Creating it now...</p>";
    
    // Create users table
    $users_sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($users_sql) === TRUE) {
        echo "<p style='color: green;'>✅ users table created successfully!</p>";
        
        // Insert a test user if none exist
        $check_users = $conn->query("SELECT COUNT(*) as count FROM users");
        $user_count = $check_users->fetch_assoc()['count'];
        
        if ($user_count == 0) {
            echo "<p>No users found. You may need to register a user first.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error creating users table: " . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='meal-plan.php'>← Back to Meal Plans</a></p>";
echo "<p><strong>Note:</strong> You can delete this file (create_users_table.php) after running it successfully.</p>";
?>


