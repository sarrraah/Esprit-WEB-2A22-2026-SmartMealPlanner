<?php
require_once __DIR__ . '/config.php';
$pdo = config::getConnexion();
$rows = $pdo->query("SELECT id, nom, image, prix FROM produit ORDER BY id")->fetchAll();
header('Content-Type: text/plain');
foreach ($rows as $r) {
    echo "id={$r['id']} | nom={$r['nom']} | prix={$r['prix']} | image=[{$r['image']}]\n";
}
