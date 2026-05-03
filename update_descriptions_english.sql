-- ============================================================
-- Translate all product & category descriptions to English
-- ============================================================

-- ── CATEGORIES ──
UPDATE categorieproduit SET
  nom = 'Individual Products',
  description = 'Fresh products sold individually — fruits, vegetables, proteins and grains selected for a healthy diet.'
WHERE nom = 'Produits Unitaires';

UPDATE categorieproduit SET
  nom = 'Smart Packs',
  description = 'Pre-grouped products ready to use — balanced combinations to simplify your healthy grocery shopping.'
WHERE nom = 'Packs Intelligents';

UPDATE categorieproduit SET
  nom = 'Meal Prep Packs',
  description = 'All the ingredients for a complete meal in one pack — cook healthy with no effort.'
WHERE nom = 'Meal Prep Packs';

-- ── INDIVIDUAL PRODUCTS ──
UPDATE produit SET description = 'Organic spinach leaves, rich in iron and vitamins.' WHERE nom = 'Épinards Bio';
UPDATE produit SET description = 'Crunchy organic carrots, rich in beta-carotene.' WHERE nom = 'Carottes Bio';
UPDATE produit SET description = 'Sweet cherry tomatoes, perfect in salads or as a snack.' WHERE nom = 'Tomates Cerises';
UPDATE produit SET description = 'Perfectly ripe avocado, rich in healthy fats and potassium.' WHERE nom = 'Avocat Hass';
UPDATE produit SET description = 'Energizing organic bananas, a natural source of magnesium.' WHERE nom = 'Bananes Bio';
UPDATE produit SET description = 'Antibiotic-free free-range chicken breast, rich in lean protein.' WHERE nom = 'Filet de Poulet';
UPDATE produit SET description = 'Fresh Atlantic salmon fillet, a source of omega-3 and quality protein.' WHERE nom = 'Saumon Atlantique';
UPDATE produit SET description = 'Organic free-range eggs, rich in protein.' WHERE nom = 'Œufs Bio (x6)';
UPDATE produit SET description = 'Whole rolled oats, ideal for a balanced breakfast.' WHERE nom = 'Flocons d\'Avoine';
UPDATE produit SET description = 'Organic whole quinoa, rich in plant protein and amino acids.' WHERE nom = 'Quinoa Bio';
UPDATE produit SET description = 'Organic green lentils, an excellent source of fiber and iron.' WHERE nom = 'Lentilles Vertes';
UPDATE produit SET description = 'Fresh broccoli rich in vitamin C and antioxidants.' WHERE nom = 'Brocoli Frais';
UPDATE produit SET description = 'Sweet potato rich in fiber and vitamin A, ideal for meal prep.' WHERE nom = 'Patate Douce';
UPDATE produit SET description = '0% Greek yogurt rich in protein, no added sugar.' WHERE nom = 'Yaourt Grec Nature';
UPDATE produit SET description = 'Natural unroasted almonds, a source of vitamin E and magnesium.' WHERE nom = 'Amandes Naturelles';

