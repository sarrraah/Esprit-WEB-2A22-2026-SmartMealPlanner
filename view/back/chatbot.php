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

$data    = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');
$history = $data['history']  ?? [];

if ($message === '') {
    echo json_encode(['error' => 'Message vide']);
    exit;
}

// ── Fetch all events from DB ──────────────────────────────────────────
try {
    $pdo  = Database::getConnection();
    $rows = $pdo->query(
        "SELECT id_event, titre, description, date_debut, date_fin,
                lieu, capacite_max, prix, statut, type
         FROM evenement
         ORDER BY date_debut ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    exit;
}

// Build events summary for the system prompt
$eventsList = '';
foreach ($rows as $r) {
    $prix  = ($r['prix'] == 0) ? 'Gratuit' : number_format($r['prix'], 2) . ' TND';
    $dates = date('d/m/Y', strtotime($r['date_debut']));
    if ($r['date_debut'] !== $r['date_fin']) {
        $dates .= ' → ' . date('d/m/Y', strtotime($r['date_fin']));
    }
    $eventsList .= "- [{$r['id_event']}] {$r['titre']} | Type: {$r['type']} | Date: {$dates} | Lieu: {$r['lieu']} | Prix: {$prix} | Statut: {$r['statut']} | Capacité: {$r['capacite_max']}\n";
    if (!empty($r['description'])) {
        $eventsList .= "  Description: " . mb_substr($r['description'], 0, 120) . "...\n";
    }
}

// ── System prompt ─────────────────────────────────────────────────────
$systemPrompt = <<<PROMPT
Tu es un assistant virtuel amical et expert pour la plateforme Smart Meal Planner Events.
Ton rôle est d'aider les utilisateurs à trouver et choisir l'événement qui leur convient le mieux.

Voici la liste complète des événements disponibles :
{$eventsList}

Règles :
- Réponds toujours en français, de façon concise et chaleureuse.
- Si l'utilisateur décrit ses intérêts, recommande 1 à 3 événements adaptés avec leur ID entre crochets.
- Donne le lien de détail sous la forme : detailEvent.php?id=ID
- Si un événement est "annulé" ou "terminé", précise-le clairement.
- Si l'utilisateur demande le prix, donne-le précisément.
- Ne réponds qu'aux questions liées aux événements de la plateforme.
- Sois bref : maximum 4 phrases par réponse.
PROMPT;

// ── Build messages array ──────────────────────────────────────────────
$messages = [['role' => 'system', 'content' => $systemPrompt]];

// Add conversation history (max last 6 exchanges)
$history = array_slice($history, -12);
foreach ($history as $h) {
    if (isset($h['role'], $h['content'])) {
        $messages[] = ['role' => $h['role'], 'content' => $h['content']];
    }
}
$messages[] = ['role' => 'user', 'content' => $message];

// ── Call Groq API (free) ──────────────────────────────────────────────
require_once __DIR__ . '/../../config.php';
$apiKey    = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
$apiUrl    = 'https://api.groq.com/openai/v1/chat/completions';
$modelName = 'llama-3.3-70b-versatile';

$payload = json_encode([
    'model'       => $modelName,
    'messages'    => $messages,
    'max_tokens'  => 300,
    'temperature' => 0.7,
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_TIMEOUT        => 20,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['error' => 'Curl error: ' . $curlErr]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode !== 200 || !isset($result['choices'][0]['message']['content'])) {
    $errMsg = $result['error']['message'] ?? 'API error';
    echo json_encode(['error' => $errMsg]);
    exit;
}

$reply = trim($result['choices'][0]['message']['content']);
echo json_encode(['reply' => $reply]);
