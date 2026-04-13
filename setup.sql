-- ============================================================
-- Smart Meal Planner — Database Setup
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_meal_planner
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smart_meal_planner;

-- ── Recettes ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS recette_repas (
    id_recette      INT           NOT NULL AUTO_INCREMENT,
    nom_recette     VARCHAR(150)  NOT NULL,
    etapes          TEXT          NULL COMMENT 'Étapes de préparation',
    temps_prep      INT           NULL COMMENT 'minutes',
    temps_cuisson   INT           NULL COMMENT 'minutes',
    difficulte      ENUM('Facile','Moyen','Difficile') NOT NULL DEFAULT 'Facile',
    nb_personnes    INT           NOT NULL DEFAULT 2,
    image_recette   VARCHAR(255)  NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_recette)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Repas ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS repas (
    id_repas        INT           NOT NULL AUTO_INCREMENT,
    nom             VARCHAR(150)  NOT NULL,
    calories        DECIMAL(8,2)  NULL,
    proteines       DECIMAL(8,2)  NULL,
    glucides        DECIMAL(8,2)  NULL,
    lipides         DECIMAL(8,2)  NULL,
    description     TEXT          NULL,
    type_repas      ENUM('Petit-dejeuner','Dejeuner','Diner','Collation') NOT NULL DEFAULT 'Dejeuner',
    id_recette      INT           NOT NULL,
    image_repas     VARCHAR(255)  NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_repas),
    CONSTRAINT fk_repas_recette FOREIGN KEY (id_recette)
        REFERENCES recette_repas(id_recette)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Ingrédients ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ingredient (
    id_ingredient   INT           NOT NULL AUTO_INCREMENT,
    nom_ingredient  VARCHAR(150)  NOT NULL,
    unite           VARCHAR(50)   NULL COMMENT 'g, ml, pièce, cuillère...',
    quantite        DECIMAL(8,2)  NULL,
    id_repas        INT           NOT NULL,
    PRIMARY KEY (id_ingredient),
    CONSTRAINT fk_ingredient_repas FOREIGN KEY (id_repas)
        REFERENCES repas(id_repas)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Recettes par défaut ───────────────────────────────────────
INSERT IGNORE INTO recette_repas (id_recette, nom_recette, etapes, temps_prep, temps_cuisson, difficulte, nb_personnes) VALUES
    (1, 'Salade César',       '1. Laver la salade\n2. Préparer la sauce\n3. Mélanger et servir', 10, 0,  'Facile',  2),
    (2, 'Poulet rôti',        '1. Mariner le poulet\n2. Préchauffer le four à 200°C\n3. Cuire 45 min', 15, 45, 'Moyen', 4),
    (3, 'Tarte aux pommes',   '1. Préparer la pâte\n2. Éplucher les pommes\n3. Cuire 30 min', 20, 30, 'Moyen',   6),
    (4, 'Smoothie banane',    '1. Éplucher les fruits\n2. Mixer avec le lait\n3. Servir frais', 5,  0,  'Facile',  1),
    (5, 'Soupe de légumes',   '1. Couper les légumes\n2. Faire revenir\n3. Ajouter l\'eau et cuire 20 min', 10, 25, 'Facile', 4);
