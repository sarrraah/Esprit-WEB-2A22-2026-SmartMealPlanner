<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$produitController  = new ProduitController();
$categorieController = new CategorieController();
$produits   = $produitController->listProduits();
$categories = $categorieController->getAllCategories();
$total      = count($produits);

// Base URL for AJAX calls
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
         . '://' . $_SERVER['HTTP_HOST']
         . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

include("header.php");
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Amatic+SC:wght@400;700&display=swap');

/* ── HERO ── */
.hero-produits {
  background: #f5f5f0;
  padding: 60px 0 50px;
}
.hero-produits h1 {
  font-family: "Amatic SC", sans-serif;
  font-size: 3rem;
  font-weight: 700;
  color: #2d2d2d;
  line-height: 1.2;
}
.hero-produits h1 span { color: #ce1212; }
.hero-produits p {
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  color: #666;
  margin: 14px 0 24px;
}
.hero-produits .btn-browse {
  background: #ce1212;
  color: white;
  border: none;
  padding: 11px 28px;
  border-radius: 25px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  display: inline-block;
  transition: 0.3s;
}
.hero-produits .btn-browse:hover { background: #b00e0e; color: white; }
.hero-produits .hero-img {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.hero-produits .hero-img img {
  width: 100%;
  height: 320px;
  object-fit: cover;
  display: block;
}

/* ── FILTERS ── */
.filters-section {
  background: white;
  padding: 20px 24px;
  border-radius: 15px;
  margin-bottom: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.filter-btn {
  margin: 4px;
  padding: 7px 18px;
  border-radius: 25px;
  border: 1px solid #ddd;
  background: white;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  transition: 0.3s;
  cursor: pointer;
  text-decoration: none;
  color: #333;
  display: inline-block;
}
.filter-btn.active, .filter-btn:hover {
  background: #ce1212;
  color: white;
  border-color: #ce1212;
}
.sort-select {
  padding: 8px 16px;
  border-radius: 25px;
  border: 1px solid #ddd;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  outline: none;
  cursor: pointer;
}
.search-input {
  border-radius: 25px !important;
  border: 1px solid #ddd !important;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  padding: 8px 16px !important;
}

/* ── PRODUCT CARD ── */
.product-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
  margin-bottom: 30px;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(206,18,18,0.15);
}
.product-img { width: 100%; height: 210px; object-fit: cover; }
.product-img-placeholder {
  width: 100%; height: 210px; background: #f5f5f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; color: #ccc;
}
.product-badge { position: absolute; top: 12px; left: 12px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-dispo   { background: #28a745; color: white; }
.badge-rupture { background: #dc3545; color: white; }
.badge-epuise  { background: #6c757d; color: white; }
.product-info  { padding: 16px; flex: 1; display: flex; flex-direction: column; }
.product-title { font-size: 0.95rem; font-weight: 600; color: #333; margin-bottom: 6px; line-height: 1.4; }
.product-desc  { font-size: 0.8rem; color: #888; margin-bottom: 8px; flex: 1; }
.product-cat   { font-size: 0.75rem; color: #999; margin-bottom: 6px; display: flex; align-items: center; gap: 5px; }
.product-cat i { color: #ce1212; }
.product-price { font-size: 1.3rem; font-weight: 700; color: #ce1212; margin: 8px 0; }
.btn-cart {
  background: #ce1212; color: white; border: none; padding: 9px 20px;
  border-radius: 25px; font-weight: 500; font-family: 'Inter', sans-serif;
  transition: 0.3s; width: 100%; font-size: 13px; cursor: pointer;
}
.btn-cart:hover { background: #b00e0e; transform: scale(1.02); color: white; }
.btn-cart:disabled { background: #ccc; cursor: not-allowed; transform: none; }

/* ── WISHLIST HEART ── */
.btn-wishlist {
  position: absolute; bottom: 10px; right: 10px;
  width: 32px; height: 32px; border-radius: 50%;
  background: rgba(255,255,255,0.92); border: none;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; cursor: pointer; transition: 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;
  color: #ccc;
}
.btn-wishlist:hover { transform: scale(1.15); }
.btn-wishlist.active { color: #ce1212; }

/* ── SECTION TITLE ── */
.section-title h2 {
  font-size: 14px; font-weight: 500; padding: 0; line-height: 1px;
  margin: 0 0 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #999; position: relative;
}
.section-title h2::after {
  content: ""; width: 120px; height: 1px; display: inline-block;
  background: #ce1212; margin: 4px 10px;
}
.section-title p {
  color: #333; margin: 0 0 24px; font-size: 36px;
  font-weight: 700; font-family: "Amatic SC", sans-serif;
}
.section-title p span { color: #ce1212; }
</style>

<!-- HERO PRODUITS -->
<section class="hero-produits">
  <div class="container">
    <div class="row gy-4 align-items-center justify-content-between">
      <div class="col-lg-5 order-2 order-lg-1">
        <div style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#fff0f0,#ffe8e8);border:1px solid rgba(206,18,18,0.2);border-radius:20px;padding:5px 12px;margin-bottom:12px;">
          <i class="bi bi-stars" style="color:#ce1212;font-size:0.8rem;"></i>
          <span style="font-size:0.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ce1212;">Recommended by our AI</span>
        </div>
        <h1>Discover our <span>fresh</span> &amp; healthy products</h1>
        <p>Fresh ingredients, smart packs and meal prep — everything you need to cook healthy every day.</p>
        <a href="#produitsGrid" class="btn-browse">Browse products</a>
      </div>
      <div class="col-lg-7 order-1 order-lg-2">
        <!-- AI Recommendations in hero -->
        <div id="hero-reco-loading" style="display:flex;align-items:center;gap:10px;color:#bbb;font-size:0.85rem;padding:20px 0;">
          <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
          Loading AI picks...
        </div>
        <div id="hero-reco-list" style="display:none;"></div>
      </div>
    </div>
  </div>
</section>

<section id="produits" class="section light-background py-3">
  <div class="container section-title" style="padding-bottom:10px;">
    <h2>Shop</h2>
    <p><span>Our</span> <span class="description-title">Products</span></p>
  </div>

  <div class="container">

    <!-- FREE DELIVERY BANNER -->
    <div style="background:linear-gradient(135deg,#fff8e1 0%,#fff3cd 100%);border:1.5px solid #ffc107;border-radius:14px;padding:14px 22px;margin-bottom:20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
      <div style="font-size:1.6rem;line-height:1;">🚚</div>
      <div style="flex:1;min-width:200px;">
        <div style="font-size:0.88rem;font-weight:700;color:#2d2d2d;">
          Free Delivery on orders over <span style="color:#ce1212;">100 DT</span>!
        </div>
        <div style="font-size:0.78rem;color:#666;margin-top:2px;">
          Add more items to your cart and enjoy free home delivery — no extra fees.
        </div>
      </div>
      <div style="background:#ce1212;color:white;border-radius:20px;padding:6px 16px;font-size:0.78rem;font-weight:700;white-space:nowrap;">
        🎁 Save 8 DT
      </div>
    </div>

    <!-- AI RECOMMENDATIONS WIDGET -->
    <div id="ai-reco-section" style="background:white;border-radius:16px;padding:20px 24px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <div style="background:linear-gradient(135deg,#ce1212,#ff4444);border-radius:8px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-stars" style="color:white;font-size:0.85rem;"></i>
            </div>
            <span style="font-size:1rem;font-weight:700;color:#2d2d2d;">AI Selection — Our best meals</span>
          </div>
          <div style="font-size:0.8rem;color:#888;margin-left:36px;">
            Our AI analyzes customer ratings, popularity and nutritional diversity to suggest the 3 best picks right now.
          </div>
        </div>
        <button onclick="loadRecommendations()" id="reco-refresh-btn"
          style="background:none;border:1px solid #e0e0e0;border-radius:20px;padding:5px 14px;font-size:0.75rem;color:#999;cursor:pointer;transition:0.2s;display:flex;align-items:center;gap:5px;flex-shrink:0;">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
      </div>

      <!-- Loading state -->
      <div id="reco-loading" style="display:flex;align-items:center;gap:10px;padding:16px 0;color:#bbb;font-size:0.85rem;">
        <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
        AI is analyzing your catalog...
      </div>

      <!-- Recommendations list -->
      <div id="reco-list" style="display:none;"></div>

      <!-- Error state -->
      <div id="reco-error" style="display:none;font-size:0.82rem;color:#bbb;padding:8px 0;">
        <i class="bi bi-exclamation-circle me-1"></i>Could not load recommendations.
        <button onclick="loadRecommendations()" style="background:none;border:none;color:#ce1212;cursor:pointer;font-size:0.82rem;text-decoration:underline;">Retry</button>
      </div>
    </div>

    <style>
    /* ── AI RECOMMENDATION CARDS ── */
    @media (max-width: 576px) {
      #reco-list > div { grid-template-columns: 1fr !important; }
    }
    @media (min-width: 577px) and (max-width: 768px) {
      #reco-list > div { grid-template-columns: repeat(2,1fr) !important; }
    }
    </style>
    <div id="achievements-section" style="background:white;border-radius:16px;padding:20px 24px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.05);overflow:hidden;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <div>
          <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;">
            <i class="bi bi-trophy-fill" style="color:#ce1212;"></i> Goals & Rewards
          </div>
          <div style="font-size:0.82rem;color:#555;margin-top:2px;">
            Buy more meals and unlock exclusive discounts 🎯
          </div>
        </div>
        <div id="achievement-active-badge" style="display:none;color:white;border-radius:20px;padding:6px 14px;font-size:0.78rem;font-weight:700;">
          <i class="bi bi-percent me-1"></i><span id="achievement-active-text">Discount active!</span>
        </div>
      </div>

      <!-- Visible tiers (1-3) -->
      <div class="row g-3" id="achievements-grid">

        <!-- Palier 1 : 5 repas → 5% — Rouge chaud -->
        <div class="col-4">
          <div class="achievement-card" id="ach-1">
            <div class="ach-hexagon" style="--ach-color1:#ce1212;--ach-color2:#ff6b35;--ach-glow:rgba(206,18,18,0.5);">
              <div class="ach-hex-inner">
                <div class="ach-number">5%</div>
                <div class="ach-icon"><i class="bi bi-basket2-fill"></i></div>
              </div>
            </div>
            <div class="ach-progress-wrap">
              <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-1" style="width:0%;background:linear-gradient(90deg,#ce1212,#ff6b35);"></div></div>
            </div>
            <div class="ach-label">🥄 First Step</div>
            <div class="ach-sublabel"><span id="ach-count-1">0</span>/5 meals</div>
          </div>
        </div>

        <!-- Palier 2 : 15 repas → 15% — Orange feu -->
        <div class="col-4">
          <div class="achievement-card" id="ach-2">
            <div class="ach-hexagon" style="--ach-color1:#ff6b00;--ach-color2:#ffb300;--ach-glow:rgba(255,107,0,0.5);">
              <div class="ach-hex-inner">
                <div class="ach-number">15%</div>
                <div class="ach-icon"><i class="bi bi-fire"></i></div>
              </div>
            </div>
            <div class="ach-progress-wrap">
              <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-2" style="width:0%;background:linear-gradient(90deg,#ff6b00,#ffb300);"></div></div>
            </div>
            <div class="ach-label">🌱 Healthy Starter</div>
            <div class="ach-sublabel"><span id="ach-count-2">0</span>/15 meals</div>
          </div>
        </div>

        <!-- Palier 3 : 20 repas → 20% — Or -->
        <div class="col-4">
          <div class="achievement-card" id="ach-3">
            <div class="ach-hexagon" style="--ach-color1:#f9a825;--ach-color2:#ffe082;--ach-glow:rgba(249,168,37,0.5);">
              <div class="ach-hex-inner">
                <div class="ach-number">20%</div>
                <div class="ach-icon"><i class="bi bi-award-fill"></i></div>
              </div>
            </div>
            <div class="ach-progress-wrap">
              <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-3" style="width:0%;background:linear-gradient(90deg,#f9a825,#ffe082);"></div></div>
            </div>
            <div class="ach-label">🔥 Discipline Builder</div>
            <div class="ach-sublabel"><span id="ach-count-3">0</span>/20 meals</div>
          </div>
        </div>

      </div>

      <!-- Hidden tiers (4-7) — revealed by "View More" -->
      <div id="ach-more-grid" style="display:none;overflow:hidden;transition:max-height 0.5s ease;">
        <div class="row g-3" style="margin-top:0;">

          <!-- Palier 4 : 30 repas → 30% — Vert nature -->
          <div class="col-4 col-md-3">
            <div class="achievement-card" id="ach-4">
              <div class="ach-hexagon" style="--ach-color1:#2e7d32;--ach-color2:#66bb6a;--ach-glow:rgba(46,125,50,0.5);">
                <div class="ach-hex-inner">
                  <div class="ach-number">30%</div>
                  <div class="ach-icon"><i class="bi bi-leaf-fill"></i></div>
                </div>
              </div>
              <div class="ach-progress-wrap">
                <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-4" style="width:0%;background:linear-gradient(90deg,#2e7d32,#66bb6a);"></div></div>
              </div>
              <div class="ach-label">🥗 Clean Eater</div>
              <div class="ach-sublabel"><span id="ach-count-4">0</span>/30 meals</div>
            </div>
          </div>

          <!-- Palier 5 : 50 repas → 50% — Bleu électrique -->
          <div class="col-4 col-md-3">
            <div class="achievement-card" id="ach-5">
              <div class="ach-hexagon" style="--ach-color1:#1565c0;--ach-color2:#42a5f5;--ach-glow:rgba(21,101,192,0.5);">
                <div class="ach-hex-inner">
                  <div class="ach-number">50%</div>
                  <div class="ach-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                </div>
              </div>
              <div class="ach-progress-wrap">
                <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-5" style="width:0%;background:linear-gradient(90deg,#1565c0,#42a5f5);"></div></div>
              </div>
              <div class="ach-label">💪 Habit Keeper</div>
              <div class="ach-sublabel"><span id="ach-count-5">0</span>/50 meals</div>
            </div>
          </div>

          <!-- Palier 6 : 75 repas → 70% — Violet galaxie -->
          <div class="col-4 col-md-3">
            <div class="achievement-card" id="ach-6">
              <div class="ach-hexagon" style="--ach-color1:#6a1b9a;--ach-color2:#ce93d8;--ach-glow:rgba(106,27,154,0.5);">
                <div class="ach-hex-inner">
                  <div class="ach-number">70%</div>
                  <div class="ach-icon"><i class="bi bi-gem"></i></div>
                </div>
              </div>
              <div class="ach-progress-wrap">
                <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-6" style="width:0%;background:linear-gradient(90deg,#6a1b9a,#ce93d8);"></div></div>
              </div>
              <div class="ach-label">⚡ Consistency Pro</div>
              <div class="ach-sublabel"><span id="ach-count-6">0</span>/75 meals</div>
            </div>
          </div>

          <!-- Palier 7 : 100 repas → 80% — Noir & or légendaire -->
          <div class="col-4 col-md-3">
            <div class="achievement-card" id="ach-7">
              <div class="ach-hexagon" style="--ach-color1:#212121;--ach-color2:#ffd700;--ach-glow:rgba(255,215,0,0.6);">
                <div class="ach-hex-inner">
                  <div class="ach-number" style="font-size:1.3rem;">80%</div>
                  <div class="ach-icon"><i class="bi bi-crown-fill"></i></div>
                </div>
              </div>
              <div class="ach-progress-wrap">
                <div class="ach-progress-bar"><div class="ach-progress-fill" id="ach-fill-7" style="width:0%;background:linear-gradient(90deg,#212121,#ffd700);"></div></div>
              </div>
              <div class="ach-label">🏆 Healthy Champion</div>
              <div class="ach-sublabel"><span id="ach-count-7">0</span>/100 meals</div>
            </div>
          </div>

        </div>
      </div>

      <!-- View More button -->
      <div style="text-align:center;margin-top:14px;">
        <button id="ach-view-more-btn" onclick="toggleMoreAchievements()"
          style="background:linear-gradient(135deg,#ce1212,#ff4444);border:none;border-radius:25px;padding:9px 24px;font-size:0.8rem;font-weight:700;color:white;cursor:pointer;transition:all 0.25s;display:inline-flex;align-items:center;gap:7px;box-shadow:0 3px 12px rgba(206,18,18,0.3);">
          <i class="bi bi-chevron-down" id="ach-chevron"></i>
          <span id="ach-view-more-text">View More Achievements</span>
        </button>
      </div>

      <!-- Next milestone hint -->
      <div id="ach-next-hint" style="margin-top:12px;padding:10px 14px;background:#f9f9f9;border-radius:10px;font-size:0.8rem;color:#666;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-info-circle-fill" style="color:#ce1212;flex-shrink:0;"></i>
        <span id="ach-next-hint-text">Buy 5 meals to unlock your first 5% discount!</span>
      </div>
    </div>

    <style>
    /* ── ACHIEVEMENT CARDS ── */
    .achievement-card {
      text-align: center;
      padding: 8px 4px;
      cursor: default;
      transition: transform 0.25s;
    }
    .achievement-card:hover { transform: translateY(-4px); }

    /* Hexagon shape using CSS custom properties */
    .ach-hexagon {
      width: 90px;
      height: 90px;
      margin: 0 auto 10px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .ach-hexagon::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #d0d0d0, #b0b0b0);
      clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);
      transition: background 0.4s, filter 0.4s;
    }
    /* Unlocked: use tier color */
    .achievement-card.unlocked .ach-hexagon::before {
      background: linear-gradient(135deg, var(--ach-color1), var(--ach-color2));
    }
    /* Active discount: pulse with tier glow */
    .achievement-card.active-discount .ach-hexagon::before {
      background: linear-gradient(135deg, var(--ach-color1), var(--ach-color2));
      animation: achPulse 1.8s ease-in-out infinite;
    }
    .achievement-card.active-discount {
      filter: drop-shadow(0 0 10px var(--ach-glow, rgba(206,18,18,0.5)));
    }
    @keyframes achPulse {
      0%, 100% { filter: brightness(1); }
      50%       { filter: brightness(1.25); }
    }

    /* Locked overlay */
    .achievement-card:not(.unlocked) .ach-hexagon::after {
      content: '🔒';
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      z-index: 2;
      opacity: 0;
      transition: opacity 0.2s;
    }
    .achievement-card:not(.unlocked):hover .ach-hexagon::after { opacity: 1; }

    .ach-hex-inner {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 3px;
    }
    .ach-number {
      font-family: 'Amatic SC', cursive;
      font-size: 1.6rem;
      font-weight: 700;
      color: white;
      line-height: 1;
      text-shadow: 0 1px 4px rgba(0,0,0,0.35);
    }
    .achievement-card:not(.unlocked) .ach-number { color: rgba(255,255,255,0.5); }
    .ach-icon {
      font-size: 0.85rem;
      color: rgba(255,255,255,0.9);
      background: rgba(0,0,0,0.18);
      border-radius: 50%;
      width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .achievement-card:not(.unlocked) .ach-icon { opacity: 0.5; }

    /* Progress bar */
    .ach-progress-wrap { padding: 0 6px; margin-bottom: 6px; }
    .ach-progress-bar {
      height: 5px;
      background: #f0f0f0;
      border-radius: 10px;
      overflow: hidden;
    }
    .ach-progress-fill {
      height: 100%;
      border-radius: 10px;
      transition: width 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .achievement-card.unlocked .ach-progress-fill { width: 100% !important; }

    .ach-label {
      font-size: 0.76rem;
      font-weight: 700;
      color: #2d2d2d;
      margin-bottom: 2px;
    }
    .ach-sublabel { font-size: 0.68rem; color: #aaa; }
    .achievement-card.unlocked .ach-sublabel { color: var(--ach-color1, #ce1212); font-weight: 600; }

    /* View more button hover */
    #ach-view-more-btn:hover {
      border-color: #ce1212;
      color: #ce1212;
      background: #fff5f5;
    }

    @media (max-width: 576px) {
      .ach-hexagon { width: 68px; height: 68px; }
      .ach-number  { font-size: 1.15rem; }
      .ach-label   { font-size: 0.68rem; }
    }
    </style>

    <!-- FILTERS -->
    <div class="filters-section">
      <div class="row align-items-center gy-2">
        <div class="col-md-5">
          <strong style="font-size:13px;font-family:'Inter',sans-serif;">Filter by category:</strong><br class="d-md-none">
          <a class="filter-btn active" href="produits.php" id="btn-all">All (<?= $total ?>)</a>
          <?php foreach ($categories as $cat): ?>
            <a class="filter-btn" href="categories.php?id=<?= (int)$cat['id_categorie'] ?>">
              <?= htmlspecialchars($cat['nom']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control search-input"
            placeholder="Search a product..." oninput="filtrerProduits()">
        </div>
        <div class="col-md-3">
          <div style="display:flex;gap:6px;align-items:center;">
            <select id="sortSel" class="sort-select" style="flex:1;" onchange="filtrerProduits();toggleResetBtn()">
              <option value="">— Sort —</option>
              <option value="nom-asc">Name A → Z</option>
              <option value="nom-desc">Name Z → A</option>
              <option value="prix-asc">Price low → high ↑</option>
              <option value="prix-desc">Price high → low ↓</option>
            </select>
            <button id="reset-sort-btn" onclick="resetSort()" title="Clear sort"
              style="display:none;background:#ce1212;color:white;border:none;border-radius:50%;width:32px;height:32px;flex-shrink:0;cursor:pointer;font-size:0.85rem;transition:0.2s;display:none;align-items:center;justify-content:center;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TOAST -->
    <div id="panier-toast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;"
         class="toast align-items-center text-bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-cart-check me-2"></i><span id="panier-toast-msg">Product added!</span></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                onclick="document.getElementById('panier-toast').style.display='none'"></button>
      </div>
    </div>

    <!-- GRID -->
    <div class="row" id="produitsGrid">
      <?php foreach ($produits as $i => $produit): ?>
        <?php
        $img = $produit['image'] ?? '';
        if (empty($img))                        $imgSrc = '';
        elseif (str_starts_with($img, 'http'))  $imgSrc = $img;
        elseif (str_starts_with($img, 'meals/')) $imgSrc = '../../view/assets/img/' . $img;
        else                                     $imgSrc = UPLOAD_URL . $img;

        $statut = determinerStatut($produit['quantiteStock'], $produit['dateExpiration']);
        $badgeCls = match($statut) {
          'Available'    => 'badge-dispo',
          'Out of Stock' => 'badge-rupture',
          default        => 'badge-epuise',
        };
        ?>
        <div class="col-lg-4 col-md-6 product-item"
             data-index="<?= $i ?>"
             data-nom="<?= htmlspecialchars(strtolower($produit['nom']), ENT_QUOTES) ?>"
             data-prix="<?= (float)$produit['prix'] ?>"
             data-stock="<?= (int)$produit['quantiteStock'] ?>">
          <div class="product-card" onclick="openProductModal(<?= (int)$produit['id'] ?>)" style="cursor:pointer;">
            <div class="position-relative">
              <?php if ($imgSrc): ?>
                <img src="<?= htmlspecialchars($imgSrc) ?>" class="product-img"
                     alt="<?= htmlspecialchars($produit['nom']) ?>">
              <?php else: ?>
                <div class="product-img-placeholder"><i class="bi bi-image"></i></div>
              <?php endif; ?>
              <span class="product-badge <?= $badgeCls ?>"><?= $statut ?></span>
              <span style="position:absolute;top:12px;right:12px;padding:4px 12px;border-radius:20px;
                           font-size:11px;font-weight:700;background:rgba(206,18,18,0.88);color:white;">
                <?= number_format((float)$produit['prix'], 2, ',', ' ') ?> DT
              </span>
              <!-- Wishlist heart button -->
              <button class="btn-wishlist" onclick="event.stopPropagation();toggleWishlist(this)"
                data-id="<?= (int)$produit['id'] ?>"
                data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
                data-prix="<?= (float)$produit['prix'] ?>"
                data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>"
                title="Add to Wishlist">
                <i class="bi bi-heart<?= '' ?>-fill" id="heart-<?= (int)$produit['id'] ?>"></i>
              </button>
              <!-- Like counter badge -->
              <span class="like-count-badge" id="like-badge-<?= (int)$produit['id'] ?>"
                style="display:none;position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,0.6);
                       color:white;border-radius:20px;padding:3px 9px;font-size:11px;font-weight:700;">
                ❤️ <span class="like-num">0</span>
              </span>
            </div>
            <div class="product-info">
              <?php if ((int)$produit['quantiteStock'] > 0 && (int)$produit['quantiteStock'] <= 5): ?>
              <!-- Low stock indicator — visible under image in product info -->
              <div style="display:flex;align-items:center;gap:6px;background:#fff8e1;border:1px solid #ffc107;
                          border-radius:8px;padding:6px 12px;margin-bottom:8px;">
                <i class="bi bi-exclamation-triangle-fill" style="color:#f57c00;font-size:0.85rem;flex-shrink:0;"></i>
                <span style="font-size:0.8rem;font-weight:700;color:#e65100;">
                  Only <?= (int)$produit['quantiteStock'] ?> left in stock!
                </span>
              </div>
              <?php endif; ?>
              <div class="product-title"><?= htmlspecialchars($produit['nom']) ?></div>
              <?php
              $isMealPrep = (int)($produit['categorie'] ?? $produit['id_categorie'] ?? 0) === 3
                         || strtolower($produit['categorie_nom'] ?? '') === 'meal prep packs';
              if ($isMealPrep):
                // Extract ingredients from description — rewrite as ingredient list
                $desc = $produit['description'] ?? '';
                // Remove "— repas complet en X min" or similar suffixes
                $desc = preg_replace('/\s*—\s*(repas|breakfast|lunch|dinner|préparez|dégustez).*/iu', '', $desc);
              ?>
              <div class="product-desc" style="font-size:0.78rem;color:#888;">
                <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:#ce1212;">
                  <i class="bi bi-basket2-fill me-1"></i>Ingredients included:
                </span><br>
                <?= htmlspecialchars($desc) ?>
              </div>
              <?php else: ?>
              <div class="product-desc"><?= htmlspecialchars($produit['description'] ?? '') ?></div>
              <?php endif; ?>
              <div class="product-cat">
                <i class="bi bi-tag-fill"></i>
                <?= htmlspecialchars($produit['categorie_nom'] ?? 'No category') ?>
              </div>
              <div class="product-price"><?= number_format((float)$produit['prix'], 2, ',', ' ') ?> DT</div>
              <?php if ($statut === 'Available'): ?>
                <button class="btn-cart btn-ajouter-panier"
                        onclick="event.stopPropagation();"
                        data-id="<?= (int)$produit['id'] ?>"
                        data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
                        data-prix="<?= (float)$produit['prix'] ?>"
                        data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>">
                  <?php if ($isMealPrep): ?>
                    <i class="bi bi-basket2 me-2"></i>Get Ingredients
                  <?php else: ?>
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                  <?php endif; ?>
                </button>
              <?php else: ?>
                <button class="btn-cart" disabled onclick="event.stopPropagation();">
                  <i class="bi bi-x-circle me-2"></i>Out of Stock
                </button>
              <?php endif; ?>
            </div>
          </div>
          <!-- Hidden data for modal -->
          <div class="product-data" style="display:none;"
               data-id="<?= (int)$produit['id'] ?>"
               data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
               data-desc="<?= htmlspecialchars($produit['description'] ?? '', ENT_QUOTES) ?>"
               data-prix="<?= (float)$produit['prix'] ?>"
               data-stock="<?= (int)$produit['quantiteStock'] ?>"
               data-expiration="<?= htmlspecialchars($produit['dateExpiration'] ?? '', ENT_QUOTES) ?>"
               data-categorie="<?= htmlspecialchars($produit['categorie_nom'] ?? 'No category', ENT_QUOTES) ?>"
               data-statut="<?= htmlspecialchars($statut, ENT_QUOTES) ?>"
               data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>"
               data-calories="<?= rand(150, 550) ?>"
               data-proteines="<?= rand(8, 35) ?>"
               data-glucides="<?= rand(10, 60) ?>"
               data-lipides="<?= rand(3, 20) ?>"></div>
        </div>
      <?php endforeach; ?>
      <div id="no-result" style="display:none;" class="col-12 text-center py-5 text-muted">
        <i class="bi bi-search" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
        Aucun produit trouvé.
      </div>
    </div>

  </div>
</section>

<!-- Modal Panier -->
<div class="modal fade" id="modalPanier" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🛒 Mon Panier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="panier-contenu">
        <p class="text-muted text-center">Your cart is empty.</p>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <strong>Total: <span id="panier-total">0.00</span> DT</strong>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-danger btn-sm" onclick="viderPanier()">Clear</button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Shopping</button>
          <button class="btn btn-danger" onclick="acheter()"><i class="bi bi-bag-check me-2"></i>Checkout</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function getPanier() { return JSON.parse(localStorage.getItem('panier') || '[]'); }
function savePanier(p) { localStorage.setItem('panier', JSON.stringify(p)); updateBadge(); }
function updateBadge() {
  var total = getPanier().reduce(function(s,p){ return s+p.quantite; },0);
  var badge = document.getElementById('panier-badge');
  if (badge) { badge.textContent = total; badge.style.display = total > 0 ? 'inline-block' : 'none'; }
}
function ouvrirPanier() {
  var panier = getPanier(), contenu = document.getElementById('panier-contenu'), totalEl = document.getElementById('panier-total');
  if (!panier.length) { contenu.innerHTML = '<p class="text-muted text-center py-3">Votre panier est vide.</p>'; totalEl.textContent = '0,00'; }
  else {
    var html = '<table class="table align-middle"><thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead><tbody>';
    var total = 0;
    panier.forEach(function(p) {
      var sous = p.prix * p.quantite; total += sous;
      html += '<tr><td><div class="d-flex align-items-center gap-2">';
      if (p.image) html += '<img src="'+p.image+'" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">';
      html += '<strong>'+p.nom+'</strong></div></td>';
      html += '<td>'+p.prix.toFixed(2).replace('.',',')+' DT</td>';
      html += '<td><div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',-1)">−</button><span>'+p.quantite+'</span><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',1)">+</button></div></td>';
      html += '<td>'+sous.toFixed(2).replace('.',',')+' DT</td>';
      html += '<td><button class="btn btn-sm btn-outline-danger" onclick="supprimerArticle(\''+p.id+'\')"><i class="bi bi-trash"></i></button></td></tr>';
    });
    html += '</tbody></table>';
    contenu.innerHTML = html; totalEl.textContent = total.toFixed(2).replace('.',',');
  }
  // Reuse existing Bootstrap instance to avoid backdrop stacking issues
  var panierEl = document.getElementById('modalPanier');
  var instance = bootstrap.Modal.getInstance(panierEl) || new bootstrap.Modal(panierEl);
  instance.show();
}
function changerQte(id,delta) {
  var panier = getPanier(), item = panier.find(function(p){ return p.id===id; });
  if (item) { item.quantite += delta; if (item.quantite <= 0) panier = panier.filter(function(p){ return p.id!==id; }); }
  savePanier(panier); ouvrirPanier();
}
function supprimerArticle(id) { savePanier(getPanier().filter(function(p){ return p.id!==id; })); ouvrirPanier(); }
function viderPanier() { savePanier([]); ouvrirPanier(); }
function acheter() {
  var panier = getPanier(); if (!panier.length) return;

  // ── Stock validation BEFORE opening checkout ──────────────────────────
  // Remove any previous stock error banner in cart
  var prevBanner = document.getElementById('panier-stock-error-banner');
  if (prevBanner) prevBanner.remove();

  // Check stock for each cart item using data attributes
  var stockErrors = [];
  panier.forEach(function(p) {
    var dataEl = document.querySelector('.product-data[data-id="' + p.id + '"]');
    if (dataEl) {
      var stockDispo = parseInt(dataEl.dataset.stock) || 0;
      if (p.quantite > stockDispo) {
        stockErrors.push({
          id: p.id,
          nom: p.nom,
          stockDisponible: stockDispo,
          quantiteDemandee: p.quantite
        });
      }
    }
  });

  if (stockErrors.length > 0) {
    // Build error banner HTML
    var html = '<div id="panier-stock-error-banner" style="background:#fff3cd;border:1.5px solid #ffc107;border-radius:12px;padding:14px 16px;margin-bottom:14px;">';
    html += '<div style="font-weight:700;font-size:0.88rem;color:#856404;margin-bottom:8px;"><i class="bi bi-exclamation-triangle-fill me-2"></i>Stock insuffisant</div>';
    stockErrors.forEach(function(e) {
      html += '<div style="font-size:0.82rem;color:#555;margin-bottom:5px;">';
      if (e.stockDisponible === 0) {
        html += '<strong>' + e.nom + '</strong> est en rupture de stock.';
      } else {
        html += '<strong>' + e.nom + '</strong> : il ne reste que <strong>' + e.stockDisponible + '</strong> en stock (vous en avez demandé ' + e.quantiteDemandee + ').';
      }
      html += '</div>';
    });
    html += '<div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;">';
    html += '<button id="btn-panier-ajuster" style="background:#ce1212;color:white;border:none;border-radius:20px;padding:7px 16px;font-size:0.8rem;font-weight:600;cursor:pointer;"><i class="bi bi-cart-check me-1"></i>Ajuster les quantités</button>';
    html += '<button id="btn-panier-annuler-stock" style="background:white;color:#555;border:1px solid #ddd;border-radius:20px;padding:7px 16px;font-size:0.8rem;font-weight:600;cursor:pointer;"><i class="bi bi-x-circle me-1"></i>Annuler</button>';
    html += '</div></div>';

    // Insert at top of cart modal body
    var contenu = document.getElementById('panier-contenu');
    contenu.insertAdjacentHTML('afterbegin', html);

    // Attach button listeners
    document.getElementById('btn-panier-ajuster').addEventListener('click', function() {
      // Adjust cart quantities to available stock
      var p2 = getPanier();
      stockErrors.forEach(function(e) {
        var item = p2.find(function(x){ return String(x.id) === String(e.id); });
        if (item) {
          if (e.stockDisponible === 0) {
            p2 = p2.filter(function(x){ return String(x.id) !== String(e.id); });
          } else {
            item.quantite = e.stockDisponible;
          }
        }
      });
      savePanier(p2);
      // Refresh cart view (removes banner and shows updated quantities)
      ouvrirPanier();
    });

    document.getElementById('btn-panier-annuler-stock').addEventListener('click', function() {
      var banner = document.getElementById('panier-stock-error-banner');
      if (banner) banner.remove();
    });

    // Do NOT proceed to checkout
    return;
  }
  // ── End stock validation ───────────────────────────────────────────────

  var subtotal = panier.reduce(function(s,p){ return s+p.prix*p.quantite; },0);
  var achDiscount   = getActiveDiscount();
  var discountedSub = achDiscount > 0 ? subtotal * (1 - achDiscount / 100) : subtotal;
  // Update checkout summary
  var lines = '';
  panier.forEach(function(p){
    lines += '<tr><td style="font-size:0.82rem;">'+p.nom+'</td><td style="font-size:0.82rem;">x'+p.quantite+'</td><td style="font-size:0.82rem;font-weight:600;color:#ce1212;">'+(p.prix*p.quantite).toFixed(2).replace('.',',')+' DT</td></tr>';
  });
  // Show discount row if applicable
  if (achDiscount > 0) {
    var saved = (subtotal - discountedSub).toFixed(2).replace('.',',');
    lines += '<tr><td colspan="2" style="font-size:0.78rem;color:#2e7d32;"><i class="bi bi-percent me-1"></i>Remise fidélité -'+achDiscount+'%</td><td style="font-size:0.78rem;font-weight:700;color:#2e7d32;">-'+saved+' DT</td></tr>';
  }
  document.getElementById('checkout-order-lines').innerHTML = lines;
  // Default: livraison selected → +8 DT (free if subtotal >= 100)
  var freeDelivery = subtotal >= 100;
  var livRow = document.getElementById('checkout-livraison-row');
  livRow.style.display = 'flex';
  livRow.innerHTML = freeDelivery
    ? '<span style="font-size:0.82rem;color:#2e7d32;"><i class="bi bi-truck me-1"></i>Delivery fee <span style="background:#e8f5e9;border-radius:10px;padding:1px 8px;font-size:0.72rem;font-weight:700;">FREE 🎁</span></span><span style="font-size:0.82rem;color:#2e7d32;font-weight:700;text-decoration:line-through;opacity:0.5;">8,00 DT</span>'
    : '<span style="font-size:0.82rem;color:#555;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Delivery fee</span><span style="font-size:0.82rem;color:#555;">8,00 DT</span>';
  var finalTotal = freeDelivery ? discountedSub : discountedSub + 8;
  document.getElementById('checkout-order-total').textContent = finalTotal.toFixed(2).replace('.',',')+' DT';
  // Hide cart modal, show checkout modal
  var cartModal = bootstrap.Modal.getInstance(document.getElementById('modalPanier'));
  if (cartModal) cartModal.hide();
  setTimeout(function(){
    new bootstrap.Modal(document.getElementById('modalCheckout')).show();
  }, 300);
}
document.querySelectorAll('.btn-ajouter-panier').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var panier = getPanier();
    var id = this.dataset.id, nom = this.dataset.nom, prix = parseFloat(this.dataset.prix), image = this.dataset.image;
    var ex = panier.find(function(p){ return p.id===id; });
    if (ex) { ex.quantite += 1; } else { panier.push({id,nom,prix,image,quantite:1}); }
    savePanier(panier);
    document.getElementById('panier-toast-msg').textContent = '"'+nom+'" ajouté !';
    var t = document.getElementById('panier-toast'); t.style.display = 'flex';
    setTimeout(function(){ t.style.display='none'; }, 3000);
  });
});

function filtrerProduits() {
  var q   = document.getElementById('searchInput').value.toLowerCase().trim();
  var tri = document.getElementById('sortSel').value;
  var items = Array.from(document.querySelectorAll('.product-item'));
  var visible = 0;
  items.forEach(function(el) {
    var nom = el.dataset.nom || '';
    el.style.display = (!q || nom.includes(q)) ? '' : 'none';
    if (el.style.display !== 'none') visible++;
  });
  if (tri) {
    var field = tri.split('-')[0], dir = tri.split('-')[1];
    var vis = items.filter(function(el){ return el.style.display !== 'none'; });
    vis.sort(function(a,b) {
      if (field==='nom')   { return dir==='asc' ? (a.dataset.nom||'').localeCompare(b.dataset.nom||'') : (b.dataset.nom||'').localeCompare(a.dataset.nom||''); }
      if (field==='prix')  { var va=parseFloat(a.dataset.prix)||0, vb=parseFloat(b.dataset.prix)||0; return dir==='asc'?va-vb:vb-va; }
      if (field==='stock') { var va=parseInt(a.dataset.stock)||0,  vb=parseInt(b.dataset.stock)||0;  return dir==='asc'?va-vb:vb-va; }
    });
    var grid = document.getElementById('produitsGrid');
    vis.forEach(function(el){ grid.appendChild(el); });
  }
  document.getElementById('no-result').style.display = visible === 0 ? '' : 'none';
}

function toggleResetBtn() {
  var sel = document.getElementById('sortSel');
  var btn = document.getElementById('reset-sort-btn');
  if (!btn) return;
  btn.style.display = sel.value ? 'flex' : 'none';
}

function resetSort() {
  var sel = document.getElementById('sortSel');
  sel.value = '';
  toggleResetBtn();
  // Restore original order
  var grid  = document.getElementById('produitsGrid');
  var items = Array.from(document.querySelectorAll('.product-item'));
  items.sort(function(a, b) {
    return parseInt(a.dataset.index || 0) - parseInt(b.dataset.index || 0);
  });
  items.forEach(function(el) { grid.appendChild(el); });
  filtrerProduits();
}

updateBadge();

// ── AI RECOMMENDATIONS ────────────────────────────────────────────────────
var RECO_MEDALS = ['🥇', '🥈', '🥉'];

function loadRecommendations() {
  var loading = document.getElementById('reco-loading');
  var list    = document.getElementById('reco-list');
  var error   = document.getElementById('reco-error');
  var btn     = document.getElementById('reco-refresh-btn');

  loading.style.display = 'flex';
  list.style.display    = 'none';
  error.style.display   = 'none';
  if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; }

  fetch('get_recommendations.php')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      loading.style.display = 'none';
      if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
      if (!Array.isArray(data) || data.length === 0) { error.style.display = 'block'; return; }

      var html = '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">';

      data.forEach(function(item, idx) {
        var medal = RECO_MEDALS[idx] || (idx + 1);
        var safeNom   = item.nom.replace(/'/g, "\\'");
        var safeImg   = (item.image||'').replace(/'/g, "\\'");
        var priceStr  = item.prix.toFixed(2).replace('.', ',') + ' DT';

        // Stars
        var starsHtml = '';
        if (item.note > 0) {
          var full = Math.round(item.note);
          starsHtml = '<div style="display:flex;align-items:center;gap:3px;margin-bottom:4px;">'
                    + '<span style="color:#f39c12;font-size:0.75rem;">' + '★'.repeat(full) + '<span style="opacity:0.3;">' + '★'.repeat(5-full) + '</span></span>'
                    + '<span style="font-size:0.65rem;color:rgba(255,255,255,0.6);margin-left:3px;">' + item.note + ' · ' + item.nb_avis + ' reviews</span>'
                    + '</div>';
        }

        // Background: image or dark fallback
        var bgStyle = item.image
          ? 'background:url(\'' + item.image + '\') center/cover no-repeat;'
          : 'background:linear-gradient(135deg,#1a1a1a,#2d2d2d);';

        var cardHtml = '<div style="'
              + bgStyle
              + 'border-radius:20px;overflow:hidden;position:relative;height:320px;cursor:pointer;'
              + 'transition:transform 0.3s,box-shadow 0.3s;flex-shrink:0;" '
              + 'onclick="openProductModal(' + item.id + ')" '
              + 'onmouseover="this.style.transform=\'scale(1.03)\';this.style.boxShadow=\'0 16px 40px rgba(0,0,0,0.35)\'" '
              + 'onmouseout="this.style.transform=\'\';this.style.boxShadow=\'\'">';

        cardHtml += '<div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.85) 0%,rgba(0,0,0,0.2) 50%,rgba(0,0,0,0.05) 100%);border-radius:20px;"></div>';

        cardHtml += '<div style="position:absolute;top:14px;left:14px;right:14px;display:flex;justify-content:space-between;align-items:center;z-index:2;">';
        cardHtml += '<span style="font-size:1.5rem;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.5));">' + medal + '</span>';
        cardHtml += '<button onclick="event.stopPropagation();recoAddToCart(' + item.id + ',\'' + safeNom + '\',' + item.prix + ',\'' + safeImg + '\')" '
              + 'style="width:36px;height:36px;border-radius:50%;background:white;border:none;cursor:pointer;'
              + 'display:flex;align-items:center;justify-content:center;font-size:0.9rem;'
              + 'box-shadow:0 2px 8px rgba(0,0,0,0.3);transition:transform 0.2s;" '
              + 'onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'\'" '
              + 'title="Add to cart">'
              + '<i class="bi bi-cart-plus" style="color:#ce1212;"></i></button>';
        cardHtml += '</div>';

        cardHtml += '<div style="position:absolute;bottom:0;left:0;right:0;padding:18px 16px;z-index:2;">';
        cardHtml += starsHtml;
        cardHtml += '<div style="font-size:1rem;font-weight:800;color:white;margin-bottom:2px;'
              + 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;'
              + 'text-shadow:0 1px 4px rgba(0,0,0,0.5);">' + item.nom + '</div>';
        if (item.categorie) {
          cardHtml += '<div style="font-size:0.7rem;color:rgba(255,255,255,0.6);margin-bottom:8px;">' + item.categorie + '</div>';
        }
        cardHtml += '<div style="font-size:1.4rem;font-weight:900;color:white;text-shadow:0 1px 4px rgba(0,0,0,0.5);">' + priceStr + '</div>';
        cardHtml += '</div>';
        cardHtml += '</div>';

        html += cardHtml;

        // Also inject into hero (same cards, smaller height)
        var heroCard = cardHtml.replace('height:320px', 'height:240px');
        if (idx === 0) {
          var heroGrid = document.getElementById('hero-reco-list');
          if (heroGrid && heroGrid._html === undefined) heroGrid._html = '';
          if (heroGrid) heroGrid._html = (heroGrid._html || '') + heroCard;
        } else if (idx === 1 || idx === 2) {
          var heroGrid = document.getElementById('hero-reco-list');
          if (heroGrid) heroGrid._html = (heroGrid._html || '') + heroCard;
        }
      });

      html += '</div>';
      list.innerHTML = html;
      list.style.display = 'block';

      // Render hero cards
      var heroLoading = document.getElementById('hero-reco-loading');
      var heroList    = document.getElementById('hero-reco-list');
      if (heroLoading) heroLoading.style.display = 'none';
      if (heroList) {
        heroList.innerHTML = '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">' + (heroList._html || '') + '</div>';
        heroList.style.display = 'block';
      }
    })
    .catch(function() {
      loading.style.display = 'none';
      error.style.display   = 'block';
      var heroLoading = document.getElementById('hero-reco-loading');
      if (heroLoading) heroLoading.style.display = 'none';
      if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
    });
}

function recoAddToCart(id, nom, prix, image) {
  var panier = getPanier();
  var ex = panier.find(function(p) { return p.id === String(id); });
  if (ex) { ex.quantite += 1; } else { panier.push({id: String(id), nom: nom, prix: prix, image: image, quantite: 1}); }
  savePanier(panier);
  document.getElementById('panier-toast-msg').textContent = '"' + nom + '" added!';
  var t = document.getElementById('panier-toast'); t.style.display = 'flex';
  setTimeout(function() { t.style.display = 'none'; }, 3000);
}

document.addEventListener('DOMContentLoaded', function() { loadRecommendations(); });

// ── ACHIEVEMENTS ──────────────────────────────────────────────────────────
var ACH_TIERS = [
  { id: 1, target: 5,   discount: 5,  label: '🥄 First Step',          color1: '#ce1212', color2: '#ff6b35' },
  { id: 2, target: 15,  discount: 15, label: '🌱 Healthy Starter',      color1: '#ff6b00', color2: '#ffb300' },
  { id: 3, target: 20,  discount: 20, label: '🔥 Discipline Builder',   color1: '#f9a825', color2: '#ffe082' },
  { id: 4, target: 30,  discount: 30, label: '🥗 Clean Eater',          color1: '#2e7d32', color2: '#66bb6a' },
  { id: 5, target: 50,  discount: 50, label: '💪 Habit Keeper',         color1: '#1565c0', color2: '#42a5f5' },
  { id: 6, target: 75,  discount: 70, label: '⚡ Consistency Pro',      color1: '#6a1b9a', color2: '#ce93d8' },
  { id: 7, target: 100, discount: 80, label: '🏆 Healthy Champion',     color1: '#212121', color2: '#ffd700' }
];

function getMealsCount() {
  return parseInt(localStorage.getItem('smp_meals_count') || '0', 10);
}
function setMealsCount(n) {
  localStorage.setItem('smp_meals_count', n);
}

function getActiveDiscount() {
  var count = getMealsCount();
  var discount = 0;
  ACH_TIERS.forEach(function(t) {
    if (count >= t.target) discount = t.discount;
  });
  return discount;
}

function updateAchievements() {
  var count = getMealsCount();
  var activeDiscount = getActiveDiscount();
  var activeTier = null;

  ACH_TIERS.forEach(function(tier) {
    var card    = document.getElementById('ach-' + tier.id);
    var fill    = document.getElementById('ach-fill-' + tier.id);
    var counter = document.getElementById('ach-count-' + tier.id);
    if (!card) return;

    var pct      = Math.min(100, Math.round((count / tier.target) * 100));
    var unlocked = count >= tier.target;

    if (counter) counter.textContent = Math.min(count, tier.target);
    if (fill)    fill.style.width    = (unlocked ? 100 : pct) + '%';

    card.classList.toggle('unlocked', unlocked);
    var isActive = unlocked && tier.discount === activeDiscount;
    card.classList.toggle('active-discount', isActive);
    if (isActive) activeTier = tier;
  });

  // Active discount badge — colored with active tier's gradient
  var badge     = document.getElementById('achievement-active-badge');
  var badgeText = document.getElementById('achievement-active-text');
  if (badge) {
    if (activeDiscount > 0 && activeTier) {
      badge.style.display = 'block';
      badge.style.background = 'linear-gradient(135deg,' + activeTier.color1 + ',' + activeTier.color2 + ')';
      badgeText.textContent = activeDiscount + '% de remise active !';
    } else {
      badge.style.display = 'none';
    }
  }

  // Next milestone hint
  var hint = document.getElementById('ach-next-hint-text');
  if (hint) {
    var next = null;
    for (var i = 0; i < ACH_TIERS.length; i++) {
      if (count < ACH_TIERS[i].target) { next = ACH_TIERS[i]; break; }
    }
    if (next) {
      var remaining = next.target - count;
      hint.textContent = 'Only ' + remaining + ' more meals to unlock ' + next.discount + '% off — ' + next.label + '!';
    } else {
      hint.textContent = '🏆 Congratulations! You have unlocked all achievements. Enjoy 80% off your next order!';
    }
  }
}

var achMoreOpen = false;
function toggleMoreAchievements() {
  var grid    = document.getElementById('ach-more-grid');
  var btn     = document.getElementById('ach-view-more-btn');
  var chevron = document.getElementById('ach-chevron');
  var label   = document.getElementById('ach-view-more-text');
  achMoreOpen = !achMoreOpen;
  if (achMoreOpen) {
    grid.style.display = 'block';
    // Animate in
    grid.style.opacity = '0';
    grid.style.transform = 'translateY(-10px)';
    grid.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    setTimeout(function() {
      grid.style.opacity = '1';
      grid.style.transform = 'translateY(0)';
    }, 10);
    chevron.className = 'bi bi-chevron-up';
    label.textContent = 'Show Less';
    btn.style.background = 'linear-gradient(135deg,#b00e0e,#ce1212)';
  } else {
    grid.style.opacity = '0';
    grid.style.transform = 'translateY(-10px)';
    setTimeout(function() { grid.style.display = 'none'; }, 280);
    chevron.className = 'bi bi-chevron-down';
    label.textContent = 'View More Achievements';
    btn.style.background = 'linear-gradient(135deg,#ce1212,#ff4444)';
  }
}

function trackMealsPurchased(items) {
  var prevCount = getMealsCount();
  var added = 0;
  items.forEach(function(p) { added += p.quantite; });
  var newCount = prevCount + added;
  setMealsCount(newCount);
  updateAchievements();

  // Show unlock toast if a new tier was just crossed
  ACH_TIERS.forEach(function(tier) {
    if (prevCount < tier.target && newCount >= tier.target) {
      showAchievementToast(tier);
    }
  });
}

function showAchievementToast(tier) {
  var toast = document.getElementById('achievement-toast');
  if (!toast) return;
  document.getElementById('ach-toast-title').textContent = '🏆 ' + tier.label + ' débloqué !';
  document.getElementById('ach-toast-msg').textContent   = 'You reached ' + tier.target + ' meals. Enjoy ' + tier.discount + '% off!';
  toast.style.display = 'flex';
  setTimeout(function() { toast.style.display = 'none'; }, 5500);
}

// Init achievements on page load
document.addEventListener('DOMContentLoaded', function() { updateAchievements(); });

// ── WISHLIST ──
function getWishlist() { return JSON.parse(localStorage.getItem('wishlist') || '[]'); }
function saveWishlist(w) { localStorage.setItem('wishlist', JSON.stringify(w)); updateWishlistBadge(); }

function updateWishlistBadge() {
  var count = getWishlist().length;
  var badge = document.getElementById('wishlist-badge');
  if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'inline-block' : 'none'; }
}

function toggleWishlist(btn) {
  var id    = btn.dataset.id;
  var nom   = btn.dataset.nom;
  var prix  = parseFloat(btn.dataset.prix);
  var image = btn.dataset.image;
  var w = getWishlist();
  var idx = w.findIndex(function(x){ return x.id === id; });
  if (idx >= 0) {
    w.splice(idx, 1);
    btn.classList.remove('active');
  } else {
    w.push({id, nom, prix, image});
    btn.classList.add('active');
    // Mini pulse animation
    btn.style.transform = 'scale(1.4)';
    setTimeout(function(){ btn.style.transform = ''; }, 200);
  }
  saveWishlist(w);
}

function ouvrirWishlist() {
  var w = getWishlist();
  var contenu = document.getElementById('wishlist-contenu');
  if (!w.length) {
    contenu.innerHTML = '<p class="text-muted text-center py-4"><i class="bi bi-heart" style="font-size:2rem;display:block;margin-bottom:8px;"></i>Your wishlist is empty.</p>';
  } else {
    var html = '<div class="row g-3">';
    w.forEach(function(p) {
      // Get extra data from product-data element if available
      var dataEl = document.querySelector('.product-data[data-id="'+p.id+'"]');
      var desc     = dataEl ? dataEl.dataset.desc     : '';
      var cat      = dataEl ? dataEl.dataset.categorie: '';
      var exp      = dataEl ? dataEl.dataset.expiration: '';
      var statut   = dataEl ? dataEl.dataset.statut   : '';
      var cal      = dataEl ? dataEl.dataset.calories  : '';
      var prot     = dataEl ? dataEl.dataset.proteines : '';
      var carb     = dataEl ? dataEl.dataset.glucides  : '';
      var fat      = dataEl ? dataEl.dataset.lipides   : '';
      var badgeBg  = statut==='Disponible'?'#28a745':statut==='Rupture'?'#dc3545':'#6c757d';

      html += '<div class="col-12 col-md-6">';
      // Wrapper with position:relative for the ✕ button
      html += '<div style="position:relative;background:white;border-radius:14px;box-shadow:0 3px 12px rgba(0,0,0,0.08);">';
      // ── ✕ top-right corner button ──
      html += '<button onclick="event.stopPropagation();removeFromWishlist(\''+p.id+'\')" title="Remove from wishlist" '
            + 'style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;'
            + 'background:#ce1212;color:white;border:2px solid white;font-size:0.75rem;font-weight:900;'
            + 'cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;'
            + 'z-index:10;box-shadow:0 2px 6px rgba(0,0,0,0.2);">✕</button>';
      // Card content (overflow hidden on inner div only)
      html += '<div style="display:flex;gap:0;cursor:pointer;border-radius:14px;overflow:hidden;" onclick="wishlistOpenDetail(\''+p.id+'\')">';
      // Image
      html += '<div style="width:110px;flex-shrink:0;position:relative;">';
      if (p.image) html += '<img src="'+p.image+'" style="width:110px;height:100%;min-height:110px;object-fit:cover;">';
      else html += '<div style="width:110px;min-height:110px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><i class="bi bi-image" style="color:#ccc;font-size:1.5rem;"></i></div>';
      if (statut) html += '<span style="position:absolute;bottom:6px;left:6px;background:'+badgeBg+';color:white;border-radius:10px;padding:2px 7px;font-size:0.65rem;font-weight:700;">'+statut+'</span>';
      html += '</div>';
      // Info
      html += '<div style="padding:12px 14px;flex:1;min-width:0;">';
      html += '<div style="font-size:0.85rem;font-weight:700;color:#2d2d2d;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+p.nom+'</div>';
      if (cat) html += '<div style="font-size:0.72rem;color:#999;margin-bottom:4px;"><i class="bi bi-tag-fill me-1" style="color:#ce1212;"></i>'+cat+'</div>';
      if (desc) html += '<div style="font-size:0.75rem;color:#888;margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">'+desc+'</div>';
      html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">';
      html += '<span style="font-size:0.95rem;font-weight:700;color:#ce1212;">'+p.prix.toFixed(2).replace('.',',')+' DT</span>';
      html += '<button onclick="event.stopPropagation();wishlistAddToCart(\''+p.id+'\')" title="Add to cart" style="background:#ce1212;color:white;border:none;border-radius:20px;padding:4px 12px;font-size:0.72rem;font-weight:600;cursor:pointer;"><i class="bi bi-cart-plus me-1"></i>Cart</button>';
      html += '</div></div></div></div></div>';
    });
    html += '</div>';
    contenu.innerHTML = html;
  }
  // Update count in header
  var ct = document.getElementById('wishlist-count-title');
  if (ct) ct.textContent = w.length ? '('+w.length+' item'+(w.length>1?'s':'')+')' : '';
  new bootstrap.Modal(document.getElementById('modalWishlist')).show();
}

function wishlistAddToCart(id) {
  var w = getWishlist();
  var p = w.find(function(x){ return x.id === id; });
  if (!p) return;
  var panier = getPanier();
  var ex = panier.find(function(x){ return x.id === id; });
  if (ex) { ex.quantite += 1; } else { panier.push({id: p.id, nom: p.nom, prix: p.prix, image: p.image, quantite: 1}); }
  savePanier(panier);
  // Auto-remove from wishlist
  removeFromWishlist(id);
  document.getElementById('panier-toast-msg').textContent = '"'+p.nom+'" added to cart!';
  var t = document.getElementById('panier-toast'); t.style.display = 'flex';
  setTimeout(function(){ t.style.display = 'none'; }, 3000);
}

function wishlistAddAllToCart() {
  var w = getWishlist();
  if (!w.length) return;
  var panier = getPanier();
  w.forEach(function(p) {
    var ex = panier.find(function(x){ return x.id === p.id; });
    if (ex) { ex.quantite += 1; } else { panier.push({id: p.id, nom: p.nom, prix: p.prix, image: p.image, quantite: 1}); }
  });
  savePanier(panier);
  // Auto-remove all from wishlist
  w.forEach(function(p) {
    var btn = document.querySelector('.btn-wishlist[data-id="'+p.id+'"]');
    if (btn) btn.classList.remove('active');
  });
  saveWishlist([]);
  bootstrap.Modal.getInstance(document.getElementById('modalWishlist')).hide();
  document.getElementById('panier-toast-msg').textContent = w.length+' item'+(w.length>1?'s':'')+' added to cart!';
  var t = document.getElementById('panier-toast'); t.style.display = 'flex';
  setTimeout(function(){ t.style.display = 'none'; }, 3000);
}

function wishlistClearAll() {
  getWishlist().forEach(function(p) {
    var btn = document.querySelector('.btn-wishlist[data-id="'+p.id+'"]');
    if (btn) btn.classList.remove('active');
  });
  saveWishlist([]);
  ouvrirWishlist();
}

function wishlistOpenDetail(id) {
  var wModal = bootstrap.Modal.getInstance(document.getElementById('modalWishlist'));
  if (wModal) {
    document.getElementById('modalWishlist').addEventListener('hidden.bs.modal', function onWHidden() {
      document.getElementById('modalWishlist').removeEventListener('hidden.bs.modal', onWHidden);
      cleanupModal();
      setTimeout(function(){ openProductModal(parseInt(id)); }, 50);
    }, {once: true});
    wModal.hide();
  } else {
    openProductModal(parseInt(id));
  }
}

function removeFromWishlist(id) {
  var w = getWishlist().filter(function(x){ return x.id !== id; });
  saveWishlist(w);
  var btn = document.querySelector('.btn-wishlist[data-id="'+id+'"]');
  if (btn) btn.classList.remove('active');
  ouvrirWishlist();
}

// Clean up Bootstrap modal leftovers (backdrop, body classes)
function cleanupModal() {
  document.querySelectorAll('.modal-backdrop').forEach(function(el){ el.remove(); });
  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('overflow');
  document.body.style.removeProperty('padding-right');
}

// Always clean up when wishlist modal closes
document.addEventListener('DOMContentLoaded', function() {
  var wModalEl = document.getElementById('modalWishlist');
  if (wModalEl) {
    wModalEl.addEventListener('hidden.bs.modal', function() {
      setTimeout(cleanupModal, 50);
    });
  }

  // Init hearts on page load
  var w = getWishlist();
  w.forEach(function(item) {
    var btn = document.querySelector('.btn-wishlist[data-id="'+item.id+'"]');
    if (btn) btn.classList.add('active');
  });
  updateWishlistBadge();
});
</script>

<!-- ── WISHLIST MODAL ── -->
<div class="modal fade" id="modalWishlist" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header" style="border-bottom:1px solid #f0f0f0;padding:16px 24px;flex-wrap:wrap;gap:10px;">
        <h5 class="modal-title" style="font-family:'Amatic SC',cursive;font-size:1.6rem;font-weight:700;color:#2d2d2d;">
          <i class="bi bi-heart-fill me-2" style="color:#ce1212;"></i>My Wishlist
          <span id="wishlist-count-title" style="font-size:0.9rem;color:#999;font-family:'Inter',sans-serif;font-weight:400;margin-left:8px;"></span>
        </h5>
        <div style="display:flex;gap:8px;margin-left:auto;margin-right:32px;">
          <button onclick="wishlistAddAllToCart()" style="background:#ce1212;color:white;border:none;border-radius:20px;padding:7px 16px;font-size:0.78rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-cart-plus me-1"></i>Add All to Cart
          </button>
          <button onclick="wishlistClearAll()" style="background:#f5f5f5;color:#666;border:none;border-radius:20px;padding:7px 16px;font-size:0.78rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-trash me-1"></i>Clear All
          </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:20px 24px;" id="wishlist-contenu">
        <p class="text-muted text-center py-4">Your wishlist is empty.</p>
      </div>
    </div>
  </div>
</div>

<!-- ── CHECKOUT MODAL ── -->
<div class="modal fade" id="modalCheckout" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header" style="border-bottom:1px solid #f0f0f0;padding:18px 24px;">
        <h5 class="modal-title" style="font-family:'Amatic SC',cursive;font-size:1.6rem;font-weight:700;color:#2d2d2d;">
          <i class="bi bi-bag-check-fill me-2" style="color:#ce1212;"></i>Checkout
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="row g-4">

          <!-- LEFT: Form -->
          <div class="col-md-7">
            <form id="checkoutForm" onsubmit="confirmerCommande(event)">

              <!-- Personal info -->
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                <i class="bi bi-person-fill" style="color:#ce1212;"></i> Personal Information
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <input type="text" class="form-control" id="co-prenom" placeholder="First name" required
                    style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                  <div id="co-prenom-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Letters only (min. 2 characters)</div>
                </div>
                <div class="col-6">
                  <input type="text" class="form-control" id="co-nom" placeholder="Last name" required
                    style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                  <div id="co-nom-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Letters only (min. 2 characters)</div>
                </div>
                <div class="col-12">
                  <input type="email" class="form-control" id="co-email" placeholder="Email address" required
                    style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                </div>
                <div class="col-12">
                  <input type="text" class="form-control" id="co-adresse" placeholder="Street address" required
                    style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                </div>
                <div class="col-12">
                  <input type="text" class="form-control" id="co-localisation" placeholder="City / Region" required
                    style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                </div>
                <div class="col-12">
                  <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:0.85rem;color:#999;">📞</span>
                    <input type="tel" class="form-control" id="co-phone" placeholder="Phone number (for delivery)"
                      oninput="this.value=this.value.replace(/[^0-9+\s\-]/g,'');validatePhoneLive(this)"
                      autocomplete="tel"
                      style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px 10px 36px;">
                  </div>
                  <div id="co-phone-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i><span id="co-phone-err-msg">Numéro invalide</span></div>
                </div>
              </div>

              <!-- Delivery method -->
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                <i class="bi bi-truck" style="color:#ce1212;"></i> Delivery Method
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label id="lbl-livraison" style="display:flex;align-items:center;gap:10px;border:2px solid #ce1212;border-radius:12px;padding:12px 14px;cursor:pointer;background:#fff8f8;">
                    <input type="radio" name="livraison" value="livraison" checked onchange="togglePaiement()" style="accent-color:#ce1212;">
                    <div>
                      <div style="font-size:0.82rem;font-weight:600;color:#2d2d2d;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Home Delivery</div>
                      <div style="font-size:0.7rem;color:#999;">Pay on delivery</div>
                    </div>
                  </label>
                </div>
                <div class="col-6">
                  <label id="lbl-carte" style="display:flex;align-items:center;gap:10px;border:2px solid #e0e0e0;border-radius:12px;padding:12px 14px;cursor:pointer;background:white;">
                    <input type="radio" name="livraison" value="carte" onchange="togglePaiement()" style="accent-color:#ce1212;">
                    <div>
                      <div style="font-size:0.82rem;font-weight:600;color:#2d2d2d;"><i class="bi bi-credit-card me-1" style="color:#1a73e8;"></i>Credit Card</div>
                      <div style="font-size:0.7rem;color:#999;">Pay now online</div>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Card details (hidden by default) -->
              <div id="card-details" style="display:none;">
                <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                  <i class="bi bi-credit-card-2-front" style="color:#1a73e8;"></i> Card Details
                </div>
                <div style="background:#f9f9f9;border-radius:12px;padding:16px;" class="mb-3">
                  <div class="mb-2">
                    <input type="text" class="form-control" id="co-card-num" placeholder="Card number (16 digits)"
                      maxlength="19" oninput="formatCardNum(this)"
                      style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;letter-spacing:2px;">
                    <div id="co-card-num-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Enter a valid 16-digit card number</div>
                  </div>
                  <div class="row g-2">
                    <div class="col-6">
                      <input type="text" class="form-control" id="co-card-exp" placeholder="MM / YY"
                        maxlength="7" oninput="formatExpiry(this)"
                        style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                      <div id="co-card-exp-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Invalid or expired date</div>
                    </div>
                    <div class="col-6">
                      <input type="text" class="form-control" id="co-card-cvv" placeholder="CVV"
                        maxlength="3" oninput="this.value=this.value.replace(/\D/g,'')"
                        style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                      <div id="co-card-cvv-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>3-digit CVV required</div>
                    </div>
                  </div>
                  <!-- Card type icons -->
                  <div id="card-type-icons" style="margin-top:10px;display:flex;gap:8px;align-items:center;">
                    <img id="icon-visa" src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" style="height:22px;opacity:0.35;transition:opacity 0.2s;">
                    <img id="icon-mc" src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" style="height:22px;opacity:0.35;transition:opacity 0.2s;">
                    <span id="card-type-label" style="font-size:0.72rem;color:#999;margin-left:4px;"></span>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-danger w-100" style="border-radius:25px;padding:12px;font-weight:600;font-size:0.9rem;">
                <i class="bi bi-check-circle me-2"></i>Confirm Order
              </button>
            </form>
          </div>

          <!-- RIGHT: Order summary -->
          <div class="col-md-5">
            <div style="background:#f9f9f9;border-radius:14px;padding:18px;">
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                <i class="bi bi-receipt" style="color:#ce1212;"></i> Order Summary
              </div>
              <table class="table table-borderless mb-0" style="font-size:0.82rem;">
                <tbody id="checkout-order-lines"></tbody>
              </table>
              <hr style="border-color:#e0e0e0;">
              <div id="checkout-livraison-row" style="display:none;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:0.82rem;color:#555;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Delivery fee</span>
                <span style="font-size:0.82rem;color:#555;">8,00 DT</span>
              </div>
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:0.85rem;font-weight:600;color:#2d2d2d;">Total</span>
                <span style="font-size:1.2rem;font-weight:700;color:#ce1212;" id="checkout-order-total">0,00 DT</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── Name validation: letters, spaces, hyphens, apostrophes only ──
function validerNom(val) {
  return /^[A-Za-zÀ-ÖØ-öø-ÿ\s'\-]+$/.test(val.trim()) && val.trim().length >= 2;
}

function attachNomValidation(inputId) {
  var el = document.getElementById(inputId);
  if (!el) return;
  el.addEventListener('input', function() {
    var ok = validerNom(this.value);
    this.style.borderColor = ok || this.value === '' ? '#e0e0e0' : '#dc3545';
    var err = document.getElementById(inputId + '-err');
    if (err) err.style.display = ok || this.value === '' ? 'none' : 'block';
  });
}
attachNomValidation('co-prenom');
attachNomValidation('co-nom');

// ── Phone validation ──
var phoneRegex = /^(\+216|00216)?[2-9]\d{7}$/;

function validerPhone(val) {
  var normalized = val.replace(/[\s\-]/g, '');
  return phoneRegex.test(normalized);
}

function validatePhoneLive(el) {
  var val = el.value.trim();
  if (val === '') {
    el.style.borderColor = '#e0e0e0';
    document.getElementById('co-phone-err').style.display = 'none';
    return;
  }
  var ok = validerPhone(val);
  el.style.borderColor = ok ? '#28a745' : '#dc3545';
  document.getElementById('co-phone-err').style.display = ok ? 'none' : 'block';
  document.getElementById('co-phone-err-msg').textContent = ok ? '' : 'Numéro invalide. Exemple : 20 123 456 ou +216 20 123 456.';
}

function togglePaiement() {
  var val = document.querySelector('input[name="livraison"]:checked').value;
  var cardDiv = document.getElementById('card-details');
  var lblLiv  = document.getElementById('lbl-livraison');
  var lblCarte= document.getElementById('lbl-carte');
  var livRow  = document.getElementById('checkout-livraison-row');
  // Recalculate total
  var panier = getPanier();
  var subtotal = panier.reduce(function(s,p){ return s+p.prix*p.quantite; },0);
  var freeDelivery = subtotal >= 100;
  var livRow = document.getElementById('checkout-livraison-row');
  if (val === 'carte') {
    cardDiv.style.display = 'block';
    lblCarte.style.border = '2px solid #1a73e8';
    lblCarte.style.background = '#f0f4ff';
    lblLiv.style.border = '2px solid #e0e0e0';
    lblLiv.style.background = 'white';
    livRow.style.display = 'none';
    document.getElementById('checkout-order-total').textContent = subtotal.toFixed(2).replace('.',',')+' DT';
    document.getElementById('co-card-num').required = true;
    document.getElementById('co-card-exp').required = true;
    document.getElementById('co-card-cvv').required = true;
  } else {
    cardDiv.style.display = 'none';
    lblLiv.style.border = '2px solid #ce1212';
    lblLiv.style.background = '#fff8f8';
    lblCarte.style.border = '2px solid #e0e0e0';
    lblCarte.style.background = 'white';
    livRow.style.display = 'flex';
    livRow.innerHTML = freeDelivery
      ? '<span style="font-size:0.82rem;color:#2e7d32;"><i class="bi bi-truck me-1"></i>Delivery fee <span style="background:#e8f5e9;border-radius:10px;padding:1px 8px;font-size:0.72rem;font-weight:700;">FREE 🎁</span></span><span style="font-size:0.82rem;color:#2e7d32;font-weight:700;text-decoration:line-through;opacity:0.5;">8,00 DT</span>'
      : '<span style="font-size:0.82rem;color:#555;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Delivery fee</span><span style="font-size:0.82rem;color:#555;">8,00 DT</span>';
    document.getElementById('checkout-order-total').textContent = freeDelivery
      ? subtotal.toFixed(2).replace('.',',')+' DT'
      : (subtotal+8).toFixed(2).replace('.',',')+' DT';
    document.getElementById('co-card-num').required = false;
    document.getElementById('co-card-exp').required = false;
    document.getElementById('co-card-cvv').required = false;
  }
}

function formatCardNum(el) {
  var raw = el.value.replace(/\D/g,'').substring(0,16);
  el.value = raw.replace(/(.{4})/g,'$1 ').trim();
  // Detect card type
  var visa = document.getElementById('icon-visa');
  var mc   = document.getElementById('icon-mc');
  var lbl  = document.getElementById('card-type-label');
  if (/^4/.test(raw)) {
    visa.style.opacity='1'; mc.style.opacity='0.2'; lbl.textContent='Visa';
  } else if (/^5[1-5]/.test(raw) || /^2[2-7]/.test(raw)) {
    mc.style.opacity='1'; visa.style.opacity='0.2'; lbl.textContent='Mastercard';
  } else {
    visa.style.opacity='0.35'; mc.style.opacity='0.35'; lbl.textContent='';
  }
  // Live validation
  var ok = raw.length === 16;
  el.style.borderColor = raw.length > 0 ? (ok ? '#28a745' : '#dc3545') : '#e0e0e0';
  document.getElementById('co-card-num-err').style.display = raw.length > 0 && !ok ? 'block' : 'none';
}

function formatExpiry(el) {
  var raw = el.value.replace(/\D/g,'').substring(0,4);
  if (raw.length >= 3) raw = raw.substring(0,2)+' / '+raw.substring(2);
  el.value = raw;
  // Validate: MM 01-12, YY >= current year
  var digits = el.value.replace(/\D/g,'');
  var ok = false;
  if (digits.length === 4) {
    var mm = parseInt(digits.substring(0,2));
    var yy = parseInt(digits.substring(2,4));
    var now = new Date();
    var curYY = now.getFullYear() % 100;
    var curMM = now.getMonth() + 1;
    ok = mm >= 1 && mm <= 12 && (yy > curYY || (yy === curYY && mm >= curMM));
  }
  el.style.borderColor = digits.length > 0 ? (ok ? '#28a745' : '#dc3545') : '#e0e0e0';
  document.getElementById('co-card-exp-err').style.display = digits.length > 0 && !ok ? 'block' : 'none';
}

// CVV live validation
document.addEventListener('DOMContentLoaded', function() {
  var cvvEl = document.getElementById('co-card-cvv');
  if (cvvEl) {
    cvvEl.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g,'');
      var ok = this.value.length === 3;
      this.style.borderColor = this.value.length > 0 ? (ok ? '#28a745' : '#dc3545') : '#e0e0e0';
      document.getElementById('co-card-cvv-err').style.display = this.value.length > 0 && !ok ? 'block' : 'none';
    });
  }
});

function confirmerCommande(e) {
  e.preventDefault();
  var prenom = document.getElementById('co-prenom').value.trim();
  var nom    = document.getElementById('co-nom').value.trim();

  if (!validerNom(prenom)) {
    document.getElementById('co-prenom').style.borderColor = '#dc3545';
    document.getElementById('co-prenom-err').style.display = 'block';
    document.getElementById('co-prenom').focus();
    return;
  }
  if (!validerNom(nom)) {
    document.getElementById('co-nom').style.borderColor = '#dc3545';
    document.getElementById('co-nom-err').style.display = 'block';
    document.getElementById('co-nom').focus();
    return;
  }

  var method = document.querySelector('input[name="livraison"]:checked').value;
  var panier = getPanier();
  var subtotal = panier.reduce(function(s,p){ return s+p.prix*p.quantite; },0);
  var freeDelivery = subtotal >= 100;
  // Apply achievement discount on subtotal
  var achDiscount  = getActiveDiscount();
  var discountedSub = achDiscount > 0 ? subtotal * (1 - achDiscount / 100) : subtotal;
  var total    = (method === 'livraison' && !freeDelivery) ? discountedSub + 8 : discountedSub;
  var items  = panier.map(function(p){ return {id: p.id, quantite: p.quantite}; });
  var methodLabel = method === 'carte' ? 'Credit Card' : (freeDelivery ? 'Home Delivery (FREE 🎁)' : 'Home Delivery (+8,00 DT)');
  if (achDiscount > 0) methodLabel += ' (-' + achDiscount + '% remise fidélité)';

  // Validate phone number
  var phoneEl = document.getElementById('co-phone');
  var phoneVal = phoneEl ? phoneEl.value.trim() : '';
  if (!phoneVal) {
    phoneEl.style.borderColor = '#dc3545';
    document.getElementById('co-phone-err-msg').textContent = 'Le numéro de téléphone est obligatoire.';
    document.getElementById('co-phone-err').style.display = 'block';
    phoneEl.focus();
    return;
  }
  if (!validerPhone(phoneVal)) {
    phoneEl.style.borderColor = '#dc3545';
    document.getElementById('co-phone-err-msg').textContent = 'Numéro invalide. Exemple : 20 123 456 ou +216 20 123 456.';
    document.getElementById('co-phone-err').style.display = 'block';
    phoneEl.focus();
    return;
  }
  phoneEl.style.borderColor = '#28a745';
  document.getElementById('co-phone-err').style.display = 'none';

  // Validate card fields if credit card selected
  if (method === 'carte') {
    var cardNum = document.getElementById('co-card-num').value.replace(/\D/g,'');
    var expRaw  = document.getElementById('co-card-exp').value.replace(/\D/g,'');
    var cvv     = document.getElementById('co-card-cvv').value;
    var cardOk  = cardNum.length === 16;
    var expOk   = false;
    if (expRaw.length === 4) {
      var mm=parseInt(expRaw.substring(0,2)), yy=parseInt(expRaw.substring(2,4));
      var now=new Date(), curYY=now.getFullYear()%100, curMM=now.getMonth()+1;
      expOk = mm>=1 && mm<=12 && (yy>curYY||(yy===curYY&&mm>=curMM));
    }
    var cvvOk = cvv.length === 3;
    if (!cardOk) {
      document.getElementById('co-card-num').style.borderColor='#dc3545';
      document.getElementById('co-card-num-err').style.display='block';
      document.getElementById('co-card-num').focus();
      return;
    }
    if (!expOk) {
      document.getElementById('co-card-exp').style.borderColor='#dc3545';
      document.getElementById('co-card-exp-err').style.display='block';
      document.getElementById('co-card-exp').focus();
      return;
    }
    if (!cvvOk) {
      document.getElementById('co-card-cvv').style.borderColor='#dc3545';
      document.getElementById('co-card-cvv-err').style.display='block';
      document.getElementById('co-card-cvv').focus();
      return;
    }
  }

  // Capture invoice data BEFORE any async work
  var email = document.getElementById('co-email').value.trim();
  var phone = document.getElementById('co-phone') ? document.getElementById('co-phone').value.trim() : '';
  var invoiceItems = panier.map(function(p){
    return {nom: p.nom, quantite: p.quantite, prix: p.prix};
  });

  // Disable confirm button to prevent double-submit
  var confirmBtn = document.querySelector('#modalCheckout button[type="submit"]');
  if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Vérification du stock...'; }

  // Validate stock BEFORE clearing cart or closing modal
  fetch('update_stock.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({items: items})
  })
  .then(function(r) { return r.json(); })
  .then(function(result) {
    // Re-enable button
    if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirm Order'; }

    if (!result.success && result.stockErrors && result.stockErrors.length > 0) {
      // ── Stock error: show inline error and offer two options ──
      afficherErreurStock(result.stockErrors, prenom, nom, email, phone, method, methodLabel, total, freeDelivery);
      return;
    }

    // ── Stock OK: proceed with order confirmation ──
    var checkoutModal = bootstrap.Modal.getInstance(document.getElementById('modalCheckout'));
    if (checkoutModal) checkoutModal.hide();

    // Clear cart
    savePanier([]);

    // Track meals for achievements
    trackMealsPurchased(invoiceItems);

    // Send invoice email
    fetch('/ryhem/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/front/send_invoice.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        prenom: prenom, nom: nom, email: email, phone: phone,
        method: methodLabel, items: invoiceItems, total: total
      })
    }).then(function(r){ return r.json(); })
      .then(function(d){ console.log('[Invoice]', d); })
      .catch(function(e){ console.error('[Invoice error]', e); });

    // Show success toast after modal animation completes
    document.getElementById('modalCheckout').addEventListener('hidden.bs.modal', function onHidden() {
      document.getElementById('modalCheckout').removeEventListener('hidden.bs.modal', onHidden);
      var msg = document.getElementById('order-success-toast');
      document.getElementById('order-success-name').textContent = prenom + ' ' + nom;
      document.getElementById('order-success-method').textContent = methodLabel;
      document.getElementById('order-success-total').textContent = total.toFixed(2).replace('.',',') + ' DT';
      msg.style.display = 'flex';
      setTimeout(function(){ msg.style.display = 'none'; }, 6000);
    }, {once: true});
  })
  .catch(function(err) {
    // Network error — re-enable button and show generic error
    if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirm Order'; }
    console.error('[Stock check error]', err);
    alert('Une erreur réseau est survenue. Veuillez réessayer.');
  });
}

/**
 * Display a stock error message inside the checkout modal and offer two options:
 * - Adjust quantities to available stock
 * - Cancel (keep cart as-is)
 */
// Store stockErrors globally so buttons can access them without inline JSON
var _pendingStockErrors = [];

function afficherErreurStock(stockErrors, prenom, nom, email, phone, method, methodLabel, total, freeDelivery) {
  // Save errors for button handlers
  _pendingStockErrors = stockErrors;

  // Build error HTML — no inline JSON to avoid quote escaping issues
  var html = '<div id="stock-error-banner" style="background:#fff3cd;border:1.5px solid #ffc107;border-radius:12px;padding:16px 18px;margin-bottom:16px;">';
  html += '<div style="font-weight:700;font-size:0.9rem;color:#856404;margin-bottom:10px;"><i class="bi bi-exclamation-triangle-fill me-2"></i>Stock insuffisant</div>';
  stockErrors.forEach(function(e) {
    html += '<div style="font-size:0.85rem;color:#555;margin-bottom:6px;">';
    html += '<strong>' + e.nom + '</strong> : ';
    if (e.stockDisponible === 0) {
      html += 'ce produit est en rupture de stock.';
    } else {
      html += 'il ne reste que <strong>' + e.stockDisponible + '</strong> en stock (vous en avez demandé ' + e.quantiteDemandee + ').';
    }
    html += '</div>';
  });
  html += '<div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">';
  html += '<button id="btn-ajuster-stock" '
        + 'style="background:#ce1212;color:white;border:none;border-radius:20px;padding:8px 18px;font-size:0.82rem;font-weight:600;cursor:pointer;">'
        + '<i class="bi bi-cart-check me-1"></i>Ajuster la quantité</button>';
  html += '<button id="btn-annuler-commande" '
        + 'style="background:white;color:#555;border:1px solid #ddd;border-radius:20px;padding:8px 18px;font-size:0.82rem;font-weight:600;cursor:pointer;">'
        + '<i class="bi bi-x-circle me-1"></i>Annuler</button>';
  html += '</div></div>';

  // Remove any existing error banner
  var existing = document.getElementById('stock-error-banner');
  if (existing) existing.parentNode.removeChild(existing);

  // Insert at top of checkout form
  var form = document.getElementById('checkoutForm');
  if (form) {
    form.insertAdjacentHTML('afterbegin', html);
    form.scrollIntoView({behavior: 'smooth', block: 'start'});
  }

  // Attach event listeners after insertion (avoids inline onclick + JSON escaping issues)
  var btnAjuster = document.getElementById('btn-ajuster-stock');
  if (btnAjuster) {
    btnAjuster.addEventListener('click', function() {
      ajusterPanierStock(_pendingStockErrors);
    });
  }
  var btnAnnuler = document.getElementById('btn-annuler-commande');
  if (btnAnnuler) {
    btnAnnuler.addEventListener('click', function() {
      annulerCommande();
    });
  }
}

/**
 * Adjust cart quantities to match available stock, then reopen cart modal.
 */
function ajusterPanierStock(stockErrors) {
  var panier = getPanier();
  stockErrors.forEach(function(e) {
    var item = panier.find(function(p) { return String(p.id) === String(e.id); });
    if (item) {
      if (e.stockDisponible === 0) {
        // Remove item entirely
        panier = panier.filter(function(p) { return String(p.id) !== String(e.id); });
      } else {
        item.quantite = e.stockDisponible;
      }
    }
  });
  savePanier(panier);

  // Close checkout modal and reopen cart
  var checkoutModal = bootstrap.Modal.getInstance(document.getElementById('modalCheckout'));
  if (checkoutModal) checkoutModal.hide();
  setTimeout(function() { ouvrirPanier(); }, 350);
}

/**
 * Cancel order — just close the checkout modal, keep cart intact.
 */
function annulerCommande() {
  var checkoutModal = bootstrap.Modal.getInstance(document.getElementById('modalCheckout'));
  if (checkoutModal) checkoutModal.hide();
}
</script>

<!-- ── ACHIEVEMENT UNLOCK TOAST ── -->
<div id="achievement-toast" style="display:none;position:fixed;bottom:28px;left:28px;z-index:9999;
     background:white;border-radius:14px;box-shadow:0 8px 32px rgba(206,18,18,0.2);
     padding:18px 22px;max-width:320px;border-left:5px solid #ce1212;animation:slideInLeft 0.4s ease;">
  <div style="display:flex;align-items:flex-start;gap:12px;">
    <div style="font-size:2rem;line-height:1;">🏆</div>
    <div style="flex:1;">
      <div style="font-weight:700;font-size:0.92rem;color:#2d2d2d;margin-bottom:3px;" id="ach-toast-title">Objectif débloqué !</div>
      <div style="font-size:0.8rem;color:#555;" id="ach-toast-msg"></div>
    </div>
    <button onclick="document.getElementById('achievement-toast').style.display='none'"
      style="background:none;border:none;font-size:1rem;color:#bbb;cursor:pointer;padding:0;line-height:1;">✕</button>
  </div>
</div>
<style>
@keyframes slideInLeft { from { transform: translateX(-100px); opacity:0; } to { transform: translateX(0); opacity:1; } }
</style>

<!-- ── ORDER SUCCESS TOAST ── -->
<div id="order-success-toast" style="display:none;position:fixed;bottom:28px;right:28px;z-index:9999;
     background:white;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,0.15);
     padding:20px 24px;max-width:340px;border-left:5px solid #28a745;animation:slideIn 0.3s ease;">
  <div style="display:flex;align-items:flex-start;gap:12px;">
    <div style="font-size:1.8rem;line-height:1;">✅</div>
    <div>
      <div style="font-weight:700;font-size:0.95rem;color:#2d2d2d;margin-bottom:4px;">Order Confirmed!</div>
      <div style="font-size:0.82rem;color:#555;">
        Thank you, <strong id="order-success-name"></strong>!<br>
        Payment: <span id="order-success-method"></span><br>
        Total: <strong style="color:#ce1212;" id="order-success-total"></strong>
      </div>
    </div>
    <button onclick="document.getElementById('order-success-toast').style.display='none'"
      style="background:none;border:none;font-size:1.1rem;color:#999;cursor:pointer;margin-left:auto;padding:0;">✕</button>
  </div>
</div>
<style>
@keyframes slideIn { from { transform: translateX(100px); opacity:0; } to { transform: translateX(0); opacity:1; } }
</style>

<!-- ── PRODUCT DETAIL MODAL ── -->
<div class="modal fade" id="modalProduitDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
      <div class="modal-body p-0">
        <div class="row g-0">
          <!-- Image -->
          <div class="col-md-5">
            <div id="modal-img-wrap" style="height:100%;min-height:320px;background:#f5f5f5;position:relative;">
              <img id="modal-img" src="" alt="" style="width:100%;height:100%;object-fit:cover;min-height:320px;display:none;">
              <div id="modal-img-placeholder" style="width:100%;height:100%;min-height:320px;display:flex;align-items:center;justify-content:center;font-size:4rem;color:#ccc;">
                <i class="bi bi-image"></i>
              </div>
              <span id="modal-badge" style="position:absolute;top:14px;left:14px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
            </div>
          </div>
          <!-- Details -->
          <div class="col-md-7 p-4 d-flex flex-column" style="max-height:90vh;overflow-y:auto;">
            <button type="button" class="btn-close ms-auto mb-2" data-bs-dismiss="modal"></button>
            <h4 id="modal-nom" style="font-weight:700;color:#2d2d2d;margin-bottom:6px;font-size:1.1rem;"></h4>
            <div id="modal-desc-wrap" style="margin-bottom:14px;"></div>

            <!-- Info rows -->
            <div style="display:flex;flex-direction:column;gap:0;margin-bottom:16px;">
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5;">
                <i class="bi bi-tag-fill" style="color:#ce1212;width:18px;"></i>
                <span style="font-size:0.78rem;color:#999;min-width:90px;text-transform:uppercase;letter-spacing:0.5px;">Category</span>
                <strong id="modal-cat" style="font-size:0.85rem;color:#333;"></strong>
              </div>
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5;">
                <i class="bi bi-calendar3" style="color:#ce1212;width:18px;"></i>
                <span style="font-size:0.78rem;color:#999;min-width:90px;text-transform:uppercase;letter-spacing:0.5px;">Expiration</span>
                <strong id="modal-exp" style="font-size:0.85rem;color:#333;"></strong>
              </div>
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5;">
                <i class="bi bi-check-circle-fill" style="color:#ce1212;width:18px;"></i>
                <span style="font-size:0.78rem;color:#999;min-width:90px;text-transform:uppercase;letter-spacing:0.5px;">Status</span>
                <span id="modal-statut" style="font-size:0.82rem;font-weight:600;padding:3px 12px;border-radius:20px;"></span>
              </div>
            </div>

            <!-- Nutrition -->
            <div style="background:#f9f9f9;border-radius:12px;padding:14px;margin-bottom:16px;">
              <div style="font-size:0.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:10px;">
                <i class="bi bi-lightning-charge-fill" style="color:#ce1212;"></i> Nutrition (per serving)
              </div>
              <div class="row g-2 text-center">
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#ce1212;" id="modal-cal">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Calories</div>
                  </div>
                </div>
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#1a73e8;" id="modal-prot">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Protein</div>
                  </div>
                </div>
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#f57f17;" id="modal-carb">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Carbs</div>
                  </div>
                </div>
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#2e7d32;" id="modal-fat">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Fat</div>
                  </div>
                </div>
              </div>
            </div>

            <div id="modal-price" style="font-size:1.8rem;font-weight:700;color:#ce1212;margin-bottom:16px;"></div>

            <!-- ── AVIS SECTION ── -->
            <div style="border-top:1px solid #f0f0f0;padding-top:14px;margin-bottom:16px;">
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-star-fill" style="color:#f39c12;"></i> Customer Reviews
                <span id="modal-avg-stars" style="color:#f39c12;font-size:0.9rem;margin-left:4px;"></span>
                <span id="modal-avg-text" style="font-size:0.72rem;color:#bbb;font-weight:400;text-transform:none;letter-spacing:0;"></span>
              </div>
              <!-- Existing reviews -->
              <div id="modal-avis-list" style="max-height:160px;overflow-y:auto;margin-bottom:12px;"></div>
              <!-- Add review form -->
              <form id="modal-avis-form" style="background:#f9f9f9;border-radius:10px;padding:12px;">
                <input type="hidden" id="modal-avis-produit-id" name="id_produit" value="">
                <input type="hidden" name="submit_avis" value="1">
                <div style="margin-bottom:8px;">
                  <div id="modal-star-picker" style="display:flex;gap:4px;cursor:pointer;">
                    <span class="mstar" data-val="1" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="2" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="3" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="4" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="5" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                  </div>
                  <input type="hidden" id="modal-note-value" name="note" value="">
                  <div style="font-size:0.7rem;color:#bbb;margin-top:2px;" id="modal-star-hint">Click to rate</div>
                </div>
                <textarea name="commentaire" id="modal-avis-comment" rows="2"
                  style="border-radius:6px;border:1px solid #e0e0e0;padding:8px 12px;font-size:0.82rem;width:100%;outline:none;resize:none;font-family:'Inter',sans-serif;margin-bottom:8px;"
                  placeholder="Your comment..."></textarea>
                <button type="submit" id="modal-avis-submit"
                  style="background:#ce1212;color:white;border:none;border-radius:20px;padding:7px 20px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                  <i class="bi bi-send me-1"></i>Submit
                </button>
                <span id="modal-avis-success" style="display:none;font-size:0.8rem;color:#2e7d32;margin-left:10px;">
                  <i class="bi bi-check-circle-fill me-1"></i>Thank you for your review!
                </span>
              </form>
            </div>

            <div class="mt-auto d-flex gap-2">
              <button id="modal-btn-cart" class="btn-cart" style="flex:1;">
                <i class="bi bi-cart-plus me-2"></i>Add to Cart
              </button>
              <button class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:25px;padding:9px 20px;font-size:13px;">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function openProductModal(id) {
  var data = document.querySelector('.product-data[data-id="'+id+'"]');
  if (!data) return;

  // Set product id for avis form and load reviews
  document.getElementById('modal-avis-produit-id').value = id;
  document.getElementById('modal-avis-success').style.display = 'none';
  document.getElementById('modal-avis-comment').value = '';
  resetModalStars();
  loadModalAvis(id);

  var nom      = data.dataset.nom;
  var desc     = data.dataset.desc;
  var prix     = parseFloat(data.dataset.prix);
  var stock    = parseInt(data.dataset.stock);
  var exp      = data.dataset.expiration;
  var cat      = data.dataset.categorie;
  var statut   = data.dataset.statut;
  var image    = data.dataset.image;
  var cal      = data.dataset.calories;
  var prot     = data.dataset.proteines;
  var carb     = data.dataset.glucides;
  var fat      = data.dataset.lipides;

  document.getElementById('modal-nom').textContent   = nom;

  // Description — adapt for Meal Prep category
  var isMealPrep = (cat.toLowerCase().indexOf('meal prep') !== -1);
  var cleanDesc = desc.replace(/\s*—\s*(repas|breakfast|lunch|dinner|préparez|dégustez).*/i, '');
  var descWrap = document.getElementById('modal-desc-wrap');
  if (isMealPrep) {
    descWrap.innerHTML =
      '<div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#ce1212;margin-bottom:4px;">' +
      '<i class="bi bi-basket2-fill me-1"></i>Ingredients included:</div>' +
      '<p style="font-size:0.85rem;color:#888;margin:0;">' + cleanDesc + '</p>';
  } else {
    descWrap.innerHTML = '<p style="font-size:0.85rem;color:#888;margin:0;">' + desc + '</p>';
  }

  document.getElementById('modal-cat').textContent   = cat;
  document.getElementById('modal-exp').textContent   = exp || '—';
  document.getElementById('modal-price').textContent = prix.toFixed(2).replace('.', ',') + ' DT';

  // Nutrition
  document.getElementById('modal-cal').textContent  = cal  ? cal + ' kcal' : '—';
  document.getElementById('modal-prot').textContent = prot ? prot + 'g'    : '—';
  document.getElementById('modal-carb').textContent = carb ? carb + 'g'    : '—';
  document.getElementById('modal-fat').textContent  = fat  ? fat + 'g'     : '—';

  // Status badge
  var badge = document.getElementById('modal-statut');
  badge.textContent = statut;
  badge.style.background = statut==='Available' ? '#e8f5e9' : statut==='Out of Stock' ? '#fdecea' : '#f5f5f5';
  badge.style.color      = statut==='Available' ? '#2e7d32' : statut==='Out of Stock' ? '#c62828' : '#757575';

  // Translate status for display
  var statutLabel = statut === 'Available' ? 'Available' : statut === 'Out of Stock' ? 'Out of Stock' : 'Expired';
  badge.textContent = statutLabel;

  // Low stock indicator in modal
  var existingLowStock = document.getElementById('modal-low-stock');
  if (existingLowStock) existingLowStock.remove();
  if (stock > 0 && stock <= 5) {
    var lowStockEl = document.createElement('div');
    lowStockEl.id = 'modal-low-stock';
    lowStockEl.style.cssText = 'display:flex;align-items:center;gap:6px;background:#fff8e1;border:1px solid #ffc107;border-radius:8px;padding:7px 12px;margin-bottom:12px;';
    lowStockEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill" style="color:#f57c00;font-size:0.9rem;flex-shrink:0;"></i>'
      + '<span style="font-size:0.82rem;font-weight:700;color:#e65100;">Only ' + stock + ' left in stock!</span>';
    var priceEl = document.getElementById('modal-price');
    priceEl.parentNode.insertBefore(lowStockEl, priceEl);
  }

  // Image badge
  var imgBadge = document.getElementById('modal-badge');
  imgBadge.textContent = statutLabel;
  imgBadge.style.background = statut==='Available' ? '#28a745' : statut==='Out of Stock' ? '#dc3545' : '#6c757d';
  imgBadge.style.color = 'white';

  // Image
  var img = document.getElementById('modal-img');
  var placeholder = document.getElementById('modal-img-placeholder');
  if (image) { img.src=image; img.style.display='block'; placeholder.style.display='none'; }
  else       { img.style.display='none'; placeholder.style.display='flex'; }

  // Cart button
  var btn = document.getElementById('modal-btn-cart');
  btn.innerHTML = isMealPrep
    ? '<i class="bi bi-basket2 me-2"></i>Get Ingredients'
    : '<i class="bi bi-cart-plus me-2"></i>Add to Cart';
  if (statut === 'Available') {
    btn.disabled = false;
    btn.style.background = '#ce1212';
    btn.onclick = function() {
      var panier = getPanier();
      var ex = panier.find(function(p){ return p.id===String(id); });
      if (ex) { ex.quantite+=1; } else { panier.push({id:String(id),nom:nom,prix:prix,image:image,quantite:1}); }
      savePanier(panier);
      document.getElementById('panier-toast-msg').textContent = '"'+nom+'" added to cart!';
      var t=document.getElementById('panier-toast'); t.style.display='flex';
      setTimeout(function(){ t.style.display='none'; },3000);
      bootstrap.Modal.getInstance(document.getElementById('modalProduitDetail')).hide();
    };
  } else {
    btn.disabled = true;
    btn.style.background = '#ccc';
    btn.innerHTML = '<i class="bi bi-x-circle me-2"></i>Out of Stock';
  }

  new bootstrap.Modal(document.getElementById('modalProduitDetail')).show();
}
</script>

<script>
// ── AVIS AJAX functions ──
function loadModalAvis(produitId) {
  var list     = document.getElementById('modal-avis-list');
  var avgStars = document.getElementById('modal-avg-stars');
  var avgText  = document.getElementById('modal-avg-text');
  list.innerHTML = '<div style="font-size:0.8rem;color:#bbb;padding:6px 0;">Chargement...</div>';

  fetch('get_avis.php?id_produit=' + produitId)
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (!data.avis || data.avis.length === 0) {
        list.innerHTML = '<div style="font-size:0.8rem;color:#bbb;font-style:italic;padding:4px 0;">No reviews yet. Be the first!</div>';
        avgStars.textContent = '';
        avgText.textContent  = '';
        return;
      }
      var avg = parseFloat(data.avg) || 0;
      avgStars.textContent = '★'.repeat(Math.round(avg)) + '☆'.repeat(5 - Math.round(avg));
      avgText.textContent  = avg.toFixed(1) + '/5 (' + data.total + ' review' + (data.total > 1 ? 's' : '') + ')';
      var html = '';
      data.avis.forEach(function(a) {
        var stars = '★'.repeat(parseInt(a.note)) + '☆'.repeat(5 - parseInt(a.note));
        html += '<div style="background:white;border-radius:8px;padding:10px 12px;margin-bottom:8px;border:1px solid #f0f0f0;">';
        html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">';
        html += '<span style="color:#f39c12;font-size:0.95rem;letter-spacing:1px;">' + stars + '</span>';
        html += '<span style="font-size:0.7rem;color:#bbb;">' + a.date_avis + '</span>';
        html += '</div>';
        html += '<p style="font-size:0.82rem;color:#555;margin:0;line-height:1.5;">' + escHtml(a.commentaire) + '</p>';
        html += '</div>';
      });
      list.innerHTML = html;
    })
    .catch(function() {
      list.innerHTML = '<div style="font-size:0.8rem;color:#bbb;">Could not load reviews.</div>';
    });
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Star picker JS ──
var currentNote = 0;
function resetModalStars() {
  currentNote = 0;
  document.querySelectorAll('.mstar').forEach(function(s){ s.style.color = '#ddd'; });
  document.getElementById('modal-star-hint').textContent = 'Click to rate';
}
document.querySelectorAll('.mstar').forEach(function(star) {
  star.addEventListener('mouseover', function() {
    var val = parseInt(this.dataset.val);
    document.querySelectorAll('.mstar').forEach(function(s){
      s.style.color = parseInt(s.dataset.val) <= val ? '#f39c12' : '#ddd';
    });
  });
  star.addEventListener('mouseout', function() {
    document.querySelectorAll('.mstar').forEach(function(s){
      s.style.color = parseInt(s.dataset.val) <= currentNote ? '#f39c12' : '#ddd';
    });
  });
  star.addEventListener('click', function() {
    currentNote = parseInt(this.dataset.val);
    document.getElementById('modal-note-value').value = currentNote;
    document.getElementById('modal-star-hint').textContent = currentNote + ' / 5';
    document.querySelectorAll('.mstar').forEach(function(s){
      s.style.color = parseInt(s.dataset.val) <= currentNote ? '#f39c12' : '#ddd';
    });
  });
});

document.getElementById('modal-avis-form').addEventListener('submit', function(e) {
  e.preventDefault();
  var note        = document.getElementById('modal-note-value').value;
  var commentaire = document.getElementById('modal-avis-comment').value.trim();
  var id_produit  = document.getElementById('modal-avis-produit-id').value;
  if (!note || !commentaire || !id_produit) return;

  var formData = new FormData();
  formData.append('note', note);
  formData.append('commentaire', commentaire);
  formData.append('id_produit', id_produit);

  fetch('submit_avis.php', { method: 'POST', body: formData })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success) {
        document.getElementById('modal-avis-success').style.display = 'inline';
        document.getElementById('modal-avis-comment').value = '';
        document.getElementById('modal-note-value').value = '';
        resetModalStars();
        loadModalAvis(id_produit);
        setTimeout(function(){ document.getElementById('modal-avis-success').style.display = 'none'; }, 3000);
      }
    });
});
</script>

<?php
// ── Handle avis submission ──
require_once __DIR__ . '/../../controller/AvisController.php';
$avisController = new AvisController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_avis'])) {
    $note        = (int)($_POST['note'] ?? 0);
    $commentaire = trim($_POST['commentaire'] ?? '');
    $id_produit  = (int)($_POST['id_produit'] ?? 0);
    if ($note >= 1 && $note <= 5 && !empty($commentaire) && $id_produit > 0) {
        $newAvis = new Avis(null, $note, $commentaire, date('Y-m-d'), $id_produit);
        $avisController->addAvis($newAvis);
    }
    header('Location: produits.php?avis_ok=' . $id_produit);
    exit;
}
?>

<style>
/* Modal star picker */
.modal-star-picker { display:inline-flex; flex-direction:row-reverse; gap:2px; }
.modal-star-picker input:checked ~ label,
.modal-star-picker label:hover,
.modal-star-picker label:hover ~ label { color:#f39c12; }
</style>

<?php include("footer.php"); ?>
