<?php
// Test rapide — pas de DB
echo "<h2>✅ Apache + PHP fonctionnent</h2>";
echo "<p>PHP version: " . phpversion() . "</p>";

// Test MySQL
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=3306", $user, $pass, [
        PDO::ATTR_TIMEOUT => 3,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p style='color:green'>✅ MySQL connecté — version: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ MySQL erreur: " . $e->getMessage() . "</p>";
}
?>
