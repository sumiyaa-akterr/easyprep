-- Recipes Table for EasyPrep
CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    cuisine_type ENUM('Bangla', 'Thai', 'Korean', 'Indian', 'Italian', 'Mexican', 'Chinese', 'Japanese', 'Mediterranean', 'American', 'French', 'Other') NOT NULL,
    cooking_time INT NOT NULL, -- in minutes
    servings INT NOT NULL DEFAULT 2,
    difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Medium',
    calories_per_serving INT,
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    image_url VARCHAR(500),
    tags VARCHAR(500), -- comma-separated tags
    is_vegetarian BOOLEAN DEFAULT FALSE,
    is_vegan BOOLEAN DEFAULT FALSE,
    is_gluten_free BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample recipes
INSERT INTO recipes (name, description, cuisine_type, cooking_time, servings, difficulty, calories_per_serving, ingredients, instructions, image_url, tags, is_vegetarian, is_vegan, is_gluten_free) VALUES
-- Bangla Cuisine
('Bhuna Khichuri', 'A comforting rice and lentil dish with aromatic spices', 'Bangla', 45, 4, 'Medium', 350, 'Rice, Red lentils, Onion, Garlic, Ginger, Turmeric, Cumin, Bay leaves, Oil, Salt', '1. Wash rice and lentils\n2. Sauté onions, garlic, and ginger\n3. Add spices and cook\n4. Add rice and lentils\n5. Cook until done', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'comfort food,spicy,traditional', FALSE, FALSE, TRUE),

('Chingri Malai Curry', 'Creamy prawn curry with coconut milk', 'Bangla', 30, 3, 'Medium', 420, 'Prawns, Coconut milk, Onion, Garlic, Ginger, Turmeric, Red chili, Oil, Salt', '1. Clean and marinate prawns\n2. Sauté aromatics\n3. Add coconut milk\n4. Cook prawns until done', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'seafood,creamy,spicy', FALSE, FALSE, TRUE),

('Aloo Bhorta', 'Mashed potato with mustard oil and spices', 'Bangla', 20, 4, 'Easy', 180, 'Potatoes, Mustard oil, Onion, Green chili, Salt, Coriander', '1. Boil potatoes\n2. Mash with spices\n3. Add mustard oil\n4. Garnish with coriander', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'vegetarian,mashed,traditional', TRUE, FALSE, TRUE),

-- Thai Cuisine
('Pad Thai', 'Classic stir-fried rice noodles with eggs and tofu', 'Thai', 25, 2, 'Medium', 380, 'Rice noodles, Eggs, Tofu, Bean sprouts, Peanuts, Tamarind paste, Fish sauce, Sugar, Lime', '1. Soak noodles\n2. Stir-fry eggs and tofu\n3. Add noodles and sauce\n4. Garnish with peanuts', 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'noodles,stir-fry,quick', FALSE, FALSE, FALSE),

('Tom Yum Goong', 'Spicy and sour shrimp soup', 'Thai', 35, 4, 'Medium', 220, 'Shrimp, Lemongrass, Kaffir lime leaves, Galangal, Thai chilies, Fish sauce, Lime juice, Mushrooms', '1. Boil lemongrass and galangal\n2. Add shrimp and mushrooms\n3. Season with fish sauce and lime\n4. Garnish with cilantro', 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'soup,spicy,sour', FALSE, FALSE, TRUE),

('Green Curry', 'Creamy green curry with vegetables and coconut milk', 'Thai', 40, 3, 'Medium', 320, 'Green curry paste, Coconut milk, Vegetables, Thai basil, Fish sauce, Palm sugar', '1. Fry curry paste\n2. Add coconut milk\n3. Add vegetables\n4. Season and serve', 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'curry,creamy,vegetarian', TRUE, FALSE, TRUE),

-- Korean Cuisine
('Bibimbap', 'Mixed rice bowl with vegetables and egg', 'Korean', 30, 2, 'Medium', 450, 'Rice, Spinach, Carrots, Bean sprouts, Egg, Gochujang, Sesame oil, Soy sauce', '1. Cook rice\n2. Prepare vegetables\n3. Fry egg\n4. Assemble bowl\n5. Add gochujang', 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'rice bowl,mixed,healthy', FALSE, FALSE, TRUE),

('Kimchi Jjigae', 'Spicy kimchi stew with pork and tofu', 'Korean', 45, 4, 'Medium', 280, 'Kimchi, Pork belly, Tofu, Onion, Garlic, Gochugaru, Sesame oil', '1. Sauté pork and kimchi\n2. Add water and simmer\n3. Add tofu\n4. Season and serve', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'stew,spicy,traditional', FALSE, FALSE, TRUE),

