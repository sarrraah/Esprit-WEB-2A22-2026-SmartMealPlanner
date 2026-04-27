CREATE DATABASE IF NOT EXISTS smart_meal_planner
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smart_meal_planner;

CREATE TABLE IF NOT EXISTS meals (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(500)    NOT NULL,
    calories    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    description TEXT            NOT NULL,
    image       VARCHAR(500)    NOT NULL DEFAULT '',
    recipeUrl   VARCHAR(500)    NOT NULL DEFAULT '#',
    mealType    ENUM('breakfast','lunch','dinner','snack') NOT NULL DEFAULT 'lunch',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plans (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name           VARCHAR(300) NOT NULL,
    description    TEXT         NOT NULL,
    mealType       ENUM('daily','weekly','diet','sport') NOT NULL DEFAULT 'daily',
    totalCalories  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    objective      VARCHAR(200) NOT NULL DEFAULT '',
    duration       TINYINT UNSIGNED NOT NULL DEFAULT 7,
    daysCompleted  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    mealsPlanned   TINYINT UNSIGNED NOT NULL DEFAULT 3,
    mealsCompleted TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alias table for backward compatibility
CREATE TABLE IF NOT EXISTS mealplan (
    id_plan       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom           VARCHAR(300) NOT NULL,
    duree         TINYINT UNSIGNED NOT NULL DEFAULT 7,
    date_debut    DATE         NOT NULL,
    date_fin      DATE         NOT NULL,
    objectif      VARCHAR(200) NOT NULL DEFAULT '',
    description   TEXT         NOT NULL,
    user_id       INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id_plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meals (name, calories, description, image, recipeUrl, mealType) VALUES
('Mediterranean Chickpea Salad with Feta & Herbs', 398, 'Chickpeas, cucumber, tomato, feta, and olive oil vinaigrette. Fresh and filling for lunch.', 'assets/img/menu/menu-item-2.png', 'https://example.com/recipes/chickpea-salad', 'lunch'),
('Ginger-Soy Turkey Stir-Fry with Market Vegetables', 524, 'Lean turkey strips with bell peppers, broccoli, and light soy-ginger sauce over brown rice.', 'assets/img/menu/menu-item-3.png', 'https://example.com/recipes/turkey-stir-fry', 'dinner'),
('Overnight Oats with Seasonal Berries & Chia', 356, 'Rolled oats soaked in almond milk with chia seeds, topped with mixed berries and a drizzle of honey.', 'assets/img/menu/menu-item-4.png', 'https://example.com/recipes/overnight-oats', 'breakfast'),
('Red Lentil Soup with Spinach & Aromatic Spices', 312, 'Hearty red lentil soup with spinach, carrots, and warm spices. Perfect for meal prep.', 'assets/img/menu/menu-item-5.png', 'https://example.com/recipes/lentil-soup', 'lunch'),
('Herb-Roasted Chicken with Caramelized Sweet Potato', 548, 'Herb-baked chicken thigh with roasted sweet potato wedges and steamed green beans.', 'assets/img/menu/menu-item-6.png', 'https://example.com/recipes/baked-chicken', 'dinner'),
('Rustic Chicken & Potato Soup', 328, 'A warming bowl of chicken soup with tender potatoes and vegetables—ideal for cozy nights.', 'assets/img/meals/meal-07.png', 'https://example.com/recipes/chicken-potato-soup', 'lunch'),
('Continental Breakfast Plate', 438, 'A relaxed morning plate inspired by a perfect home café—great with your favorite brew.', 'assets/img/meals/meal-08.png', 'https://example.com/recipes/cafe-breakfast', 'breakfast'),
('Wok-Seared Beef Fried Rice with Scrambled Egg', 684, 'Classic savory fried rice with tender beef strips and fluffy scrambled eggs.', 'assets/img/meals/meal-09.png', 'https://example.com/recipes/beef-egg-fried-rice', 'dinner'),
('Sesame-Glazed Chicken with Jasmine Rice & Cucumber', 592, 'Bite-sized chicken in a glossy sesame glaze with rice, peas, and fresh cucumber.', 'assets/img/meals/meal-10.png', 'https://example.com/recipes/sesame-chicken-bowl', 'dinner'),
('Crispy Chicken Breast on Mixed Garden Greens', 612, 'Mixed greens with tomatoes and sweet peppers, topped with golden crispy chicken.', 'assets/img/meals/meal-11.png', 'https://example.com/recipes/crispy-chicken-salad', 'lunch'),
('Pan-Seared Steak with Pommes Purée & Roasted Asparagus', 798, 'Sliced steak with balsamic glaze, creamy mashed potatoes, and charred asparagus.', 'assets/img/meals/meal-12.png', 'https://example.com/recipes/steak-mash-asparagus', 'dinner'),
('Seared Beef with Broccoli & Honey-Glazed Carrots', 538, 'Pan-seared steak strips with roasted broccoli and glossy glazed carrots.', 'assets/img/meals/meal-13.png', 'https://example.com/recipes/steak-broccoli-carrots', 'dinner'),
('Chargrilled Chicken Brochettes with Seasoned Frites', 862, 'BBQ-glazed chicken skewers with peppers, crispy seasoned fries, and dipping sauce.', 'assets/img/meals/meal-14.png', 'https://example.com/recipes/chicken-skewers-fries', 'dinner'),
('Chilled Shrimp Salad with Avocado & Citrus Dressing', 428, 'Shrimp with avocado, cherry tomatoes, red onion, cilantro, and lime-forward dressing.', 'assets/img/meals/meal-15.png', 'https://example.com/recipes/shrimp-avocado-salad', 'lunch'),
('Grilled Chicken Bowl with Brown Rice & Garden Vegetables', 548, 'Glazed grilled chicken over brown rice with lettuce, cucumber, and fresh herbs.', 'assets/img/meals/meal-16.png', 'https://example.com/recipes/chicken-brown-rice-bowl', 'lunch'),
('Beef Tenderloin & Broccoli with Stir-Fry Noodles', 668, 'Thick noodles with beef, broccoli, carrots, and a savory soy-style sauce, sesame finish.', 'assets/img/meals/meal-17.png', 'https://example.com/recipes/beef-broccoli-noodles', 'dinner'),
('Mediterranean Grilled Chicken Fusilli with Tzatziki', 612, 'Grilled chicken with rotini, cucumber, cherry tomatoes, red onion, and tzatziki.', 'assets/img/meals/meal-18.png', 'https://example.com/recipes/mediterranean-chicken-pasta-bowl', 'lunch'),
('Seasonal Berry & Banana Smoothie Bowl with Granola', 445, 'Pink smoothie base topped with granola, nuts, banana, berries, and a mint garnish.', 'assets/img/meals/meal-19.png', 'https://example.com/recipes/strawberry-banana-smoothie-bowl', 'breakfast'),
('Creamy Rotini Pasta Salad with Garden Vegetables', 482, 'Rotini with tomatoes, cucumber, olives, carrot, red onion, and creamy dressing.', 'assets/img/meals/meal-20.png', 'https://example.com/recipes/creamy-rotini-salad', 'lunch'),
('Avocado Tartine with Soft Scramble & Seasonal Berries', 465, 'Whole-grain avocado toast, fluffy scrambled eggs, and fresh sliced strawberries.', 'assets/img/meals/meal-21.png', 'https://example.com/recipes/avocado-toast-breakfast', 'breakfast'),
('Gulf Shrimp & Broccoli in Light Garlic Sauce', 356, 'Juicy shrimp and bright broccoli in a light savory, peppery stir-fry sauce.', 'assets/img/meals/meal-22.png', 'https://example.com/recipes/shrimp-broccoli-stir-fry', 'dinner'),
('Antioxidant Berry Smoothie Bowl with Nut Butter', 418, 'Purple berry smoothie base with banana, kiwi, pomegranate, blueberries, citrus, and nut butter.', 'assets/img/meals/meal-23.png', 'https://example.com/recipes/berry-smoothie-bowl', 'snack'),
('Slow-Braised Beef with Root Vegetables in Herb Broth', 465, 'Slow-cooked beef with corn, carrots, potato or yuca, and herb-infused broth.', 'assets/img/meals/meal-24.png', 'https://example.com/recipes/beef-vegetable-stew', 'dinner');

-- Stores meal overrides per plan day and meal type
CREATE TABLE IF NOT EXISTS plan_meals (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    plan_id    INT UNSIGNED NOT NULL,
    meal_date  DATE         NOT NULL,
    meal_type  VARCHAR(20)  NOT NULL,
    meal_id    INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_plan_date_type (plan_id, meal_date, meal_type),
    KEY idx_plan_date (plan_id, meal_date),
    FOREIGN KEY (plan_id) REFERENCES mealplan(id_plan) ON DELETE CASCADE,
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

