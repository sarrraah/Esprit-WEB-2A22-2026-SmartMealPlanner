<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Smart Meal Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(135deg, #1f7a4f, #38a169);
            color: #fff;
            border-radius: 16px;
            padding: 2.5rem;
        }
        .meal-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="home.php">Smart Meal Planner</a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-success" href="home.php">Home</a>
                <a class="btn btn-success" href="repas.php">Repas</a>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <section class="hero mb-4">
            <h1 class="display-6 fw-bold mb-2">Bienvenue sur Smart Meal Planner</h1>
            <p class="mb-0">Planifiez vos repas facilement avec une interface claire et inspirante.</p>
        </section>

        <section>
            <h2 class="h4 mb-3">Idées de repas</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <img class="meal-img" src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=80" alt="Salade healthy">
                </div>
                <div class="col-md-4">
                    <img class="meal-img" src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80" alt="Repas équilibré">
                </div>
                <div class="col-md-4">
                    <img class="meal-img" src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=900&q=80" alt="Bowl nutritif">
                </div>
            </div>
        </section>
    </main>
</body>
</html>
