<?php
require_once __DIR__ . '/../../controller/EvenementController.php';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Validation ─────────────────────────────────────────────────────────
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $date_debut   = $_POST['date_debut'] ?? '';
    $date_fin     = $_POST['date_fin'] ?? '';
    $lieu         = trim($_POST['lieu'] ?? '');
    $capacite_max = $_POST['capacite_max'] ?? '';
    $prix         = $_POST['prix'] ?? '';
    $statut       = $_POST['statut'] ?? '';
    $type         = trim($_POST['type'] ?? '');

    if (strlen($titre) < 3)
        $errors[] = "Le titre doit contenir au moins 3 caractères.";
    if (empty($date_debut))
        $errors[] = "La date de début est obligatoire.";
    if (empty($date_fin))
        $errors[] = "La date de fin est obligatoire.";
    if (!empty($date_debut) && !empty($date_fin) && $date_fin <= $date_debut)
        $errors[] = "La date de fin doit être postérieure à la date de début.";
    if (!is_numeric($capacite_max) || (int)$capacite_max < 1)
        $errors[] = "La capacité maximale doit être un entier positif (≥ 1).";
    if (!is_numeric($prix) || (float)$prix < 0)
        $errors[] = "Le prix doit être un nombre positif.";
    if (!in_array($statut, ['actif', 'annulé', 'terminé']))
        $errors[] = "Veuillez choisir un statut valide.";

    if (empty($errors)) {
        $controller  = new EvenementController();
        $evenement   = new Evenement(
            $titre, $description, $date_debut, $date_fin,
            $lieu, (int)$capacite_max, (float)$prix, $statut, $type
        );
        $controller->addEvenement($evenement);
        header('Location: listEvenements.php?msg=added');
        exit;
    }
}

include 'header.php';
?>

<div class="container">
    <div class="card">
        <h2>➕ Ajouter un Événement</h2>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="titre"
                           placeholder="Entrez le titre de l'événement"
                           value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required minlength="3">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <input type="text" name="type"
                           placeholder="Ex: Conférence, Concert, Atelier..."
                           value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"
                          placeholder="Décrivez l'événement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date de Début *</label>
                    <input type="datetime-local" name="date_debut"
                           value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Date de Fin *</label>
                    <input type="datetime-local" name="date_fin"
                           value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Lieu</label>
                    <input type="text" name="lieu"
                           placeholder="Ex: Tunis, Salle Horizon..."
                           value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Capacité Maximale *</label>
                    <input type="number" name="capacite_max" min="1"
                           placeholder="Ex: 200"
                           value="<?= htmlspecialchars($_POST['capacite_max'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Prix (TND) *</label>
                    <input type="number" name="prix" min="0" step="0.01"
                           placeholder="Ex: 25.00"
                           value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Statut *</label>
                    <select name="statut" required>
                        <option value="">-- Choisir un statut --</option>
                        <option value="actif"    <?= (($_POST['statut'] ?? '') === 'actif')    ? 'selected' : '' ?>>Actif</option>
                        <option value="annulé"   <?= (($_POST['statut'] ?? '') === 'annulé')   ? 'selected' : '' ?>>Annulé</option>
                        <option value="terminé"  <?= (($_POST['statut'] ?? '') === 'terminé')  ? 'selected' : '' ?>>Terminé</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-success" style="padding:10px 28px;font-size:1rem;">
                💾 Enregistrer l'Événement
            </button>
            <a href="listEvenements.php" class="btn btn-primary" style="padding:10px 28px;font-size:1rem;margin-left:10px;">
                ← Retour à la liste
            </a>
        </form>
    </div>
</div>
</body>
</html>
