-- ============================================================
-- Migration : ajout de la colonne video_youtube
-- À exécuter UNE SEULE FOIS sur une base existante
-- ============================================================

USE smart_meal_planner;

-- Ajouter la colonne video_youtube si elle n'existe pas déjà
ALTER TABLE recette_repas
    ADD COLUMN IF NOT EXISTS video_youtube VARCHAR(20) NULL
    COMMENT 'ID de la vidéo YouTube (ex: dQw4w9WgXcQ)'
    AFTER image_recette;
