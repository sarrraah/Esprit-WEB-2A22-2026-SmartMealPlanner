# Tasks: Gestion des Avis — Back-Office

## Implementation Plan

### Task 1: Update `header.php` — sidebar link and topbar title

- [ ] 1.1 Add the "Avis" `<a>` link in the sidebar immediately after the "Categories" link in the Shop section, with icon `bi-chat-square-text` and active-state detection for `afficherAvis.php` and `supprimerAvis.php`
- [ ] 1.2 Add `'afficherAvis.php' => 'Reviews'` and `'supprimerAvis.php' => 'Delete Review'` to the `$titles` array in the topbar section

---

### Task 2: Create `view/back/afficherAvis.php`

- [ ] 2.1 Add PHP header: `require_once` for `config.php` and `AvisController.php`, instantiate controller, call `getAllAvis()`, compute the 4 stat values (total, avg, 5-star count, 1-star count)
- [ ] 2.2 Add PHP filter logic: read `$_GET['produit']`, `$_GET['note']`, `$_GET['q']`; apply `array_filter` with AND logic to produce `$filtered`; build distinct product list for the dropdown
- [ ] 2.3 Define the `renderStars(int $note): string` helper function that returns 5 Bootstrap Icon `<i>` elements (filled/empty)
- [ ] 2.4 Add the `<style>` block with `.dashboard-banner`, `.stat-card`, `.section-card`, `.table`, `.btn-delete` styles matching `afficherCategorie.php`
- [ ] 2.5 Render the dashboard banner (gradient overlay + title "Customer **Reviews**")
- [ ] 2.6 Render the 4 stat cards (total, average note, 5-star count, 1-star count) with colored left borders
- [ ] 2.7 Render the filter row: product `<select>` (populated from distinct products in `$allAvis`), note `<select>` (1–5), text search `<input>`, all as GET form or direct links
- [ ] 2.8 Render the reviews table with columns: #, Product, Note (stars via `renderStars()`), Comment (truncated at 80 chars), Date, Sentiment (conditional on `isset`), Actions (delete button → `supprimerAvis.php?id=`)
- [ ] 2.9 Render the empty-state row when `count($filtered) === 0`
- [ ] 2.10 Include `header.php` at the top and `footer.php` at the bottom

---

### Task 3: Create `view/back/supprimerAvis.php`

- [ ] 3.1 Add PHP header: `require_once` for `config.php` and `AvisController.php`; read and cast `$_GET['id']` to int; call `getAvisById($id)`; redirect to `afficherAvis.php` if null
- [ ] 3.2 Add POST handler: call `deleteAvis($id)` and redirect to `afficherAvis.php`
- [ ] 3.3 Add the `<style>` block with `.confirm-card` styles matching `supprimerCategorie.php`
- [ ] 3.4 Render the confirmation card: warning icon, title, review details (product name, note as stars, truncated comment), POST form with confirm button and cancel link

---

## Verification

After implementing all tasks:

1. Open `afficherAvis.php` — verify stat cards show correct counts, table shows all reviews with star icons, filters work
2. Apply each filter individually — verify only matching rows appear
3. Click delete on a review — verify `supprimerAvis.php` shows the confirmation card with correct details
4. Confirm deletion — verify redirect to list and the review is gone
5. Access `supprimerAvis.php?id=99999` — verify redirect to list (no crash)
6. Check sidebar on any back-office page — verify "Avis" link appears below "Categories" and becomes active on the reviews pages
