<?php
require_once 'auth.php';
require_once '../../config.php';

$userId = $_SESSION['user_id'];

try {
    $pdo = config::getConnexion();

    $sqlUpdate = "UPDATE user SET statut = :statut WHERE id = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        'statut' => 'active',
        'id' => $userId
    ]);

    header("Location: index.php?login=success");
    exit();
} catch (Exception $e) {
    header("Location: ../index.php");
    exit();
}