('Bulgogi', 'Marinated beef with sweet and savory sauce', 'Korean', 25, 4, 'Easy', 380, 'Beef, Soy sauce, Sugar, Sesame oil, Garlic, Ginger, Green onions', '1. Marinate beef\n2. Grill or pan-fry\n3. Garnish with green onions', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'beef,grilled,marinated', FALSE, FALSE, TRUE),

-- Indian Cuisine
('Butter Chicken', 'Creamy tomato-based curry with tender chicken', 'Indian', 50, 4, 'Medium', 420, 'Chicken, Yogurt, Tomato puree, Cream, Butter, Garam masala, Fenugreek, Kasoori methi', '1. Marinate chicken\n2. Grill chicken\n3. Make sauce\n4. Combine and simmer', 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'curry,creamy,popular', FALSE, FALSE, TRUE),

('Palak Paneer', 'Spinach curry with fresh cheese cubes', 'Indian', 35, 3, 'Medium', 280, 'Spinach, Paneer, Onion, Garlic, Ginger, Garam masala, Cream', '1. Blanch spinach\n2. Make spinach puree\n3. Add paneer\n4. Season and serve', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'vegetarian,spinach,healthy', TRUE, FALSE, TRUE),

('Biryani', 'Aromatic rice dish with meat and spices', 'Indian', 90, 6, 'Hard', 550, 'Basmati rice, Meat, Onion, Yogurt, Saffron, Garam masala, Mint, Coriander', '1. Marinate meat\n2. Par-cook rice\n3. Layer rice and meat\n4. Dum cook', 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'rice,aromatic,special', FALSE, FALSE, TRUE),

-- Italian Cuisine
('Margherita Pizza', 'Classic pizza with tomato sauce and mozzarella', 'Italian', 30, 4, 'Medium', 280, 'Pizza dough, Tomato sauce, Mozzarella, Basil, Olive oil, Salt', '1. Stretch dough\n2. Add sauce and cheese\n3. Bake until golden\n4. Garnish with basil', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'pizza,classic,vegetarian', TRUE, FALSE, FALSE),

('Spaghetti Carbonara', 'Creamy pasta with eggs, cheese, and pancetta', 'Italian', 25, 2, 'Medium', 420, 'Spaghetti, Eggs, Pecorino cheese, Pancetta, Black pepper, Salt', '1. Cook pasta\n2. Cook pancetta\n3. Mix eggs and cheese\n4. Combine and serve', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'pasta,creamy,quick', FALSE, FALSE, FALSE),

('Risotto ai Funghi', 'Creamy mushroom risotto', 'Italian', 40, 3, 'Medium', 320, 'Arborio rice, Mushrooms, Onion, White wine, Parmesan, Butter, Stock', '1. Sauté mushrooms\n2. Add rice and wine\n3. Gradually add stock\n4. Finish with cheese', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'risotto,creamy,vegetarian', TRUE, FALSE, TRUE);






-- Additional Bengali Recipes for EasyPrep Database
-- Insert authentic Bengali recipes researched from traditional cuisine

INSERT INTO recipes (name, description, cuisine_type, cooking_time, servings, difficulty, calories_per_serving, ingredients, instructions, image_url, tags, is_vegetarian, is_vegan, is_gluten_free) VALUES

-- Fish Recipes
('Shorshe Ilish', 'Traditional Bengali hilsa fish cooked in mustard seed paste with green chilies and turmeric - the king of Bengali fish dishes', 'Bangla', 30, 4, 'Medium', 320, 'Hilsa fish (1 kg), Mustard seeds (3 tbsp), Green chilies (4-5), Turmeric (1 tsp), Mustard oil (3 tbsp), Salt to taste, Water (1 cup)', '1. Clean and cut hilsa into pieces\n2. Marinate with turmeric and salt\n3. Grind mustard seeds with green chilies and little water\n4. Heat mustard oil and lightly fry fish\n5. Add mustard paste and simmer for 10 minutes\n6. Serve hot with steamed rice', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'fish,traditional,mustard,spicy,bengali special', FALSE, FALSE, TRUE),

('Bhapa Ilish', 'Steamed hilsa fish marinated with mustard paste and wrapped in banana leaves - aromatic and flavorful', 'Bangla', 40, 4, 'Medium', 340, 'Hilsa fish (1 kg), Mustard seeds (4 tbsp), Green chilies (6), Coconut (2 tbsp grated), Mustard oil (2 tbsp), Turmeric (1 tsp), Salt, Banana leaves', '1. Clean fish and marinate with turmeric and salt\n2. Make paste of mustard seeds, green chilies, and coconut\n3. Mix paste with fish pieces\n4. Wrap in banana leaves with mustard oil\n5. Steam for 25-30 minutes\n6. Serve with rice', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'steamed,fish,mustard,traditional,banana leaves', FALSE, FALSE, TRUE),

