<?php

require_once __DIR__ . '/../model/Plan.php';

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
        }

        return ['ok' => true, 'errors' => []];
    }

    public static function deletePlan(): void
    {
        $plan = Plan::first();
        if ($plan) {
            PlanDbStore::delete($plan->id);
        }
    }
}
