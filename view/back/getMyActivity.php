<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$email = trim($_GET['email'] ?? '');

if (!$email) {
    echo json_encode(['error' => 'Email required']);
    exit;
}

try {
    $pdo = config::getConnexion();

    $hasParticipation = (bool) $pdo->query("SHOW TABLES LIKE 'participation'")->fetchColumn();

    if (!$hasParticipation) {
        echo json_encode([
            'stats'          => ['total_events' => 0, 'confirmed' => 0, 'total_comments' => 0, 'total_spent' => 0],
            'participations' => [],
            'comments'       => [],
        ]);
        exit;
    }

    // Describe participation table to find email column
    $cols = $pdo->query("DESCRIBE participation")->fetchAll(PDO::FETCH_COLUMN);
    $hasEmail = in_array('email', $cols);
    $hasUserId = in_array('user_id', $cols) || in_array('id_user', $cols);

    if ($hasEmail) {
        $emailCol = 'p.email';
        $whereClause = 'WHERE p.email = :email';
        $bindParam = [':email' => $email];
    } elseif ($hasUserId) {
        // Look up user_id from email
        $userStmt = $pdo->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
        $userStmt->execute([':email' => $email]);
        $userId = $userStmt->fetchColumn();
        if (!$userId) {
            echo json_encode([
                'stats'          => ['total_events' => 0, 'confirmed' => 0, 'total_comments' => 0, 'total_spent' => 0],
                'participations' => [],
            ]);
            exit;
        }
        $userIdCol = in_array('user_id', $cols) ? 'user_id' : 'id_user';
        $whereClause = "WHERE p.$userIdCol = :uid";
        $bindParam = [':uid' => $userId];
    } else {
        echo json_encode(['error' => 'Cannot identify user in participation table']);
        exit;
    }

    // Fetch participations with event details
    $hasPrix   = in_array('prix',   $cols);
    $hasPlaces = in_array('places', $cols);
    $hasStatut = in_array('statut', $cols);

    $prixSel   = $hasPrix   ? 'COALESCE(p.prix, e.prix, 0)'   : 'COALESCE(e.prix, 0)';
    $placesSel = $hasPlaces ? 'COALESCE(p.places, 1)'          : '1';
    $statutSel = $hasStatut ? 'COALESCE(p.statut, e.statut)'   : 'e.statut';

    $stmt = $pdo->prepare("
        SELECT
            p.id_participation,
            e.id_event,
            e.titre,
            e.lieu,
            e.type,
            DATE_FORMAT(e.date_debut, '%d/%m/%Y') AS date,
            $statutSel AS statut,
            $prixSel   AS prix,
            $placesSel AS places
        FROM participation p
        JOIN evenement e ON e.id_event = p.id_event
        $whereClause
        ORDER BY e.date_debut DESC
        LIMIT 20
    ");
    $stmt->execute($bindParam);
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count and fetch comments by this user
    $totalComments = 0;
    $comments = [];
    $hasCommentaire = (bool) $pdo->query("SHOW TABLES LIKE 'commentaire'")->fetchColumn();
    if ($hasCommentaire) {
        $commentCols = $pdo->query("DESCRIBE commentaire")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('email', $commentCols)) {
            $cStmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire WHERE email = :email");
            $cStmt->execute([':email' => $email]);
            $totalComments = (int) $cStmt->fetchColumn();

            // Fetch recent comments with event title
            $cListStmt = $pdo->prepare("
                SELECT c.contenu, c.created_at,
                       COALESCE(e.titre, 'Unknown event') AS event_titre
                FROM commentaire c
                LEFT JOIN evenement e ON e.id_event = c.id_event
                WHERE c.email = :email
                ORDER BY c.created_at DESC
                LIMIT 10
            ");
            $cListStmt->execute([':email' => $email]);
            $comments = $cListStmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif (in_array('auteur', $commentCols)) {
            // fallback: match by auteur name if no email column
            $comments = [];
        }
    }

    // Build stats
    $totalEvents  = count($participations);
    $confirmed    = 0;
    $totalSpent   = 0.0;
    foreach ($participations as $p) {
        if (stripos($p['statut'], 'confirm') !== false || stripos($p['statut'], 'actif') !== false) {
            $confirmed++;
        }
        $totalSpent += (float)$p['prix'] * (int)$p['places'];
    }

    echo json_encode([
        'stats' => [
            'total_events'   => $totalEvents,
            'confirmed'      => $confirmed,
            'total_comments' => $totalComments,
            'total_spent'    => round($totalSpent, 2),
        ],
        'participations' => $participations,
        'comments'       => $comments,
    ]);

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
