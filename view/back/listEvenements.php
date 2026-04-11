<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();

if (isset($_GET['delete'])) {
    $controller->deleteEvenement((int)$_GET['delete']);
    header('Location: listEvenements.php?msg=deleted');
    exit;
}

$evenements = $controller->listEvenements();
$msg        = $_GET['msg'] ?? '';

include 'header.php';
?>

<div class="container">

    <?php if ($msg === 'added'):   ?><div class="alert alert-success">✅ Événement ajouté avec succès.</div><?php endif; ?>
    <?php if ($msg === 'updated'): ?><div class="alert alert-success">✅ Événement modifié avec succès.</div><?php endif; ?>
    <?php if ($msg === 'deleted'): ?><div class="alert alert-danger">🗑️ Événement supprimé.</div><?php endif; ?>

    <div class="card">
        <h2>📋 Liste des Événements</h2>
        <a class="btn btn-success" href="addEvenement.php" style="margin-bottom:16px;display:inline-block;">
            + Nouvel Événement
        </a>

        <?php if (empty($evenements)): ?>
            <p style="color:#888;text-align:center;padding:30px 0;">Aucun événement enregistré.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Lieu</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Capacité</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($evenements as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e->getIdEvent()) ?></td>
                    <td><b><?= htmlspecialchars($e->getTitre()) ?></b></td>
                    <td><?= htmlspecialchars($e->getType()) ?></td>
                    <td><?= htmlspecialchars($e->getLieu()) ?></td>
                    <td><?= htmlspecialchars($e->getDateDebut()) ?></td>
                    <td><?= htmlspecialchars($e->getDateFin()) ?></td>
                    <td><?= htmlspecialchars($e->getCapaciteMax()) ?></td>
                    <td><?= number_format($e->getPrix(), 2) ?> TND</td>
                    <td>
                        <?php
                        $s = strtolower($e->getStatut());
                        $badge = match(true) {
                            str_contains($s,'actif')  => 'badge-active',
                            str_contains($s,'annul')  => 'badge-annule',
                            str_contains($s,'termin') => 'badge-termine',
                            default                   => 'badge-active',
                        };
                        ?>
                        <span class="badge <?= $badge ?>"><?= htmlspecialchars($e->getStatut()) ?></span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a class="btn btn-warning"
                           href="updateEvenement.php?id=<?= $e->getIdEvent() ?>">✏️ Modifier</a>
                        <a class="btn btn-info"
                           href="listParticipations.php?id_event=<?= $e->getIdEvent() ?>">👥 Participants</a>
                        <a class="btn btn-danger"
                           href="listEvenements.php?delete=<?= $e->getIdEvent() ?>"
                           onclick="return confirm('Supprimer cet événement ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>