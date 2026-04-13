<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Smart Meal Planner') ?></title>
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Amatic+SC:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>

<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">
        <a href="home.php" class="logo d-flex align-items-center me-auto me-xl-0">
            <h1 class="sitename">Smart<span>Meal</span></h1>
        </a>
        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="home.php" <?= ($activePage??'')==='home' ? 'class="active"' : '' ?>>Accueil</a></li>
                <li><a href="repas.php" <?= ($activePage??'')==='repas' ? 'class="active"' : '' ?>>Repas</a></li>
                <li><a href="add_repas.php" <?= ($activePage??'')==='add_repas' ? 'class="active"' : '' ?>>Ajouter un Repas</a></li>
                <li><a href="../back/index.php">Admin</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
        <a class="btn-getstarted" href="repas.php">Voir les Repas</a>
    </div>
</header>
