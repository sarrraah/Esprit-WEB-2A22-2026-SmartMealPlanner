<?php
require_once __DIR__ . '/../../model/Database.php';

$pdo = Database::getConnection();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?")->execute([$id]);
    header('Location: listCommentaires.php?deleted=1');
    exit;
}

// Fetch all comments with event title
$id_event_filter = (int)($_GET['id_event'] ?? 0);
if ($id_event_filter) {
    $stmt = $pdo->prepare(
        "SELECT c.id_commentaire AS id, c.auteur, c.contenu, c.date_commentaire AS created_at, e.titre
         FROM commentaire c
         JOIN evenement e ON e.id_event = c.id_event
         WHERE c.id_event = ?
         ORDER BY c.date_commentaire DESC"
    );
    $stmt->execute([$id_event_filter]);
} else {
    $stmt = $pdo->query(
        "SELECT c.id_commentaire AS id, c.auteur, c.contenu, c.date_commentaire AS created_at, e.titre
         FROM commentaire c
         JOIN evenement e ON e.id_event = c.id_event
         ORDER BY c.date_commentaire DESC"
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh;display:flex;}
.admin-shell{display:flex;width:100%;min-height:100vh;}
.main-area{flex:1;padding:32px;overflow-y:auto;}
.container{max-width:1000px;margin:0 auto;}
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
<div class="admin-shell">
  <?php include 'sidebar.php'; ?>
  <main class="main-area">
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
  </main>
</div>
</body>
</html>
