<?php
$pageTitle  = 'Smart Meal Planner';
$activePage = 'home';
require_once __DIR__ . '/partials/header.php';
?>

<!-- ======= Hero ======= -->
<section id="hero" class="hero section light-background">
    <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-5 justify-content-between">
            <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
                <h2><span>Bienvenue sur </span><span class="accent">Smart Meal Planner</span></h2>
                <p>Planifiez vos repas intelligemment. Découvrez des recettes équilibrées, gérez vos repas quotidiens et atteignez vos objectifs nutritionnels.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="repas.php" class="btn-get-started">Découvrir les Repas</a>
                    <a href="add_repas.php" class="btn-watch-video d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle-fill"></i><span>Ajouter un Repas</span>
                    </a>
                </div>
            </div>
            <div class="col-lg-5 order-1 order-lg-2">
                <img src="../assets/img/hero-img.png" class="img-fluid" alt="" data-aos="zoom-out" data-aos-delay="300">
            </div>
        </div>
    </div>
    <div class="icon-boxes position-relative" data-aos="fade-up" data-aos-delay="200">
        <div class="container position-relative">
            <div class="row gy-4 mt-5">
                <div class="col-xl-3 col-md-6">
                    <div class="icon-box">
                        <div class="icon"><i class="bi bi-egg-fried"></i></div>
                        <h4 class="title"><a href="repas.php">Repas Équilibrés</a></h4>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="icon-box">
                        <div class="icon"><i class="bi bi-journal-richtext"></i></div>
                        <h4 class="title"><a href="repas.php">Recettes Variées</a></h4>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="icon-box">
                        <div class="icon"><i class="bi bi-heart-pulse"></i></div>
                        <h4 class="title"><a href="#">Suivi Nutritionnel</a></h4>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="icon-box">
                        <div class="icon"><i class="bi bi-leaf"></i></div>
                        <h4 class="title"><a href="#">Aliments Durables</a></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======= Actions Rapides ======= -->
<section class="section" style="padding:50px 0 30px;">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Actions Rapides</h2>
            <p><span>Gérez</span> <span class="description-title">Vos Repas en un Clic</span></p>
        </div>
        <div class="row g-4 justify-content-center mt-2">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm text-center h-100"
                     style="border-radius:16px;transition:transform .25s;"
                     onmouseover="this.style.transform='translateY(-6px)'"
                     onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body p-4">
                        <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;background:#fde8e8;border-radius:16px;">
                            <i class="bi bi-plus-circle-fill" style="font-size:1.8rem;color:#ce1212;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Ajouter un Repas</h5>
                        <p class="text-muted small mb-3">Enregistrez un nouveau repas avec ses informations nutritionnelles.</p>
                        <a href="add_repas.php" class="btn btn-danger w-100">
                            <i class="bi bi-plus-lg me-1"></i>Ajouter
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm text-center h-100"
                     style="border-radius:16px;transition:transform .25s;"
                     onmouseover="this.style.transform='translateY(-6px)'"
                     onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body p-4">
                        <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;background:#e8f4fd;border-radius:16px;">
                            <i class="bi bi-bowl-hot" style="font-size:1.8rem;color:#0d6efd;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Voir les Repas</h5>
                        <p class="text-muted small mb-3">Consultez et gérez tous les repas enregistrés.</p>
                        <a href="repas.php" class="btn btn-primary w-100">
                            <i class="bi bi-list-ul me-1"></i>Consulter
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm text-center h-100"
                     style="border-radius:16px;transition:transform .25s;"
                     onmouseover="this.style.transform='translateY(-6px)'"
                     onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body p-4">
                        <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;background:#e8fdf0;border-radius:16px;">
                            <i class="bi bi-speedometer2" style="font-size:1.8rem;color:#198754;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Admin Dashboard</h5>
                        <p class="text-muted small mb-3">Gérez recettes, repas et statistiques.</p>
                        <a href="../back/index.php" class="btn btn-success w-100">
                            <i class="bi bi-gear me-1"></i>Administrer
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card border-0 shadow-sm text-center h-100"
                     style="border-radius:16px;transition:transform .25s;"
                     onmouseover="this.style.transform='translateY(-6px)'"
                     onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body p-4">
                        <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:64px;height:64px;background:#fff8e1;border-radius:16px;">
                            <i class="bi bi-journal-richtext" style="font-size:1.8rem;color:#fd7e14;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Gérer les Recettes</h5>
                        <p class="text-muted small mb-3">Créez et organisez vos recettes.</p>
                        <a href="../back/recette.php" class="btn btn-warning w-100">
                            <i class="bi bi-journal-plus me-1"></i>Recettes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======= About ======= -->
