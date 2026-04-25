<?php
// Cherche le fichier dans tout le projet
$filename = 'event_69e3c9e25e15a3.68732677.jpg';
$root = 'C:/Users/Rana/Desktop/xampp/htdocs/projet_nutriplanner/';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if ($file->getFilename() === $filename) {
        echo "✅ Trouvé ici : " . $file->getPathname();
    }
}
echo "Recherche terminée.";
?>