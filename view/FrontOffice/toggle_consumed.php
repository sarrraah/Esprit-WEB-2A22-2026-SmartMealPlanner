<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../model/Plan.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false]); exit;
}

$mealType = trim($_POST['meal_type'] ?? '');
$date     = trim($_POST['date']      ?? date('Y-m-d'));
$plan     = Plan::first();

if (!$plan || $mealType === '') {
    echo json_encode(['ok' => false]); exit;
}

$key = 'consumed_' . $plan->id . '_' . $date;
if (!isset($_SESSION[$key])) $_SESSION[$key] = [];

if (isset($_SESSION[$key][$mealType])) {
    unset($_SESSION[$key][$mealType]);
    echo json_encode(['ok' => true, 'consumed' => false]);
} else {
    $_SESSION[$key][$mealType] = true;
    echo json_encode(['ok' => true, 'consumed' => true]);
}
