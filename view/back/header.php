<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    /* Reset template fonts completely */
    body, h1, h2, h3, h4, h5, h6, p, span, a, div, td, th, input, select, textarea, button {
      font-family: 'Raleway', sans-serif !important;
    }

    body { background: #f4f6f9; display: flex; min-height: 100vh; }

    /* ── SIDEBAR ── */
    #sidebar {
      width: 170px;
      min-height: 100vh;
      background: #1a1f2e;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      z-index: 100;
    }

    #sidebar .logo {
      padding: 22px 18px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    #sidebar .logo .brand {
      font-family: 'Raleway', sans-serif;
      font-size: 1.1rem;
      color: #fff;
      line-height: 1;
      letter-spacing: 3px;
      text-transform: uppercase;
    }
    #sidebar .logo .brand .light { font-weight: 300; }
    #sidebar .logo .brand .bold  { font-weight: 700; color: #e74c3c; }
    #sidebar .logo .sub {
      font-size: 0.6rem;
      color: #aaa;
      letter-spacing: 4px;
      text-transform: uppercase;
      margin-top: 4px;
      font-weight: 300;
      font-family: 'Raleway', sans-serif;
    }

    #sidebar nav { flex: 1; padding: 12px 0; }
    #sidebar nav .nav-section {
      font-size: 0.55rem;
      color: #666;
      letter-spacing: 2px;
      text-transform: uppercase;
      font-weight: 700;
      padding: 12px 18px 4px;
    }
    #sidebar nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 18px;
      color: #ccc;
      text-decoration: none;
      font-size: 0.75rem;
      font-weight: 300;
      letter-spacing: 0.5px;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }
    #sidebar nav a:hover { background: rgba(231,76,60,0.1); color: #fff; border-left-color: #e74c3c; }
    #sidebar nav a.active { background: #e74c3c; color: #fff; border-left-color: #e74c3c; font-weight: 700; }
    #sidebar nav a i { font-size: 0.95rem; }

    #sidebar .front-btn {
      padding: 14px 18px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
    #sidebar .front-btn a {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #ccc;
      text-decoration: none;
      font-size: 0.72rem;
      font-weight: 300;
      letter-spacing: 0.5px;
    }
    #sidebar .front-btn a:hover { color: #fff; }

    /* ── MAIN CONTENT ── */
    #main-content {
      margin-left: 170px;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ── TOP BAR ── */
    #topbar {
      background: #fff;
      border-bottom: 2px solid #e74c3c;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 99;
    }
    #topbar .page-title {
      font-family: 'Raleway', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      color: #2d2d2d;
      letter-spacing: 3px;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    #topbar .top-actions { display: flex; gap: 8px; }
    #topbar .btn-add {
      background: #e74c3c;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 6px 14px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    #topbar .btn-add:hover { background: #c0392b; color: #fff; }
    #topbar .btn-add-outline {
      background: #fff;
      color: #333;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 6px 14px;
      font-size: 0.75rem;
      font-weight: 300;
      letter-spacing: 0.5px;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    #topbar .btn-add-outline:hover { background: #f5f5f5; color: #333; }

    /* ── PAGE BODY ── */
    .page-body { padding: 24px; flex: 1; }

    /* ── CARDS ── */
    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .stat-card .stat-label { font-size: 0.7rem; color: #999; margin-bottom: 4px; font-weight: 300; letter-spacing: 1px; text-transform: uppercase; }
    .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; color: #2d2d2d; }
    .stat-card .stat-icon {
      width: 42px; height: 42px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
      margin-bottom: 10px;
    }

    .content-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .content-card .card-header-title {
      font-family: 'Raleway', sans-serif;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #2d2d2d;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 16px;
    }
  </style>
</head>
<body>

<?php
$_sbCurrentPage = basename($_SERVER['PHP_SELF']);

$_sbShopPages   = ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php','afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php','afficherAvis.php','supprimerAvis.php','afficherReclamations.php'];
$_sbEventPages  = ['listEvenements.php','addEvenement.php','updateEvenement.php','deleteEvenement.php','listParticipations.php','addParticipation.php','updateParticipation.php','searchParticipations.php','listCommentaires.php','listPromoCodes.php'];
$_sbMealPages   = ['meal_bo_index.php','meals_admin.php','plans_admin.php'];
$_sbRecipePages = ['index.php','repas.php','search_repas.php','recette.php','statistiques.php','aliments_durables.php','contenu_nutritionnel.php','add_repas.php','edit_repas.php','add_recette.php','edit_recette.php','view_recette.php','ingredients.php'];

$_sbSection = '';
if (in_array($_sbCurrentPage, $_sbShopPages))   $_sbSection = 'shop';
if (in_array($_sbCurrentPage, $_sbEventPages))  $_sbSection = 'events';
if (in_array($_sbCurrentPage, $_sbMealPages))   $_sbSection = 'meal';
if (in_array($_sbCurrentPage, $_sbRecipePages)) $_sbSection = 'recipes';
?>
<style>
  /* Force unified sidebar */
  body { display: flex !important; min-height: 100vh !important; }
  #main-content { flex: 1 !important; overflow-y: auto !important; min-width: 0 !important; margin-left: 0 !important; }

  /* Hide old sidebar */
  #sidebar { display: none !important; }

  #unified-sidebar {
    width: 170px !important;
    min-width: 170px !important;
    min-height: 100vh !important;
    height: 100vh !important;
    background: #1a1f2e !important;
    display: flex !important;
    flex-direction: column !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    flex-shrink: 0 !important;
    overflow-y: auto !important;
    font-family: 'Raleway', sans-serif !important;
  }
  #unified-sidebar * { font-family: 'Raleway', sans-serif !important; box-sizing: border-box !important; }

  #unified-sidebar .usb-logo {
    padding: 22px 18px 16px !important;
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
  }
  #unified-sidebar .usb-brand {
    font-size: 1.1rem !important; color: #fff !important; line-height: 1 !important;
    letter-spacing: 3px !important; text-transform: uppercase !important;
  }
  #unified-sidebar .usb-light { font-weight: 300 !important; }
  #unified-sidebar .usb-bold  { font-weight: 700 !important; color: #e74c3c !important; }
  #unified-sidebar .usb-sub {
    font-size: 0.6rem !important; color: #aaa !important; letter-spacing: 4px !important;
    text-transform: uppercase !important; margin-top: 4px !important; font-weight: 300 !important;
    display: block !important;
  }

  #unified-sidebar .usb-nav { flex: 1 !important; padding: 12px 0 !important; }
  #unified-sidebar .usb-group { border-bottom: 1px solid rgba(255,255,255,0.05) !important; }

  #unified-sidebar .usb-header {
    display: flex !important; align-items: center !important; justify-content: space-between !important;
    padding: 10px 18px !important;
    color: #ccc !important;
    font-size: 0.72rem !important; font-weight: 700 !important;
    letter-spacing: 1.5px !important; text-transform: uppercase !important;
    cursor: pointer !important;
    border-left: 3px solid transparent !important;
    transition: all 0.2s !important;
    user-select: none !important;
    background: transparent !important;
    text-decoration: none !important;
  }
  #unified-sidebar .usb-header:hover { background: rgba(231,76,60,0.1) !important; color: #fff !important; border-left-color: #e74c3c !important; }
  #unified-sidebar .usb-header.open  { color: #e74c3c !important; border-left-color: #e74c3c !important; background: rgba(231,76,60,0.08) !important; }
  #unified-sidebar .usb-left { display: flex !important; align-items: center !important; gap: 10px !important; }
  #unified-sidebar .usb-chevron { font-size: 0.65rem !important; transition: transform 0.25s !important; display: inline-block !important; }
  #unified-sidebar .usb-header.open .usb-chevron { transform: rotate(180deg) !important; }

  #unified-sidebar .usb-body { display: none !important; background: rgba(0,0,0,0.15) !important; }
  #unified-sidebar .usb-body.open { display: block !important; }
  #unified-sidebar .usb-body a {
    display: flex !important; align-items: center !important; gap: 8px !important;
    padding: 8px 18px 8px 36px !important;
    color: #aaa !important; text-decoration: none !important;
    font-size: 0.72rem !important; font-weight: 300 !important;
    letter-spacing: 0.5px !important;
    transition: all 0.2s !important;
    border-left: 3px solid transparent !important;
    background: transparent !important;
  }
  #unified-sidebar .usb-body a:hover { background: rgba(231,76,60,0.1) !important; color: #fff !important; border-left-color: #e74c3c !important; }
  #unified-sidebar .usb-body a.active { background: #e74c3c !important; color: #fff !important; border-left-color: #e74c3c !important; font-weight: 700 !important; }

  #unified-sidebar .usb-front {
    padding: 14px 18px !important;
    border-top: 1px solid rgba(255,255,255,0.08) !important;
  }
  #unified-sidebar .usb-front a {
    display: flex !important; align-items: center !important; gap: 8px !important;
    color: #ccc !important; text-decoration: none !important;
    font-size: 0.72rem !important; font-weight: 300 !important;
    background: transparent !important;
  }
  #unified-sidebar .usb-front a:hover { color: #fff !important; }
