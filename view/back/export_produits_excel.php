<?php
/**
 * Export all products as Excel (CSV UTF-8 BOM — opens natively in Excel)
 */
require_once __DIR__ . '/../../controller/ProduitController.php';

$produitController = new ProduitController();
$produits = $produitController->listProduits();

// Add statut to each product
foreach ($produits as &$p) {
    $p['statut'] = determinerStatut($p['quantiteStock'], $p['dateExpiration']);
}
unset($p);

$filename = 'stock_produits_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
fputcsv($output, [
    'ID',
    'Name',
    'Category',
    'Description',
    'Price (DT)',
    'Stock Qty',
    'Expiration Date',
    'Status',
    'Image'
], ';');

// Data rows
foreach ($produits as $p) {
    fputcsv($output, [
        $p['id'],
        $p['nom'],
        $p['categorie_nom'] ?? '—',
        $p['description'] ?? '',
        number_format((float)$p['prix'], 2, '.', ''),
        (int)$p['quantiteStock'],
        $p['dateExpiration'] ?? '',
        $p['statut'],
        $p['image'] ?? ''
    ], ';');
}

fclose($output);
exit;
