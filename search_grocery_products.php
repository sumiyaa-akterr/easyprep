<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get search parameters
$query = $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20; // Products per page

try {
    // Get curated grocery products based on search/category
    $products = getGroceryProducts($query, $category, $page, $limit);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total_results' => count($products),
        'page' => $page,
        'has_more' => count($products) === $limit
    ]);
    
} catch (Exception $e) {
    // Return fallback products on error
    $fallback_products = getFallbackProducts($query, $category);
    
    echo json_encode([
        'success' => true,
        'products' => $fallback_products,
        'total_results' => count($fallback_products),
        'message' => 'Using fallback products due to error: ' . $e->getMessage()
    ]);
}

// Get curated grocery products
function getGroceryProducts($query = '', $category = '', $page = 1, $limit = 20) {
    $all_products = [
        // PRODUCE
        [
            'id' => 'produce-1',
            'title' => 'Fresh Bananas',
            'description' => 'Sweet and ripe bananas, perfect for snacking or baking',
            'image' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 2.99,
            'unit' => 'bunch',
            'category' => 'produce',
            'badges' => [['type' => 'organic', 'text' => 'Organic']]
        ],
        [
            'id' => 'produce-2',
            'title' => 'Roma Tomatoes',
            'description' => 'Fresh, ripe tomatoes perfect for cooking and salads',
            'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 3.49,
            'unit' => 'lb',
            'category' => 'produce',
            'badges' => []
        ],
        [
            'id' => 'produce-3',
            'title' => 'Fresh Spinach',
            'description' => 'Crisp, leafy spinach for salads and cooking',
            'image' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.49,
            'unit' => 'bag',
            'category' => 'produce',
            'badges' => [['type' => 'organic', 'text' => 'Organic']]
        ],
        [
            'id' => 'produce-4',
            'title' => 'Sweet Onions',
            'description' => 'Large sweet onions, great for cooking',
            'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 2.99,
            'unit' => 'lb',
            'category' => 'produce',
            'badges' => []
        ],
        [
            'id' => 'produce-5',
            'title' => 'Fresh Garlic',
            'description' => 'Plump garlic bulbs for cooking',
            'image' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 1.99,
            'unit' => 'head',
            'category' => 'produce',
            'badges' => []
        ],
        [
            'id' => 'produce-6',
            'title' => 'Red Bell Peppers',
            'description' => 'Sweet red bell peppers, perfect for salads',
            'image' => 'https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.99,
            'unit' => 'lb',
            'category' => 'produce',
            'badges' => []
        ],
        
        // MEAT & SEAFOOD
        [
            'id' => 'meat-1',
            'title' => 'Chicken Breast',
            'description' => 'Boneless, skinless chicken breast, perfect for healthy meals',
            'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 7.99,
            'unit' => 'lb',
            'category' => 'meat',
            'badges' => []
        ],
        [
            'id' => 'meat-2',
            'title' => 'Ground Beef',
            'description' => 'Lean ground beef, 85% lean',
            'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 8.99,
            'unit' => 'lb',
            'category' => 'meat',
            'badges' => []
        ],
        [
            'id' => 'meat-3',
            'title' => 'Atlantic Salmon',
            'description' => 'Fresh salmon fillet, wild caught and sustainably sourced',
            'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 12.99,
            'unit' => 'lb',
            'category' => 'meat',
            'badges' => []
        ],
        [
            'id' => 'meat-4',
            'title' => 'Pork Chops',
            'description' => 'Center-cut pork chops, bone-in',
            'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 6.99,
            'unit' => 'lb',
            'category' => 'meat',
            'badges' => []
        ],
        
        // DAIRY & EGGS
        [
            'id' => 'dairy-1',
            'title' => 'Fresh Eggs',
            'description' => 'Farm-fresh large eggs, great for breakfast and baking',
            'image' => 'https://images.unsplash.com/photo-1569288063648-5bb7eaa0c8b5?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.99,
            'unit' => 'dozen',
            'category' => 'dairy',
            'badges' => []
        ],
        [
            'id' => 'dairy-2',
            'title' => 'Whole Milk',
            'description' => 'Fresh whole milk, great for drinking and cooking',
            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.29,
            'unit' => 'gallon',
            'category' => 'dairy',
            'badges' => []
        ],
        [
            'id' => 'dairy-3',
            'title' => 'Sharp Cheddar Cheese',
            'description' => 'Aged sharp cheddar cheese, perfect for cooking',
            'image' => 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 5.99,
            'unit' => '8oz',
            'category' => 'dairy',
            'badges' => []
        ],
        [
            'id' => 'dairy-4',
            'title' => 'Greek Yogurt',
            'description' => 'Creamy Greek yogurt, high in protein',
            'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 6.49,
            'unit' => '32oz',
            'category' => 'dairy',
            'badges' => [['type' => 'high-protein', 'text' => 'High Protein']]
        ],
        
        // PANTRY
        [
            'id' => 'pantry-1',
            'title' => 'Basmati Rice',
            'description' => 'Aromatic long-grain rice, perfect for Indian and Asian dishes',
            'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 8.99,
            'unit' => '5lb bag',
            'category' => 'pantry',
            'badges' => []
        ],
        [
            'id' => 'pantry-2',
            'title' => 'Spaghetti Pasta',
            'description' => 'Italian spaghetti pasta, perfect for classic dishes',
            'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 2.49,
            'unit' => '1lb box',
            'category' => 'pantry',
            'badges' => []
        ],
        [
            'id' => 'pantry-3',
            'title' => 'Extra Virgin Olive Oil',
            'description' => 'Premium extra virgin olive oil for cooking and dressings',
            'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 12.99,
            'unit' => '16.9oz',
            'category' => 'pantry',
            'badges' => [['type' => 'organic', 'text' => 'Organic']]
        ],
        [
            'id' => 'pantry-4',
            'title' => 'All-Purpose Flour',
            'description' => 'Versatile all-purpose flour for baking and cooking',
            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 3.49,
            'unit' => '5lb bag',
            'category' => 'pantry',
            'badges' => []
        ],
        [
            'id' => 'pantry-5',
            'title' => 'Canned Black Beans',
            'description' => 'Nutritious black beans, ready to use',
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 1.49,
            'unit' => '15oz can',
            'category' => 'pantry',
            'badges' => [['type' => 'vegan', 'text' => 'Vegan']]
        ],
        
        // FROZEN
        [
            'id' => 'frozen-1',
            'title' => 'Mixed Vegetables',
            'description' => 'Frozen mixed vegetables, convenient and nutritious',
            'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 3.99,
            'unit' => '16oz bag',
            'category' => 'frozen',
            'badges' => []
        ],
        [
            'id' => 'frozen-2',
            'title' => 'Frozen Berries',
            'description' => 'Mixed frozen berries for smoothies and baking',
            'image' => 'https://images.unsplash.com/photo-1498557850523-fd3d118b962e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 5.99,
            'unit' => '16oz bag',
            'category' => 'frozen',
            'badges' => []
        ],
        
        // BEVERAGES
        [
            'id' => 'beverages-1',
            'title' => 'Sparkling Water',
            'description' => 'Refreshing sparkling water, zero calories',
            'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.99,
            'unit' => '12-pack',
            'category' => 'beverages',
            'badges' => [['type' => 'zero-calorie', 'text' => 'Zero Calorie']]
        ],
        [
            'id' => 'beverages-2',
            'title' => 'Orange Juice',
            'description' => 'Fresh-squeezed orange juice, no pulp',
            'image' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 5.49,
            'unit' => '64oz',
            'category' => 'beverages',
            'badges' => []
        ]
    ];
    
    // Filter by query if provided
    if ($query) {
        $query_lower = strtolower($query);
        $filtered_products = array_filter($all_products, function($product) use ($query_lower) {
            return strpos(strtolower($product['title']), $query_lower) !== false ||
                   strpos(strtolower($product['description']), $query_lower) !== false ||
                   strpos(strtolower($product['category']), $query_lower) !== false;
        });
        $all_products = array_values($filtered_products);
    }
    
    // Filter by category if provided
    if ($category) {
        $filtered_products = array_filter($all_products, function($product) use ($category) {
            return $product['category'] === $category;
        });
        $all_products = array_values($filtered_products);
    }
    
    // Apply pagination
    $start = ($page - 1) * $limit;
    $products = array_slice($all_products, $start, $limit);
    
    return $products;
}

// Fallback products when there's an error
function getFallbackProducts($query = '', $category = '') {
    return getGroceryProducts($query, $category, 1, 8);
}
?>