</style>

<div id="unified-sidebar">
  <div class="usb-logo">
    <div class="usb-brand"><span class="usb-light">Smart</span><span class="usb-bold">Meal</span></div>
    <span class="usb-sub">Admin</span>
  </div>

  <div class="usb-nav">

    <!-- SHOP -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'shop' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-shop"></i> Shop</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'shop' ? 'open' : '' ?>">
        <a href="afficherProduit.php" class="<?= in_array($_sbCurrentPage, ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php']) ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Products
        </a>
        <a href="afficherCategorie.php" class="<?= in_array($_sbCurrentPage, ['afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php']) ? 'active' : '' ?>">
          <i class="bi bi-tags"></i> Categories
        </a>
        <a href="afficherAvis.php" class="<?= in_array($_sbCurrentPage, ['afficherAvis.php','supprimerAvis.php']) ? 'active' : '' ?>">
          <i class="bi bi-chat-square-text"></i> Reviews
        </a>
        <a href="afficherReclamations.php" class="<?= $_sbCurrentPage === 'afficherReclamations.php' ? 'active' : '' ?>">
          <i class="bi bi-megaphone"></i> Complaints
        </a>
      </div>
    </div>

    <!-- EVENTS -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'events' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-calendar-event"></i> Events</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'events' ? 'open' : '' ?>">
        <a href="listEvenements.php" class="<?= in_array($_sbCurrentPage, ['listEvenements.php','addEvenement.php','updateEvenement.php','deleteEvenement.php']) ? 'active' : '' ?>">
          <i class="bi bi-calendar3"></i> Events
        </a>
        <a href="listParticipations.php" class="<?= in_array($_sbCurrentPage, ['listParticipations.php','addParticipation.php','updateParticipation.php','searchParticipations.php']) ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Participations
        </a>
        <a href="listCommentaires.php" class="<?= $_sbCurrentPage === 'listCommentaires.php' ? 'active' : '' ?>">
          <i class="bi bi-chat-dots"></i> Comments
        </a>
        <a href="listPromoCodes.php" class="<?= $_sbCurrentPage === 'listPromoCodes.php' ? 'active' : '' ?>">
          <i class="bi bi-ticket-perforated"></i> Discount Codes
        </a>
      </div>
    </div>

    <!-- MEAL PLANNING -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'meal' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-journal-text"></i> Meal Planning</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'meal' ? 'open' : '' ?>">
        <a href="meal_bo_index.php" class="<?= $_sbCurrentPage === 'meal_bo_index.php' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="meals_admin.php" class="<?= $_sbCurrentPage === 'meals_admin.php' ? 'active' : '' ?>">
          <i class="bi bi-egg-fried"></i> Meals
        </a>
        <a href="plans_admin.php" class="<?= $_sbCurrentPage === 'plans_admin.php' ? 'active' : '' ?>">
          <i class="bi bi-calendar-check"></i> My Plans
        </a>
      </div>
    </div>

    <!-- RECIPES -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'recipes' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-book"></i> Recipes</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'recipes' ? 'open' : '' ?>">
        <a href="index.php" class="<?= $_sbCurrentPage === 'index.php' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="repas.php" class="<?= in_array($_sbCurrentPage, ['repas.php','add_repas.php','edit_repas.php']) ? 'active' : '' ?>">
          <i class="bi bi-bowl-hot"></i> Gestion des Repas
        </a>
        <a href="search_repas.php" class="<?= $_sbCurrentPage === 'search_repas.php' ? 'active' : '' ?>">
          <i class="bi bi-search"></i> Repas par Recette
        </a>
        <a href="recette.php" class="<?= in_array($_sbCurrentPage, ['recette.php','add_recette.php','edit_recette.php','view_recette.php']) ? 'active' : '' ?>">
          <i class="bi bi-journal-richtext"></i> Gestion des Recettes
        </a>
        <a href="statistiques.php" class="<?= $_sbCurrentPage === 'statistiques.php' ? 'active' : '' ?>">
          <i class="bi bi-bar-chart-line"></i> Statistiques
        </a>
        <a href="users.php" class="<?= $_sbCurrentPage === 'users.php' ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Utilisateurs
        </a>
        <a href="aliments_durables.php" class="<?= $_sbCurrentPage === 'aliments_durables.php' ? 'active' : '' ?>">
          <i class="bi bi-leaf"></i> Aliments Durables
        </a>
        <a href="contenu_nutritionnel.php" class="<?= $_sbCurrentPage === 'contenu_nutritionnel.php' ? 'active' : '' ?>">
          <i class="bi bi-heart-pulse"></i> Contenu Nutritionnel
        </a>
      </div>
    </div>

  </div>

  <div class="usb-front">
    <a href="../index.php">
      <i class="bi bi-arrow-left-circle"></i> Front Office
    </a>
  </div>
