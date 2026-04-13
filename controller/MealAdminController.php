<?php

require_once __DIR__ . '/../model/Meal.php';
require_once __DIR__ . '/../model/MealValidator.php';
require_once __DIR__ . '/../model/MealDbStore.php';

/**
 * Back-office CRUD for meals (JSON file + uploads).
 * Add authentication before production use.
 */
class MealAdminController
{
    private static function uploadsAbsoluteDir(): string
    {
        return dirname(__DIR__) . '/view/assets/img/meals/uploads';
    }

    /**
     * @param array{tmp_name?:string,name?:string,error?:int,size?:int} $file
     */
    public static function storeUploadedImage(array $file): ?string
    {
        $dir = self::uploadsAbsoluteDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return null;
        }
        $mime = null;
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp) ?: null;
        }
        if ($mime === null && function_exists('mime_content_type')) {
            $mime = mime_content_type($tmp) ?: null;
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => null,
        };
        if ($ext === null) {
            return null;
        }
        $basename = 'meal_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $basename;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }
        return 'assets/img/meals/uploads/' . $basename;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @return array{ok: bool, errors: string[], message: string}
     */
    public static function handlePost(array $post, array $files): array
    {
        $action = (string) ($post['action'] ?? '');

        if ($action === 'delete') {
            return self::handleDelete($post);
        }

        if ($action !== 'save') {
            return ['ok' => false, 'errors' => ['Unknown action.'], 'message' => ''];
        }

        return self::handleSave($post, $files);
    }

    /**
     * @param array<string, mixed> $post
     * @return array{ok: bool, errors: string[], message: string}
     */
    private static function handleDelete(array $post): array
    {
        $id = (int) ($post['selected_id'] ?? 0);
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Select a meal in the list before deleting.'], 'message' => ''];
        }

        // If DB table is available, delete from DB. (UI can still show 1..N as "display id")
        if (MealDbStore::tableExists()) {
            MealDbStore::delete($id);
            return ['ok' => true, 'errors' => [], 'message' => 'Meal deleted.'];
        }

        $meals = Meal::all();
        $before = count($meals);
        $meals = array_values(array_filter($meals, fn (Meal $m) => $m->id !== $id));
        if (count($meals) === $before) {
            return ['ok' => false, 'errors' => ['Selected meal was not found.'], 'message' => ''];
        }
        // Reassign IDs sequentially starting from 1 (JSON mode)
        foreach ($meals as $index => $m) {
            $meals[$index] = new Meal($index + 1, $m->name, $m->calories, $m->description, $m->image, $m->recipeUrl, $m->mealType);
        }
        MealJsonStore::saveRows(array_map(fn (Meal $m) => $m->toArray(), $meals));
        return ['ok' => true, 'errors' => [], 'message' => 'Meal deleted.'];
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @return array{ok: bool, errors: string[], message: string}
     */
    private static function handleSave(array $post, array $files): array
    {
        $name = trim((string) ($post['name'] ?? ''));
        $type = strtolower(trim((string) ($post['meal_type'] ?? '')));
        $calRaw = $post['calories'] ?? '';
        $desc = trim((string) ($post['description'] ?? ''));
        $recipe = trim((string) ($post['recipe_url'] ?? ''));
        if ($recipe === '') {
            $recipe = '#';
        }

        $errors = array_merge(
            MealValidator::validateName($name),
            MealValidator::validateMealType($type),
            MealValidator::validateCalories($calRaw),
            MealValidator::validateDescription($desc),
        );

        $editId = (int) ($post['editing_id'] ?? 0);
        $existingImage = trim((string) ($post['existing_image'] ?? ''));

        $file = isset($files['meal_image']) && is_array($files['meal_image']) ? $files['meal_image'] : null;
        $hasNewUpload = $file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

        if ($editId > 0) {
            $imgErrors = $hasNewUpload
                ? MealValidator::validateImageUpload($file, false)
                : [];
            if (!$hasNewUpload && $existingImage === '') {
                $imgErrors = ['Keep the current image or upload a new one.'];
            }
        } else {
            $imgErrors = MealValidator::validateImageUpload($file, true);
        }

        $errors = array_merge($errors, $imgErrors);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'message' => ''];
        }

        $imagePath = $existingImage;
        if ($hasNewUpload) {
            $stored = self::storeUploadedImage($file);
            if ($stored === null) {
                return ['ok' => false, 'errors' => ['Could not save the uploaded image.'], 'message' => ''];
            }
            $imagePath = $stored;
        }

        $calories = (int) $calRaw;

        if ($editId > 0) {
            if (MealDbStore::tableExists()) {
                MealDbStore::update($editId, new Meal($editId, $name, $calories, $desc, $imagePath, $recipe, $type));
                $msg = 'Meal updated.';
            } else {
                $meals = Meal::all();
                $replaced = false;
                $newList = [];
                foreach ($meals as $m) {
                    if ($m->id === $editId) {
                        $newList[] = new Meal($editId, $name, $calories, $desc, $imagePath, $recipe, $type);
                        $replaced = true;
                    } else {
                        $newList[] = $m;
                    }
                }
                if (!$replaced) {
                    return ['ok' => false, 'errors' => ['Meal to update was not found.'], 'message' => ''];
                }
                $meals = $newList;
                MealJsonStore::saveRows(array_map(fn (Meal $m) => $m->toArray(), $meals));
                $msg = 'Meal updated.';
            }
        } else {
            if (MealDbStore::tableExists()) {
                MealDbStore::insert(new Meal(0, $name, $calories, $desc, $imagePath, $recipe, $type));
                $msg = 'Meal added.';
            } else {
                $meals = Meal::all();
                $meals[] = new Meal(Meal::nextId($meals), $name, $calories, $desc, $imagePath, $recipe, $type);
                MealJsonStore::saveRows(array_map(fn (Meal $m) => $m->toArray(), $meals));
                $msg = 'Meal added.';
            }
        }
        return ['ok' => true, 'errors' => [], 'message' => $msg];
    }
}
