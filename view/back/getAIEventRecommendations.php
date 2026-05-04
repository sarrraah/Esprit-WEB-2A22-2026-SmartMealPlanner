<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../model/Database.php';

// ── 1. Fetch events with ratings ──────────────────────────────────────
try {
    $pdo = Database::getConnection();

    $sql = "SELECT e.id_event, e.titre, e.type, e.lieu, e.prix, e.statut,
                   e.date_debut, e.date_fin, e.image, e.capacite_max,
                   COALESCE(ROUND(AVG(r.stars), 1), 0) AS avg_note,
                   COALESCE(COUNT(r.id), 0)            AS nb_avis
            FROM evenement e
            LEFT JOIN rating r ON r.id_event = e.id_event
            WHERE LOWER(e.statut) LIKE '%actif%'
            GROUP BY e.id_event
            ORDER BY (COALESCE(AVG(r.stars), 0) * 2 + LOG(COUNT(r.id) + 1)) DESC, e.date_debut ASC
            LIMIT 15";

    $allEvents = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) {
    echo json_encode(['error' => 'DB error: ' . $ex->getMessage()]);
    exit;
}

if (empty($allEvents)) {
    // Try without status filter
    try {
        $sql2 = "SELECT e.id_event, e.titre, e.type, e.lieu, e.prix, e.statut,
                        e.date_debut, e.date_fin, e.image, e.capacite_max,
                        COALESCE(ROUND(AVG(r.stars), 1), 0) AS avg_note,
                        COALESCE(COUNT(r.id), 0)            AS nb_avis
                 FROM evenement e
                 LEFT JOIN rating r ON r.id_event = e.id_event
                 GROUP BY e.id_event
                 ORDER BY e.date_debut ASC
                 LIMIT 15";
        $allEvents = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $ex2) {}
}

if (empty($allEvents)) {
    echo json_encode(['error' => 'No events found in database']);
    exit;
}

// ── 2. Build Gemini prompt ────────────────────────────────────────────
$lines = [];
foreach ($allEvents as $ev) {
    $note   = round((float)$ev['avg_note'], 1);
    $rating = $note > 0 ? "{$note}/5 ({$ev['nb_avis']} reviews)" : "no reviews";
    $prix   = $ev['prix'] == 0 ? 'Free' : $ev['prix'] . ' TND';
    $date   = date('d/m/Y', strtotime($ev['date_debut']));
    $lines[] = "ID:{$ev['id_event']} | {$ev['titre']} | {$ev['type']} | {$prix} | {$date} | Rating: {$rating}";
}
$eventList = implode("\n", $lines);

$prompt = "You are an event recommendation AI for 'Smart Meal Planner' in Tunisia.\n\n"
        . "Active events:\n{$eventList}\n\n"
        . "Pick exactly 3 to recommend. Prioritize high ratings, diverse types, upcoming dates.\n"
        . "Return ONLY a JSON array:\n"
        . '[{"id":5,"reason":"Short reason"},{"id":12,"reason":"..."},{"id":3,"reason":"..."}]';

// ── 3. Try Gemini API ─────────────────────────────────────────────────
$recs   = null;
$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
if (!$apiKey) { $apiKey = ''; } // fallback local scoring if no key
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'contents'         => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 300],
    ]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 8,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpCode === 200) {
    $data    = json_decode($response, true);
    $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $rawText = preg_replace('/```json\s*/i', '', $rawText);
    $rawText = preg_replace('/```\s*/i',     '', $rawText);
    $parsed  = json_decode(trim($rawText), true);
    if (is_array($parsed) && count($parsed) >= 3) {
        $recs = $parsed;
    }
}

// ── 4. Smart local fallback ───────────────────────────────────────────
if (!is_array($recs) || empty($recs)) {
    usort($allEvents, function ($a, $b) {
        $sa = (float)$a['avg_note'] * 2 + log((int)$a['nb_avis'] + 1);
        $sb = (float)$b['avg_note'] * 2 + log((int)$b['nb_avis'] + 1);
        return $sb <=> $sa;
    });

    $usedTypes = [];
    $recs      = [];
    foreach ($allEvents as $ev) {
        $type = $ev['type'];
        $note = round((float)$ev['avg_note'], 1);
        $nb   = (int)$ev['nb_avis'];

        if ($note >= 4.5 && $nb > 0)     $reason = "Rated {$note}/5 by {$nb} attendees — a crowd favourite";
        elseif ($note >= 4.0 && $nb > 0) $reason = "Highly rated at {$note}/5 with {$nb} positive reviews";
        elseif ($nb >= 3)                 $reason = "One of the most reviewed events of type {$type}";
        else                              $reason = "Top pick in the {$type} category this season";

        if (in_array($type, $usedTypes) && count($recs) < 2) continue;
        $usedTypes[] = $type;
        $recs[]      = ['id' => (int)$ev['id_event'], 'reason' => $reason];
        if (count($recs) >= 3) break;
    }

    // Fill remaining if needed
    foreach ($allEvents as $ev) {
        if (count($recs) >= 3) break;
        $ids = array_column($recs, 'id');
        if (!in_array((int)$ev['id_event'], $ids)) {
            $recs[] = ['id' => (int)$ev['id_event'], 'reason' => 'Popular choice among our attendees'];
        }
    }
}

// ── 5. Enrich with full event data ────────────────────────────────────
$map = [];
foreach ($allEvents as $ev) { $map[(int)$ev['id_event']] = $ev; }

$medals = ['🥇', '🥈', '🥉'];
$result = [];
foreach (array_slice($recs, 0, 3) as $i => $rec) {
    $id = (int)($rec['id'] ?? 0);
    if (!isset($map[$id])) continue;
    $ev  = $map[$id];
    $img = $ev['image'] ?? '';

    if (empty($img))                        $imgUrl = '';
    elseif (str_starts_with($img, 'http'))  $imgUrl = $img;
    else                                    $imgUrl = '../../uploads/evenements/' . $img;

    $result[] = [
        'id'       => $id,
        'titre'    => $ev['titre'],
        'type'     => $ev['type'],
        'prix'     => (float)$ev['prix'],
        'lieu'     => $ev['lieu'],
        'date'     => date('d/m/Y', strtotime($ev['date_debut'])),
        'image'    => $imgUrl,
        'reason'   => $rec['reason'] ?? 'Recommended by AI',
        'note'     => round((float)$ev['avg_note'], 1),
        'nb_avis'  => (int)$ev['nb_avis'],
        'medal'    => $medals[$i] ?? '🏅',
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
