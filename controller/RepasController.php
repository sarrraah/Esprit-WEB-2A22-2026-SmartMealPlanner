<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Repas.php';

$repasModel = new Repas();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$projectPath = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$relativeProject = str_replace($docRoot, '', $projectPath);
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;

function uploadRepasImage($fileInputName)
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpPath = $_FILES[$fileInputName]['tmp_name'];
    $originalName = $_FILES[$fileInputName]['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $allowed, true)) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/repas';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = 'repas_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpPath, $destination)) {
        return null;
    }

    return 'uploads/repas/' . $filename;
}

function deleteRepasImageFile($imagePath)
{
    if (empty($imagePath) || strpos($imagePath, 'uploads/repas/') !== 0) {
        return;
    }
    $absolutePath = __DIR__ . '/../' . $imagePath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'update')) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $calories = isset($_POST['calories']) ? floatval($_POST['calories']) : null;
    $idRecette = isset($_POST['id_recette']) ? intval($_POST['id_recette']) : null;
    $from = isset($_POST['from']) ? $_POST['from'] : 'back';

    $imageRepas = uploadRepasImage('image_repas');
    $repasModel->addRepas($nom, $calories, $description, $idRecette, $imageRepas);

    $redirect = $from === 'front' ? $baseUrl . '/view/front/repas.php?success=1' : $baseUrl . '/view/back/repas.php?success=1';
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $repasToDelete = $repasModel->getRepasById($id);
    $repasModel->deleteRepas($id);
    if ($repasToDelete && isset($repasToDelete['image_repas'])) {
        deleteRepasImageFile($repasToDelete['image_repas']);
    }
    $redirect = isset($_GET['from']) && $_GET['from'] === 'front' ? $baseUrl . '/view/front/repas.php?deleted=1' : $baseUrl . '/view/back/repas.php?success=1';
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $calories = isset($_POST['calories']) ? floatval($_POST['calories']) : null;
    $idRecette = isset($_POST['id_recette']) ? intval($_POST['id_recette']) : null;
    $from = isset($_POST['from']) ? $_POST['from'] : 'back';

    $currentImage = $_POST['current_image'] ?? null;
    $newImage = uploadRepasImage('image_repas');
    $imageRepas = $newImage ?: $currentImage;
    $repasModel->updateRepas($id, $nom, $calories, $description, $idRecette, $imageRepas);
    if ($newImage && $currentImage && $newImage !== $currentImage) {
        deleteRepasImageFile($currentImage);
    }
    $redirect = $from === 'front' ? $baseUrl . '/view/front/repas.php?success=1' : $baseUrl . '/view/back/repas.php?success=1';
    header('Location: ' . $redirect);
    exit();
}
?>