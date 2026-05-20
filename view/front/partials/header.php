<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_hUserId  = $_SESSION['user_id'] ?? '';
$_hNom     = '';
$_hPrenom  = '';
$_hPicture = 'default.png';
if ($_hUserId !== '') {
  try {
    if (!class_exists('config')) {
      require_once __DIR__ . '/../../../config.php';
    }
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT nom, prenom, profile_picture FROM user WHERE id = :id");
    $stmt->execute(['id' => $_hUserId]);
    $u = $stmt->fetch();
    if ($u) {
      $_hNom     = $u['nom'];
      $_hPrenom  = $u['prenom'];
      $_hPicture = $u['profile_picture'] ?? 'default.png';
    }
  } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Smart Meal Planner') ?></title>
  <link rel="icon" type="image/jpeg" href="../assets/img/favicon.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
  <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body class="index-page">

<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container position-relative d-flex align-items-center justify-content-between">
    <a href="../index.php" class="logo d-flex align-items-center me-auto me-xl-0" style="gap:6px;">
      <img src="../assets/img/logo-smp.jpg" alt="SmartMealPlanner" style="height:42px;width:auto;">
      <span class="fw-bold" style="font-size:1.25rem;color:#2d2d2d;letter-spacing:0;">SmartMealPlanner</span>
    </a>

    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="../index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>Home</a></li>
        <li><a href="interfaceevent.php" <?= $currentPage === 'interfaceevent.php' ? 'class="active"' : '' ?>>Events</a></li>
        <li><a href="produits.php" <?= $currentPage === 'produits.php' ? 'class="active"' : '' ?>>Shop</a></li>
        <li><a href="Meals.php" <?= in_array($currentPage, ['Meals.php','day_plan.php','Plans.php','view_plan.php','create_plan.php']) ? 'class="active"' : '' ?>>Meals</a></li>
        <li><a href="Plans.php" <?= $currentPage === 'Plans.php' ? 'class="active"' : '' ?>>My Plan</a></li>
        <li><a href="repas.php" <?= in_array($currentPage, ['repas.php','detail_repas.php','edit_repas.php','home.php']) ? 'class="active"' : '' ?>>Recipes</a></li>
        <li><a href="#footer">Contact</a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

    <!-- User account -->
    <div class="d-flex align-items-center gap-2 ms-3">
      <?php if ($_hUserId !== ''): ?>
        <a href="profile.php" class="d-flex flex-column align-items-center text-decoration-none" style="line-height:1.1;">
          <img src="../assets/img/profiles/<?= htmlspecialchars($_hPicture) ?>"
               alt="Profile"
               style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
          <small style="font-size:0.7rem;color:#ce1212;font-weight:600;">
            <?= htmlspecialchars(trim($_hNom . ' ' . $_hPrenom) ?: 'User') ?>
          </small>
        </a>
        <a href="logout.php" style="color:#ce1212;font-weight:600;font-size:0.85rem;text-decoration:none;">Logout</a>
      <?php else: ?>
        <a href="signin.php" style="color:#ce1212;font-weight:600;font-size:0.85rem;text-decoration:none;">Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</header>
