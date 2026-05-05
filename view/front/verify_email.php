<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';

$token = $_GET['token'] ?? '';

if ($token === '') {
    die('Invalid confirmation link.');
}

try {
    $db = config::getConnexion();

    $stmt = $db->prepare("SELECT * FROM user WHERE email_token = :token LIMIT 1");
    $stmt->execute([
        'token' => $token
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Invalid or expired confirmation link.');
    }

    $update = $db->prepare("
        UPDATE user
        SET email_verified = 1,
            email_token = NULL
        WHERE id = :id
    ");

    $update->execute([
        'id' => $user['id']
    ]);

    header('Location: signin.php?verified=success');
    exit;
} catch (Exception $e) {
    die('Verification error: ' . $e->getMessage());
}
