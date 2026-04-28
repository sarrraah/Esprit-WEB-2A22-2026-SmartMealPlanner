<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner - Shop</title>
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
    <a href="interfaceclient.php" class="logo d-flex align-items-center me-auto me-xl-0">
      <h1 class="sitename">Smart Meal Planner</h1><span>.</span>
    </a>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="interfaceclient.php" <?= $currentPage === 'interfaceclient.php' ? 'class="active"' : '' ?>>Home</a></li>
        <li><a href="produits.php" <?= $currentPage === 'produits.php' ? 'class="active"' : '' ?>>Produits</a></li>
        <li><a href="categories.php" <?= $currentPage === 'categories.php' ? 'class="active"' : '' ?>>Catégories</a></li>
        <li><a href="#evenements" <?= $currentPage === 'evenements.php' ? 'class="active"' : '' ?>>Événements</a></li>
        <li><a href="#mealplanner" <?= $currentPage === 'mealplanner.php' ? 'class="active"' : '' ?>>Meal Planner</a></li>
        <li><a href="#recettes" <?= $currentPage === 'recettes.php' ? 'class="active"' : '' ?>>Recettes</a></li>
        <li><a href="#footer">Contact</a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>
    <button class="btn-getstarted position-relative" onclick="ouvrirPanier()" style="border:none;cursor:pointer;">
      🛒 <span id="panier-badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#fff;color:#c0392b;border-radius:50%;width:18px;height:18px;font-size:11px;font-weight:700;line-height:18px;text-align:center;">0</span>
    </button>
  </div>
</header>
<main class="main">
