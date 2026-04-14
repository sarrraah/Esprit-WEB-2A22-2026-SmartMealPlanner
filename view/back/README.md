# Smart Meal Planner - Système de Gestion des Produits

## 📋 Structure des Fichiers

```
view/back/
├── config.php                 # ✅ Connexion PDO MySQL
├── afficherProduit.php        # ✅ Affichage grille responsive avec filtres
├── ajouterProduit.php         # ✅ Formulaire d'ajout complet
├── modifierProduit.php        # ✅ Formulaire de modification avec upload image
├── supprimerProduit.php       # ✅ Suppression produit + photo
├── schema.sql                 # ✅ Script de création table
└── uploads/                   # ✅ Dossier pour stocker les images
```

## 🚀 Installation Rapide

### 1. Importer la base de données

**Option A - Via PhpMyAdmin :**
- Ouvrir PhpMyAdmin (http://localhost/phpmyadmin)
- Sélectionner la base `smart_meal_planner`
- Aller à l'onglet "SQL"
- Copier-coller le contenu de `schema.sql`
- Cliquer sur "Exécuter"

**Option B - Ligne de commande :**
```bash
mysql -u root -p smart_meal_planner < schema.sql
```

### 2. Vérifier les permissions

S'assurer que le dossier `uploads/` a les bonnes permissions (755) :
```bash
chmod 755 uploads/
```

### 3. Accéder à l'application

- **Affichage des produits :** http://localhost/ryhem/view/back/afficherProduit.php
- **Ajouter un produit :** http://localhost/ryhem/view/back/ajouterProduit.php

## 📊 Fonctionnalités

### afficherProduit.php
✅ Grille responsive (3 colonnes sur desktop, adaptée mobile)
✅ Filtres par statut : Tous / Disponible / Rupture / Épuisé
✅ Barre de recherche en temps réel
✅ Cartes produits avec :
  - Image du produit
  - Nom et description
  - Prix en DT
  - Quantité en stock
  - Date d'expiration
  - Statut automatique
✅ Boutons Modifier/Supprimer
✅ Design dégradé violet avec animations
✅ Responsive mobile

### ajouterProduit.php
✅ Formulaire complet avec tous les champs
✅ Upload d'image (JPG, PNG, GIF, WEBP - Max 5 MB)
✅ Prévisualisation de l'image avant upload
✅ Validation des données
✅ Messages d'erreur/succès
✅ Redirection automatique vers la liste

### modifierProduit.php
✅ Formulaire pré-rempli avec les données actuelles
✅ Aperçu de la photo courante
✅ Upload nouvelle image (remplace l'ancienne)
✅ Validation et sauvegarde
✅ Gestion des erreurs

### supprimerProduit.php
✅ Confirmation avant suppression
✅ Affichage des détails du produit
✅ Suppression du produit en BD
✅ Suppression de la photo du serveur
✅ Message de confirmation

## 🗄️ Structure Base de Données

Table : `produit`

| Champ | Type | Description |
|-------|------|-------------|
| `id` | INT | Clé primaire auto-incrémentée |
| `nom` | VARCHAR(255) | Nom du produit |
| `description` | TEXT | Description |
| `prix` | DECIMAL(10,2) | Prix en DT |
| `quantiteStock` | INT | Quantité disponible |
| `dateExpiration` | DATE | Date d'expiration |
| `estDurable` | TINYINT(1) | Produit durable (0/1) |
| `image` | VARCHAR(255) | Nom fichier image |
| `statut` | VARCHAR(50) | Disponible/Rupture/Épuisé |
| `id_categorie` | INT | ID catégorie |
| `created_at` | TIMESTAMP | Date création |
| `updated_at` | TIMESTAMP | Date modification |

## 🎨 Design & Style

- **Gradient violet :** #667eea → #764ba2
- **Ombres élévées :** Hover animations
- **Cartes responsive :** Grid auto-fill minmax(280px)
- **Icônes Font Awesome** 6.4.0
- **Bootstrap 5.3.0** pour la grille

## 🔄 Statut Produit - Logique Automatique

```php
function determinerStatut($quantiteStock, $dateExpiration) {
    if (dateExpiration < aujourd'hui) → "Épuisé" (Rouge)
    elseif (quantiteStock == 0) → "Rupture" (Orange)
    elseif (quantiteStock > 0) → "Disponible" (Vert)
}
```

## ⚙️ Configuration (config.php)

```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''
DB_NAME = 'smart_meal_planner'
UPLOAD_DIR = '/view/back/uploads/'
MAX_FILE_SIZE = 5 MB
ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp']
```

## 📁 Uploads

Les images téléchargées sont sauvegardées avec un timestamp unique :
```
uploads/1713087644_507a3c8_photo.jpg
uploads/1713087698_605b2e1_cuisine.png
```

## ✅ Contrôles & Validation

**Ajout/Modification :**
- ✅ Nom obligatoire
- ✅ Prix > 0
- ✅ Date expiration requise
- ✅ Extension fichier valide
- ✅ Taille max 5 MB

**Suppression :**
- ✅ Confirmation avant suppression
- ✅ Suppression photo serveur
- ✅ Message de confirmation

## 🐛 Dépannage

### Erreur : "Erreur de connexion à la base de données"
→ Vérifier `config.php` (host, user, password, database)

### Erreur : "Permission denied" uploads
→ Exécuter : `chmod 755 uploads/`

### Image non affichée
→ Vérifier chemin dans `config.php` ligne UPLOAD_URL

### Statut non mis à jour
→ Le statut se recalcule automatiquement à chaque affichage

## 📝 Notes

- Les images anciennes sont supprimées lors de la modification
- Le statut est calculé en temps réel (pas stocké en temps réel)
- PDO Protection contre les injections SQL
- htmlspecialchars() utilisé partout pour la sécurité

---

**Version:** 1.0
**Dernière mise à jour:** 14/04/2026
**Framework:** PHP 7.4+ / MySQL 5.7+
