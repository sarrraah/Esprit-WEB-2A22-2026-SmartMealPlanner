<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false]); exit;
}

$mealId = (int) ($_POST['meal_id'] ?? 0);
$userId = 1; // single user for now

if ($mealId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid meal ID']); exit;
}

try {
    $pdo = Database::pdo();

    // Ensure table exists
    $pdo->exec('CREATE TABLE IF NOT EXISTS favourites (
        id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id    INT UNSIGNED NOT NULL DEFAULT 1,
        meal_id    INT UNSIGNED NOT NULL,
        created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_user_meal (user_id, meal_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    // Check if already favourited
    $check = $pdo->prepare('SELECT id FROM favourites WHERE user_id=:uid AND meal_id=:mid');
    $check->execute([':uid' => $userId, ':mid' => $mealId]);

    if ($check->fetch()) {
        // Remove from favourites
        $pdo->prepare('DELETE FROM favourites WHERE user_id=:uid AND meal_id=:mid')
            ->execute([':uid' => $userId, ':mid' => $mealId]);
        echo json_encode(['ok' => true, 'favourited' => false]);
    } else {
        // Add to favourites
        $pdo->prepare('INSERT INTO favourites (user_id, meal_id) VALUES (:uid, :mid)')
            ->execute([':uid' => $userId, ':mid' => $mealId]);
        echo json_encode(['ok' => true, 'favourited' => true]);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
