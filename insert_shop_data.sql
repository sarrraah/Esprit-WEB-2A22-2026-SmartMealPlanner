-- ============================================================
-- Smart Meal Planner - Shop Data
-- 3 catégories + 35 produits
-- ============================================================

-- Vider les anciennes données
DELETE FROM produit;
DELETE FROM categorieproduit;
ALTER TABLE categorieproduit AUTO_INCREMENT = 1;
ALTER TABLE produit AUTO_INCREMENT = 1;

-- ============================================================
-- CATÉGORIES
-- ============================================================
INSERT INTO categorieproduit (nom, description, image) VALUES
('Produits Unitaires',  'Produits frais vendus individuellement — fruits, légumes, protéines et céréales sélectionnés pour une alimentation saine.', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600'),
('Packs Intelligents',  'Produits groupés prêts à l\'usage — combinaisons équilibrées pour simplifier vos courses healthy.',                          'https://images.unsplash.com/photo-1543362906-acfc16c67564?w=600'),
('Meal Prep Packs',     'Tous les ingrédients d\'un repas complet réunis dans un seul pack — cuisinez sain sans effort.',                             'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600');

-- ============================================================
-- PRODUITS UNITAIRES (categorie = 1) — 15 produits
-- ============================================================
INSERT INTO produit (nom, description, prix, quantiteStock, estDurable, dateExpiration, image, statut, categorie) VALUES
('Épinards Bio',        'Feuilles d\'épinards biologiques, riches en fer et vitamines.',                   2.90,  50, 0, '2026-05-10', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=600', 'Disponible', 1),
('Carottes Bio',        'Carottes biologiques croquantes, riches en bêta-carotène.',                       1.80,  80, 0, '2026-05-20', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600', 'Disponible', 1),
('Tomates Cerises',     'Tomates cerises sucrées, parfaites en salade ou en snack.',                       3.20,  60, 0, '2026-05-08', 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=600', 'Disponible', 1),
('Avocat Hass',         'Avocat mûr à point, riche en bonnes graisses et en potassium.',                   2.50,  40, 0, '2026-05-06', 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=600', 'Disponible', 1),
('Bananes Bio',         'Bananes biologiques énergisantes, source naturelle de magnésium.',                 1.50,  90, 0, '2026-05-07', 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=600', 'Disponible', 1),
('Filet de Poulet',     'Filet de poulet fermier sans antibiotiques, riche en protéines maigres.',          8.90,  30, 0, '2026-05-05', 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=600', 'Disponible', 1),
('Saumon Atlantique',   'Pavé de saumon frais, source d\'oméga-3 et de protéines de qualité.',            12.50,  20, 0, '2026-05-04', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600', 'Disponible', 1),
('Œufs Bio (x6)',       'Œufs biologiques de poules élevées en plein air, riches en protéines.',           3.50,  70, 0, '2026-05-25', 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=600', 'Disponible', 1),
('Flocons d\'Avoine',   'Flocons d\'avoine complets, idéaux pour un petit-déjeuner équilibré.',             2.20, 100, 1, '2027-01-01', 'https://images.unsplash.com/photo-1614961233913-a5113a4a34ed?w=600', 'Disponible', 1),
('Quinoa Bio',          'Quinoa biologique complet, riche en protéines végétales et acides aminés.',        4.90,  50, 1, '2027-06-01', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600', 'Disponible', 1),
('Lentilles Vertes',    'Lentilles vertes biologiques, excellente source de fibres et de fer.',             2.80,  60, 1, '2027-03-01', 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=600', 'Disponible', 1),
('Brocoli Frais',       'Brocoli frais riche en vitamine C et antioxydants.',                               2.40,  45, 0, '2026-05-09', 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=600', 'Disponible', 1),
('Patate Douce',        'Patate douce riche en fibres et en vitamine A, idéale pour le meal prep.',         1.90,  55, 0, '2026-05-15', 'https://images.unsplash.com/photo-1596097635121-14b63b7a0c19?w=600', 'Disponible', 1),
('Yaourt Grec Nature',  'Yaourt grec 0% riche en protéines, sans sucres ajoutés.',                         3.10,  40, 0, '2026-05-12', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=600', 'Disponible', 1),
('Amandes Naturelles',  'Amandes naturelles non grillées, source de vitamine E et de magnésium.',           6.50,  35, 1, '2027-02-01', 'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?w=600', 'Disponible', 1);

-- ============================================================
-- PACKS INTELLIGENTS (categorie = 2) — 10 produits
-- ============================================================
INSERT INTO produit (nom, description, prix, quantiteStock, estDurable, dateExpiration, image, statut, categorie) VALUES
('Pack Fruits Rouges',
 'Fraises (250g) · Framboises (150g) · Myrtilles (150g) · Cerises (200g) · Groseilles (100g) — 5 fruits rouges de saison, riches en antioxydants et vitamine C.',
 11.90, 25, 0, '2026-05-06', 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=600', 'Disponible', 2),

('Pack Légumes Verts Détox',
 'Épinards (200g) · Brocoli (300g) · Courgette (2 pcs) · Haricots verts (200g) · Concombre (1 pc) · Céleri (2 branches) — 6 légumes verts pour une cure détox complète.',
 9.90, 30, 0, '2026-05-08', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600', 'Disponible', 2),

('Pack Protéines Maigres',
 'Filet de poulet (300g) · Thon en boîte (2×160g) · Œufs bio (×6) · Blanc de dinde (200g) · Fromage blanc 0% (250g) — 5 sources de protéines maigres pour sportifs et actifs.',
 21.90, 20, 0, '2026-05-05', 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=600', 'Disponible', 2),

('Pack Céréales & Graines',
 'Quinoa bio (500g) · Flocons d\'avoine (500g) · Riz complet (500g) · Graines de chia (200g) · Graines de lin (200g) · Boulgour (300g) — 6 céréales et graines pour une énergie durable.',
 14.90, 40, 1, '2027-01-01', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=600', 'Disponible', 2),

('Pack Smoothie Vert',
 'Épinards (150g) · Banane (2 pcs) · Gingembre frais (50g) · Citron (2 pcs) · Pomme verte (2 pcs) · Concombre (1 pc) — 6 ingrédients pour 4 smoothies verts détox maison.',
 8.90, 35, 0, '2026-05-07', 'https://images.unsplash.com/photo-1610970881699-44a5587cabec?w=600', 'Disponible', 2),

('Pack Snack Healthy',
 'Amandes naturelles (100g) · Noix de cajou (100g) · Noix (80g) · Dattes Medjool (150g) · Abricots secs (100g) · Raisins secs (80g) — 6 snacks sains pour grignoter sans culpabilité.',
 13.90, 30, 1, '2026-12-01', 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?w=600', 'Disponible', 2),

('Pack Salade Repas',
 'Roquette (100g) · Tomates cerises (200g) · Avocat (1 pc) · Feta (100g) · Graines de tournesol (50g) · Olives noires (80g) · Vinaigrette balsamique — tout pour une salade repas en 5 minutes.',
 12.50, 25, 0, '2026-05-06', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600', 'Disponible', 2),

('Pack Breakfast Équilibré',
 'Flocons d\'avoine (400g) · Fruits rouges mélangés (200g) · Yaourt grec nature (500g) · Miel bio (250g) · Banane (2 pcs) · Beurre d\'amande (200g) — 6 ingrédients pour 5 breakfasts équilibrés.',
 15.90, 20, 0, '2026-05-10', 'https://images.unsplash.com/photo-1484723091739-30a097e8f929?w=600', 'Disponible', 2),

('Pack Anti-Inflammatoire',
 'Curcuma frais (100g) · Gingembre frais (100g) · Myrtilles (200g) · Noix (100g) · Saumon (200g) · Huile d\'olive extra vierge (250ml) · Thé vert (20 sachets) — 7 alliés naturels contre l\'inflammation.',
 17.90, 15, 1, '2026-10-01', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600', 'Disponible', 2),

('Pack Vegan Power',
 'Tofu ferme (300g) · Pois chiches cuits (400g) · Lentilles vertes (300g) · Tempeh (200g) · Edamame (200g) · Noix de cajou (100g) · Lait de coco (400ml) — 7 protéines végétales pour une journée 100% vegan.',
 18.90, 20, 0, '2026-05-09', 'https://images.unsplash.com/photo-1543362906-acfc16c67564?w=600', 'Disponible', 2);

-- ============================================================
-- MEAL PREP PACKS (categorie = 3) — 10 produits
-- ============================================================
INSERT INTO produit (nom, description, prix, quantiteStock, estDurable, dateExpiration, image, statut, categorie) VALUES
('Meal Prep : Bowl Poulet-Quinoa',    'Filet de poulet, quinoa, épinards, tomates cerises et sauce tahini — repas complet en 20 min.',    19.90, 15, 0, '2026-05-05', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600', 'Disponible', 3),
('Meal Prep : Saumon-Patate Douce',   'Pavé de saumon, patate douce rôtie, brocoli vapeur et citron — riche en oméga-3.',                22.90, 12, 0, '2026-05-04', 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600', 'Disponible', 3),
('Meal Prep : Buddha Bowl Vegan',     'Quinoa, pois chiches rôtis, avocat, carottes râpées et sauce houmous — 100% végétal.',             17.90, 18, 0, '2026-05-07', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600', 'Disponible', 3),
('Meal Prep : Omelette Légumes',      'Œufs bio, poivrons, épinards, champignons et fromage de chèvre — petit-déjeuner protéiné.',       14.90, 20, 0, '2026-05-05', 'https://images.unsplash.com/photo-1510693206972-df098062cb71?w=600', 'Disponible', 3),
('Meal Prep : Curry Lentilles',       'Lentilles corail, lait de coco, épices douces, riz basmati — repas chaud réconfortant.',           16.90, 15, 0, '2026-05-08', 'https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=600', 'Disponible', 3),
('Meal Prep : Wrap Healthy',          'Tortilla complète, poulet grillé, avocat, salade, tomates et sauce yaourt — lunch rapide.',        13.90, 22, 0, '2026-05-06', 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=600', 'Disponible', 3),
('Meal Prep : Porridge Protéiné',     'Flocons d\'avoine, protéine de whey vanille, banane, beurre d\'amande — breakfast muscle.',        12.90, 25, 0, '2026-05-10', 'https://images.unsplash.com/photo-1517673132405-a56a62b18caf?w=600', 'Disponible', 3),
('Meal Prep : Soupe Détox',           'Brocoli, courgette, épinards, gingembre et bouillon de légumes — soupe verte purifiante.',         11.90, 20, 0, '2026-05-07', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600', 'Disponible', 3),
('Meal Prep : Steak-Légumes Grillés', 'Steak de bœuf maigre, poivrons grillés, courgettes et patate douce — repas sportif complet.',     24.90, 10, 0, '2026-05-04', 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=600', 'Disponible', 3),
('Meal Prep : Overnight Oats',        'Avoine, lait d\'amande, chia, fruits rouges et miel — préparez la veille, dégustez le matin.',    10.90, 30, 0, '2026-05-09', 'https://images.unsplash.com/photo-1484723091739-30a097e8f929?w=600', 'Disponible', 3);
