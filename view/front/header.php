<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is banned and log them out
if (isset($_SESSION['user_id']) && isset($_SESSION['statut']) && strtolower(trim($_SESSION['statut'])) === 'banned') {
    session_unset();
    session_destroy();
    header("Location: signin.php?error=banned");
    exit();
}

$_hUserId  = $_SESSION['user_id'] ?? '';
$_hNom     = '';
$_hPrenom  = '';
$_hPicture = 'default.png';
$_hEmail   = '';
if ($_hUserId !== '') {
  try {
    if (!class_exists('config')) {
      require_once __DIR__ . '/../../config.php';
    }
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT nom, prenom, profile_picture, email, statut FROM user WHERE id = :id");
    $stmt->execute(['id' => $_hUserId]);
    $u = $stmt->fetch();
    if ($u) {
      $_hNom     = $u['nom'];
      $_hPrenom  = $u['prenom'];
      $_hPicture = $u['profile_picture'] ?? 'default.png';
      $_hEmail   = $u['email'] ?? '';
      
      // Update session status from database
      $_SESSION['statut'] = $u['statut'];
      
      // Check if user is banned (real-time check)
      if (strtolower(trim($u['statut'])) === 'banned') {
        session_unset();
        session_destroy();
        header("Location: signin.php?error=banned");
        exit();
      }
    }
  } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner</title>
  <link rel="icon" type="image/jpeg" href="../assets/img/favicon.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Amatic+SC:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
  <link href="../assets/css/main.css" rel="stylesheet">
  <style>
    /* ── LOCKED NAVBAR — zero layout shift ── */
    #smp-nav {
      background: #fff;
      border-bottom: 1px solid #f0f0f0;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    #smp-nav .smp-inner {
      display: flex;
      align-items: center;
      height: 64px;
      padding: 0 1.5rem;
      max-width: 1320px;
      margin: 0 auto;
      gap: 0;
    }
    /* Logo — fixed width so it never shifts */
    #smp-nav .smp-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      flex-shrink: 0;
      width: 220px;
    }
    #smp-nav .smp-logo img { height: 40px; width: auto; }
    #smp-nav .smp-logo span {
      font-size: 1.15rem;
      font-weight: 700;
      color: #2d2d2d;
      white-space: nowrap;
    }
    /* Nav links — centered, fixed layout */
    #smp-nav .smp-links {
      display: flex;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0;
      flex: 1;
      justify-content: center;
      gap: 0;
    }
    #smp-nav .smp-links li {
      /* Each li has a fixed padding so width never changes */
      padding: 0 14px;
      position: relative;
    }
    #smp-nav .smp-links li a {
      display: block;
      font-size: 15px;
      font-weight: 600;          /* ALWAYS bold — never changes */
      color: #7f7f90;
      text-decoration: none;
      white-space: nowrap;
      padding: 4px 0;
      position: relative;
      transition: color .2s;
      /* Reserve space for underline without affecting layout */
      padding-bottom: 6px;
    }
    /* Underline — absolutely positioned, never affects flow */
    #smp-nav .smp-links li a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: #ce1212;
      transition: width .25s ease;
    }
    #smp-nav .smp-links li a:hover { color: #ce1212; }
    #smp-nav .smp-links li a:hover::after { width: 100%; }
    #smp-nav .smp-links li a.active { color: #ce1212; }
    #smp-nav .smp-links li a.active::after { width: 100%; }

    /* Right side — fixed width so it never shifts the nav */
    #smp-nav .smp-right {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
      width: 220px;
      justify-content: flex-end;
    }
    /* Shop-only buttons: visibility:hidden keeps space reserved */
    #smp-nav .smp-shop-btn {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
      color: #ce1212;
      position: relative;
      padding: 4px 6px;
      line-height: 1;
    }
    #smp-nav .smp-shop-btn.hidden-btn {
      visibility: hidden;
      pointer-events: none;
    }
    #smp-nav .smp-auth a {
      color: #ce1212;
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
      white-space: nowrap;
    }
    #smp-nav .smp-auth a:hover { text-decoration: underline; }
    #smp-nav .smp-profile {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      line-height: 1.1;
    }
    #smp-nav .smp-profile img {
      width: 34px; height: 34px;
      border-radius: 50%;
      object-fit: cover;
    }
    #smp-nav .smp-profile small {
      font-size: 0.65rem;
      color: #ce1212;
      font-weight: 600;
      max-width: 80px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    /* Mobile hamburger */
    #smp-nav .smp-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #2d2d2d;
      cursor: pointer;
      margin-left: auto;
    }
    @media (max-width: 1199px) {
      #smp-nav .smp-links { display: none; flex-direction: column; position: absolute; top: 64px; left: 0; right: 0; background: #fff; border-bottom: 1px solid #f0f0f0; padding: 1rem 0; box-shadow: 0 4px 12px rgba(0,0,0,.1); z-index: 999; }
      #smp-nav .smp-links.open { display: flex; }
      #smp-nav .smp-links li { padding: 0; width: 100%; }
      #smp-nav .smp-links li a { padding: 10px 1.5rem; }
      #smp-nav .smp-toggle { display: block; }
      #smp-nav .smp-logo { width: auto; }
      #smp-nav .smp-right { width: auto; }
    }
  </style>
