<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
    $pdo = config::getConnexion();

    // Check if participation table exists
    $hasParticipation = (bool) $pdo->query("SHOW TABLES LIKE 'participation'")->fetchColumn();

    if ($hasParticipation) {
        $stmt = $pdo->query("
            SELECT e.id_event, e.titre, e.description, e.date_debut, e.lieu,
                   e.prix, e.type, e.image, e.statut,
                   COALESCE(e.likes, 0) AS likes,
                   COUNT(p.id_participation) AS participation_count,
                   (COALESCE(e.likes, 0) * 2 + COUNT(p.id_participation) * 3) AS score
            FROM evenement e
            LEFT JOIN participation p ON p.id_event = e.id_event
            GROUP BY e.id_event
            ORDER BY score DESC, e.date_debut ASC
            LIMIT 3
        ");
    } else {
        $stmt = $pdo->query("
            SELECT e.id_event, e.titre, e.description, e.date_debut, e.lieu,
                   e.prix, e.type, e.image, e.statut,
                   COALESCE(e.likes, 0) AS likes,
                   0 AS participation_count,
                   COALESCE(e.likes, 0) * 2 AS score
            FROM evenement e
            ORDER BY score DESC, e.date_debut ASC
            LIMIT 3
        ");
    }

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build absolute URL base for images
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $projRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    $basePath = str_replace($docRoot, '', $projRoot);
    $imgBase  = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/view/assets/img/events/';

    // Medals and reasons for top 3
    $medals  = ['🥇', '🥈', '🥉'];
    $reasons = [
        'Top pick based on popularity and attendee engagement.',
        'Highly rated by our community members.',
        'Great match for your interests and schedule.',
    ];

    $result = [];
    foreach ($events as $i => $e) {
        $img = ($e['image'] && trim($e['image']) !== '')
            ? $imgBase . $e['image']
            : null;

        $result[] = [
            'id'          => (int) $e['id_event'],
            'titre'       => $e['titre'],
            'description' => mb_substr($e['description'] ?? '', 0, 120) . '...',
            'date'        => $e['date_debut'] ? date('M j, Y', strtotime($e['date_debut'])) : '',
            'lieu'        => $e['lieu'],
            'prix'        => (float) $e['prix'],
            'type'        => $e['type'],
            'image'       => $img,
            'likes'       => (int) $e['likes'],
            'participants'=> (int) $e['participation_count'],
            'score'       => (int) $e['score'],
            'note'        => 0,
            'medal'       => $medals[$i] ?? '🏅',
            'reason'      => $reasons[$i] ?? 'Recommended for you.',
        ];
    }

    echo json_encode($result);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
