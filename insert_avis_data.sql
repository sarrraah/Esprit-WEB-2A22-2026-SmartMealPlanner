-- ============================================================
-- Données de test pour la table avis
-- IDs produits vérifiés depuis la base
-- ============================================================

-- Produit 1 : Épinards Bio
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(1, 5, 'Excellents épinards, très frais et bien conditionnés. Je recommande vivement !', '2026-04-10', 1),
(2, 4, 'Bonne qualité, livraison rapide. Parfaits pour mes smoothies du matin.', '2026-04-15', 1),
(3, 5, 'Bio et délicieux, on sent vraiment la différence avec les épinards classiques.', '2026-04-20', 1);

-- Produit 2 : Carottes Bio
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(4, 4, 'Très croquantes et sucrées. Idéales pour les jus et les salades.', '2026-04-12', 2),
(5, 3, 'Correctes mais un peu petites. Goût agréable quand même.', '2026-04-18', 2);

-- Produit 4 : Avocat Hass
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(6, 5, 'Avocats parfaitement mûrs à la livraison. Texture crémeuse, goût exceptionnel !', '2026-04-08', 4),
(7, 5, 'Les meilleurs avocats que j\'ai commandés en ligne. Toujours au top.', '2026-04-22', 4),
(8, 4, 'Très bons, juste un peu chers mais la qualité est là.', '2026-04-25', 4);

-- Produit 6 : Filet de Poulet
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(9,  5, 'Poulet fermier de qualité supérieure. Tendre et savoureux, parfait pour le meal prep.', '2026-04-05', 6),
(10, 4, 'Très bonne qualité, bien emballé et frais à la réception.', '2026-04-14', 6),
(11, 2, 'Un peu déçu, les filets étaient plus petits que prévu.', '2026-04-19', 6);

-- Produit 7 : Saumon Atlantique
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(12, 5, 'Saumon frais et savoureux, fondant en bouche. Livraison impeccable.', '2026-04-07', 7),
(13, 5, 'Qualité restaurant à la maison ! Je commande régulièrement.', '2026-04-16', 7);

-- Produit 9 : Flocons d'Avoine
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(14, 4, 'Flocons de bonne qualité, cuits rapidement et très bon goût.', '2026-04-11', 9),
(15, 5, 'Excellent pour le petit-déjeuner, je l\'utilise tous les matins.', '2026-04-23', 9);

-- Produit 12 : Brocoli Frais
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(16, 4, 'Brocoli très frais, bien vert et croquant. Parfait pour la vapeur.', '2026-04-13', 12),
(17, 5, 'Excellent brocoli, riche en vitamines. Je recommande !', '2026-04-21', 12);

-- Produit 16 : Pack Fruits Rouges
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(18, 5, 'Pack incroyable ! Les fruits sont frais, sucrés et bien conditionnés.', '2026-04-09', 16),
(19, 4, 'Très bon pack, parfait pour mes smoothies. Je recommande.', '2026-04-17', 16);

-- Produit 18 : Pack Protéines Maigres
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(20, 5, 'Pack idéal pour les sportifs. Tout est frais et de qualité.', '2026-04-06', 18),
(21, 4, 'Bon rapport qualité/prix pour un pack protéiné complet.', '2026-04-21', 18),
(22, 5, 'Je commande ce pack chaque semaine pour ma préparation physique !', '2026-04-26', 18);

-- Produit 26 : Meal Prep Bowl Poulet-Quinoa
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(23, 5, 'Le meilleur meal prep que j\'ai essayé ! Tout est inclus, c\'est pratique et délicieux.', '2026-04-10', 26),
(24, 4, 'Très bon pack, gain de temps énorme. La sauce tahini est excellente.', '2026-04-18', 26),
(25, 5, 'Parfait pour la semaine, je prépare tout en 20 min le dimanche.', '2026-04-24', 26);

-- Produit 27 : Saumon-Patate Douce
INSERT INTO avis (id_avis, note, commentaire, date_avis, id_produit) VALUES
(26, 5, 'Combo saumon-patate douce absolument délicieux. Très frais et bien dosé.', '2026-04-13', 27),
(27, 4, 'Excellent pack, le saumon est de très bonne qualité.', '2026-04-20', 27);
