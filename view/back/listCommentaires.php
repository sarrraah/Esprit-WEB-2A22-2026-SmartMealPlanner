<?php
require_once __DIR__ . '/../../model/Database.php';

$pdo = Database::getConnection();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM commentaire_event WHERE id = ?")->execute([$id]);
    header('Location: listCommentaires.php?deleted=1');
    exit;
}

// Fetch all comments with event title
$id_event_filter = (int)($_GET['id_event'] ?? 0);
if ($id_event_filter) {
    $stmt = $pdo->prepare(
        "SELECT c.*, e.titre FROM commentaire_event c
         JOIN evenement e ON e.id_event = c.id_event
         WHERE c.id_event = ?
         ORDER BY c.created_at DESC"
    );
    $stmt->execute([$id_event_filter]);
} else {
    $stmt = $pdo->query(
        "SELECT c.*, e.titre FROM commentaire_event c
         JOIN evenement e ON e.id_event = c.id_event
         ORDER BY c.created_at DESC"
    );
}
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch events for filter
$events = $pdo->query("SELECT id_event, titre FROM evenement ORDER BY titre")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion Commentaires – Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}
nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}.logo span{color:#b91c1c}
.nav-links a{font-size:14px;color:#9a3535;text-decoration:none;font-weight:500;margin-left:24px}
.nav-links a:hover{color:#b91c1c}
.container{max-width:1000px;margin:0 auto;padding:32px}
h1{font-size:22px;font-weight:600;margin-bottom:24px}
.card{background:#fff;border:1px solid #fde8e8;border-radius:14px;padding:24px;margin-bottom:24px}
.card h2{font-size:15px;font-weight:600;margin-bottom:16px;border-bottom:1px solid #fce8e8;padding-bottom:12px}
.filter-row{display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap}
select,input{border:1px solid #fde8e8;border-radius:8px;padding:8px 12px;font-size:13px;font-family:inherit;outline:none}
select:focus,input:focus{border-color:#b91c1c}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .15s;text-decoration:none}
.btn-primary{background:#b91c1c;color:#fff}.btn-primary:hover{background:#991b1b}
.btn-danger{background:#fce8e8;color:#b91c1c;border:1px solid #f7c1c1}.btn-danger:hover{background:#f7c1c1}
.alert-success{background:#f0fdf4;color:#166534;border:1px solid #86efac;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
table{width:100%;border-collapse:collapse;font-size:13px}
th{text-align:left;padding:10px 12px;background:#fce8e8;color:#7f1d1d;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px}
td{padding:10px 12px;border-bottom:1px solid #fce8e8;vertical-align:top}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fff5f5}
.badge-event{background:#fce8e8;color:#7f1d1d;border:1px solid #f7c1c1;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.comment-text{color:#4a1515;line-height:1.5;max-width:400px}
.date-cell{color:#9a3535;font-size:12px;white-space:nowrap}
.total-badge{background:#b91c1c;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700}
</style>
</head>
<body>
<nav>
  <a href="listEvenements.php" class="logo">Event <span>Admin</span></a>
  <div class="nav-links">
    <a href="listEvenements.php">Événements</a>
    <a href="listParticipations.php">Participations</a>
    <a href="listPromoCodes.php">Codes Promo</a>
    <a href="listCommentaires.php" style="color:#b91c1c">Commentaires</a>
  </div>
</nav>

<div class="container">
  <h1>💬 Gestion des Commentaires
    <span class="total-badge"><?= count($comments) ?></span>
  </h1>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert-success">✅ Commentaire supprimé avec succès.</div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="filter-row">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <select name="id_event" onchange="this.form.submit()">
        <option value="">Tous les événements</option>
        <?php foreach ($events as $ev): ?>
          <option value="<?= $ev['id_event'] ?>" <?= $id_event_filter == $ev['id_event'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($ev['titre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if ($id_event_filter): ?>
        <a href="listCommentaires.php" class="btn btn-primary">Voir tous</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="card">
    <h2>📋 Liste des commentaires</h2>
    <?php if (empty($comments)): ?>
      <p style="color:#9a3535;font-size:13px">Aucun commentaire trouvé.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Événement</th>
          <th>Auteur</th>
          <th>Commentaire</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comments as $c): ?>
        <tr>
          <td style="color:#9a3535"><?= $c['id'] ?></td>
          <td><span class="badge-event"><?= htmlspecialchars($c['titre']) ?></span></td>
          <td><strong><?= htmlspecialchars($c['auteur']) ?></strong></td>
          <td><div class="comment-text"><?= htmlspecialchars($c['contenu']) ?></div></td>
          <td class="date-cell"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Supprimer ce commentaire ?')">
              <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
              <button type="submit" class="btn btn-danger">🗑 Supprimer</button>
            </form>
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
