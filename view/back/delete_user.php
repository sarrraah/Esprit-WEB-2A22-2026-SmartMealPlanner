<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$id = $_POST['id'] ?? '';

if ($id === '') {
    header('Location: users.php');
    exit;
}

$controller = new UserController();

try {
    $controller->delete($id);
} catch (Exception $e) {
}

header('Location: users.php');
exit;