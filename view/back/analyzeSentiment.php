<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$id_event = (int)($data['id_event'] ?? 0);

if (!$id_event) {
    echo json_encode(['error' => 'id_event manquant']);
    exit;
}

// ── Fetch comments from DB ────────────────────────────────────────────
try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare(
        "SELECT id, auteur, contenu FROM commentaire_event WHERE id_event = ? ORDER BY created_at DESC LIMIT 20"
    );
    $stmt->execute([$id_event]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo json_encode(['error' => 'DB: ' . $e->getMessage()]);
    exit;
}

if (empty($comments)) {
    echo json_encode([
        'total'    => 0,
        'positive' => 0,
        'neutral'  => 0,
        'negative' => 0,
        'score'    => 0,
        'label'    => 'Aucun commentaire',
        'emoji'    => '💬',
        'details'  => []
    ]);
    exit;
}

// ── Build prompt ──────────────────────────────────────────────────────
$commentLines = '';
foreach ($comments as $c) {
    $commentLines .= "ID:{$c['id']} | {$c['auteur']}: {$c['contenu']}\n";
}

$prompt = <<<PROMPT
Analyse le sentiment de chaque commentaire ci-dessous.
Pour chaque commentaire, réponds UNIQUEMENT avec ce format JSON (tableau):
[
  {"id": 1, "sentiment": "positive", "score": 0.9, "emoji": "😊"},
  {"id": 2, "sentiment": "neutral",  "score": 0.5, "emoji": "😐"},
  {"id": 3, "sentiment": "negative", "score": 0.2, "emoji": "😞"}
]

Règles strictes:
- "sentiment" doit être exactement: "positive", "neutral", ou "negative"
- "score" entre 0.0 (très négatif) et 1.0 (très positif)
- "emoji": "😊" pour positif, "😐" pour neutre, "😞" pour négatif
- Réponds UNIQUEMENT avec le tableau JSON, rien d'autre

Commentaires:
{$commentLines}
PROMPT;

// ── Call Groq API ─────────────────────────────────────────────────────
require_once __DIR__ . '/../../config.api.php';
$apiKey  = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
$payload = json_encode([
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [
        ['role' => 'system', 'content' => 'Tu es un expert en analyse de sentiment. Tu réponds uniquement en JSON valide.'],
        ['role' => 'user',   'content' => $prompt]
    ],
    'max_tokens'  => 800,
    'temperature' => 0.1,
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_TIMEOUT => 20,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr || $httpCode !== 200) {
    echo json_encode(['error' => 'API error: ' . ($curlErr ?: $httpCode)]);
    exit;
}

$result  = json_decode($response, true);
$content = trim($result['choices'][0]['message']['content'] ?? '');

// Extract JSON from response (in case there's extra text)
preg_match('/\[.*\]/s', $content, $matches);
$jsonStr = $matches[0] ?? $content;
$details = json_decode($jsonStr, true);

if (!is_array($details)) {
    echo json_encode(['error' => 'Parse error: ' . $content]);
    exit;
}

// ── Build summary ─────────────────────────────────────────────────────
$positive = 0; $neutral = 0; $negative = 0;
$totalScore = 0;

// Map comment IDs to details
$detailsMap = [];
foreach ($details as $d) {
    $detailsMap[$d['id']] = $d;
}

$enriched = [];
foreach ($comments as $c) {
    $d = $detailsMap[$c['id']] ?? ['sentiment' => 'neutral', 'score' => 0.5, 'emoji' => '😐'];
    $sentiment = $d['sentiment'];
    if ($sentiment === 'positive') $positive++;
    elseif ($sentiment === 'negative') $negative++;
    else $neutral++;
    $totalScore += (float)($d['score'] ?? 0.5);
    $enriched[] = [
        'id'        => $c['id'],
        'auteur'    => $c['auteur'],
        'contenu'   => $c['contenu'],
        'sentiment' => $sentiment,
        'score'     => round((float)($d['score'] ?? 0.5), 2),
        'emoji'     => $d['emoji'] ?? '😐',
    ];
}

$total     = count($comments);
$avgScore  = $total > 0 ? round($totalScore / $total, 2) : 0.5;
$pct       = $total > 0 ? round(($positive / $total) * 100) : 0;

$label = match(true) {
    $avgScore >= 0.75 => 'Très positif',
    $avgScore >= 0.55 => 'Positif',
    $avgScore >= 0.45 => 'Neutre',
    $avgScore >= 0.30 => 'Mitigé',
    default           => 'Négatif',
};
$emoji = match(true) {
    $avgScore >= 0.75 => '🤩',
    $avgScore >= 0.55 => '😊',
    $avgScore >= 0.45 => '😐',
    $avgScore >= 0.30 => '😕',
    default           => '😞',
};

echo json_encode([
    'total'    => $total,
    'positive' => $positive,
    'neutral'  => $neutral,
    'negative' => $negative,
    'score'    => $avgScore,
    'pct'      => $pct,
    'label'    => $label,
    'emoji'    => $emoji,
    'details'  => $enriched,
], JSON_UNESCAPED_UNICODE);
