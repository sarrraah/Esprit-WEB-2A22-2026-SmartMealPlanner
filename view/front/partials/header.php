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
    <style>
        /* Force all nav items visible on desktop */
        @media (min-width: 1200px) {
            .navmenu ul { display: flex !important; align-items: center; gap: 4px; }
            .navmenu ul li { padding: 15px 10px !important; }
            .navmenu ul li:last-child { padding-right: 0 !important; }
        }
        /* Admin button style in nav */
        .nav-admin-btn {
            background: var(--accent-color, #ce1212) !important;
            color: #fff !important;
            border-radius: 6px;
            padding: 6px 14px !important;
            font-size: .85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        .nav-admin-btn:hover { background: #a50e0e !important; color: #fff !important; }
        /* Add repas highlight */
        .nav-add-btn { color: var(--accent-color, #ce1212) !important; font-weight: 600; }
    </style>
</head>
<body>

<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

        <!-- Logo -->
        <a href="home.php" class="logo d-flex align-items-center me-auto me-xl-0">
            <h1 class="sitename">Smart<span>Meal</span></h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li>
                    <a href="home.php" <?= ($activePage??'')==='home' ? 'class="active"' : '' ?>>
                        Accueil
                    </a>
                </li>
                <li>
                    <a href="repas.php" <?= ($activePage??'')==='repas' ? 'class="active"' : '' ?>>
                        Repas
                    </a>
                </li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        <a class="btn-getstarted" href="repas.php">Voir les Repas</a>

    </div>
</header>
