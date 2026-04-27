CREATE DATABASE IF NOT EXISTS smart_meal_planner
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smart_meal_planner;

-- ─────────────────────────────────────────────
-- 1. Meal catalog (standalone, reusable)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS meal (
    id_meal     INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom_meal    VARCHAR(500)    NOT NULL,
    type        ENUM('breakfast','lunch','dinner','snack') NOT NULL DEFAULT 'lunch',
    calories    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    notes       TEXT            NOT NULL,
    image       VARCHAR(500)    NOT NULL DEFAULT '',
    recipe_url  VARCHAR(500)    NOT NULL DEFAULT '#',
    PRIMARY KEY (id_meal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────
-- 2. Meal plans
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS mealplan (
    id_plan     INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom         VARCHAR(300)    NOT NULL,
    duree       TINYINT UNSIGNED NOT NULL DEFAULT 7,
    date_debut  DATE            NOT NULL,
    date_fin    DATE            NOT NULL,
    objectif    VARCHAR(200)    NOT NULL DEFAULT '',
    description TEXT            NOT NULL,
    user_id     INT UNSIGNED    NOT NULL DEFAULT 1,
    PRIMARY KEY (id_plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────
-- 3. Plan detail — links meals to a plan per day/type
--    FK: plan_id → mealplan.id_plan
--    FK: meal_id → meal.id_meal
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS plan_detail (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    plan_id     INT UNSIGNED    NOT NULL,
    meal_date   DATE            NOT NULL,
    meal_type   VARCHAR(20)     NOT NULL,
    meal_id     INT UNSIGNED    NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_plan_date_type (plan_id, meal_date, meal_type),
    CONSTRAINT fk_pd_plan FOREIGN KEY (plan_id) REFERENCES mealplan(id_plan) ON DELETE CASCADE,
    CONSTRAINT fk_pd_meal FOREIGN KEY (meal_id) REFERENCES meal(id_meal)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
