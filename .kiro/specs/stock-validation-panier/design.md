# Stock Validation Panier — Bugfix Design

## Overview

Le bug se situe dans `view/front/update_stock.php` et dans la logique JavaScript de `view/front/produits.php`.

Actuellement, `update_stock.php` décrémente le stock sans vérifier si la quantité commandée dépasse le stock disponible — il utilise `max(0, stock - qty)` ce qui absorbe silencieusement le dépassement. Côté client, `confirmerCommande()` appelle `update_stock.php` en "fire and forget" sans attendre la réponse ni gérer les erreurs de stock.

La correction consiste à :
1. Ajouter une validation de stock dans `update_stock.php` qui retourne une erreur détaillée si `quantite > stockDisponible`
2. Modifier `confirmerCommande()` pour attendre la réponse de `update_stock.php` **avant** de vider le panier et afficher le succès, et afficher un message d'erreur avec les deux options (ajuster ou annuler) en cas de dépassement
3. Ajouter un indicateur visuel "Only X left in stock" sur les cartes produit quand `quantiteStock ≤ 5`

## Glossary

- **Bug_Condition (C)** : La condition qui déclenche le bug — `quantite commandée > stockDisponible`
- **Property (P)** : Le comportement attendu quand C est vraie — la commande est rejetée avec un message d'erreur clair et le stock reste inchangé
- **Preservation** : Les comportements existants qui ne doivent pas changer — commandes valides, badge "Rupture", affichage normal des produits avec stock suffisant
- **update_stock.php** : Endpoint AJAX dans `view/front/update_stock.php` qui décrémente le stock après confirmation de commande
- **confirmerCommande()** : Fonction JavaScript dans `view/front/produits.php` qui soumet le formulaire de commande et appelle `update_stock.php`
- **isBugCondition(X)** : Prédicat qui retourne `true` si `X.quantite > X.stockDisponible`
- **stockDisponible** : Valeur de `quantiteStock` dans la table `produit` au moment de la commande

## Bug Details

### Bug Condition

Le bug se manifeste quand un client soumet une commande avec une quantité supérieure au stock disponible. `update_stock.php` ne vérifie pas le dépassement et utilise `max(0, stock - qty)` pour éviter un stock négatif, ce qui accepte silencieusement la commande et perd la différence.

**Formal Specification:**
```
FUNCTION isBugCondition(X)
  INPUT: X de type OrderItem { id: int, quantite: int }
  OUTPUT: boolean

  stockDisponible ← SELECT quantiteStock FROM produit WHERE id = X.id
  RETURN X.quantite > stockDisponible
END FUNCTION
```

### Examples

- Commande de 4 unités, stock = 3 → commande acceptée, stock mis à 0 (perte de 1 unité) **[BUG]**
- Commande de 10 unités, stock = 2 → commande acceptée, stock mis à 0 (perte de 8 unités) **[BUG]**
- Commande de 3 unités, stock = 3 → commande acceptée, stock mis à 0 **[OK — comportement préservé]**
- Commande de 1 unité, stock = 5 → commande acceptée, stock mis à 4 **[OK — comportement préservé]**
- Commande de 1 unité, stock = 0 → déjà bloqué côté UI (bouton désactivé) **[OK — comportement préservé]**

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Les commandes avec `quantite ≤ stockDisponible` doivent continuer à être acceptées et décrémenter le stock correctement
- Le badge "Rupture" et le bouton désactivé pour les produits à stock = 0 doivent rester inchangés
- Les produits avec un stock suffisant (> 5) ne doivent afficher aucun indicateur de stock limité
- Les commandes multi-articles où tous les articles ont un stock suffisant doivent continuer à fonctionner normalement

**Scope:**
Tous les inputs où `isBugCondition` retourne `false` ne doivent pas être affectés par ce correctif.

## Hypothesized Root Cause

1. **Absence de validation côté serveur** : `update_stock.php` utilise `max(0, stock - qty)` au lieu de vérifier `qty > stock` et retourner une erreur. C'est la cause principale.

2. **Appel "fire and forget" côté client** : `confirmerCommande()` appelle `fetch('update_stock.php', ...)` avec `.catch(function(){})` sans traiter la réponse. Même si le serveur retournait une erreur, le client l'ignorerait.

3. **Ordre des opérations incorrect** : Le panier est vidé et le toast de succès est affiché **avant** que la réponse de `update_stock.php` soit reçue, rendant impossible toute gestion d'erreur.

4. **Absence d'indicateur visuel de stock faible** : La page produits calcule un statut ("Disponible", "Rupture") mais n'affiche pas d'avertissement intermédiaire pour les stocks ≤ 5.

## Correctness Properties

Property 1: Bug Condition — Rejet des commandes en dépassement de stock

_For any_ OrderItem X où `isBugCondition(X)` est vraie (quantite > stockDisponible), le endpoint `update_stock.php` corrigé SHALL retourner `success: false` avec un message d'erreur contenant "Il ne reste que {stockDisponible} en stock", et le stock en base de données SHALL rester inchangé.

**Validates: Requirements 2.1, 2.3**

Property 2: Preservation — Commandes valides non affectées

