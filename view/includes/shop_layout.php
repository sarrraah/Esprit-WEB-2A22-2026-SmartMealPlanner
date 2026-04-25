<?php
function shopLayoutStart($title)
{
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/ryhem/view/assets/css" rel="stylesheet">
</head>
<body>';
}

function shopAdminHeader($active = 'produits')
{
    $isProduits = $active === 'produits' ? 'active' : '';
    $isCategories = $active === 'categories' ? 'active' : '';
    echo '<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-store me-2"></i>Smart Meal Planner</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#shopAdminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="shopAdminNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link ' . $isProduits . '" href="/ryhem/view/back/afficherProduit.php">Produits</a></li>
                <li class="nav-item"><a class="nav-link ' . $isCategories . '" href="/ryhem/view/back/afficherCategorie.php">Catégories</a></li>
                <li class="nav-item"><a class="nav-link" href="/ryhem/view/front/interfaceclient.php">Front Shop</a></li>
            </ul>
        </div>
    </div>
</nav>';
}

function shopFrontHeader($active = 'shop')
{
    $isShop = $active === 'shop' ? 'active' : '';
    $isCategories = $active === 'categories' ? 'active' : '';
    echo '<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/ryhem/view/front/interfaceclient.php"><i class="fas fa-utensils me-2"></i>Smart Meal Planner</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#shopFrontNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="shopFrontNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link ' . $isShop . '" href="/ryhem/view/front/interfaceclient.php">Boutique</a></li>
                <li class="nav-item"><a class="nav-link ' . $isCategories . '" href="/ryhem/view/front/categories.php">Catégories</a></li>
            </ul>
        </div>
    </div>
</nav>';
}

function shopLayoutEnd()
{
    echo '<footer class="mt-5">
    <div class="container text-center">
        <p class="mb-1">&copy; 2026 Smart Meal Planner</p>
        <small>Module Shop - Interface commune</small>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
}
?>
