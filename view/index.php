<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
$nom      = $_SESSION['nom']    ?? '';
$prenom   = $_SESSION['prenom'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner — Portal</title>
  <link rel="icon" type="image/jpeg" href="assets/img/favicon.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #fff5f5 0%, #ffffff 50%, #fff8f0 100%);
      font-family: 'Segoe UI', system-ui, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }

    /* Logo + title */
    .portal-header {
      text-align: center;
      margin-bottom: 3rem;
    }
    .portal-header img {
      height: 64px;
      width: auto;
      margin-bottom: 1rem;
    }
    .portal-header h1 {
      font-size: 2rem;
      font-weight: 800;
      color: #1a1a2e;
      letter-spacing: -.5px;
    }
    .portal-header p {
      color: #6b7280;
      font-size: 1rem;
      margin-top: .4rem;
    }

    /* Cards */
    .portal-cards {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
      justify-content: center;
      width: 100%;
      max-width: 780px;
    }
    .portal-card {
      flex: 1 1 300px;
      max-width: 340px;
      background: #fff;
      border-radius: 20px;
      padding: 2.5rem 2rem;
      text-align: center;
      text-decoration: none;
      color: inherit;
      box-shadow: 0 4px 24px rgba(0,0,0,.07);
      border: 2px solid transparent;
      transition: transform .2s, box-shadow .2s, border-color .2s;
      position: relative;
      overflow: hidden;
    }
    .portal-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: var(--accent);
    }
    .portal-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 36px rgba(0,0,0,.12);
      border-color: var(--accent);
      color: inherit;
      text-decoration: none;
    }
    .portal-card .card-icon {
      width: 72px;
      height: 72px;
      border-radius: 18px;
      background: var(--accent-light);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
      font-size: 2rem;
      color: var(--accent);
    }
    .portal-card h2 {
      font-size: 1.3rem;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: .5rem;
    }
    .portal-card p {
      font-size: .9rem;
      color: #6b7280;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }
    .portal-card .card-btn {
      display: inline-block;
      padding: .6rem 1.6rem;
      border-radius: 999px;
      background: var(--accent);
      color: #fff;
      font-weight: 600;
      font-size: .9rem;
      text-decoration: none;
      transition: opacity .2s;
    }
    .portal-card .card-btn:hover { opacity: .88; color: #fff; }

    /* Front office — red */
    .card-front { --accent: #ce1212; --accent-light: #fff0f0; }
    /* Back office — dark */
    .card-back  { --accent: #1a1f2e; --accent-light: #f0f1f4; }

    /* User greeting */
    .portal-greeting {
      margin-bottom: 2rem;
      text-align: center;
    }
    .portal-greeting span {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: #fff;
      border: 1px solid #f0f0f0;
      border-radius: 999px;
      padding: .4rem 1rem;
      font-size: .9rem;
      color: #374151;
      box-shadow: 0 2px 8px rgba(0,0,0,.05);
    }
    .portal-greeting span i { color: #ce1212; }

    /* Footer note */
    .portal-footer {
      margin-top: 3rem;
      font-size: .8rem;
      color: #9ca3af;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="portal-header">
    <img src="assets/img/logo-smp.jpg" alt="SmartMealPlanner">
    <h1>Smart Meal Planner</h1>
    <p>Choose where you want to go</p>
  </div>

  <?php if ($loggedIn && ($nom || $prenom)): ?>
  <div class="portal-greeting">
    <span><i class="bi bi-person-circle"></i> Welcome back, <strong><?= htmlspecialchars(trim($prenom . ' ' . $nom)) ?></strong></span>
  </div>
  <?php endif; ?>

  <div class="portal-cards">

    <!-- Front Office -->
    <a href="front/home.php" class="portal-card card-front">
      <div class="card-icon">
        <i class="bi bi-house-heart-fill"></i>
      </div>
      <h2>Front Office</h2>
      <p>Browse meals, plan your week, explore events, shop products and manage your profile.</p>
      <span class="card-btn">Enter Front Office</span>
    </a>

    <!-- Back Office -->
    <a href="back/index.php" class="portal-card card-back">
      <div class="card-icon">
        <i class="bi bi-speedometer2"></i>
      </div>
      <h2>Back Office</h2>
      <p>Manage products, categories, events, meal plans, recipes and review complaints.</p>
      <span class="card-btn">Enter Back Office</span>
    </a>

  </div>

  <div class="portal-footer">
    &copy; <?= date('Y') ?> Smart Meal Planner &mdash; Esprit WEB 2A22
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