</head>
<body class="index-page">

<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<nav id="smp-nav">
  <div class="smp-inner">

    <!-- Logo -->
    <a href="home.php" class="smp-logo">
      <img src="../assets/img/logo-smp.jpg" alt="SmartMealPlanner">
      <span>SmartMealPlanner</span>
    </a>

    <!-- Nav links -->
    <ul class="smp-links" id="smpLinks">
      <li><a href="home.php"          <?= in_array($currentPage, ['index.php','home.php']) ? 'class="active"' : '' ?>>Home</a></li>
      <li><a href="interfaceevent.php" <?= $currentPage === 'interfaceevent.php' ? 'class="active"' : '' ?>>Events</a></li>
      <li><a href="produits.php"       <?= in_array($currentPage, ['produits.php','categories.php']) ? 'class="active"' : '' ?>>Shop</a></li>
      <li><a href="Meals.php"          <?= $currentPage === 'Meals.php' ? 'class="active"' : '' ?>>Meals</a></li>
      <li><a href="Plans.php"          <?= in_array($currentPage, ['Plans.php','day_plan.php','view_plan.php','create_plan.php']) ? 'class="active"' : '' ?>>My Plan</a></li>
      <li><a href="repas.php"          <?= in_array($currentPage, ['repas.php','detail_repas.php','edit_repas.php']) ? 'class="active"' : '' ?>>Recipes</a></li>
      <li><a href="#footer">Contact</a></li>
    </ul>

    <!-- Right side -->
    <div class="smp-right">
      <!-- Shop-only buttons (visibility:hidden on other pages = space always reserved) -->
      <button class="smp-shop-btn <?= !in_array($currentPage, ['produits.php','categories.php']) ? 'hidden-btn' : '' ?>"
              onclick="ouvrirPanier()" title="Cart">
        &#128722;
        <span id="panier-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#fff;color:#c0392b;border-radius:50%;width:16px;height:16px;font-size:10px;font-weight:700;line-height:16px;text-align:center;">0</span>
      </button>
      <button class="smp-shop-btn <?= !in_array($currentPage, ['produits.php','categories.php']) ? 'hidden-btn' : '' ?>"
              onclick="ouvrirWishlist()" title="Wishlist">
        <i class="bi bi-heart-fill"></i>
        <span id="wishlist-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#ce1212;color:white;border-radius:50%;width:16px;height:16px;font-size:10px;font-weight:700;line-height:16px;text-align:center;">0</span>
      </button>
      <button class="smp-shop-btn <?= !in_array($currentPage, ['produits.php','categories.php']) ? 'hidden-btn' : '' ?>"
              onclick="ouvrirReclamation()" title="Complaint">
        <i class="bi bi-megaphone-fill"></i>
      </button>

      <!-- Auth -->
      <div class="smp-auth">
        <?php if ($_hUserId !== ''): ?>
          <div style="display:flex;align-items:center;gap:10px;">
            <?php 
            // Get user role from session
            $userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
            ?>
            <?php if ($userRole === 'admin'): ?>
              <a href="../back/users.php" style="padding:5px 14px;border:1.5px solid #28a745;border-radius:20px;color:#28a745;font-weight:600;font-size:.78rem;text-decoration:none;transition:.2s;white-space:nowrap;"
                 onmouseover="this.style.background='#28a745'; this.style.color='white';"
                 onmouseout="this.style.background='transparent'; this.style.color='#28a745';">
                Admin Panel
              </a>
            <?php endif; ?>
            <a href="profile.php" class="smp-profile" style="display:flex;align-items:center;gap:7px;text-decoration:none;">
              <img src="../assets/img/profiles/<?= htmlspecialchars($_hPicture) ?>" alt="Profile"
                   style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #fde8e8;">
              <span style="font-size:.8rem;font-weight:600;color:#2d2d2d;max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= htmlspecialchars(trim($_hPrenom . ' ' . $_hNom) ?: 'User') ?>
              </span>
            </a>
            <a href="logout.php" style="padding:5px 14px;border:1.5px solid #ce1212;border-radius:20px;color:#ce1212;font-weight:600;font-size:.78rem;text-decoration:none;transition:.2s;white-space:nowrap;"
               onmouseover="this.style.background='#ce1212';this.style.color='#fff'"
               onmouseout="this.style.background='transparent';this.style.color='#ce1212'">
              Logout
            </a>
          </div>
        <?php else: ?>
          <div style="display:flex;align-items:center;gap:8px;">
            <a href="signin.php" style="padding:6px 16px;border:2px solid #ce1212;border-radius:20px;color:#ce1212;font-weight:600;font-size:.85rem;text-decoration:none;transition:.2s;" onmouseover="this.style.background='#ce1212';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='#ce1212'">Sign In</a>
            <a href="signup.php" style="padding:6px 16px;background:#ce1212;border:2px solid #ce1212;border-radius:20px;color:#fff;font-weight:600;font-size:.85rem;text-decoration:none;transition:.2s;" onmouseover="this.style.background='#b00e0e';this.style.borderColor='#b00e0e'" onmouseout="this.style.background='#ce1212';this.style.borderColor='#ce1212'">Sign Up</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mobile toggle -->
    <button class="smp-toggle" onclick="document.getElementById('smpLinks').classList.toggle('open')">
      <i class="bi bi-list"></i>
    </button>

  </div>
