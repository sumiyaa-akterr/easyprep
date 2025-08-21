<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$meal_plan_id = $input['meal_plan_id'] ?? null;

if (!$meal_plan_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Meal plan ID required']);
    exit();
}

try {
    // Get user ID from email
    $email = $_SESSION['email'];
    $user_query = "SELECT id FROM users WHERE email = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Get the meal plan data
    $meal_plan_query = "SELECT * FROM meal_plans WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($meal_plan_query);
    $stmt->bind_param("ii", $meal_plan_id, $user['id']);
    $stmt->execute();
    $meal_plan_result = $stmt->get_result();
    $meal_plan = $meal_plan_result->fetch_assoc();
    
    if (!$meal_plan) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Meal plan not found']);
        exit();
    }
    
    // Parse the meal data
    $meal_data = json_decode($meal_plan['meal_data'], true);
    
    if (!$meal_data) {
        echo json_encode(['success' => false, 'message' => 'No meal data found']);
        exit();
    }
    
    // Extract ingredients from all meals
    $ingredients = [];
    $ingredient_counts = [];
    
    foreach ($meal_data as $day => $meals) {
        if (is_array($meals)) {
            foreach ($meals as $meal) {
                if (isset($meal['ingredients']) && is_array($meal['ingredients'])) {
                    foreach ($meal['ingredients'] as $ingredient) {
                        $name = strtolower(trim($ingredient['name']));
                        
                        if (!empty($name)) {
                            if (isset($ingredient_counts[$name])) {
                                $ingredient_counts[$name]['quantity'] += $ingredient['amount'] ?? 1;
                            } else {
                                $ingredient_counts[$name] = [
                                    'name' => ucfirst($name),
                                    'quantity' => $ingredient['amount'] ?? 1,
                                    'unit' => $ingredient['unit'] ?? 'piece',
                                    'estimated_price' => estimateIngredientPrice($name, $ingredient['amount'] ?? 1)
                                ];
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Convert to array format
    $ingredients = array_values($ingredient_counts);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients,
        'total_ingredients' => count($ingredients)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// Helper function to estimate ingredient prices
function estimateIngredientPrice($ingredient_name, $quantity) {
    $price_map = [
        'chicken' => 7.99,
        'beef' => 8.99,
        'pork' => 6.99,
        'salmon' => 12.99,
        'rice' => 8.99,
        'pasta' => 2.49,
        'tomatoes' => 3.49,
        'onions' => 2.99,
        'garlic' => 1.99,
        'potatoes' => 4.99,
        'carrots' => 2.49,
        'broccoli' => 3.99,
        'spinach' => 4.49,
        'eggs' => 4.99,
        'milk' => 4.29,
        'cheese' => 5.99,
        'bread' => 3.99,
        'flour' => 3.49,
        'sugar' => 2.99,
        'oil' => 4.99
    ];
    
    $base_price = $price_map[strtolower($ingredient_name)] ?? 3.99;
    return round($base_price * $quantity, 2);
}
?>
