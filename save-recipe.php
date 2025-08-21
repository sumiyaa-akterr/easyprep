<?php
session_start();
include("connect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$email = $_SESSION['email'];

// Resolve current user id
$user_id = null;
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
if ($user_stmt) {
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_res = $user_stmt->get_result();
    $row = $user_res ? $user_res->fetch_assoc() : null;
    if ($row && isset($row['id'])) {
        $user_id = (int)$row['id'];
    }
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Ensure saved-recipes pivot table exists
try {
    $conn->query("CREATE TABLE IF NOT EXISTS user_saved_recipes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        recipe_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_recipe (user_id, recipe_id)
    )");
} catch (Exception $e) {
    // Continue; we'll still try to respond gracefully
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit();
}

$action = isset($data['action']) ? $data['action'] : '';

if ($action === 'unsave' && isset($data['recipe_id'])) {
    $rid = (int)$data['recipe_id'];
    $del_stmt = $conn->prepare("DELETE FROM user_saved_recipes WHERE user_id = ? AND recipe_id = ?");
    if ($del_stmt) {
        $del_stmt->bind_param("ii", $user_id, $rid);
        $del_stmt->execute();
    }
    echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
    exit();
}

if ($action === 'save') {
    // External save: if external_id present, upsert into recipes table minimally
    $isExternal = isset($data['external_id']) && isset($data['name']);
    if ($isExternal) {
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $cuisine = $data['cuisine_type'] ?? 'Other';
        $cooking_time = !empty($data['cooking_time']) ? (int)$data['cooking_time'] : 30;
        $servings = !empty($data['servings']) ? (int)$data['servings'] : 2;
        $image_url = $data['image_url'] ?? '';

        // Check recipes table exists
        $recipes_table_exists = false;
        try {
            $check = $conn->query("SHOW TABLES LIKE 'recipes'");
            $recipes_table_exists = $check && $check->num_rows > 0;
        } catch (Exception $e) {
            $recipes_table_exists = false;
        }

        $recipe_id = null;
        if ($recipes_table_exists) {
            // Try to find an existing recipe by name + image_url
            $find_stmt = $conn->prepare("SELECT id FROM recipes WHERE name = ? AND (image_url = ? OR (image_url IS NULL AND ? = '')) LIMIT 1");
            if ($find_stmt) {
                $img_param = $image_url;
                $find_stmt->bind_param("sss", $name, $image_url, $img_param);
                $find_stmt->execute();
                $find_res = $find_stmt->get_result();
                $found = $find_res ? $find_res->fetch_assoc() : null;
                if ($found && isset($found['id'])) {
                    $recipe_id = (int)$found['id'];
                }
            }

            if (!$recipe_id) {
                // Minimal insert to make recipe available locally
                $ins_stmt = $conn->prepare("INSERT INTO recipes (name, description, cuisine_type, cooking_time, servings, difficulty, calories_per_serving, ingredients, instructions, image_url, tags, is_vegetarian, is_vegan, is_gluten_free) VALUES (?,?,?,?,?, 'Medium', NULL, '', '', ?, '', 0, 0, 0)");
                if ($ins_stmt) {
                    $ins_stmt->bind_param("sssiis", $name, $description, $cuisine, $cooking_time, $servings, $image_url);
                    if ($ins_stmt->execute()) {
                        $recipe_id = (int)$conn->insert_id;
                    }
                }
            }
        }

        if ($recipe_id) {
            // Link to user favorites (ignore duplicates)
            $link_stmt = $conn->prepare("INSERT IGNORE INTO user_saved_recipes (user_id, recipe_id) VALUES (?, ?)");
            if ($link_stmt) {
                $link_stmt->bind_param("ii", $user_id, $recipe_id);
                $link_stmt->execute();
            }
            echo json_encode(['success' => true, 'message' => 'Saved to favorites', 'recipe_id' => $recipe_id]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to save recipe (missing recipes table?)']);
            exit();
        }
    }

    // Local save by recipe_id (already in DB)
    if (isset($data['recipe_id'])) {
        $rid = (int)$data['recipe_id'];
        $link_stmt = $conn->prepare("INSERT IGNORE INTO user_saved_recipes (user_id, recipe_id) VALUES (?, ?)");
        if ($link_stmt) {
            $link_stmt->bind_param("ii", $user_id, $rid);
            $link_stmt->execute();
        }
        echo json_encode(['success' => true, 'message' => 'Saved to favorites']);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Unsupported action']);


