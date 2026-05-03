# Requirements Document

## Introduction

Cette fonctionnalité ajoute un contrôle de saisie complet sur le champ numéro de téléphone dans le formulaire de commande (panier) de l'interface client (`view/front/produits.php`). Le champ `#co-phone` existe déjà dans la modale de checkout mais ne dispose que d'un filtre de caractères basique (`oninput`) sans validation de format, sans message d'erreur, et sans blocage de soumission. L'objectif est d'ajouter une validation côté client (JavaScript) cohérente avec les patterns déjà utilisés dans le projet (`produit-validation.js`, `categorie-validation.js`), ainsi qu'une validation côté serveur (PHP) dans `send_invoice.php`.

## Glossaire

- **Checkout_Form** : Le formulaire HTML `#checkoutForm` dans la modale `#modalCheckout` de `view/front/produits.php`.
- **Phone_Field** : Le champ `<input type="tel" id="co-phone">` du Checkout_Form.
- **Phone_Validator** : Le module JavaScript de validation du Phone_Field, à créer dans `view/assets/js/panier-validation.js`.
- **Invoice_Handler** : Le script PHP `view/front/send_invoice.php` qui reçoit les données de commande et envoie la facture.
- **Numéro_Tunisien** : Un numéro de téléphone tunisien valide composé de 8 chiffres, pouvant être précédé du préfixe international `+216` ou `00216`.
- **Format_Valide** : Un numéro correspondant au regex `^(\+216|00216)?[2-9]\d{7}$` (8 chiffres, premier chiffre entre 2 et 9).

---

## Requirements

### Requirement 1 : Validation du format du numéro de téléphone côté client

**User Story :** En tant que client, je veux être informé immédiatement si mon numéro de téléphone est invalide, afin de corriger ma saisie avant de soumettre ma commande.

#### Acceptance Criteria

1. WHEN le client soumet le Checkout_Form avec le Phone_Field vide, THE Phone_Validator SHALL afficher le message d'erreur « Le numéro de téléphone est obligatoire. » et empêcher la soumission du formulaire.
2. WHEN le client soumet le Checkout_Form avec un Phone_Field contenant moins de 8 chiffres, THE Phone_Validator SHALL afficher le message d'erreur « Le numéro doit contenir 8 chiffres. » et empêcher la soumission du formulaire.
3. WHEN le client soumet le Checkout_Form avec un Phone_Field ne correspondant pas au Format_Valide, THE Phone_Validator SHALL afficher le message d'erreur « Numéro invalide. Exemple : 20 123 456 ou +216 20 123 456. » et empêcher la soumission du formulaire.
4. WHEN le client soumet le Checkout_Form avec un Phone_Field correspondant au Format_Valide, THE Phone_Validator SHALL autoriser la soumission du formulaire sans afficher d'erreur.
5. WHEN le client modifie la valeur du Phone_Field après une erreur de validation, THE Phone_Validator SHALL effacer le message d'erreur et retirer la classe CSS `is-invalid` du Phone_Field.

### Requirement 2 : Retour visuel sur le champ téléphone

**User Story :** En tant que client, je veux voir un retour visuel clair (rouge/vert) sur le champ téléphone, afin de savoir instantanément si ma saisie est correcte.

#### Acceptance Criteria

1. WHEN le Phone_Validator détecte une erreur sur le Phone_Field, THE Phone_Validator SHALL appliquer la classe CSS Bootstrap `is-invalid` au Phone_Field et afficher un élément `.invalid-feedback` contenant le message d'erreur.
2. WHEN le Phone_Validator valide avec succès le Phone_Field, THE Phone_Validator SHALL appliquer la classe CSS Bootstrap `is-valid` au Phone_Field et retirer tout élément `.invalid-feedback` visible.
3. THE Phone_Validator SHALL réutiliser les fonctions `showError` et `clearError` conformément au pattern établi dans `produit-validation.js` et `categorie-validation.js`.

### Requirement 3 : Validation côté serveur dans Invoice_Handler

**User Story :** En tant qu'administrateur système, je veux que le serveur rejette les numéros de téléphone invalides, afin d'éviter l'enregistrement de données incorrectes même si la validation JavaScript est contournée.

#### Acceptance Criteria

1. WHEN l'Invoice_Handler reçoit une requête POST avec un champ `phone` vide, THE Invoice_Handler SHALL retourner une réponse JSON `{"success": false, "error": "Numéro de téléphone obligatoire."}` avec le code HTTP 422.
2. WHEN l'Invoice_Handler reçoit une requête POST avec un champ `phone` ne correspondant pas au Format_Valide (après suppression des espaces et tirets), THE Invoice_Handler SHALL retourner une réponse JSON `{"success": false, "error": "Numéro de téléphone invalide."}` avec le code HTTP 422.
3. WHEN l'Invoice_Handler reçoit une requête POST avec un champ `phone` correspondant au Format_Valide, THE Invoice_Handler SHALL traiter la commande normalement et inclure le numéro dans la facture email.
4. THE Invoice_Handler SHALL normaliser le numéro de téléphone en supprimant les espaces et tirets avant d'effectuer la validation regex.

### Requirement 4 : Accessibilité et expérience utilisateur

**User Story :** En tant que client utilisant un lecteur d'écran ou un appareil mobile, je veux que le champ téléphone soit correctement annoté et utilisable, afin de compléter ma commande sans difficulté.

#### Acceptance Criteria

1. THE Phone_Field SHALL posséder un attribut `aria-describedby` pointant vers l'identifiant de l'élément `.invalid-feedback` associé.
2. THE Phone_Field SHALL posséder un attribut `autocomplete="tel"` pour faciliter la saisie sur mobile.
3. WHEN le Phone_Validator affiche un message d'erreur, THE Phone_Validator SHALL mettre à jour l'attribut `aria-invalid="true"` sur le Phone_Field.
4. WHEN le Phone_Validator efface une erreur, THE Phone_Validator SHALL mettre à jour l'attribut `aria-invalid="false"` sur le Phone_Field.

### Requirement 5 : Cohérence avec les validations existantes du projet

**User Story :** En tant que développeur, je veux que la validation du téléphone suive les mêmes conventions que les autres validations du projet, afin de maintenir une base de code homogène.

#### Acceptance Criteria

1. THE Phone_Validator SHALL être intégré directement dans le bloc `<script>` existant de `view/front/produits.php`, sans fichier JS externe, suivant le même pattern que la validation de la carte bancaire (fonctions inline, messages d'erreur via `<div id="co-phone-err">`).
2. THE Phone_Validator SHALL utiliser le même style de message d'erreur que les autres champs du formulaire checkout (div avec `id="co-phone-err"`, `font-size:0.75rem`, `color:#dc3545`).
3. THE Phone_Validator SHALL effectuer la validation dans la fonction `confirmerCommande()` existante, avant l'envoi de la commande, en suivant le même pattern que la validation des champs carte bancaire.
4. THE Phone_Validator SHALL ajouter une validation en temps réel sur l'événement `input` du Phone_Field, changeant la couleur de bordure en vert (`#28a745`) ou rouge (`#dc3545`) selon la validité.
