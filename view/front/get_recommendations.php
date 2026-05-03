<?php
/**
 * AI Meal Recommendations
 * Uses Gemini API when available, falls back to smart local scoring
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config.php';

// ── 1. Fetch products with ratings ────────────────────────────────────────
try {
    $db = config::getConnexion();

    // Check which column exists: id_categorie or categorie
    $colCheck = $db->query("SHOW COLUMNS FROM produit LIKE 'id_categorie'")->fetch();
    $catJoin  = $colCheck
        ? "LEFT JOIN categorieproduit c ON c.id_categorie = p.id_categorie"
        : "LEFT JOIN categorieproduit c ON c.id_categorie = CAST(p.categorie AS UNSIGNED)";

    $sql = "
        SELECT p.id, p.nom, p.description, p.prix, p.image,
               COALESCE(AVG(a.note), 0) AS avg_note,
               COUNT(a.id_avis)         AS nb_avis,
               p.quantiteStock,
               COALESCE(c.nom, 'General') AS categorie_nom
        FROM produit p
        LEFT JOIN avis a ON a.id_produit = p.id
        {$catJoin}
        WHERE p.quantiteStock > 0
        GROUP BY p.id
        ORDER BY (AVG(a.note) * 2 + LOG(COUNT(a.id_avis) + 1)) DESC, p.quantiteStock DESC
        LIMIT 15
    ";
    $allProducts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    exit;
}

if (empty($allProducts)) {
    echo json_encode(['error' => 'No products available']);
    exit;
}

// ── 2. Build Gemini prompt ─────────────────────────────────────────────────
$lines = [];
foreach ($allProducts as $p) {
    $note   = round((float)$p['avg_note'], 1);
    $rating = $note > 0 ? "{$note}/5 ({$p['nb_avis']} reviews)" : "no reviews";
    $lines[] = "ID:{$p['id']} | {$p['nom']} | {$p['prix']} DT | {$p['categorie_nom']} | Rating: {$rating}";
}
$productList = implode("\n", $lines);

$prompt = "You are a meal recommendation AI for 'Smart Meal Planner' in Tunisia.\n\n"
        . "Products:\n{$productList}\n\n"
        . "Pick exactly 3 to recommend. Prioritize high ratings, diverse categories.\n"
        . "Return ONLY a JSON array:\n"
        . '[{"id":5,"reason":"Short reason"},{"id":12,"reason":"..."},{"id":3,"reason":"..."}]';

// ── 3. Try Gemini API ──────────────────────────────────────────────────────
$recs   = null;
$apiKey = 'AIzaSyBysJrty1ByclcqGmnx_54awJEZfkUTMhY';
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'contents'         => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 300]
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
    $rawText = preg_replace('/```\s*/i', '', $rawText);
    $parsed  = json_decode(trim($rawText), true);
    if (is_array($parsed) && count($parsed) >= 3) {
        $recs = $parsed;
    }
}

// ── 4. Smart local fallback ────────────────────────────────────────────────
if (!is_array($recs) || empty($recs)) {
    usort($allProducts, function($a, $b) {
        $sa = (float)$a['avg_note'] * 2 + log((int)$a['nb_avis'] + 1);
        $sb = (float)$b['avg_note'] * 2 + log((int)$b['nb_avis'] + 1);
        return $sb <=> $sa;
    });

    $used = [];
    $recs = [];
    foreach ($allProducts as $p) {
        $cat  = $p['categorie_nom'];
        $note = round((float)$p['avg_note'], 1);
        $nb   = (int)$p['nb_avis'];

        if ($note >= 4.5 && $nb > 0)     $reason = "Rated {$note}/5 by {$nb} customers — a crowd favourite";
        elseif ($note >= 4.0 && $nb > 0) $reason = "Highly rated at {$note}/5 with {$nb} positive reviews";
        elseif ($nb >= 3)                 $reason = "One of the most reviewed products in {$cat}";
        elseif ($p['quantiteStock'] > 30) $reason = "Best seller in {$cat} — high demand this week";
        else                              $reason = "Top pick for healthy eating in {$cat}";

        if (in_array($cat, $used) && count($recs) < 2) continue;
        $used[] = $cat;
        $recs[] = ['id' => (int)$p['id'], 'reason' => $reason];
        if (count($recs) >= 3) break;
    }
    // Fill remaining if needed
    foreach ($allProducts as $p) {
        if (count($recs) >= 3) break;
        $ids = array_column($recs, 'id');
        if (!in_array((int)$p['id'], $ids)) {
            $recs[] = ['id' => (int)$p['id'], 'reason' => 'Popular choice among our customers'];
        }
    }
}

// ── 5. Enrich with full product data ──────────────────────────────────────
$map = [];
foreach ($allProducts as $p) { $map[(int)$p['id']] = $p; }

$result = [];
foreach (array_slice($recs, 0, 3) as $rec) {
    $id = (int)($rec['id'] ?? 0);
    if (!isset($map[$id])) continue;
    $p   = $map[$id];
    $img = $p['image'] ?? '';
    if (empty($img))                       $imgUrl = '';
    elseif (str_starts_with($img, 'http')) $imgUrl = $img;
    else                                   $imgUrl = '../../uploads/' . $img;

    $result[] = [
        'id'       => $id,
        'nom'      => $p['nom'],
        'prix'     => (float)$p['prix'],
        'image'    => $imgUrl,
        'reason'   => $rec['reason'] ?? 'Recommended by AI',
        'note'     => round((float)$p['avg_note'], 1),
        'nb_avis'  => (int)$p['nb_avis'],
        'categorie'=> $p['categorie_nom'],
    ];
}

echo json_encode($result);
