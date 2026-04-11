<?php

/**
 * Back-office validation rules for meals.
 */
class MealValidator
{
    public const MAX_NAME_WORDS = 60;
    public const MAX_DESCRIPTION_WORDS = 1200;
    public const MAX_CALORIES = 3000;

    public const MEAL_TYPES = ['breakfast', 'lunch', 'dinner', 'snack'];

    public static function wordCount(string $text): int
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return 0;
        }
        return count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * @return string[] error messages (empty if valid)
     */
    public static function validateName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['Name is required.'];
        }
        if (self::wordCount($name) > self::MAX_NAME_WORDS) {
            return ['Name must not exceed ' . self::MAX_NAME_WORDS . ' words.'];
        }
        return [];
    }

    /**
     * @return string[]
     */
    public static function validateMealType(string $type): array
    {
        $type = strtolower(trim($type));
        if (!in_array($type, self::MEAL_TYPES, true)) {
            return ['Meal type must be Breakfast, Lunch, Dinner, or Snacks.'];
        }
        return [];
    }

    /**
     * @return string[]
     */
    public static function validateCalories(mixed $value): array
    {
        if ($value === '' || $value === null) {
            return ['Calories are required.'];
        }
        if (is_string($value) && !preg_match('/^\d+$/', trim($value))) {
            return ['Calories must be whole numbers only.'];
        }
        $n = (int) $value;
        if ($n < 0 || $n > self::MAX_CALORIES) {
            return ['Calories must be between 0 and ' . self::MAX_CALORIES . '.'];
        }
        return [];
    }

    /**
     * @return string[]
     */
    public static function validateDescription(string $description): array
    {
        $description = trim($description);
        if ($description === '') {
            return ['Description is required.'];
        }
        if (self::wordCount($description) > self::MAX_DESCRIPTION_WORDS) {
            return ['Description must not exceed ' . self::MAX_DESCRIPTION_WORDS . ' words.'];
        }
        return [];
    }

    /**
     * @param array{name?:string,tmp_name?:string,size?:int,error?:int} $file $_FILES['field']
     * @return string[] errors; empty if optional and no file
     */
    public static function validateImageUpload(?array $file, bool $required): array
    {
        if ($file === null) {
            return $required ? ['Please choose an image file.'] : [];
        }
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            return $required ? ['Please choose an image file.'] : [];
        }
        if ($err !== UPLOAD_ERR_OK) {
            return ['Image upload failed.'];
        }
        $maxBytes = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            return ['Image must be 5 MB or smaller.'];
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            'image/gif' => true,
        ];
        if (!isset($allowed[$mime])) {
            return ['Image must be JPEG, PNG, WebP, or GIF.'];
        }
        return [];
    }
}