<section id="about" class="about section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">
            <div class="col-lg-6">
                <div class="row gy-4 align-items-center">
                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="200">
                        <img src="../assets/img/about.jpg" class="img-fluid rounded-4 w-100" alt="">
                    </div>
                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="300">
                        <img src="../assets/img/about-2.jpg" class="img-fluid rounded-4 w-100 mt-4 mt-md-0" alt="">
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="content ps-0 ps-lg-5">
                    <p class="fst-italic">Smart Meal Planner vous aide à organiser vos repas de façon saine et équilibrée.</p>
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i><span>Planification facile de vos repas hebdomadaires</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Suivi des calories et des macronutriments</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Recettes adaptées à vos préférences alimentaires</span></li>
                    </ul>
                    <p>Notre plateforme vous offre une interface intuitive pour gérer vos repas quotidiens et atteindre vos objectifs de santé.</p>
                    <div class="position-relative mt-4">
                        <img src="../assets/img/reservation.jpg" class="img-fluid rounded-4" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======= Stats ======= -->
<section id="stats-counter" class="stats-counter section dark-background">
    <img src="../assets/img/stats-bg.jpg" alt="" data-aos="fade-in">
    <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="150" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Recettes</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="500" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Repas Planifiés</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="200" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Utilisateurs</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="50" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Aliments Durables</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======= Menu ======= -->
<section id="menu" class="menu section">
    <div class="container section-title" data-aos="fade-up">
        <h2>Nos Repas</h2>
        <p><span>Découvrez</span> <span class="description-title">Nos Repas du Moment</span></p>
    </div>
    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-4 justify-content-center">
            <?php
            $menuItems = [
                ['img'=>'menu-item-1.png','nom'=>'Salade Fraîche','ing'=>'Laitue, tomates, concombre','cal'=>'320'],
                ['img'=>'menu-item-2.png','nom'=>'Bowl Protéiné','ing'=>'Quinoa, poulet, avocat','cal'=>'520'],
                ['img'=>'menu-item-3.png','nom'=>'Pasta Complète','ing'=>'Pâtes complètes, sauce tomate','cal'=>'480'],
            ];
            foreach ($menuItems as $item):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="menu-item">
                    <a href="../assets/img/menu/<?= $item['img'] ?>" class="glightbox">
                        <img src="../assets/img/menu/<?= $item['img'] ?>" class="menu-img img-fluid" alt="">
                    </a>
                    <h4><?= $item['nom'] ?></h4>
                    <p class="ingredients"><?= $item['ing'] ?></p>
                    <div class="price"><?= $item['cal'] ?> kcal</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="repas.php" class="btn-get-started">Voir tous les repas</a>
        </div>
    </div>
</section>

<!-- ======= Gallery ======= -->
<section id="gallery" class="gallery section light-background">
    <div class="container section-title" data-aos="fade-up">
        <h2>Galerie</h2>
        <p><span>Quelques</span> <span class="description-title">Photos de nos Repas</span></p>
    </div>
    <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-0">
            <?php for ($i = 1; $i <= 8; $i++): ?>
            <div class="col-lg-3 col-md-4">
                <div class="gallery-item">
                    <a href="../assets/img/gallery/gallery-<?= $i ?>.jpg" class="glightbox" data-gallery="images-gallery">
                        <img src="../assets/img/gallery/gallery-<?= $i ?>.jpg" alt="Gallery <?= $i ?>" class="img-fluid">
                    </a>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
