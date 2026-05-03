<?php
/**
 * view/index.php — Point d'entrée de la vue
 *
 * Redirige automatiquement vers le fichier index.php principal
 * situé à la racine du projet, pour éviter l'accès direct au dossier view/.
 */
header('Location: ../index.php');
exit;
