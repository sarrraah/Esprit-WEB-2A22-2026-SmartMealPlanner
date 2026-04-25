<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/Plan.php';
$plans = Plan::all();
echo json_encode(array_map(fn(Plan $p) => $p->toArray() + ['mealTypeLabel' => $p->mealTypeLabel()], $plans), JSON_UNESCAPED_UNICODE);
