<?php

/**
 * Persists meals in data/meals.json (replace with DB in production).
 */
class MealJsonStore
{
    private const PATH = __DIR__ . '/../data/meals.json';

    public static function path(): string
    {
        return self::PATH;
    }

    public static function exists(): bool
    {
        return is_file(self::PATH) && is_readable(self::PATH);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function loadRows(): array
    {
        if (!self::exists()) {
            return [];
        }
        $raw = file_get_contents(self::PATH);
        if ($raw === false || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public static function saveRows(array $rows): void
    {
        $dir = dirname(self::PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(
            self::PATH,
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
