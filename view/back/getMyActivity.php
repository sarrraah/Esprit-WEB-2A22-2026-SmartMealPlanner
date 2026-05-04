<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../model/Database.php';

$email = trim($_GET['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Email invalide']);
    exit;
}

try {
    $pdo = Database::getConnection();

    // ── Participations ────────────────────────────────────────────────
    $stmt = $pdo->prepare(
        "SELECT p.id_participation, p.nombre_places_reservees, p.statut, p.date_participation,
                e.titre, e.lieu, e.date_debut, e.type, e.image, e.prix
         FROM participation p
         JOIN evenement e ON e.id_event = p.id_event
         WHERE p.email = ?
         ORDER BY p.date_participation DESC"
    );
    $stmt->execute([$email]);
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Comments ──────────────────────────────────────────────────────
    $stmt2 = $pdo->prepare(
        "SELECT c.id, c.contenu, c.created_at, e.titre AS event_titre
         FROM commentaire_event c
         JOIN evenement e ON e.id_event = c.id_event
         WHERE c.auteur = ?
         ORDER BY c.created_at DESC
         LIMIT 10"
    );
    // Use email as auteur fallback — try both
    $stmt2->execute([$email]);
    $comments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // ── Stats ─────────────────────────────────────────────────────────
    $totalEvents   = count($participations);
    $confirmed     = count(array_filter($participations, fn($p) => str_contains($p['statut'], 'confirm')));
    $pending       = count(array_filter($participations, fn($p) => str_contains($p['statut'], 'attente')));
    $totalComments = count($comments);
    $totalSpent    = array_sum(array_map(fn($p) => (float)$p['prix'] * (int)$p['nombre_places_reservees'], $participations));

    echo json_encode([
        'email'          => $email,
        'stats'          => [
            'total_events'   => $totalEvents,
            'confirmed'      => $confirmed,
            'pending'        => $pending,
            'total_comments' => $totalComments,
            'total_spent'    => round($totalSpent, 2),
        ],
        'participations' => array_map(fn($p) => [
            'titre'    => $p['titre'],
            'lieu'     => $p['lieu'],
            'date'     => date('d/m/Y', strtotime($p['date_debut'])),
            'type'     => $p['type'],
            'statut'   => $p['statut'],
            'places'   => $p['nombre_places_reservees'],
            'prix'     => (float)$p['prix'],
            'image'    => $p['image'] ? '../../uploads/evenements/' . $p['image'] : null,
        ], $participations),
        'comments'       => array_map(fn($c) => [
            'contenu'      => $c['contenu'],
            'event_titre'  => $c['event_titre'],
            'created_at'   => date('d/m/Y H:i', strtotime($c['created_at'])),
        ], $comments),
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
