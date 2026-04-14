<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Repas.php';

function repasBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

function uploadRepasImage(string $field): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../uploads/repas';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $filename = 'repas_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $filename)) return null;
    return 'uploads/repas/' . $filename;
}

function deleteRepasFile(?string $path): void {
    if (empty($path) || strpos($path, 'uploads/repas/') !== 0) return;
    $abs = __DIR__ . '/../' . $path;
    if (is_file($abs)) @unlink($abs);
}

function getRepasFields(): array {
    $f = function(string $k): ?float {
        $v = $_POST[$k] ?? '';
        return ($v !== '' && is_numeric($v)) ? (float)$v : null;
    };
    return [
        'nom'         => trim($_POST['nom'] ?? ''),
        'calories'    => $f('calories'),
        'proteines'   => $f('proteines'),
        'glucides'    => $f('glucides'),
        'lipides'     => $f('lipides'),
        'description' => trim($_POST['description'] ?? ''),
        'type_repas'  => $_POST['type_repas'] ?? 'Dejeuner',
        'id_recette'  => (int)($_POST['id_recette'] ?? 0),
    ];
}

$model  = new Repas();
$base   = repasBaseUrl();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$from   = $_POST['from'] ?? ($_GET['from'] ?? 'back');
$back   = $base . '/view/back/repas.php';
$front  = $base . '/view/front/repas.php';
$redir  = $from === 'front' ? $front : $back;

// ADD
if ($method === 'POST' && $action === '') {
    $f = getRepasFields();
    $image = uploadRepasImage('image_repas');
    if ($f['nom'] !== '' && $f['id_recette'] > 0) {
        $model->addRepas($f['nom'], $f['calories'], $f['proteines'], $f['glucides'], $f['lipides'], $f['description'], $f['type_repas'], $f['id_recette'], $image);
        $newId = (int) config::getConnexion()->lastInsertId();
        if ($from === 'back') {
            header('Location: ' . $base . '/view/back/add_repas.php?id_repas=' . $newId);
        } else {
            header('Location: ' . $front . '?success=1');
        }
    } else {
        header('Location: ' . $redir . '?success=1');
    }
    exit;
}

// UPDATE
if ($method === 'POST' && $action === 'update') {
    $id      = (int)($_POST['id'] ?? 0);
    $f       = getRepasFields();
    $current = $_POST['current_image'] ?? null;
    $new     = uploadRepasImage('image_repas');
    $image   = $new ?: $current;
    if ($id > 0 && $f['nom'] !== '' && $f['id_recette'] > 0) {
        $model->updateRepas($id, $f['nom'], $f['calories'], $f['proteines'], $f['glucides'], $f['lipides'], $f['description'], $f['type_repas'], $f['id_recette'], $image);
        if ($new && $current && $new !== $current) deleteRepasFile($current);
    }
    header('Location: ' . $redir . '?success=1'); exit;
}

// DELETE
if ($method === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $item = $model->getRepasById($id);
    $model->deleteRepas($id);
    if ($item && !empty($item['image_repas'])) deleteRepasFile($item['image_repas']);
    header('Location: ' . $redir . '?deleted=1'); exit;
}

header('Location: ' . $back); exit;
