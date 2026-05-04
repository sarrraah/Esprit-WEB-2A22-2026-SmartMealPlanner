<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../model/Database.php';

$email = trim($_GET['email'] ?? '');

// Milestones definition
$milestones = [
    ['id' => 1, 'count' => 1,  'discount' => 5,  'emoji' => '🥄', 'label' => 'First Step'],
    ['id' => 2, 'count' => 3,  'discount' => 10, 'emoji' => '🌱', 'label' => 'Event Explorer'],
    ['id' => 3, 'count' => 5,  'discount' => 15, 'emoji' => '🔥', 'label' => 'Event Enthusiast'],
    ['id' => 4, 'count' => 10, 'discount' => 20, 'emoji' => '🥗', 'label' => 'Regular Attendee'],
    ['id' => 5, 'count' => 15, 'discount' => 25, 'emoji' => '💪', 'label' => 'Dedicated Member'],
    ['id' => 6, 'count' => 20, 'discount' => 30, 'emoji' => '⚡', 'label' => 'Event Champion'],
    ['id' => 7, 'count' => 30, 'discount' => 40, 'emoji' => '🏆', 'label' => 'VIP Attendee'],
];

// Count participations for this email
$total = 0;
$pdo   = null;
try {
    $pdo = Database::getConnection();
    if ($email) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE email = ?");
        $stmt->execute([$email]);
        $total = (int)$stmt->fetchColumn();
    }
} catch (Throwable $e) {}

// Fetch milestone codes defined by admin
$milestoneCodes = [];
if ($pdo) {
    try {
        $rows = $pdo->query("SELECT milestone_id, code FROM promo_code WHERE milestone_id IS NOT NULL AND active = 1")
                    ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) $milestoneCodes[(int)$r['milestone_id']] = $r['code'];
    } catch (Throwable $e) {}
}

// Build milestones with unlocked status + promo code
$result = [];
foreach ($milestones as $m) {
    $unlocked  = $total >= $m['count'];
    $promoCode = ($unlocked && isset($milestoneCodes[$m['id']])) ? $milestoneCodes[$m['id']] : null;

    $result[] = [
        'count'      => $m['count'],
        'discount'   => $m['discount'],
        'emoji'      => $m['emoji'],
        'label'      => $m['label'],
        'unlocked'   => $unlocked,
        'promo_code' => $promoCode,
    ];
}

// Next milestone
$next = null;
foreach ($milestones as $m) {
    if ($total < $m['count']) {
        $next = [
            'label'    => $m['label'],
            'discount' => $m['discount'],
            'emoji'    => $m['emoji'],
            'need'     => $m['count'] - $total,
            'count'    => $m['count'],
        ];
        break;
    }
}

echo json_encode([
    'total'      => $total,
    'milestones' => $result,
    'next'       => $next,
], JSON_UNESCAPED_UNICODE);
