# Document de Requirements — Correction de Bug

## Introduction

Lors du passage d'une commande, le système accepte des quantités supérieures au stock disponible sans retourner d'erreur. Par exemple, si un produit n'a que 3 unités en stock et qu'un client en commande 4, la commande est validée silencieusement et le stock est ramené à 0 (au lieu de bloquer la commande). De plus, la page produits n'affiche aucun indicateur visuel lorsque le stock est faible, ce qui ne permet pas au client d'anticiper la situation.

Ce bug impacte l'intégrité des stocks et la confiance des clients.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN un client soumet une commande avec une quantité supérieure au stock disponible (ex : commande de 4 unités, stock = 3) THEN le système accepte la commande sans erreur et décrémente le stock à 0 (perte silencieuse de la différence)

1.2 WHEN le stock d'un produit est inférieur à un seuil bas (ex : ≤ 5 unités) THEN le système n'affiche aucun indicateur visuel sur la page produits pour avertir le client

1.3 WHEN un client tente de commander une quantité supérieure au stock disponible THEN le système ne propose aucune alternative (ni ajout de la quantité disponible, ni annulation guidée)

### Expected Behavior (Correct)

2.1 WHEN un client soumet une commande avec une quantité supérieure au stock disponible THEN le système SHALL rejeter la commande et retourner un message d'erreur du type "Il ne reste que X en stock" sans modifier le stock

2.2 WHEN le stock d'un produit est inférieur ou égal à un seuil bas (ex : ≤ 5 unités) THEN le système SHALL afficher un indicateur visuel sur la page produits du type "Only X left in stock"

2.3 WHEN le système détecte une quantité commandée supérieure au stock disponible THEN le système SHALL proposer au client deux options : ajouter exactement la quantité disponible en stock, ou annuler la commande

### Unchanged Behavior (Regression Prevention)

3.1 WHEN un client soumet une commande avec une quantité inférieure ou égale au stock disponible THEN le système SHALL CONTINUE TO accepter la commande et décrémenter le stock correctement

3.2 WHEN un produit est en rupture de stock (quantiteStock = 0) THEN le système SHALL CONTINUE TO afficher le badge "Rupture" et désactiver le bouton d'ajout au panier

3.3 WHEN un client consulte la page produits pour des articles avec un stock suffisant THEN le système SHALL CONTINUE TO afficher les produits normalement sans indicateur de stock limité

3.4 WHEN plusieurs articles sont commandés simultanément et tous ont un stock suffisant THEN le système SHALL CONTINUE TO traiter la commande complète et mettre à jour tous les stocks correctement

---

## Bug Condition (Pseudocode)

**Condition du bug — identifie les entrées qui déclenchent le bug :**

```pascal
FUNCTION isBugCondition(X)
  INPUT: X de type OrderItem { id: int, quantite: int, stockDisponible: int }
  OUTPUT: boolean

  RETURN X.quantite > X.stockDisponible
END FUNCTION
```

**Propriété — Fix Checking :**

```pascal
// Propriété : vérification de la correction
FOR ALL X WHERE isBugCondition(X) DO
  result ← updateStock'(X)
  ASSERT result.success = false
    AND result.error CONTAINS "Il ne reste que " + X.stockDisponible + " en stock"
    AND stockInDB(X.id) = X.stockDisponible  // stock inchangé
END FOR
```

**Propriété — Preservation Checking :**

```pascal
// Propriété : préservation du comportement existant
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT updateStock(X) = updateStock'(X)
    AND stockInDB(X.id) = X.stockDisponible - X.quantite
END FOR
```
