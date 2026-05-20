<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$email = trim($_GET['email'] ?? '');

if (!$email) {
    echo json_encode(['error' => 'Email required']);
    exit;
}

// Milestones: attend N events → unlock discount
$milestones = [
    ['count' => 1,  'label' => 'Starter',    'emoji' => '🌱', 'discount' => 5],
    ['count' => 3,  'label' => 'Explorer',   'emoji' => '🚀', 'discount' => 10],
    ['count' => 5,  'label' => 'Enthusiast', 'emoji' => '⭐', 'discount' => 15],
    ['count' => 10, 'label' => 'VIP',        'emoji' => '👑', 'discount' => 25],
];

try {
    $pdo = config::getConnexion();

    // participation table uses email column directly
    $hasParticipation = (bool) $pdo->query("SHOW TABLES LIKE 'participation'")->fetchColumn();

    $total = 0;
    if ($hasParticipation) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $total = (int) $stmt->fetchColumn();
    }

    // Find next milestone and unlocked ones
    $next     = null;
    $unlocked = [];
    foreach ($milestones as $m) {
        if ($total >= $m['count']) {
            $unlocked[] = $m;
        } elseif (!$next) {
            $next = array_merge($m, ['need' => $m['count'] - $total]);
        }
    }

    echo json_encode([
        'total'     => $total,
        'milestones'=> $milestones,
        'unlocked'  => $unlocked,
        'next'      => $next,
    ]);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
