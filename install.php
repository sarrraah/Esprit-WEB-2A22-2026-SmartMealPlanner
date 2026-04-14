<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'smart_meal_planner';

$steps = [];
$ok    = true;

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    $steps[] = [1, "Base de données <b>$db</b> créée / vérifiée."];

    $pdo->exec("CREATE TABLE IF NOT EXISTS recette_repas (
        id_recette    INT NOT NULL AUTO_INCREMENT,
        nom_recette   VARCHAR(150) NOT NULL,
        description   TEXT NULL,
        temps_prep    INT NULL,
        temps_cuisson INT NULL,
        difficulte    ENUM('Facile','Moyen','Difficile') NOT NULL DEFAULT 'Facile',
        nb_personnes  INT NOT NULL DEFAULT 2,
        image_recette VARCHAR(255) NULL,
        created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_recette)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $steps[] = [1, "Table <b>recette_repas</b> créée."];

    $pdo->exec("CREATE TABLE IF NOT EXISTS repas (
        id_repas    INT NOT NULL AUTO_INCREMENT,
        nom         VARCHAR(150) NOT NULL,
        calories    DECIMAL(8,2) NULL,
        proteines   DECIMAL(8,2) NULL,
        glucides    DECIMAL(8,2) NULL,
        lipides     DECIMAL(8,2) NULL,
        description TEXT NULL,
        type_repas  ENUM('Petit-dejeuner','Dejeuner','Diner','Collation') NOT NULL DEFAULT 'Dejeuner',
        id_recette  INT NOT NULL,
        image_repas VARCHAR(255) NULL,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_repas),
        CONSTRAINT fk_repas_recette FOREIGN KEY (id_recette)
            REFERENCES recette_repas(id_recette)
            ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $steps[] = [1, "Table <b>repas</b> créée."];

    $pdo->exec("INSERT IGNORE INTO recette_repas
        (id_recette, nom_recette, description, temps_prep, temps_cuisson, difficulte, nb_personnes)
        VALUES
        (1,'Entree','Plats servis en debut de repas',10,0,'Facile',2),
        (2,'Plat principal','Plat central du repas',20,30,'Moyen',4),
        (3,'Dessert','Plats sucres en fin de repas',15,20,'Facile',4),
        (4,'Boisson','Boissons chaudes ou froides',5,0,'Facile',1),
        (5,'Snack','En-cas legers',5,0,'Facile',1),
        (6,'Soupe','Soupes et velouts',10,25,'Facile',4),
        (7,'Salade','Salades composees',10,0,'Facile',2)");
    $steps[] = [1, "7 recettes par défaut insérées."];

    // Add missing columns to repas if they don't exist
    $cols = $pdo->query("SHOW COLUMNS FROM repas")->fetchAll(PDO::FETCH_COLUMN);
    $alterMap = [
        'type_repas'  => "ADD COLUMN type_repas ENUM('Petit-dejeuner','Dejeuner','Diner','Collation') NOT NULL DEFAULT 'Dejeuner' AFTER description",
        'proteines'   => "ADD COLUMN proteines DECIMAL(8,2) NULL AFTER calories",
        'glucides'    => "ADD COLUMN glucides DECIMAL(8,2) NULL AFTER proteines",
        'lipides'     => "ADD COLUMN lipides DECIMAL(8,2) NULL AFTER glucides",
        'id_recette'  => "ADD COLUMN id_recette INT NULL",
        'image_repas' => "ADD COLUMN image_repas VARCHAR(255) NULL",
        'created_at'  => "ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
    ];
    foreach ($alterMap as $col => $sql) {
        if (!in_array($col, $cols)) {
            $pdo->exec("ALTER TABLE repas $sql");
            $steps[] = [1, "Colonne <b>$col</b> ajoutée à repas."];
        }
    }

    // Same for recette_repas
    $cols2 = $pdo->query("SHOW COLUMNS FROM recette_repas")->fetchAll(PDO::FETCH_COLUMN);
    foreach (['ingredients','etapes','description','temps_prep','temps_cuisson','difficulte','nb_personnes','image_recette','created_at'] as $c) {
        if (!in_array($c, $cols2)) {
            $alter = match($c) {
                'ingredients'   => "ADD COLUMN ingredients TEXT NULL",
                'etapes'        => "ADD COLUMN etapes TEXT NULL",
                'description'   => "ADD COLUMN description TEXT NULL",
                'temps_prep'    => "ADD COLUMN temps_prep INT NULL",
                'temps_cuisson' => "ADD COLUMN temps_cuisson INT NULL",
                'difficulte'    => "ADD COLUMN difficulte ENUM('Facile','Moyen','Difficile') NOT NULL DEFAULT 'Facile'",
                'nb_personnes'  => "ADD COLUMN nb_personnes INT NOT NULL DEFAULT 2",
                'image_recette' => "ADD COLUMN image_recette VARCHAR(255) NULL",
                'created_at'    => "ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
            };
            $pdo->exec("ALTER TABLE recette_repas $alter");
            $steps[] = [1, "Colonne <b>$c</b> ajoutée à recette_repas."];
        }
    }
    $steps[] = [1, "Structure complète et à jour."];

    // Create ingredient table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ingredient (
        id_ingredient   INT           NOT NULL AUTO_INCREMENT,
        nom_ingredient  VARCHAR(150)  NOT NULL,
        unite           VARCHAR(50)   NULL,
        quantite        DECIMAL(8,2)  NULL,
        id_repas        INT           NULL,
        id_recette      INT           NULL,
        PRIMARY KEY (id_ingredient)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $steps[] = [1, "Table <b>ingredient</b> créée."];

    // Add id_recette column if missing
    $ingCols = $pdo->query("SHOW COLUMNS FROM ingredient")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('id_recette', $ingCols)) {
        $pdo->exec("ALTER TABLE ingredient ADD COLUMN id_recette INT NULL");
        $steps[] = [1, "Colonne <b>id_recette</b> ajoutée à ingredient."];
    }
    if (!in_array('id_repas', $ingCols)) {
        $pdo->exec("ALTER TABLE ingredient ADD COLUMN id_repas INT NULL");
        $steps[] = [1, "Colonne <b>id_repas</b> ajoutée à ingredient."];
    }

} catch (PDOException $e) {
    $steps[] = [0, "Erreur : " . htmlspecialchars($e->getMessage())];
    $ok = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation SmartMeal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="card border-0 shadow" style="width:520px;border-radius:16px;overflow:hidden;">
    <div class="card-header py-3 <?= $ok ? 'bg-success' : 'bg-danger' ?> text-white">
        <h5 class="mb-0"><?= $ok ? '✅ Installation réussie !' : '❌ Erreur' ?></h5>
    </div>
    <div class="card-body p-4">
        <ul class="list-group list-group-flush mb-4">
            <?php foreach ($steps as [$s, $msg]): ?>
            <li class="list-group-item d-flex align-items-center gap-2">
                <span class="<?= $s ? 'text-success' : 'text-danger' ?> fw-bold fs-5"><?= $s ? '✓' : '✗' ?></span>
                <?= $msg ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($ok): ?>
        <div class="d-grid gap-2">
            <a href="view/front/home.php" class="btn btn-success btn-lg">🏠 Front Office</a>
            <a href="view/front/add_repas.php" class="btn btn-danger btn-lg">➕ Ajouter un Repas</a>
            <a href="view/back/index.php" class="btn btn-outline-dark">⚙️ Admin Dashboard</a>
        </div>
        <?php else: ?>
        <div class="alert alert-warning mb-0">Vérifiez que MySQL est démarré dans XAMPP.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
