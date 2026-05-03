# Stock Validation Panier — Tasks

## Tasks

- [x] 1. Corriger `update_stock.php` — validation côté serveur
  - [x] 1.1 Ajouter la vérification `qty > stockDisponible` avant la mise à jour
  - [x] 1.2 Retourner `success: false` avec `stockErrors` (id, nom, stockDisponible, quantiteDemandee) en cas de dépassement
  - [x] 1.3 Ne pas modifier le stock si la quantité dépasse le disponible
  - [x] 1.4 Supprimer le `max(0, ...)` et utiliser une décrémentation directe pour les cas valides

- [x] 2. Corriger `confirmerCommande()` dans `produits.php` — gestion de la réponse serveur
  - [x] 2.1 Transformer l'appel `update_stock.php` pour attendre la réponse avant de vider le panier
  - [x] 2.2 Afficher un message d'erreur "Il ne reste que X en stock" si `success: false`
  - [x] 2.3 Proposer deux boutons dans le message d'erreur : "Ajuster la quantité" et "Annuler"
  - [x] 2.4 L'action "Ajuster la quantité" met à jour le panier avec la quantité disponible et rouvre le panier
  - [x] 2.5 Ne vider le panier et afficher le toast de succès que si `success: true`

- [x] 3. Ajouter l'indicateur visuel "Only X left in stock" dans `produits.php`
  - [x] 3.1 Dans la boucle PHP de rendu des cartes, détecter `quantiteStock > 0 && quantiteStock <= 5`
  - [x] 3.2 Afficher un badge orange/ambre "Only X left in stock" sur la carte produit concernée
