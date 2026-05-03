<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AvisController.php';
require_once __DIR__ . '/../../model/Avis.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$note        = (int)($_POST['note'] ?? 0);
$commentaire = trim($_POST['commentaire'] ?? '');
$id_produit  = (int)($_POST['id_produit'] ?? 0);

if ($note < 1 || $note > 5 || empty($commentaire) || $id_produit <= 0) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

// ── Gemini Sentiment Analysis ─────────────────────────────────────────────
$GEMINI_API_KEY = 'AIzaSyBysJrty1ByclcqGmnx_54awJEZfkUTMhY';
$sentiment_emoji = '😐'; // default neutral

function analyserSentimentGemini(string $commentaire, int $note, string $apiKey): string {
    $prompt = "You are a sentiment analysis system for a food product review website.\n\n"
            . "Review text: \"{$commentaire}\"\n"
            . "Star rating: {$note}/5\n\n"
            . "IMPORTANT: The reviewer may have typos or informal language. Be lenient.\n"
            . "Examples: 'live it' means 'love it', 'amzing' means 'amazing', 'gret' means 'great'.\n\n"
            . "Classification rules (star rating is the PRIMARY signal):\n"
            . "- 5 stars → VERY_POSITIVE (unless text is clearly negative)\n"
            . "- 4 stars → POSITIVE (unless text is clearly negative)\n"
            . "- 3 stars → NEUTRAL\n"
            . "- 2 stars → NEGATIVE (unless text is clearly positive)\n"
            . "- 1 star → VERY_NEGATIVE (unless text is clearly positive)\n\n"
            . "Respond with ONLY one of: VERY_POSITIVE, POSITIVE, NEUTRAL, NEGATIVE, VERY_NEGATIVE\n"
            . "No explanation. Just the word.";

    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature'     => 0.1,
            'maxOutputTokens' => 10,
        ]
    ]);

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        // Fallback based on note
        return $note >= 5 ? 'VERY_POSITIVE' : ($note >= 4 ? 'POSITIVE' : ($note <= 2 ? 'NEGATIVE' : 'NEUTRAL'));
    }

    $data = json_decode($response, true);
    $text = strtoupper(trim($data['candidates'][0]['content']['parts'][0]['text'] ?? ''));
    $text = preg_replace('/[^A-Z_]/', '', $text);

    $valid = ['VERY_POSITIVE', 'POSITIVE', 'NEUTRAL', 'NEGATIVE', 'VERY_NEGATIVE'];

    // If Gemini returns NEUTRAL for a 5-star or 1-star review, override with note-based fallback
    if ($text === 'NEUTRAL' || !in_array($text, $valid)) {
        if ($note === 5) return 'VERY_POSITIVE';
        if ($note === 4) return 'POSITIVE';
        if ($note === 2) return 'NEGATIVE';
        if ($note === 1) return 'VERY_NEGATIVE';
        return 'NEUTRAL';
    }

    return $text;
}

$emojiMap = [
    'VERY_POSITIVE' => '😍',
    'POSITIVE'      => '😊',
    'NEUTRAL'       => '😐',
    'NEGATIVE'      => '😕',
    'VERY_NEGATIVE' => '😡',
];

try {
    $sentiment_label = analyserSentimentGemini($commentaire, $note, $GEMINI_API_KEY);
    $sentiment_emoji = $emojiMap[$sentiment_label] ?? '😐';
} catch (Exception $e) {
    $sentiment_emoji = '😐';
}

// ── Save to database ──────────────────────────────────────────────────────
// Add sentiment column if it doesn't exist yet
try {
    $db = config::getConnexion();
    $db->exec("ALTER TABLE avis ADD COLUMN IF NOT EXISTS sentiment VARCHAR(10) DEFAULT NULL");
} catch (Exception $e) {
    // Column may already exist — ignore
}

$avisController = new AvisController();
$avis = new Avis(null, $note, $commentaire, date('Y-m-d'), $id_produit);

if ($avisController->addAvisWithSentiment($avis, $sentiment_emoji)) {
    echo json_encode(['success' => true, 'sentiment' => $sentiment_emoji]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
?>
