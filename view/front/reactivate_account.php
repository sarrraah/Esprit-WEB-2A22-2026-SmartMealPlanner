<?php
require_once '../../config.php';

$userId = $_GET['id'] ?? '';

if ($userId == '') {
    header("Location: ../index.php");
    exit();
}

try {
    $pdo = config::getConnexion();

    $sqlUpdate = "UPDATE user SET statut = :statut WHERE id = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        'statut' => 'active',
        'id' => $userId
    ]);

    header("Location: index.php?id=" . urlencode($userId) . "&login=success");
    exit();
} catch (Exception $e) {
    header("Location: ../index.php");
    exit();
}
