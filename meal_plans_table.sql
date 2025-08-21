-- Meal Plans Table for EasyPrep
CREATE TABLE IF NOT EXISTS meal_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'My Meal Plan',
    servings INT NOT NULL DEFAULT 2,
    dietary_restrictions JSON, -- Store as JSON: {"nuts": false, "gluten": false, "dairy": false, "vegetarian": true, "vegan": false, "low_carb": false}
    week_start_date DATE NOT NULL,
    meal_data JSON NOT NULL, -- Store complete meal plan as JSON
    total_calories INT DEFAULT 0,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Grocery Lists Table for EasyPrep
CREATE TABLE IF NOT EXISTS grocery_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_plan_id INT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'Grocery List',
    items JSON NOT NULL, -- Store grocery items as JSON: [{"name": "Rice", "quantity": "2kg", "price": 150, "category": "Grains"}]
    total_items INT DEFAULT 0,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'ordered', 'delivered') DEFAULT 'pending',
    delivery_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (meal_plan_id) REFERENCES meal_plans(id) ON DELETE SET NULL
);






