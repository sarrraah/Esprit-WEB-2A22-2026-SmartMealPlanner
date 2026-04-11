/**
 * Builds the meal card grid on static Meal.html (mirrors model/Meal.php).
 */
(function () {
  'use strict';

  var typeLabels = {
    breakfast: 'Breakfast',
    lunch: 'Lunch',
    dinner: 'Dinner',
    snack: 'Snacks'
  };

  var meals = [
    { id: 1, name: 'Atlantic Salmon with Quinoa & Seasonal Vegetables', calories: 612, type: 'dinner', description: 'Atlantic salmon with quinoa, roasted vegetables, and lemon herb dressing. High in protein and omega-3.', image: '../assets/img/menu/menu-item-1.png', recipe: 'https://example.com/recipes/grilled-salmon-bowl' },
    { id: 2, name: 'Mediterranean Chickpea Salad with Feta & Herbs', calories: 398, type: 'lunch', description: 'Chickpeas, cucumber, tomato, feta, and olive oil vinaigrette. Fresh and filling for lunch.', image: '../assets/img/menu/menu-item-2.png', recipe: 'https://example.com/recipes/chickpea-salad' },
    { id: 3, name: 'Ginger-Soy Turkey Stir-Fry with Market Vegetables', calories: 524, type: 'dinner', description: 'Lean turkey strips with bell peppers, broccoli, and light soy-ginger sauce over brown rice.', image: '../assets/img/menu/menu-item-3.png', recipe: 'https://example.com/recipes/turkey-stir-fry' },
    { id: 4, name: 'Overnight Oats with Seasonal Berries & Chia', calories: 356, type: 'breakfast', description: 'Rolled oats soaked in almond milk with chia seeds, topped with mixed berries and a drizzle of honey.', image: '../assets/img/menu/menu-item-4.png', recipe: 'https://example.com/recipes/overnight-oats' },
    { id: 5, name: 'Red Lentil Soup with Spinach & Aromatic Spices', calories: 312, type: 'lunch', description: 'Hearty red lentil soup with spinach, carrots, and warm spices. Perfect for meal prep.', image: '../assets/img/menu/menu-item-5.png', recipe: 'https://example.com/recipes/lentil-soup' },
    { id: 6, name: 'Herb-Roasted Chicken with Caramelized Sweet Potato', calories: 548, type: 'dinner', description: 'Herb-baked chicken thigh with roasted sweet potato wedges and steamed green beans.', image: '../assets/img/menu/menu-item-6.png', recipe: 'https://example.com/recipes/baked-chicken' },
    { id: 7, name: 'Rustic Chicken & Potato Soup', calories: 328, type: 'lunch', description: 'A warming bowl of chicken soup with tender potatoes and vegetables—ideal for cozy nights.', image: '../assets/img/meals/meal-07.png', recipe: 'https://example.com/recipes/chicken-potato-soup' },
    { id: 8, name: 'Continental Breakfast Plate', calories: 438, type: 'breakfast', description: 'A relaxed morning plate inspired by a perfect home café—great with your favorite brew.', image: '../assets/img/meals/meal-08.png', recipe: 'https://example.com/recipes/cafe-breakfast' },
    { id: 9, name: 'Wok-Seared Beef Fried Rice with Scrambled Egg', calories: 684, type: 'dinner', description: 'Classic savory fried rice with tender beef strips and fluffy scrambled eggs.', image: '../assets/img/meals/meal-09.png', recipe: 'https://example.com/recipes/beef-egg-fried-rice' },
    { id: 10, name: 'Sesame-Glazed Chicken with Jasmine Rice & Cucumber', calories: 592, type: 'dinner', description: 'Bite-sized chicken in a glossy sesame glaze with rice, peas, and fresh cucumber.', image: '../assets/img/meals/meal-10.png', recipe: 'https://example.com/recipes/sesame-chicken-bowl' },
    { id: 11, name: 'Crispy Chicken Breast on Mixed Garden Greens', calories: 612, type: 'lunch', description: 'Mixed greens with tomatoes and sweet peppers, topped with golden crispy chicken.', image: '../assets/img/meals/meal-11.png', recipe: 'https://example.com/recipes/crispy-chicken-salad' },
    { id: 12, name: 'Pan-Seared Steak with Pommes Purée & Roasted Asparagus', calories: 798, type: 'dinner', description: 'Sliced steak with balsamic glaze, creamy mashed potatoes, and charred asparagus.', image: '../assets/img/meals/meal-12.png', recipe: 'https://example.com/recipes/steak-mash-asparagus' },
    { id: 13, name: 'Seared Beef with Broccoli & Honey-Glazed Carrots', calories: 538, type: 'dinner', description: 'Pan-seared steak strips with roasted broccoli and glossy glazed carrots.', image: '../assets/img/meals/meal-13.png', recipe: 'https://example.com/recipes/steak-broccoli-carrots' },
    { id: 14, name: 'Chargrilled Chicken Brochettes with Seasoned Frites', calories: 862, type: 'dinner', description: 'BBQ-glazed chicken skewers with peppers, crispy seasoned fries, and dipping sauce.', image: '../assets/img/meals/meal-14.png', recipe: 'https://example.com/recipes/chicken-skewers-fries' },
    { id: 15, name: 'Chilled Shrimp Salad with Avocado & Citrus Dressing', calories: 428, type: 'lunch', description: 'Shrimp with avocado, cherry tomatoes, red onion, cilantro, and lime-forward dressing.', image: '../assets/img/meals/meal-15.png', recipe: 'https://example.com/recipes/shrimp-avocado-salad' },
    { id: 16, name: 'Grilled Chicken Bowl with Brown Rice & Garden Vegetables', calories: 548, type: 'lunch', description: 'Glazed grilled chicken over brown rice with lettuce, cucumber, and fresh herbs.', image: '../assets/img/meals/meal-16.png', recipe: 'https://example.com/recipes/chicken-brown-rice-bowl' },
    { id: 17, name: 'Beef Tenderloin & Broccoli with Stir-Fry Noodles', calories: 668, type: 'dinner', description: 'Thick noodles with beef, broccoli, carrots, and a savory soy-style sauce, sesame finish.', image: '../assets/img/meals/meal-17.png', recipe: 'https://example.com/recipes/beef-broccoli-noodles' },
    { id: 18, name: 'Mediterranean Grilled Chicken Fusilli with Tzatziki', calories: 612, type: 'lunch', description: 'Grilled chicken with rotini, cucumber, cherry tomatoes, red onion, and tzatziki.', image: '../assets/img/meals/meal-18.png', recipe: 'https://example.com/recipes/mediterranean-chicken-pasta-bowl' },
    { id: 19, name: 'Seasonal Berry & Banana Smoothie Bowl with Granola', calories: 445, type: 'breakfast', description: 'Pink smoothie base topped with granola, nuts, banana, berries, and a mint garnish.', image: '../assets/img/meals/meal-19.png', recipe: 'https://example.com/recipes/strawberry-banana-smoothie-bowl' },
    { id: 20, name: 'Creamy Rotini Pasta Salad with Garden Vegetables', calories: 482, type: 'lunch', description: 'Rotini with tomatoes, cucumber, olives, carrot, red onion, and creamy dressing.', image: '../assets/img/meals/meal-20.png', recipe: 'https://example.com/recipes/creamy-rotini-salad' },
    { id: 21, name: 'Avocado Tartine with Soft Scramble & Seasonal Berries', calories: 465, type: 'breakfast', description: 'Whole-grain avocado toast, fluffy scrambled eggs, and fresh sliced strawberries.', image: '../assets/img/meals/meal-21.png', recipe: 'https://example.com/recipes/avocado-toast-breakfast' },
    { id: 22, name: 'Gulf Shrimp & Broccoli in Light Garlic Sauce', calories: 356, type: 'dinner', description: 'Juicy shrimp and bright broccoli in a light savory, peppery stir-fry sauce.', image: '../assets/img/meals/meal-22.png', recipe: 'https://example.com/recipes/shrimp-broccoli-stir-fry' },
    { id: 23, name: 'Antioxidant Berry Smoothie Bowl with Nut Butter', calories: 418, type: 'snack', description: 'Purple berry smoothie base with banana, kiwi, pomegranate, blueberries, citrus, and nut butter.', image: '../assets/img/meals/meal-23.png', recipe: 'https://example.com/recipes/berry-smoothie-bowl' },
    { id: 24, name: 'Slow-Braised Beef with Root Vegetables in Herb Broth', calories: 465, type: 'dinner', description: 'Slow-cooked beef with corn, carrots, potato or yuca, and herb-infused broth.', image: '../assets/img/meals/meal-24.png', recipe: 'https://example.com/recipes/beef-vegetable-stew' }
  ];

  var grid = document.getElementById('meal-grid');
  if (!grid) return;

  meals.forEach(function (m) {
    var col = document.createElement('div');
    col.className = 'col-lg-3 col-md-4 col-sm-6';

    var article = document.createElement('article');
    article.className = 'meal-card';
    article.setAttribute('data-meal-id', String(m.id));
    article.setAttribute('data-meal-name', m.name);
    article.setAttribute('data-meal-calories', String(m.calories));
    article.setAttribute('data-meal-description', m.description);
    article.setAttribute('data-meal-image', m.image);
    article.setAttribute('data-meal-recipe', m.recipe);
    article.setAttribute('data-meal-type', m.type);
    article.setAttribute('data-meal-type-label', typeLabels[m.type] || 'Meal');

    var media = document.createElement('div');
    media.className = 'meal-card__media';
    var img = document.createElement('img');
    img.src = m.image;
    img.alt = m.name;
    img.loading = 'lazy';
    media.appendChild(img);

    var body = document.createElement('div');
    body.className = 'meal-card__body';
    var h3 = document.createElement('h3');
    h3.className = 'meal-card__name';
    h3.textContent = m.name;
    var p = document.createElement('p');
    p.className = 'meal-card__calories';
    var strong = document.createElement('strong');
    strong.textContent = String(m.calories);
    p.appendChild(strong);
    p.appendChild(document.createTextNode(' kcal'));
    body.appendChild(h3);
    body.appendChild(p);

    article.appendChild(media);
    article.appendChild(body);
    col.appendChild(article);
    grid.appendChild(col);
  });
})();
