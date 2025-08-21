<?php
session_start();
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    // Get popular grocery products
    $popular_products = getPopularProducts();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'products' => $popular_products,
        'total_results' => count($popular_products)
    ]);
    
} catch (Exception $e) {
    // Return fallback products on error
    $fallback_products = getFallbackProducts();
    
    echo json_encode([
        'success' => true,
        'products' => $fallback_products,
        'total_results' => count($fallback_products),
        'message' => 'Using fallback products due to error: ' . $e->getMessage()
    ]);
}

// Get popular grocery products
function getPopularProducts() {
    return [
        [
            'id' => 'popular-1',
            'title' => 'Fresh Bananas',
            'description' => 'Sweet and ripe bananas, perfect for snacking or baking',
            'image' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 2.99,
            'unit' => 'bunch',
            'category' => 'produce',
            'badges' => [['type' => 'organic', 'text' => 'Organic']]
        ],
        [
            'id' => 'popular-2',
            'title' => 'Chicken Breast',
            'description' => 'Boneless, skinless chicken breast, perfect for healthy meals',
            'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 7.99,
            'unit' => 'lb',
            'category' => 'meat',
            'badges' => []
        ],
        [
            'id' => 'popular-3',
            'title' => 'Fresh Eggs',
            'description' => 'Farm-fresh large eggs, great for breakfast and baking',
            'image' => 'https://images.unsplash.com/photo-1569288063648-5bb7eaa0c8b5?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.99,
            'unit' => 'dozen',
            'category' => 'dairy',
            'badges' => []
        ],
        [
            'id' => 'popular-4',
            'title' => 'Roma Tomatoes',
            'description' => 'Fresh, ripe tomatoes perfect for cooking and salads',
            'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 3.49,
            'unit' => 'lb',
            'category' => 'produce',
            'badges' => []
        ],
        [
            'id' => 'popular-5',
            'title' => 'Basmati Rice',
            'description' => 'Aromatic long-grain rice, perfect for Indian and Asian dishes',
            'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 8.99,
            'unit' => '5lb bag',
            'category' => 'pantry',
            'badges' => []
        ],
        [
            'id' => 'popular-6',
            'title' => 'Whole Milk',
            'description' => 'Fresh whole milk, great for drinking and cooking',
            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 4.29,
            'unit' => 'gallon',
            'category' => 'dairy',
            'badges' => []
        ],
        [
            'id' => 'popular-7',
            'title' => 'Atlantic Salmon',
            'description' => 'Fresh salmon fillet, wild caught and sustainably sourced',
            'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 12.99,
            'unit' => 'lb',
            'category' => 'meat',
            'badges' => []
        ],
        [
            'id' => 'popular-8',
            'title' => 'Mixed Vegetables',
            'description' => 'Frozen mixed vegetables, convenient and nutritious',
            'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            'price' => 3.99,
            'unit' => '16oz bag',
            'category' => 'frozen',
            'badges' => []
        ]
    ];
}

// Fallback products when there's an error
function getFallbackProducts() {
    return getPopularProducts();
}
?>