('Rui Macher Jhol', 'Bengali rohu fish curry cooked in light gravy with potatoes and tomatoes - comfort food at its best', 'Bangla', 35, 4, 'Medium', 290, 'Rohu fish (500g), Potatoes (2 medium), Tomatoes (2), Onion (1 large), Ginger-garlic paste (1 tbsp), Turmeric (1 tsp), Red chili powder (1 tsp), Cumin powder (1 tsp), Mustard oil (3 tbsp), Bay leaves (2), Salt, Water', '1. Cut fish and marinate with turmeric and salt\n2. Fry fish pieces lightly\n3. Sauté onions until golden\n4. Add ginger-garlic paste and spices\n5. Add tomatoes and potatoes\n6. Add water and simmer until done\n7. Add fried fish and cook for 5 minutes', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'fish curry,gravy,comfort food,traditional', FALSE, FALSE, TRUE),

('Katla Macher Kalia', 'Rich katla fish curry cooked in onion-tomato gravy with yogurt and aromatic spices', 'Bangla', 45, 4, 'Medium', 310, 'Katla fish (600g), Onions (3 large), Yogurt (1/2 cup), Tomatoes (2), Ginger-garlic paste (2 tbsp), Red chili powder (2 tsp), Turmeric (1 tsp), Garam masala (1 tsp), Mustard oil (4 tbsp), Bay leaves (2), Green chilies (3), Salt', '1. Marinate fish with turmeric and salt\n2. Deep fry fish until golden\n3. Make onion paste by frying and grinding\n4. Cook onion paste with spices\n5. Add yogurt and tomatoes\n6. Add fried fish and simmer\n7. Garnish with garam masala', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'fish curry,rich gravy,special occasion,aromatic', FALSE, FALSE, TRUE),

