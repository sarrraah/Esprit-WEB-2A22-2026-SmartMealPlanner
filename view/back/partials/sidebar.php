<?php
$cur = basename($_SERVER['PHP_SELF']);
function sideActive(string $f): string { global $cur; return $cur === $f ? 'active' : ''; }
?>
<nav class="admin-sidebar">
    <div class="sidebar-brand">Smart<span>Meal</span> <small style="font-size:.85rem;opacity:.6;">Admin</small></div>
    <hr class="sidebar-divider">
    <ul class="nav flex-column px-2 py-3 flex-grow-1 gap-1">
        <li><a class="nav-link <?= sideActive('index.php') ?>" href="index.php"><i class="bi bi-speedometer2"></i>Tableau de Bord</a></li>
        <li><a class="nav-link <?= sideActive('repas.php') ?>" href="repas.php"><i class="bi bi-bowl-hot"></i>Gestion des Repas</a></li>
        <li><a class="nav-link <?= sideActive('search_repas.php') ?>" href="search_repas.php"><i class="bi bi-search"></i>Repas par Recette</a></li>
        <li><a class="nav-link <?= sideActive('recette.php') ?>" href="recette.php"><i class="bi bi-journal-richtext"></i>Gestion des Recettes</a></li>
        <li><a class="nav-link <?= sideActive('statistiques.php') ?>" href="statistiques.php"><i class="bi bi-bar-chart-line"></i>Statistiques</a></li>
        <li><a class="nav-link <?= sideActive('utilisateurs.php') ?>" href="utilisateurs.php"><i class="bi bi-people"></i>Utilisateurs</a></li>
        <li><a class="nav-link <?= sideActive('aliments_durables.php') ?>" href="aliments_durables.php"><i class="bi bi-leaf"></i>Aliments Durables</a></li>
        <li><a class="nav-link <?= sideActive('contenu_nutritionnel.php') ?>" href="contenu_nutritionnel.php"><i class="bi bi-heart-pulse"></i>Contenu Nutritionnel</a></li>
    </ul>
    <hr class="sidebar-divider">
    <div class="px-3 py-3">
        <a href="../../index.php" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-arrow-left-circle me-1"></i>Front Office
        </a>
    </div>
</nav>
