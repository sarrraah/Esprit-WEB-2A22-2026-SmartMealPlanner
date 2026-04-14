<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner - Planifiez vos repas sainement</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
   * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
    
    /* Header & Navigation */
    .navbar {
      background: white;
      box-shadow: 0 2px 20px rgba(0,0,0,0.1);
      padding: 1rem 0;
    }
    .navbar-brand {
      font-size: 1.5rem;
      font-weight: 700;
      color: #ff4444 !important;
    }
    .navbar-brand i {
      color: #ff4444;
    }
    .nav-link {
      font-weight: 500;
      color: #333 !important;
      transition: 0.3s;
    }
    .nav-link:hover { color: #ff4444 !important; }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
      color: white;
      padding: 80px 0;
      text-align: center;
    }
    .hero h1 { font-size: 3rem; font-weight: 700; margin-bottom: 20px; }
    .hero p { font-size: 1.2rem; opacity: 0.9; }

    /* Products Section */
    .products-section {
      padding: 60px 0;
    }
    .section-title {
      text-align: center;
      margin-bottom: 50px;
    }
    .section-title h2 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #333;
    }
    .section-title p {
      color: #ff4444;
      font-size: 1.1rem;
    }
    
    /* Product Card */
    .product-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      transition: transform 0.3s, box-shadow 0.3s;
      margin-bottom: 30px;
      cursor: pointer;
    }
    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    .product-img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }
    .product-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    .badge-disponible { background: #28a745; color: white; }
    .badge-rupture { background: #dc3545; color: white; }
    .badge-epuise { background: #ffc107; color: #333; }
    
    .product-info {
      padding: 20px;
    }
    .product-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 10px;
      color: #333;
    }
    .product-description {
      font-size: 0.85rem;
      color: #666;
      margin-bottom: 15px;
      line-height: 1.4;
    }
    .product-price {
      font-size: 1.5rem;
      font-weight: 700;
      color: #ff4444;
      margin-bottom: 10px;
    }
    .product-stock {
      font-size: 0.8rem;
      color: #666;
      margin-bottom: 15px;
    }
    .product-expiry {
      font-size: 0.75rem;
      color: #ff6b6b;
      margin-bottom: 15px;
    }
    .btn-add-cart {
      background: #ff4444;
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 25px;
      font-weight: 500;
      transition: 0.3s;
      width: 100%;
    }
    .btn-add-cart:hover {
      background: #cc0000;
      transform: scale(1.05);
    }
    .btn-add-cart:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    
    /* Filters */
    .filters-section {
      background: white;
      padding: 20px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .filter-btn {
      margin: 5px;
      padding: 8px 20px;
      border-radius: 25px;
      border: 1px solid #ddd;
      background: white;
      transition: 0.3s;
    }
    .filter-btn.active, .filter-btn:hover {
      background: #ff4444;
      color: white;
      border-color: #ff4444;
    }
    
    /* Search button red */
    .search-btn-red {
      background-color: #ff4444;
      border-color: #ff4444;
      color: white;
    }
    .search-btn-red:hover {
      background-color: #cc0000;
      border-color: #cc0000;
    }
    
    /* Modal */
    .modal-product-img {
      width: 100%;
      border-radius: 10px;
    }
    .modal-price {
      font-size: 2rem;
      color: #ff4444;
      font-weight: 700;
    }
    
    /* Cart Sidebar */
    .cart-sidebar {
      position: fixed;
      right: -400px;
      top: 0;
      width: 400px;
      height: 100vh;
      background: white;
      box-shadow: -2px 0 20px rgba(0,0,0,0.1);
      z-index: 1050;
      transition: 0.3s;
      padding: 20px;
      overflow-y: auto;
    }
    .cart-sidebar.open {
      right: 0;
    }
    .cart-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1040;
      display: none;
    }
    .cart-overlay.open {
      display: block;
    }
    .cart-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }
    .cart-total {
      font-size: 1.3rem;
      font-weight: 700;
      margin: 20px 0;
    }
    
    footer {
      background: #2c3e50;
      color: white;
      padding: 40px 0;
      margin-top: 60px;
    }
    
    @media (max-width: 768px) {
      .cart-sidebar { width: 100%; right: -100%; }
    }
  </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#">
      <i class="fas fa-utensils me-2"></i>Smart Meal Planner
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#home">Accueil</a></li>
  
        <li class="nav-item"><a class="nav-link" href="#about">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
      </ul>
      <button class="btn btn-outline-danger ms-3" onclick="toggleCart()">
        <i class="fas fa-shopping-cart"></i> 
        <span id="cartCount">0</span>
      </button>
    </div>
  </div>
</nav>



<!-- Products Section -->
<section id="products" class="products-section">
  <div class="container">
    <div class="section-title">
      <h2>Nos Produits</h2>
      <p>Qualité et fraîcheur garanties</p>
    </div>
    
    <!-- Filters -->
    <div class="filters-section">
      <div class="row align-items-center">
        <div class="col-md-6">
          <strong>Filtrer par statut :</strong>
          <button class="filter-btn active" data-filter="all">Tous</button>
          <button class="filter-btn" data-filter="disponible">Disponible</button>
          <button class="filter-btn" data-filter="rupture">Rupture</button>
          <button class="filter-btn" data-filter="epuise">Épuisé</button>
        </div>
        <div class="col-md-6">
          <div class="input-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un produit...">
            <button class="btn search-btn-red" onclick="searchProducts()">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Products Grid -->
    <div class="row" id="productsContainer"></div>
  </div>
</section>

<!-- About Section -->
<section id="about" class="about section py-5">
  <div class="container">
    <div class="about-card p-4 rounded-4 shadow-sm bg-white">
      <div class="row align-items-center gy-4">
        <div class="col-lg-6">
          <h2>À propos de Smart Meal Planner</h2>
          <p class="mb-4">Smart Meal Planner est votre partenaire santé pour une alimentation équilibrée et savoureuse. Nous vous proposons des produits frais, de saison et de qualité supérieure.</p>
          <div class="row g-3">
            <div class="col-6">
              <div class="icon-box d-flex align-items-start gap-3">
                <i class="fas fa-leaf fa-2x icon-gradient"></i>
                <div>
                  <h5>Frais et de saison</h5>
                  <p class="mb-0">Produits soigneusement sélectionnés.</p>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="icon-box d-flex align-items-start gap-3">
                <i class="fas fa-utensils fa-2x icon-gradient"></i>
                <div>
                  <h5>Plans de repas</h5>
                  <p class="mb-0">Menus simples et personnalisés.</p>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="icon-box d-flex align-items-start gap-3">
                <i class="fas fa-shipping-fast fa-2x icon-gradient"></i>
                <div>
                  <h5>Livraison rapide</h5>
                  <p class="mb-0">Directement à domicile.</p>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="icon-box d-flex align-items-start gap-3">
                <i class="fas fa-heartbeat fa-2x icon-gradient"></i>
                <div>
                  <h5>Conseils nutritionnels</h5>
                  <p class="mb-0">Pour mieux manger chaque jour.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="about-image rounded-4 overflow-hidden shadow-sm">
            <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=900" class="img-fluid" alt="Smart Meal Planner">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5">
  <div class="container">
    <div class="section-title">
      <h2>Contactez-nous</h2>
      <p>Nous sommes à votre écoute</p>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="text-center mb-4">
          <i class="fas fa-map-marker-alt fa-3x text-danger mb-3"></i>
          <h5>Adresse</h5>
          <p>Esprit Ghazela, Tunis, Tunisie</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="text-center mb-4">
          <i class="fas fa-phone fa-3x text-danger mb-3"></i>
          <h5>Téléphone</h5>
          <p>+216 50547135</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="text-center mb-4">
          <i class="fas fa-envelope fa-3x text-danger mb-3"></i>
          <h5>Email</h5>
          <p>contact@smartmealplanner.tn</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Cart Sidebar -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-sidebar" id="cartSidebar">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="fas fa-shopping-cart"></i> Mon Panier</h4>
    <button class="btn-close" onclick="toggleCart()"></button>
  </div>
  <div id="cartItems"></div>
  <div class="cart-total" id="cartTotal">Total: 0.00 DT</div>
  <button class="btn btn-danger w-100" onclick="checkout()">Passer la commande</button>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <img id="modalImage" class="modal-product-img" alt="">
          </div>
          <div class="col-md-6">
            <p id="modalDescription"></p>
            <p><strong>Prix:</strong> <span id="modalPrice" class="modal-price"></span></p>
            <p><strong>Stock:</strong> <span id="modalStock"></span></p>
            <p><strong>Date d'expiration:</strong> <span id="modalExpiry"></span></p>
            <p><strong>Statut:</strong> <span id="modalStatus"></span></p>
            <button id="modalAddBtn" class="btn btn-danger mt-3" onclick="addToCartFromModal()">Ajouter au panier</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer>
  <div class="container text-center">
    <p>&copy; 2026 Smart Meal Planner. Tous droits réservés.</p>
    <div>
      <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
      <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php
require_once __DIR__ . '/../../config.php';

$stmt = $pdo->prepare("SELECT id, nom, description, prix, quantiteStock, dateExpiration, image, statut FROM produit ORDER BY id DESC");
$stmt->execute();
$produits = $stmt->fetchAll();

foreach ($produits as &$p) {
    $p['id_produit'] = $p['id'];
    $p['nom_produit'] = $p['nom'];

    if (empty($p['image']) || filter_var($p['image'], FILTER_VALIDATE_URL) === false) {
        $uploadPath = '../back/uploads/' . rawurlencode($p['image']);
        if (!empty($p['image']) && file_exists(__DIR__ . '/../back/uploads/' . $p['image'])) {
            $p['image'] = $uploadPath;
        } else {
            $p['image'] = 'https://via.placeholder.com/300x200?text=Image+non+disponible';
        }
    }

    $statut = mb_strtolower($p['statut'] ?? '');
    $statut = str_replace(['é', 'è', 'ê', 'É', 'È', 'Ê'], 'e', $statut);
    if ($statut === 'disponible') {
        $p['statut'] = 'disponible';
    } elseif ($statut === 'rupture') {
        $p['statut'] = 'rupture';
    } elseif ($statut === 'epuise' || $statut === 'epuise') {
        $p['statut'] = 'epuise';
    } else {
        $p['statut'] = 'disponible';
    }

    $p['quantiteStock'] = (int) $p['quantiteStock'];
    $p['prix'] = (float) $p['prix'];
}
unset($p);
?>
  let products = <?= json_encode($produits, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
  
  let cart = [];
  let currentFilter = "all";
  let currentSearch = "";
  let currentProductId = null;
  
  // Rendu des produits
  function renderProducts() {
    let filtered = products.filter(p => {
      if (currentFilter !== "all" && p.statut !== currentFilter) return false;
      if (currentSearch && !p.nom_produit.toLowerCase().includes(currentSearch.toLowerCase())) return false;
      return true;
    });
    
    const container = document.getElementById('productsContainer');
    if (filtered.length === 0) {
      container.innerHTML = `<div class="col-12 text-center"><p>Aucun produit trouvé</p></div>`;
      return;
    }
    
    container.innerHTML = filtered.map(p => `
      <div class="col-md-4 col-lg-3">
        <div class="product-card" onclick="showProductDetails(${p.id_produit})">
          <div style="position: relative;">
            <img src="${p.image}" class="product-img" alt="${p.nom_produit}">
            <span class="product-badge ${getBadgeClass(p.statut)}">${getStatusText(p.statut)}</span>
          </div>
          <div class="product-info">
            <h5 class="product-title">${p.nom_produit}</h5>
            <p class="product-description">${p.description.substring(0, 60)}...</p>
            <div class="product-price">${p.prix.toFixed(2)} DT</div>
            <div class="product-stock">Stock: ${p.quantiteStock} unités</div>
            <div class="product-expiry">Exp: ${formatDate(p.dateExpiration)}</div>
            <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart(${p.id_produit})" ${p.quantiteStock === 0 ? 'disabled' : ''}>
              <i class="fas fa-shopping-cart"></i> Ajouter
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }
  
  function getBadgeClass(statut) {
    switch(statut) {
      case 'disponible': return 'badge-disponible';
      case 'rupture': return 'badge-rupture';
      case 'epuise': return 'badge-epuise';
      default: return '';
    }
  }
  
  function getStatusText(statut) {
    switch(statut) {
      case 'disponible': return 'Disponible';
      case 'rupture': return 'Rupture';
      case 'epuise': return 'Épuisé';
      default: return '';
    }
  }
  
  function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR');
  }
  
  // Panier
  function addToCart(productId) {
    const product = products.find(p => p.id_produit === productId);
    if (!product || product.quantiteStock === 0) return;
    
    const existing = cart.find(item => item.id === productId);
    if (existing) {
      if (existing.quantity < product.quantiteStock) {
        existing.quantity++;
      } else {
        alert("Stock insuffisant !");
        return;
      }
    } else {
      cart.push({ ...product, quantity: 1 });
    }
    
    updateCart();
    showNotification(`${product.nom_produit} ajouté au panier`);
  }
  
  function updateCart() {
    const cartContainer = document.getElementById('cartItems');
    const cartCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartTotal = cart.reduce((sum, item) => sum + (item.prix * item.quantity), 0);
    
    document.getElementById('cartCount').innerText = cartCount;
    document.getElementById('cartTotal').innerHTML = `Total: ${cartTotal.toFixed(2)} DT`;
    
    if (cart.length === 0) {
      cartContainer.innerHTML = '<p class="text-center text-muted">Votre panier est vide</p>';
      return;
    }
    
    cartContainer.innerHTML = cart.map(item => `
      <div class="cart-item">
        <div class="d-flex justify-content-between">
          <div>
            <h6>${item.nom_produit}</h6>
            <small>${item.prix.toFixed(2)} DT x ${item.quantity}</small>
          </div>
          <div>
            <span class="fw-bold">${(item.prix * item.quantity).toFixed(2)} DT</span>
            <button class="btn btn-sm btn-danger ms-2" onclick="removeFromCart(${item.id_produit})">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }
  
  function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
  }
  
  function toggleCart() {
    document.getElementById('cartSidebar').classList.toggle('open');
    document.getElementById('cartOverlay').classList.toggle('open');
  }
  
  function checkout() {
    if (cart.length === 0) {
      alert("Votre panier est vide !");
      return;
    }
    alert(`Commande validée ! Total: ${cart.reduce((s,i)=>s+(i.prix*i.quantity),0).toFixed(2)} DT\nMerci de votre achat !`);
    cart = [];
    updateCart();
    toggleCart();
  }
  
  // Filtres et recherche
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentFilter = this.dataset.filter;
      renderProducts();
    });
  });
  
  function searchProducts() {
    currentSearch = document.getElementById('searchInput').value;
    renderProducts();
  }
  
  document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') searchProducts();
  });
  
  // Modal produit
  let currentModalProduct = null;
  
  function showProductDetails(productId) {
    currentProductId = productId;
    const product = products.find(p => p.id_produit === productId);
    if (!product) return;
    currentModalProduct = product;
    
    document.getElementById('modalTitle').innerText = product.nom_produit;
    document.getElementById('modalImage').src = product.image;
    document.getElementById('modalDescription').innerText = product.description;
    document.getElementById('modalPrice').innerHTML = `${product.prix.toFixed(2)} DT`;
    document.getElementById('modalStock').innerHTML = `${product.quantiteStock} unités disponibles`;
    document.getElementById('modalExpiry').innerHTML = formatDate(product.dateExpiration);
    document.getElementById('modalStatus').innerHTML = getStatusText(product.statut);
    
    const modalBtn = document.getElementById('modalAddBtn');
    if (product.quantiteStock === 0) {
      modalBtn.disabled = true;
      modalBtn.innerHTML = 'Rupture de stock';
    } else {
      modalBtn.disabled = false;
      modalBtn.innerHTML = 'Ajouter au panier';
    }
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
  }
  
  function addToCartFromModal() {
    if (currentProductId) {
      addToCart(currentProductId);
      bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
    }
  }
  
  function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success position-fixed bottom-0 end-0 m-3';
    notification.style.zIndex = '9999';
    notification.innerHTML = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 2000);
  }
  
  // Initialisation
  renderProducts();
</script>
</body>
</html>