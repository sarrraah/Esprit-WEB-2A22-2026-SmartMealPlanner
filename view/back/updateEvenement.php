<?php
require_once('../controller/EvenementController.php');

$controller = new EvenementController();
$errors     = [];

// ── Charger les données de l'événement à modifier ──────────────────────────
$id       = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$evenement = $controller->getEvenementById($id);

if (!$evenement) {
    header('Location: listEvenements.php');
    exit;
}

// ── Traitement du formulaire de modification ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $evenement->setTitre($titre);
        $evenement->setDescription($description);
        $evenement->setDateDebut($date_debut);
        $evenement->setDateFin($date_fin);
        $evenement->setLieu($lieu);
        $evenement->setCapaciteMax((int)$capacite_max);
        $evenement->setPrix((float)$prix);
        $evenement->setStatut($statut);
        $evenement->setType($type);

        $controller->updateEvenement($evenement);
        header('Location: listEvenements.php?msg=updated');
        exit;
    }
    // En cas d'erreur, on recharge les valeurs postées
    $evenement->setTitre($_POST['titre']);
    $evenement->setDescription($_POST['description']);
    $evenement->setDateDebut($_POST['date_debut']);
    $evenement->setDateFin($_POST['date_fin']);
    $evenement->setLieu($_POST['lieu']);
    $evenement->setCapaciteMax($_POST['capacite_max']);
    $evenement->setPrix($_POST['prix']);
    $evenement->setStatut($_POST['statut']);
    $evenement->setType($_POST['type']);
}

// Formater les dates pour datetime-local input
$dd = date('Y-m-d\TH:i', strtotime($evenement->getDateDebut()));
$df = date('Y-m-d\TH:i', strtotime($evenement->getDateFin()));

include 'header.php';
?>

<div class="container">
    <div class="card">
        <h2>✏️ Modifier l'Événement #<?= $id ?></h2>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="titre" required minlength="3"
                           value="<?= htmlspecialchars($evenement->getTitre()) ?>">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <input type="text" name="type"
                           value="<?= htmlspecialchars($evenement->getType()) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($evenement->getDescription()) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date de Début *</label>
                    <input type="datetime-local" name="date_debut" required value="<?= $dd ?>">
                </div>
                <div class="form-group">
                    <label>Date de Fin *</label>
                    <input type="datetime-local" name="date_fin" required value="<?= $df ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Lieu</label>
                    <input type="text" name="lieu"
                           value="<?= htmlspecialchars($evenement->getLieu()) ?>">
                </div>
                <div class="form-group">
                    <label>Capacité Maximale *</label>
                    <input type="number" name="capacite_max" min="1"
                           value="<?= htmlspecialchars($evenement->getCapaciteMax()) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Prix (TND) *</label>
                    <input type="number" name="prix" min="0" step="0.01"
                           value="<?= htmlspecialchars($evenement->getPrix()) ?>" required>
                </div>
                <div class="form-group">
                    <label>Statut *</label>
                    <select name="statut" required>
                        <option value="actif"   <?= $evenement->getStatut() === 'actif'   ? 'selected' : '' ?>>Actif</option>
                        <option value="annulé"  <?= $evenement->getStatut() === 'annulé'  ? 'selected' : '' ?>>Annulé</option>
                        <option value="terminé" <?= $evenement->getStatut() === 'terminé' ? 'selected' : '' ?>>Terminé</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-warning" style="padding:10px 28px;font-size:1rem;">
                💾 Enregistrer les modifications
            </button>
            <a href="listEvenements.php" class="btn btn-primary"
               style="padding:10px 28px;font-size:1rem;margin-left:10px;">
                ← Retour
            </a>
        </form>
    </div>
</div>
</body>
</html>
