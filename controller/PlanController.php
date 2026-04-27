<?php

require_once __DIR__ . '/../model/Plan.php';
require_once __DIR__ . '/../model/Meal.php';
require_once __DIR__ . '/../config/Database.php';

class PlanController
{
    public static function getFirstPlan(): ?Plan
    {
        return Plan::first();
    }

    public static function createPlan(array $post): array
    {
        $nom      = trim($post['nom']      ?? '');
        $objectif = trim($post['objectif'] ?? '');
        $duree    = max(1, (int) ($post['duree'] ?? 7));
        $calories = trim($post['calories'] ?? '');

        $errors = [];
        if ($nom === '')      $errors[] = 'Plan name is required.';
        if ($objectif === '') $errors[] = 'Objective is required.';

        if ($errors) return ['ok' => false, 'errors' => $errors];

        $dateDebut = date('Y-m-d');
        $existing  = Plan::first();

        if ($existing) {
            PlanDbStore::update($existing->id, [
                'nom'         => $nom,
                'duree'       => $duree,
                'date_debut'  => $existing->dateDebut ?: $dateDebut,
                'date_fin'    => date('Y-m-d', strtotime("+{$duree} days", strtotime($existing->dateDebut ?: $dateDebut))),
                'objectif'    => $objectif,
                'description' => $calories ? "Daily target: {$calories} kcal" : '',
            ]);
            $plan = Plan::first();
        } else {
            PlanDbStore::insert([
                'nom'         => $nom,
                'duree'       => $duree,
                'date_debut'  => $dateDebut,
                'date_fin'    => date('Y-m-d', strtotime("+{$duree} days")),
                'objectif'    => $objectif,
                'description' => $calories ? "Daily target: {$calories} kcal" : '',
                'user_id'     => 1,
            ]);
            $plan = Plan::first();
        }

        // Seed all days of the plan into plan_detail
        if ($plan) {
            self::seedPlanDetail($plan);
        }

        return ['ok' => true, 'errors' => []];
    }

    /**
     * Pre-populate plan_detail with auto-seeded meals for every day of the plan.
     * Uses INSERT IGNORE so existing overrides are never overwritten.
     */
    public static function seedPlanDetail(Plan $plan): void
    {
        $allMeals = Meal::all();
        $types    = ['breakfast', 'lunch', 'dinner', 'snack'];
        $start    = strtotime($plan->dateDebut);

        try {
            $pdo  = Database::pdo();
            $stmt = $pdo->prepare('
                INSERT IGNORE INTO plan_detail (plan_id, meal_date, meal_type, meal_id)
                VALUES (:plan_id, :meal_date, :meal_type, :meal_id)
            ');

            for ($day = 0; $day < $plan->duree; $day++) {
                $date   = date('Y-m-d', strtotime("+{$day} days", $start));
                $dayNum = $day + 1;

                foreach ($types as $i => $type) {
                    $filtered = array_values(array_filter($allMeals, fn($m) => $m->mealType === $type));
                    if (!$filtered) continue;
                    $meal = $filtered[($dayNum + $i) % count($filtered)];

                    $stmt->execute([
                        ':plan_id'   => $plan->id,
                        ':meal_date' => $date,
                        ':meal_type' => $type,
                        ':meal_id'   => $meal->id,
                    ]);
                }
            }
        } catch (Throwable $e) {}
    }

    public static function deletePlan(): void
    {
        $plan = Plan::first();
        if ($plan) {
            PlanDbStore::delete($plan->id);
        }
    }
}
