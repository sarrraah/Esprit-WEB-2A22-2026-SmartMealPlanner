<?php

require_once __DIR__ . '/MealJsonStore.php';

/**
 * Meal entity. Catalog from data/meals.json when present and non-empty; else built-in defaults.
 * mealType: breakfast | lunch | dinner | snack (display: Snacks for snack)
 */
class Meal
{
    public function __construct(
        public int $id,
        public string $name,
        public int $calories,
        public string $description,
        public string $image,
        public string $recipeUrl,
        public string $mealType
    ) {
    }

    public static function fromArray(array $r): self
    {
        return new self(
            (int) ($r['id'] ?? 0),
            (string) ($r['name'] ?? ''),
            (int) ($r['calories'] ?? 0),
            (string) ($r['description'] ?? ''),
            (string) ($r['image'] ?? ''),
            (string) ($r['recipeUrl'] ?? '#'),
            (string) ($r['mealType'] ?? 'lunch'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'calories' => $this->calories,
            'description' => $this->description,
            'image' => $this->image,
            'recipeUrl' => $this->recipeUrl,
            'mealType' => $this->mealType,
        ];
    }

    public function mealTypeLabel(): string
    {
        return match ($this->mealType) {
            'breakfast' => 'Breakfast',
            'lunch' => 'Lunch',
            'dinner' => 'Dinner',
            'snack' => 'Snacks',
            default => 'Meal',
        };
    }

    /**
     * @return Meal[]
     */
    public static function all(): array
    {
        if (!MealJsonStore::exists()) {
            return self::defaultCatalog();
        }
        $rows = MealJsonStore::loadRows();
        if ($rows === []) {
            return self::defaultCatalog();
        }
        $meals = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $meals[] = self::fromArray($row);
        }
        return $meals;
    }

    public static function find(int $id): ?self
    {
        foreach (self::all() as $meal) {
            if ($meal->id === $id) {
                return $meal;
            }
        }
        return null;
    }

