<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$data    = json_decode(file_get_contents('php://input'), true);
$id_event = (int)($data['id_event'] ?? 0);

if (!$id_event) {
    echo json_encode(['error' => 'Missing id_event']); exit;
}

$positive_words = ['super','excellent','parfait','génial','bravo','merci','top','bien','bonne','great','good','amazing','love','best','fantastic','wonderful','magnifique','incroyable','adoré','utile','recommande','satisfait','content','heureux','sympa'];
$negative_words = ['nul','mauvais','décevant','horrible','terrible','bad','awful','worst','hate','poor','disappointing','boring','ennuyeux','inutile','déçu','problème','erreur','raté','catastrophe','nul','médiocre'];

try {
    $pdo = config::getConnexion();

    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS commentaire (
        id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
        id_event INT, id_produit INT, id_user INT,
        auteur VARCHAR(100), contenu TEXT NOT NULL,
        date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(id_event)
    )");

    $stmt = $pdo->prepare("SELECT id_commentaire AS id, auteur, contenu FROM commentaire WHERE id_event = :id ORDER BY date_commentaire DESC LIMIT 50");
    $stmt->execute([':id' => $id_event]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total    = count($comments);
    $positive = 0;
    $negative = 0;
    $neutral  = 0;
    $details  = [];

    foreach ($comments as $c) {
        $text = strtolower($c['contenu'] ?? '');
        $isPos = false; $isNeg = false;
        foreach ($positive_words as $w) { if (str_contains($text, $w)) { $isPos = true; break; } }
        foreach ($negative_words as $w) { if (str_contains($text, $w)) { $isNeg = true; break; } }

        if ($isPos && !$isNeg)      { $sentiment = 'positive'; $emoji = '😊'; $positive++; }
        elseif ($isNeg && !$isPos)  { $sentiment = 'negative'; $emoji = '😞'; $negative++; }
        else                        { $sentiment = 'neutral';  $emoji = '😐'; $neutral++;  }

        $details[] = [
            'id'        => (int)$c['id'],
            'auteur'    => $c['auteur'] ?? 'Anonymous',
            'contenu'   => mb_substr($c['contenu'], 0, 100),
            'sentiment' => $sentiment,
            'emoji'     => $emoji,
        ];
    }

    $pct   = $total > 0 ? round(($positive / $total) * 100) : 0;
    $score = $total > 0 ? ($positive - $negative) / $total : 0;
    $label = $score > 0.2 ? 'Positif' : ($score < -0.2 ? 'Négatif' : 'Neutre');
    $bigEmoji = $score > 0.2 ? '😊' : ($score < -0.2 ? '😞' : '😐');

    echo json_encode([
        'total'    => $total,
        'positive' => $positive,
        'neutral'  => $neutral,
        'negative' => $negative,
        'pct'      => $pct,
        'label'    => $label,
        'emoji'    => $bigEmoji,
        'details'  => $details,
    ]);

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
