<?php
/**
 * test_image.php — Diagnostic de la génération d'image
 * Accéder via : http://localhost/.../test_image.php
 * Supprimer après utilisation.
 */
header('Content-Type: text/html; charset=utf-8');
echo '<pre>';

// Test 1 : cURL disponible ?
echo "=== cURL ===\n";
echo 'cURL activé : ' . (function_exists('curl_init') ? '✅ OUI' : '❌ NON') . "\n\n";

// Test 2 : allow_url_fopen ?
echo "=== allow_url_fopen ===\n";
echo 'allow_url_fopen : ' . (ini_get('allow_url_fopen') ? '✅ OUI' : '❌ NON') . "\n\n";

// Test 3 : Connexion réseau depuis PHP
echo "=== Test connexion TheMealDB ===\n";
$ch = curl_init('https://www.themealdb.com/api/json/v1/1/search.php?s=pizza');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT      => 'SmartMealPlanner/1.0',
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);
echo "HTTP Code : $code\n";
echo "cURL Error : " . ($err ?: 'aucune') . "\n";
if ($code === 200) {
    $data = json_decode($body, true);
    $img  = $data['meals'][0]['strMealThumb'] ?? 'non trouvé';
    echo "Image URL : $img\n";
} else {
    echo "Body : " . substr($body, 0, 200) . "\n";
}

echo "\n=== Test connexion Foodish ===\n";
$ch2 = curl_init('https://foodish-api.com/api/images/pizza');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT      => 'SmartMealPlanner/1.0',
]);
$body2 = curl_exec($ch2);
$code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$err2  = curl_error($ch2);
curl_close($ch2);
echo "HTTP Code : $code2\n";
echo "cURL Error : " . ($err2 ?: 'aucune') . "\n";
echo "Body : " . substr($body2, 0, 200) . "\n";

echo "\n=== Test Wikimedia (fallback) ===\n";
$ch3 = curl_init('https://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Good_Food_Display_-_NCI_Visuals_Online.jpg/800px-Good_Food_Display_-_NCI_Visuals_Online.jpg');
curl_setopt_array($ch3, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_NOBODY         => true, // HEAD seulement
]);
curl_exec($ch3);
$code3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
$err3  = curl_error($ch3);
curl_close($ch3);
echo "HTTP Code : $code3\n";
echo "cURL Error : " . ($err3 ?: 'aucune') . "\n";

echo "\n=== PHP Version ===\n";
echo PHP_VERSION . "\n";

echo '</pre>';