</div>

<script>
function usbToggle(header) {
  var body = header.nextElementSibling;
  var isOpen = header.classList.contains('open');
  document.querySelectorAll('#unified-sidebar .usb-header').forEach(function(h) {
    h.classList.remove('open');
    if (h.nextElementSibling) h.nextElementSibling.classList.remove('open');
  });
  if (!isOpen && body) {
    header.classList.add('open');
    body.classList.add('open');
  }
}
</script>

<!-- MAIN CONTENT -->
<div id="main-content">

<!-- TOP BAR -->
<div id="topbar">
  <div class="page-title">
    <i class="bi bi-speedometer2 text-danger"></i>
    <?php
    $titles = [
      'afficherProduit.php'   => 'Products',
      'ajouterProduit.php'    => 'Add Product',
      'modifierProduit.php'   => 'Edit Product',
      'supprimerProduit.php'  => 'Delete Product',
      'afficherCategorie.php' => 'Categories',
      'ajouterCategorie.php'  => 'Add Category',
      'modifierCategorie.php' => 'Edit Category',
      'supprimerCategorie.php'=> 'Delete Category',
      'afficherReclamations.php' => 'Complaints',
      'afficherAvis.php'         => 'Reviews',
      'supprimerAvis.php'        => 'Delete Review',
    ];
    echo $titles[$_sbCurrentPage] ?? 'Dashboard';
    ?>
  </div>
  <div class="top-actions">
    <?php if (in_array($_sbCurrentPage, ['afficherProduit.php','ajouterProduit.php','modifierProduit.php'])): ?>
      <a href="ajouterProduit.php" class="btn-add"><i class="bi bi-plus-lg"></i> New Product</a>
    <?php endif; ?>
    <?php if (in_array($_sbCurrentPage, ['afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php'])): ?>
      <a href="ajouterCategorie.php" class="btn-add"><i class="bi bi-plus-lg"></i> New Category</a>
    <?php endif; ?>
  </div>
</div>

<div class="page-body">