-- ── SMART PACKS ──
UPDATE produit SET description = 'Strawberries (250g) · Raspberries (150g) · Blueberries (150g) · Cherries (200g) · Redcurrants (100g) — 5 seasonal red fruits, rich in antioxidants and vitamin C.' WHERE nom = 'Pack Fruits Rouges';
UPDATE produit SET description = 'Spinach (200g) · Broccoli (300g) · Zucchini (2 pcs) · Green beans (200g) · Cucumber (1 pc) · Celery (2 stalks) — 6 green vegetables for a complete detox cleanse.' WHERE nom = 'Pack Légumes Verts Détox';
UPDATE produit SET description = 'Chicken breast (300g) · Canned tuna (2×160g) · Organic eggs (×6) · Turkey breast (200g) · 0% cottage cheese (250g) — 5 lean protein sources for athletes and active people.' WHERE nom = 'Pack Protéines Maigres';
UPDATE produit SET description = 'Organic quinoa (500g) · Rolled oats (500g) · Brown rice (500g) · Chia seeds (200g) · Flaxseeds (200g) · Bulgur (300g) — 6 grains and seeds for lasting energy.' WHERE nom = 'Pack Céréales & Graines';
UPDATE produit SET description = 'Spinach (150g) · Banana (2 pcs) · Fresh ginger (50g) · Lemon (2 pcs) · Green apple (2 pcs) · Cucumber (1 pc) — 6 ingredients for 4 homemade detox green smoothies.' WHERE nom = 'Pack Smoothie Vert';
UPDATE produit SET description = 'Natural almonds (100g) · Cashews (100g) · Walnuts (80g) · Medjool dates (150g) · Dried apricots (100g) · Raisins (80g) — 6 healthy snacks to munch on guilt-free.' WHERE nom = 'Pack Snack Healthy';
UPDATE produit SET description = 'Arugula (100g) · Cherry tomatoes (200g) · Avocado (1 pc) · Feta (100g) · Sunflower seeds (50g) · Black olives (80g) · Balsamic dressing — everything for a meal salad in 5 minutes.' WHERE nom = 'Pack Salade Repas';
UPDATE produit SET description = 'Rolled oats (400g) · Mixed red fruits (200g) · Plain Greek yogurt (500g) · Organic honey (250g) · Banana (2 pcs) · Almond butter (200g) — 6 ingredients for 5 balanced breakfasts.' WHERE nom = 'Pack Breakfast Équilibré';
UPDATE produit SET description = 'Fresh turmeric (100g) · Fresh ginger (100g) · Blueberries (200g) · Walnuts (100g) · Salmon (200g) · Extra virgin olive oil (250ml) · Green tea (20 bags) — 7 natural allies against inflammation.' WHERE nom = 'Pack Anti-Inflammatoire';
UPDATE produit SET description = 'Firm tofu (300g) · Cooked chickpeas (400g) · Green lentils (300g) · Tempeh (200g) · Edamame (200g) · Cashews (100g) · Coconut milk (400ml) — 7 plant proteins for a 100% vegan day.' WHERE nom = 'Pack Vegan Power';

-- ── MEAL PREP PACKS ──
UPDATE produit SET description = 'Chicken breast, quinoa, spinach, cherry tomatoes and tahini sauce — complete meal in 20 min.' WHERE nom = 'Meal Prep : Bowl Poulet-Quinoa';
UPDATE produit SET description = 'Salmon fillet, roasted sweet potato, steamed broccoli and lemon — rich in omega-3.' WHERE nom = 'Meal Prep : Saumon-Patate Douce';
UPDATE produit SET description = 'Quinoa, roasted chickpeas, avocado, grated carrots and hummus sauce — 100% plant-based.' WHERE nom = 'Meal Prep : Buddha Bowl Vegan';
UPDATE produit SET description = 'Organic eggs, peppers, spinach, mushrooms and goat cheese — protein-packed breakfast.' WHERE nom = 'Meal Prep : Omelette Légumes';
UPDATE produit SET description = 'Red lentils, coconut milk, mild spices, basmati rice — warm comforting meal.' WHERE nom = 'Meal Prep : Curry Lentilles';
UPDATE produit SET description = 'Whole wheat tortilla, grilled chicken, avocado, lettuce, tomatoes and yogurt sauce — quick lunch.' WHERE nom = 'Meal Prep : Wrap Healthy';
UPDATE produit SET description = 'Rolled oats, vanilla whey protein, banana, almond butter — muscle-building breakfast.' WHERE nom = 'Meal Prep : Porridge Protéiné';
UPDATE produit SET description = 'Broccoli, zucchini, spinach, ginger and vegetable broth — purifying green soup.' WHERE nom = 'Meal Prep : Soupe Détox';
UPDATE produit SET description = 'Lean beef steak, grilled peppers, zucchini and sweet potato — complete athlete meal.' WHERE nom = 'Meal Prep : Steak-Légumes Grillés';
UPDATE produit SET description = 'Oats, almond milk, chia, mixed berries and honey — prep the night before, enjoy in the morning.' WHERE nom = 'Meal Prep : Overnight Oats';