-- Prawn Recipe
('Chingri Malai Curry', 'Succulent prawns simmered in rich coconut milk with cardamom, cinnamon and aromatic Bengali spices', 'Bangla', 25, 4, 'Easy', 280, 'Large prawns (500g), Coconut milk (1 cup), Onion (1 medium), Ginger-garlic paste (1 tbsp), Green chilies (3), Cardamom (4), Cinnamon (1 inch), Bay leaves (2), Turmeric (1/2 tsp), Red chili powder (1 tsp), Sugar (1 tsp), Mustard oil (2 tbsp), Salt', '1. Clean and devein prawns\n2. Marinate with turmeric and salt\n3. Heat oil and add whole spices\n4. Sauté onions until soft\n5. Add ginger-garlic paste and spices\n6. Add coconut milk and simmer\n7. Add prawns and cook until done\n8. Add sugar and adjust seasoning', 'https://images.unsplash.com/photo-1565299507177-b0ac66763828?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'prawns,coconut curry,mild spices,creamy', FALSE, FALSE, TRUE),

-- Dal Recipes
('Masoor Dal', 'Red lentil curry tempered with cumin, garlic, and dried red chilies - Bengali household staple', 'Bangla', 25, 4, 'Easy', 220, 'Red lentils (1 cup), Onion (1 medium), Garlic (4 cloves), Dried red chilies (2), Cumin seeds (1 tsp), Turmeric (1/2 tsp), Mustard oil (2 tbsp), Bay leaves (2), Salt, Water (3 cups)', '1. Wash and boil lentils with turmeric\n2. Heat oil and add cumin seeds\n3. Add bay leaves and dried chilies\n4. Add sliced onions and garlic\n5. Sauté until golden\n6. Add boiled dal and mix\n7. Simmer for 10 minutes\n8. Garnish with fresh coriander', 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'lentils,dal,healthy,protein,everyday', TRUE, TRUE, TRUE),

('Chana Dal', 'Split chickpea dal tempered with cumin seeds, bay leaves, and dried red chilies', 'Bangla', 30, 4, 'Easy', 240, 'Split chickpeas (1 cup), Onion (1), Garlic (3 cloves), Cumin seeds (1 tsp), Bay leaves (2), Dried red chilies (2), Turmeric (1/2 tsp), Garam masala (1/2 tsp), Mustard oil (2 tbsp), Salt, Water (3 cups)', '1. Soak chana dal for 30 minutes\n2. Boil with turmeric until soft\n3. Heat oil and add cumin seeds\n4. Add bay leaves and chilies\n5. Sauté onions and garlic\n6. Add boiled dal and spices\n7. Simmer until thick\n8. Garnish with garam masala', 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'chickpea dal,protein rich,vegetarian,traditional', TRUE, TRUE, TRUE),

-- Vegetable Dishes
('Aloo Posto', 'Bengali potato curry cooked with poppy seed paste, green chilies and mustard oil', 'Bangla', 20, 4, 'Easy', 180, 'Potatoes (4 medium), Poppy seeds (3 tbsp), Green chilies (4), Turmeric (1/2 tsp), Mustard oil (3 tbsp), Salt, Water (1/2 cup)', '1. Peel and dice potatoes\n2. Soak poppy seeds and make paste\n3. Heat mustard oil\n4. Add potatoes and turmeric\n5. Cook until half done\n6. Add poppy seed paste\n7. Add green chilies and salt\n8. Cook until potatoes are tender', 'https://images.unsplash.com/photo-1585032226651-759b368d7246?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'vegetarian,poppy seeds,traditional,simple', TRUE, TRUE, TRUE),

('Shukto', 'Bengali mixed vegetable curry with bitter gourd, drumstick, and potato in milk-based sauce', 'Bangla', 30, 4, 'Medium', 160, 'Bitter gourd (100g), Drumsticks (2), Potato (1), Brinjal (1 small), Ridge gourd (100g), Milk (1/2 cup), Ginger paste (1 tsp), Panch phoron (1 tsp), Turmeric (1/2 tsp), Green chilies (2), Mustard oil (2 tbsp), Salt, Sugar (1 tsp)', '1. Cut all vegetables into pieces\n2. Heat oil and add panch phoron\n3. Add vegetables starting with harder ones\n4. Add turmeric and green chilies\n5. Cook covered until tender\n6. Add milk and ginger paste\n7. Simmer for 5 minutes\n8. Add sugar and salt to taste', 'https://images.unsplash.com/photo-1585032226651-759b368d7246?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'mixed vegetables,bitter gourd,traditional,healthy', TRUE, FALSE, TRUE),

-- Meat Recipe
('Kala Bhuna', 'Rich dark beef curry from Chittagong, slow-cooked with onions and aromatic spices until deep brown', 'Bangla', 90, 6, 'Hard', 450, 'Beef (1 kg), Onions (4 large), Ginger-garlic paste (3 tbsp), Red chili powder (2 tbsp), Turmeric (1 tsp), Coriander powder (2 tsp), Cumin powder (1 tsp), Garam masala (1 tbsp), Mustard oil (1/2 cup), Bay leaves (3), Cinnamon (2 inch), Cardamom (6), Salt, Water', '1. Cut beef into medium pieces\n2. Marinate with spices for 30 minutes\n3. Slice onions thinly\n4. Deep fry onions until dark brown\n5. Cook meat with half fried onions\n6. Add spices and cook until oil separates\n7. Add remaining onions and cook slowly\n8. Simmer until meat is tender and dark', 'https://images.unsplash.com/photo-1574484284002-952d92456975?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'beef curry,dark curry,chittagong special,slow cooked', FALSE, FALSE, TRUE),

-- Street Food
('Mughlai Paratha', 'Pan-fried flatbread stuffed with spiced minced meat, eggs, and onions - popular Dhaka street food', 'Bangla', 35, 2, 'Medium', 520, 'All-purpose flour (2 cups), Minced meat (200g), Eggs (3), Onions (2 medium), Ginger-garlic paste (1 tbsp), Red chili powder (1 tsp), Garam masala (1/2 tsp), Coriander leaves (2 tbsp), Oil for frying, Salt', '1. Make soft dough with flour\n2. Cook minced meat with spices\n3. Beat eggs and mix with meat\n4. Add chopped onions and coriander\n5. Roll dough and add filling\n6. Seal edges carefully\n7. Cook on tawa with oil\n8. Serve hot with sauce', 'https://images.unsplash.com/photo-1618040996337-56904b7850b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'street food,stuffed bread,meat filling,dhaka special', FALSE, FALSE, FALSE),

-- Dessert
('Mishti Doi', 'Traditional Bengali sweet yogurt dessert made with jaggery and cardamom - the perfect ending to any meal', 'Bangla', 15, 4, 'Easy', 140, 'Full-fat milk (1 liter), Yogurt starter (2 tbsp), Jaggery (1/2 cup), Cardamom powder (1/2 tsp), Nuts for garnish (optional)', '1. Boil milk and reduce to 3/4 quantity\n2. Melt jaggery in little milk\n3. Mix jaggery with reduced milk\n4. Cool to lukewarm temperature\n5. Add yogurt starter and cardamom\n6. Mix well and pour in earthen pots\n7. Keep in warm place for 6-8 hours\n8. Refrigerate and serve chilled', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'dessert,sweet yogurt,traditional,festive', TRUE, FALSE, TRUE);