_For any_ OrderItem X où `isBugCondition(X)` est fausse (quantite ≤ stockDisponible), le endpoint `update_stock.php` corrigé SHALL produire exactement le même résultat que l'original : `success: true` et `stockInDB = stockDisponible - quantite`.

**Validates: Requirements 3.1, 3.4**

## Fix Implementation

### Changes Required

**Fichier 1 : `view/front/update_stock.php`**

**Changements spécifiques :**
1. **Validation de stock** : Avant de décrémenter, vérifier que `qty <= stockDisponible`. Si non, ne pas modifier le stock et retourner `success: false` avec `stockDisponible` et le nom du produit.
2. **Réponse enrichie** : En cas d'erreur, retourner `{ success: false, stockErrors: [{ id, nom, stockDisponible, quantiteDemandee }] }`.
3. **Suppression du `max(0, ...)`** : Remplacer par une mise à jour directe `stockDisponible - qty` (la validation garantit que le résultat est ≥ 0).

**Fichier 2 : `view/front/produits.php`**

**Changements spécifiques :**
1. **`confirmerCommande()` — attendre la réponse** : Transformer l'appel `update_stock.php` en `await fetch(...)` (ou chaîner `.then()`) et ne vider le panier / afficher le succès qu'après confirmation `success: true`.
2. **Gestion des erreurs de stock** : Si la réponse contient `stockErrors`, afficher une modale/alerte avec le message "Il ne reste que X en stock" et proposer deux boutons : "Ajuster la quantité" (met à jour le panier avec la quantité disponible) et "Annuler".
3. **Indicateur visuel stock faible** : Dans la boucle PHP de rendu des cartes produit, ajouter un badge "Only X left in stock" (style orange/ambre) quand `quantiteStock > 0 && quantiteStock <= 5`.

## Testing Strategy

### Validation Approach

La stratégie suit deux phases : d'abord reproduire le bug sur le code non corrigé pour confirmer la cause racine, puis vérifier que le correctif fonctionne et ne régresse pas les comportements existants.

### Exploratory Bug Condition Checking

**Goal** : Reproduire le bug AVANT le correctif pour confirmer l'analyse de cause racine.

**Test Plan** : Envoyer une requête POST à `update_stock.php` avec `quantite > stockDisponible` et observer que la réponse est `success: true` et que le stock est modifié en base.

**Test Cases** :
1. **Dépassement simple** : POST `{items: [{id: X, quantite: stockDisponible + 1}]}` → attend `success: false`, obtient `success: true` (bug confirmé)
2. **Dépassement important** : POST `{items: [{id: X, quantite: stockDisponible * 2}]}` → même observation
3. **Commande valide** : POST `{items: [{id: X, quantite: stockDisponible}]}` → doit retourner `success: true` (comportement de référence)

**Expected Counterexamples** :
- `update_stock.php` retourne `{"success":true,"errors":[]}` même quand `quantite > stockDisponible`
- Le stock en base est mis à 0 au lieu de rester inchangé

### Fix Checking

**Goal** : Vérifier que pour tous les inputs où `isBugCondition` est vraie, le code corrigé produit le comportement attendu.

**Pseudocode :**
```
FOR ALL X WHERE isBugCondition(X) DO
  response ← POST update_stock_fixed(X)
  ASSERT response.success = false
  ASSERT response.stockErrors[0].stockDisponible = stockInitial(X.id)
  ASSERT stockInDB(X.id) = stockInitial(X.id)  // inchangé
END FOR
```

### Preservation Checking

**Goal** : Vérifier que pour tous les inputs où `isBugCondition` est fausse, le code corrigé produit le même résultat que l'original.

**Pseudocode :**
```
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT update_stock_original(X) = update_stock_fixed(X)
  ASSERT stockInDB(X.id) = stockInitial(X.id) - X.quantite
END FOR
```

**Test Cases** :
1. **Commande exacte** : `quantite = stockDisponible` → `success: true`, stock = 0
2. **Commande partielle** : `quantite < stockDisponible` → `success: true`, stock décrémenté correctement
3. **Multi-articles valides** → tous décrémentés correctement

### Unit Tests

- Tester `update_stock.php` avec `quantite > stock` → réponse d'erreur, stock inchangé
- Tester `update_stock.php` avec `quantite = stock` → succès, stock = 0
- Tester `update_stock.php` avec `quantite < stock` → succès, stock décrémenté
- Tester l'affichage du badge "Only X left in stock" pour `quantiteStock ∈ {1, 2, 3, 4, 5}`
- Tester l'absence du badge pour `quantiteStock > 5`

### Property-Based Tests

- Générer des quantités aléatoires `q ∈ [1, 100]` et un stock `s ∈ [0, 50]` : si `q > s`, la réponse doit être une erreur ; si `q ≤ s`, le stock doit être `s - q`
- Générer des paniers multi-articles aléatoires avec stocks suffisants et vérifier que tous sont décrémentés correctement

### Integration Tests

- Simuler un checkout complet avec dépassement de stock : vérifier que le panier n'est pas vidé et que le message d'erreur s'affiche
- Simuler un checkout complet valide : vérifier que le panier est vidé et le toast de succès s'affiche
- Vérifier que le badge "Only X left in stock" apparaît sur la page produits pour les articles à stock ≤ 5
