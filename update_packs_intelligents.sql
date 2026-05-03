-- ============================================================
-- Mise à jour des Packs Intelligents
-- Chaque pack liste ses éléments constitutifs dans la description
-- ============================================================

-- Pack Fruits Rouges (fruits de saison antioxydants)
UPDATE produit SET
  nom = 'Pack Fruits Rouges',
  description = 'Fraises (250g) · Framboises (150g) · Myrtilles (150g) · Cerises (200g) · Groseilles (100g) — 5 fruits rouges de saison, riches en antioxydants et vitamine C pour booster votre immunité.',
  prix = 11.90
WHERE nom = 'Pack Fruits Rouges' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Légumes Verts (détox complet)
UPDATE produit SET
  nom = 'Pack Légumes Verts Détox',
  description = 'Épinards (200g) · Brocoli (300g) · Courgette (2 pcs) · Haricots verts (200g) · Concombre (1 pc) · Céleri (2 branches) — 6 légumes verts pour une cure détox complète.',
  prix = 9.90
WHERE nom = 'Pack Légumes Verts' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Protéines Maigres
UPDATE produit SET
  nom = 'Pack Protéines Maigres',
  description = 'Filet de poulet (300g) · Thon en boîte (2x160g) · Œufs bio (x6) · Blanc de dinde (200g) · Fromage blanc 0% (250g) — 5 sources de protéines maigres pour sportifs et actifs.',
  prix = 21.90
WHERE nom = 'Pack Protéines Maigres' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Céréales Complètes
UPDATE produit SET
  nom = 'Pack Céréales & Graines',
  description = 'Quinoa bio (500g) · Flocons d\'avoine (500g) · Riz complet (500g) · Graines de chia (200g) · Graines de lin (200g) · Boulgour (300g) — 6 céréales et graines pour une énergie durable.',
  prix = 14.90
WHERE nom = 'Pack Céréales Complètes' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Smoothie Détox
UPDATE produit SET
  nom = 'Pack Smoothie Vert',
  description = 'Épinards (150g) · Banane (2 pcs) · Gingembre frais (50g) · Citron (2 pcs) · Pomme verte (2 pcs) · Concombre (1 pc) — 6 ingrédients pour 4 smoothies verts détox maison.',
  prix = 8.90
WHERE nom = 'Pack Smoothie Détox' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Snack Healthy
UPDATE produit SET
  nom = 'Pack Snack Healthy',
  description = 'Amandes naturelles (100g) · Noix de cajou (100g) · Noix (80g) · Dattes Medjool (150g) · Abricots secs (100g) · Raisins secs (80g) — 6 snacks sains pour grignoter sans culpabilité.',
  prix = 13.90
WHERE nom = 'Pack Snack Healthy' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Salade Complète
UPDATE produit SET
  nom = 'Pack Salade Repas',
  description = 'Roquette (100g) · Tomates cerises (200g) · Avocat (1 pc) · Feta (100g) · Graines de tournesol (50g) · Olives noires (80g) · Vinaigrette balsamique (1 flacon) — tout pour une salade repas en 5 minutes.',
  prix = 12.50
WHERE nom = 'Pack Salade Complète' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Petit-Déjeuner
UPDATE produit SET
  nom = 'Pack Breakfast Équilibré',
  description = 'Flocons d\'avoine (400g) · Fruits rouges mélangés (200g) · Yaourt grec nature (500g) · Miel bio (250g) · Banane (2 pcs) · Beurre d\'amande (200g) — 6 ingrédients pour 5 breakfasts équilibrés.',
  prix = 15.90
WHERE nom = 'Pack Petit-Déjeuner' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Anti-Inflammatoire
UPDATE produit SET
  nom = 'Pack Anti-Inflammatoire',
  description = 'Curcuma frais (100g) · Gingembre frais (100g) · Myrtilles (200g) · Noix (100g) · Saumon (200g) · Huile d\'olive extra vierge (250ml) · Thé vert (20 sachets) — 7 alliés naturels contre l\'inflammation.',
  prix = 17.90
WHERE nom = 'Pack Anti-Inflammatoire' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);

-- Pack Vegan Complet
UPDATE produit SET
  nom = 'Pack Vegan Power',
  description = 'Tofu ferme (300g) · Pois chiches cuits (400g) · Lentilles vertes (300g) · Tempeh (200g) · Edamame (200g) · Noix de cajou (100g) · Lait de coco (400ml) — 7 protéines végétales pour une journée 100% vegan.',
  prix = 18.90
WHERE nom = 'Pack Vegan Complet' AND id_categorie = (SELECT id_categorie FROM categorieproduit WHERE nom = 'Packs Intelligents' LIMIT 1);
