<?php
require 'config.php';
$stmt = $pdo->query('SELECT id, nom, image, categorie FROM produit ORDER BY id DESC LIMIT 20');
foreach ($stmt as $row) {
    $image = strlen($row['image']) ? $row['image'] : '[EMPTY]';
    echo $row['id'] . ' | ' . $row['nom'] . ' | ' . $image . ' | ' . $row['categorie'] . PHP_EOL;
}
