<?php

require_once __DIR__ . '/../model/Plan.php';
require_once __DIR__ . '/../model/PlanDbStore.php';

class PlanAdminController
{
    public static function handlePost(array $post): array
    {
        $action = (string) ($post['action'] ?? '');

        if ($action === 'delete') return self::handleDelete($post);
        if ($action === 'save')   return self::handleSave($post);

        return ['ok' => false, 'errors' => ['Unknown action.'], 'message' => ''];
    }

    private static function handleDelete(array $post): array
    {
        $id = (int) ($post['selected_id'] ?? 0);
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Select a plan before deleting.'], 'message' => ''];
        }
        if (!Plan::find($id)) {
            return ['ok' => false, 'errors' => ['Plan not found.'], 'message' => ''];
        }
        PlanDbStore::delete($id);
        return ['ok' => true, 'errors' => [], 'message' => 'Plan deleted.'];
    }

    private static function handleSave(array $post): array
    {
        $name     = trim((string) ($post['name']           ?? ''));
        $desc     = trim((string) ($post['description']    ?? ''));
        $obj      = trim((string) ($post['objective']      ?? ''));
        $dur      = (int) ($post['duration']       ?? 7);
        $calRaw   = trim((string) ($post['total_calories'] ?? '0'));
        $editId   = (int) ($post['editing_id']     ?? 0);

        $errors = [];
        if ($name === '') $errors[] = 'Name is required.';
        if (!preg_match('/^\d+$/', $calRaw)) $errors[] = 'Total calories must be a whole number.';
        if ($errors) return ['ok' => false, 'errors' => $errors, 'message' => ''];

        if ($editId > 0) {
            $existing = Plan::find($editId);
            if (!$existing) return ['ok' => false, 'errors' => ['Plan not found.'], 'message' => ''];
            PlanDbStore::update($editId, [
                'nom'         => $name,
                'duree'       => $dur,
                'date_debut'  => $existing->dateDebut,
                'date_fin'    => $existing->dateFin,
                'objectif'    => $obj,
                'description' => $desc,
            ]);
            return ['ok' => true, 'errors' => [], 'message' => 'Plan updated.'];
        }

        // Insert new plan
        $dateDebut = date('Y-m-d');
        PlanDbStore::insert([
            'nom'         => $name,
            'duree'       => $dur,
            'date_debut'  => $dateDebut,
            'date_fin'    => date('Y-m-d', strtotime("+{$dur} days")),
            'objectif'    => $obj,
            'description' => $desc,
            'user_id'     => 1,
        ]);
        return ['ok' => true, 'errors' => [], 'message' => 'Plan added.'];
    }
}
