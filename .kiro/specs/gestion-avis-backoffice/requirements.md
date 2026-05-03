# Requirements: Gestion des Avis — Back-Office

## Introduction

This document defines the functional requirements for the customer review management interface in the SmartMeal admin panel. The feature allows administrators to view, filter, and delete customer reviews (avis) submitted from the front office. It is derived from the technical design in `design.md`.

---

## Requirements

### 1. Sidebar Navigation

#### 1.1 Avis link in sidebar

**User Story**: As an admin, I want a direct link to the reviews management page in the sidebar, so I can access it quickly from any back-office page.

**Acceptance Criteria**:

- [ ] 1.1.1 The sidebar in `header.php` contains an "Avis" link with icon `bi-chat-square-text` positioned immediately after the "Categories" link in the Shop section.
- [ ] 1.1.2 The "Avis" link points to `afficherAvis.php`.
- [ ] 1.1.3 The link has the `active` CSS class when the current page is `afficherAvis.php` or `supprimerAvis.php`.
- [ ] 1.1.4 The topbar title map in `header.php` includes `'afficherAvis.php' => 'Reviews'` and `'supprimerAvis.php' => 'Delete Review'`.

---

### 2. Review List Page (`afficherAvis.php`)

#### 2.1 Data loading

**User Story**: As an admin, I want to see all customer reviews in one place, so I can monitor feedback across all products.

**Acceptance Criteria**:

- [ ] 2.1.1 The page loads all reviews by calling `AvisController::getAllAvis()`, which returns rows joined with `produit.nom AS produit_nom`.
- [ ] 2.1.2 Reviews are displayed in descending order by `date_avis` (most recent first), as returned by the controller.

#### 2.2 Statistics cards

**User Story**: As an admin, I want a quick summary of review metrics at the top of the page, so I can assess overall customer satisfaction at a glance.

**Acceptance Criteria**:

- [ ] 2.2.1 A stat card displays the total number of reviews (`count($allAvis)`).
- [ ] 2.2.2 A stat card displays the average note rounded to 1 decimal place. When there are no reviews, it displays `0`.
- [ ] 2.2.3 A stat card displays the count of 5-star reviews.
- [ ] 2.2.4 A stat card displays the count of 1-star reviews.
- [ ] 2.2.5 Each stat card has a colored left border following the existing pattern (e.g., red for total, blue for average, gold for 5-star, green for 1-star).

#### 2.3 Filters

**User Story**: As an admin, I want to filter reviews by product, star rating, and keyword, so I can quickly find relevant feedback.

**Acceptance Criteria**:

- [ ] 2.3.1 A product dropdown filter is populated with the distinct products that have reviews. Selecting a product shows only reviews for that product (`id_produit` match).
- [ ] 2.3.2 A note dropdown filter (values 1–5) shows only reviews with the selected star rating when chosen.
- [ ] 2.3.3 A text search input filters reviews whose `commentaire` contains the search string (case-insensitive, PHP `stripos`).
- [ ] 2.3.4 Multiple filters are combined with AND logic: only reviews matching all active filters are shown.
- [ ] 2.3.5 When no filter is active (all params absent or zero/empty), all reviews are displayed.
- [ ] 2.3.6 Filters are applied server-side (PHP `array_filter`) on the full dataset returned by `getAllAvis()`.

#### 2.4 Review table

**User Story**: As an admin, I want to see review details in a table, so I can read and act on individual reviews.

**Acceptance Criteria**:

- [ ] 2.4.1 The table has columns: `#` (row index), Product, Note, Comment, Date, Sentiment, Actions.
- [ ] 2.4.2 The Note column renders star icons: exactly `note` filled `bi-star-fill` icons (gold `#f57f17`) and `5 - note` empty `bi-star` icons (grey), never a raw number.
- [ ] 2.4.3 The Comment column truncates long comments (e.g., at 80 characters with `…`) to keep the table readable.
- [ ] 2.4.4 The Sentiment column renders the `sentiment` value if the field is present in the row (`isset($row['sentiment'])`). If the column is absent from the DB schema, the cell renders `—`.
- [ ] 2.4.5 The Product column renders `—` or "Produit supprimé" in muted text when `produit_nom` is null (orphaned review).
- [ ] 2.4.6 The Actions column contains a delete button linking to `supprimerAvis.php?id=<id_avis>` styled as `btn-delete` (red background, trash icon).
- [ ] 2.4.7 When no reviews match the active filters, the table body renders a single "No reviews found" row spanning all columns, with an inbox icon.

---

### 3. Delete Review Page (`supprimerAvis.php`)

#### 3.1 Guard and confirmation

**User Story**: As an admin, I want a confirmation step before deleting a review, so I don't accidentally remove valid feedback.

**Acceptance Criteria**:

- [ ] 3.1.1 When accessed with a valid `?id=<int>`, the page displays a confirmation card showing the product name, note (as stars), and a truncated version of the comment.
- [ ] 3.1.2 When accessed with an invalid, missing, or non-existent ID, the page immediately redirects to `afficherAvis.php` without rendering any content.
- [ ] 3.1.3 The confirmation card contains a "Confirm Delete" submit button (POST form) and a "Cancel" link back to `afficherAvis.php`.

#### 3.2 Deletion

**User Story**: As an admin, I want to permanently delete a review after confirming, so I can remove inappropriate or erroneous feedback.

**Acceptance Criteria**:

- [ ] 3.2.1 On POST, the page calls `AvisController::deleteAvis($id)` with the review ID cast to `(int)`.
- [ ] 3.2.2 After successful deletion, the page redirects to `afficherAvis.php`.
- [ ] 3.2.3 The deleted review no longer appears in the list on `afficherAvis.php` after the redirect.

---

### 4. Visual Style

#### 4.1 Consistent back-office appearance

**User Story**: As an admin, I want the reviews pages to look identical to the rest of the back-office, so the interface feels cohesive.

**Acceptance Criteria**:

- [ ] 4.1.1 All pages use the Raleway font, `#e74c3c` red accent color, and white cards with `box-shadow: 0 2px 10px rgba(0,0,0,0.06)` and `border-radius: 12px`, matching `afficherCategorie.php` and `afficherReclamations.php`.
- [ ] 4.1.2 The dashboard banner uses the same `linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55))` overlay pattern with a background image, matching existing pages.
- [ ] 4.1.3 Table headers use `font-size: 0.7rem`, `letter-spacing: 1.5px`, `text-transform: uppercase`, `color: #999`, matching the existing table style.
- [ ] 4.1.4 The delete confirmation card in `supprimerAvis.php` uses the same `.confirm-card` style as `supprimerCategorie.php` (centered, max-width 500px, large warning icon).

---

## Non-Functional Requirements

- **No new dependencies**: The feature uses only existing libraries and controllers already present in the project.
- **No edit functionality**: Reviews are read-only for admins; only deletion is permitted.
- **XSS prevention**: All user-generated content (commentaire, produit_nom, sentiment) is escaped with `htmlspecialchars()` before rendering.
- **SQL injection prevention**: The delete ID is cast to `(int)` and used in a PDO prepared statement.