    /**
     * @return Meal[]
     */
    private static function defaultCatalog(): array
    {
        return [
            new self(1, 'Atlantic Salmon with Quinoa & Seasonal Vegetables', 612, 'Atlantic salmon with quinoa, roasted vegetables, and lemon herb dressing. High in protein and omega-3.', 'assets/img/menu/menu-item-1.png', 'https://example.com/recipes/grilled-salmon-bowl', 'dinner'),
            new self(2, 'Mediterranean Chickpea Salad with Feta & Herbs', 398, 'Chickpeas, cucumber, tomato, feta, and olive oil vinaigrette. Fresh and filling for lunch.', 'assets/img/menu/menu-item-2.png', 'https://example.com/recipes/chickpea-salad', 'lunch'),
            new self(3, 'Ginger-Soy Turkey Stir-Fry with Market Vegetables', 524, 'Lean turkey strips with bell peppers, broccoli, and light soy-ginger sauce over brown rice.', 'assets/img/menu/menu-item-3.png', 'https://example.com/recipes/turkey-stir-fry', 'dinner'),
            new self(4, 'Overnight Oats with Seasonal Berries & Chia', 356, 'Rolled oats soaked in almond milk with chia seeds, topped with mixed berries and a drizzle of honey.', 'assets/img/menu/menu-item-4.png', 'https://example.com/recipes/overnight-oats', 'breakfast'),
            new self(5, 'Red Lentil Soup with Spinach & Aromatic Spices', 312, 'Hearty red lentil soup with spinach, carrots, and warm spices. Perfect for meal prep.', 'assets/img/menu/menu-item-5.png', 'https://example.com/recipes/lentil-soup', 'lunch'),
            new self(6, 'Herb-Roasted Chicken with Caramelized Sweet Potato', 548, 'Herb-baked chicken thigh with roasted sweet potato wedges and steamed green beans.', 'assets/img/menu/menu-item-6.png', 'https://example.com/recipes/baked-chicken', 'dinner'),
            new self(7, 'Rustic Chicken & Potato Soup', 328, 'A warming bowl of chicken soup with tender potatoes and vegetables—ideal for cozy nights.', 'assets/img/meals/meal-07.png', 'https://example.com/recipes/chicken-potato-soup', 'lunch'),
            new self(8, 'Continental Breakfast Plate', 438, 'A relaxed morning plate inspired by a perfect home café—great with your favorite brew.', 'assets/img/meals/meal-08.png', 'https://example.com/recipes/cafe-breakfast', 'breakfast'),
            new self(9, 'Wok-Seared Beef Fried Rice with Scrambled Egg', 684, 'Classic savory fried rice with tender beef strips and fluffy scrambled eggs.', 'assets/img/meals/meal-09.png', 'https://example.com/recipes/beef-egg-fried-rice', 'dinner'),
            new self(10, 'Sesame-Glazed Chicken with Jasmine Rice & Cucumber', 592, 'Bite-sized chicken in a glossy sesame glaze with rice, peas, and fresh cucumber.', 'assets/img/meals/meal-10.png', 'https://example.com/recipes/sesame-chicken-bowl', 'dinner'),
            new self(11, 'Crispy Chicken Breast on Mixed Garden Greens', 612, 'Mixed greens with tomatoes and sweet peppers, topped with golden crispy chicken.', 'assets/img/meals/meal-11.png', 'https://example.com/recipes/crispy-chicken-salad', 'lunch'),
            new self(12, 'Pan-Seared Steak with Pommes Purée & Roasted Asparagus', 798, 'Sliced steak with balsamic glaze, creamy mashed potatoes, and charred asparagus.', 'assets/img/meals/meal-12.png', 'https://example.com/recipes/steak-mash-asparagus', 'dinner'),
            new self(13, 'Seared Beef with Broccoli & Honey-Glazed Carrots', 538, 'Pan-seared steak strips with roasted broccoli and glossy glazed carrots.', 'assets/img/meals/meal-13.png', 'https://example.com/recipes/steak-broccoli-carrots', 'dinner'),
            new self(14, 'Chargrilled Chicken Brochettes with Seasoned Frites', 862, 'BBQ-glazed chicken skewers with peppers, crispy seasoned fries, and dipping sauce.', 'assets/img/meals/meal-14.png', 'https://example.com/recipes/chicken-skewers-fries', 'dinner'),
            new self(15, 'Chilled Shrimp Salad with Avocado & Citrus Dressing', 428, 'Shrimp with avocado, cherry tomatoes, red onion, cilantro, and lime-forward dressing.', 'assets/img/meals/meal-15.png', 'https://example.com/recipes/shrimp-avocado-salad', 'lunch'),
            new self(16, 'Grilled Chicken Bowl with Brown Rice & Garden Vegetables', 548, 'Glazed grilled chicken over brown rice with lettuce, cucumber, and fresh herbs.', 'assets/img/meals/meal-16.png', 'https://example.com/recipes/chicken-brown-rice-bowl', 'lunch'),
            new self(17, 'Beef Tenderloin & Broccoli with Stir-Fry Noodles', 668, 'Thick noodles with beef, broccoli, carrots, and a savory soy-style sauce, sesame finish.', 'assets/img/meals/meal-17.png', 'https://example.com/recipes/beef-broccoli-noodles', 'dinner'),
            new self(18, 'Mediterranean Grilled Chicken Fusilli with Tzatziki', 612, 'Grilled chicken with rotini, cucumber, cherry tomatoes, red onion, and tzatziki.', 'assets/img/meals/meal-18.png', 'https://example.com/recipes/mediterranean-chicken-pasta-bowl', 'lunch'),
            new self(19, 'Seasonal Berry & Banana Smoothie Bowl with Granola', 445, 'Pink smoothie base topped with granola, nuts, banana, berries, and a mint garnish.', 'assets/img/meals/meal-19.png', 'https://example.com/recipes/strawberry-banana-smoothie-bowl', 'breakfast'),
            new self(20, 'Creamy Rotini Pasta Salad with Garden Vegetables', 482, 'Rotini with tomatoes, cucumber, olives, carrot, red onion, and creamy dressing.', 'assets/img/meals/meal-20.png', 'https://example.com/recipes/creamy-rotini-salad', 'lunch'),
            new self(21, 'Avocado Tartine with Soft Scramble & Seasonal Berries', 465, 'Whole-grain avocado toast, fluffy scrambled eggs, and fresh sliced strawberries.', 'assets/img/meals/meal-21.png', 'https://example.com/recipes/avocado-toast-breakfast', 'breakfast'),
            new self(22, 'Gulf Shrimp & Broccoli in Light Garlic Sauce', 356, 'Juicy shrimp and bright broccoli in a light savory, peppery stir-fry sauce.', 'assets/img/meals/meal-22.png', 'https://example.com/recipes/shrimp-broccoli-stir-fry', 'dinner'),
            new self(23, 'Antioxidant Berry Smoothie Bowl with Nut Butter', 418, 'Purple berry smoothie base with banana, kiwi, pomegranate, blueberries, citrus, and nut butter.', 'assets/img/meals/meal-23.png', 'https://example.com/recipes/berry-smoothie-bowl', 'snack'),
            new self(24, 'Slow-Braised Beef with Root Vegetables in Herb Broth', 465, 'Slow-cooked beef with corn, carrots, potato or yuca, and herb-infused broth.', 'assets/img/meals/meal-24.png', 'https://example.com/recipes/beef-vegetable-stew', 'dinner'),
        ];
    }

    /**
     * Next ID for a new meal (max existing + 1).
     *
     * @param Meal[] $meals
     */
    public static function nextId(array $meals): int
    {
        $max = 0;
        foreach ($meals as $m) {
            if ($m->id > $max) {
                $max = $m->id;
            }
        }
        return $max + 1;
    }
}