</nav>

<!-- COMPLAINT MODAL -->
<div class="modal fade" id="modalReclamation" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
      <div style="background:linear-gradient(135deg,#ce1212,#ff4444);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
        <div>
          <div style="font-size:1.1rem;font-weight:700;color:white;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-megaphone-fill"></i> Submit a Complaint
          </div>
          <div style="font-size:0.75rem;color:rgba(255,255,255,0.8);margin-top:2px;">We will review your message as soon as possible</div>
        </div>
        <button type="button" data-bs-dismiss="modal"
          style="background:rgba(255,255,255,0.2);border:none;border-radius:50%;width:30px;height:30px;color:white;font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">
          &#x2715;
        </button>
      </div>
      <div style="padding:24px;">
        <div id="reclamation-success" style="display:none;background:#e8f5e9;border-radius:10px;padding:14px 16px;margin-bottom:16px;text-align:center;">
          <i class="bi bi-check-circle-fill" style="color:#2e7d32;font-size:1.5rem;display:block;margin-bottom:6px;"></i>
          <div style="font-weight:700;color:#2e7d32;font-size:0.9rem;">Complaint submitted!</div>
          <div style="font-size:0.8rem;color:#555;margin-top:4px;">Thank you. We will get back to you shortly.</div>
        </div>
        <form id="reclamationForm" onsubmit="soumettreReclamation(event)">
          <div style="margin-bottom:14px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Full Name *</label>
            <input type="text" id="rec-nom" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;" placeholder="Your full name">
          </div>
          <div style="margin-bottom:14px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Email *</label>
            <input type="email" id="rec-email" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;" placeholder="your@email.com">
          </div>
          <div style="margin-bottom:14px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Subject *</label>
            <select id="rec-sujet" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;background:white;cursor:pointer;">
              <option value="">-- Select a subject --</option>
              <option value="Delivery issue">Delivery issue</option>
              <option value="Wrong product received">Wrong product received</option>
              <option value="Product quality">Product quality</option>
              <option value="Payment problem">Payment problem</option>
              <option value="Missing item">Missing item</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div style="margin-bottom:20px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Message *</label>
            <textarea id="rec-message" required rows="4" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;resize:none;" placeholder="Describe your issue in detail..."></textarea>
          </div>
          <button type="submit" style="width:100%;background:#ce1212;color:white;border:none;border-radius:25px;padding:12px;font-size:0.9rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-send me-2"></i>Send Complaint
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function ouvrirReclamation() {
  document.getElementById('reclamation-success').style.display = 'none';
  document.getElementById('reclamationForm').style.display = 'block';
  document.getElementById('reclamationForm').reset();
  var modal = bootstrap.Modal.getInstance(document.getElementById('modalReclamation'))
           || new bootstrap.Modal(document.getElementById('modalReclamation'));
  modal.show();
}
function soumettreReclamation(e) {
  e.preventDefault();
  var nom = document.getElementById('rec-nom').value.trim();
  var email = document.getElementById('rec-email').value.trim();
  var sujet = document.getElementById('rec-sujet').value;
  var message = document.getElementById('rec-message').value.trim();
  if (!nom || !email || !sujet || !message) return;
  var reclamations = JSON.parse(localStorage.getItem('reclamations') || '[]');
  reclamations.unshift({ id: Date.now(), nom, email, sujet, message, statut: 'pending', date: new Date().toLocaleString() });
  localStorage.setItem('reclamations', JSON.stringify(reclamations));
  document.getElementById('reclamationForm').style.display = 'none';
  document.getElementById('reclamation-success').style.display = 'block';
  setTimeout(function() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('modalReclamation'));
    if (modal) modal.hide();
  }, 3000);
}
</script>

<main class="main">