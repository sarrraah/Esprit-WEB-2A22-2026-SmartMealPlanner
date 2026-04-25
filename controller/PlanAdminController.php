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
        $type     = trim((string) ($post['meal_type']      ?? ''));
        $calRaw   = trim((string) ($post['total_calories'] ?? '0'));
        $obj      = trim((string) ($post['objective']      ?? ''));
        $dur      = (int) ($post['duration']       ?? 7);
        $daysDone = (int) ($post['days_completed'] ?? 0);
        $mPlanned = (int) ($post['meals_planned']  ?? 3);
        $mDone    = (int) ($post['meals_completed'] ?? 0);
        $editId   = (int) ($post['editing_id']     ?? 0);

        $errors = [];
        if ($name === '') $errors[] = 'Name is required.';
        if ($type === '') $errors[] = 'Plan type is required.';
        if (!preg_match('/^\d+$/', $calRaw)) $errors[] = 'Total calories must be a whole number.';

        if ($errors) return ['ok' => false, 'errors' => $errors, 'message' => ''];

        $plan = new Plan(0, $name, $desc, $type, (int) $calRaw, $obj, $dur, $daysDone, $mPlanned, $mDone);

        if ($editId > 0) {
            PlanDbStore::update($editId, $plan);
            return ['ok' => true, 'errors' => [], 'message' => 'Plan updated.'];
        }

        PlanDbStore::insert($plan);
        return ['ok' => true, 'errors' => [], 'message' => 'Plan added.'];
    }
}
