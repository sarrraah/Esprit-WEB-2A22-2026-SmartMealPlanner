<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner - Admin</title>
  <link href="../assets/template/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/template/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/template/vendor/aos/aos.css" rel="stylesheet">
  <link href="../assets/template/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/template/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="../assets/template/css/main.css" rel="stylesheet">
</head>
<body class="index-page">
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container position-relative d-flex align-items-center justify-content-between">
    <a href="afficherProduit.php" class="logo d-flex align-items-center me-auto me-xl-0">
      <h1 class="sitename">Smart Meal Planner</h1><span>.</span>
    </a>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="afficherProduit.php" <?= in_array($currentPage, ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php']) ? 'class="active"' : '' ?>>Produits</a></li>
        <li><a href="afficherCategorie.php" <?= in_array($currentPage, ['afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php']) ? 'class="active"' : '' ?>>Catégories</a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>
    <a class="btn-getstarted" href="ajouterProduit.php">Ajouter Produit</a>
  </div>
</header>
<main class="main">
