<?php
/**
 * SearchYoutubeController.php — Recherche de vidéo YouTube pour une recette
 *
 * Reçoit une requête POST JSON avec :
 *   - nom : nom de la recette (obligatoire)
 *
 * Retourne un JSON :
 *   {
 *     "video_id"   : "dQw4w9WgXcQ",
 *     "title"      : "Titre de la vidéo",
 *     "thumbnail"  : "https://img.youtube.com/vi/.../hqdefault.jpg",
 *     "channel"    : "Nom de la chaîne",
 *     "embed_url"  : "https://www.youtube.com/embed/dQw4w9WgXcQ"
 *   }
 *   ou { "error": "message" }
 *
 * ⚠ CONFIGURATION REQUISE :
 *   Remplacez YOUTUBE_API_KEY ci-dessous par votre clé API YouTube Data v3.
 *   Obtenez-la gratuitement sur : https://console.cloud.google.com/
 *   → Activer "YouTube Data API v3" → Créer une clé API
 */

header('Content-Type: application/json; charset=utf-8');

// ── Clé API YouTube Data v3 ───────────────────────────────────────────────────
// Remplacez cette valeur par votre propre clé API
define('YOUTUBE_API_KEY', 'AIzaSyDj6CMZFEdJIWpQqMA5oFYJbivrn7Dt1Fo');

// Lire le corps JSON de la requête
$input = json_decode(file_get_contents('php://input'), true);
$nom   = trim($input['nom'] ?? '');

if ($nom === '') {
    echo json_encode(['error' => 'Le nom de la recette est requis.']);
    exit;
}

// Construire la requête de recherche : "recette [nom] cuisine tutoriel"
$query = 'recette ' . $nom . ' cuisine comment préparer';

// Appel à l'API YouTube Data v3 — endpoint search
$apiUrl = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
    'part'       => 'snippet',
    'q'          => $query,
    'type'       => 'video',
    'maxResults' => 5,
    'relevanceLanguage' => 'fr',
    'safeSearch' => 'strict',
    'key'        => YOUTUBE_API_KEY,
]);

// Effectuer la requête HTTP avec cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT      => 'SmartMealPlanner/1.0',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Gérer les erreurs cURL
if ($curlError) {
    echo json_encode(['error' => 'Erreur réseau : ' . $curlError]);
    exit;
}

// Décoder la réponse JSON de YouTube
$data = json_decode($response, true);

// Gérer les erreurs API YouTube
if ($httpCode !== 200 || isset($data['error'])) {
    $msg = $data['error']['message'] ?? 'Erreur API YouTube (code ' . $httpCode . ')';

    // Message spécifique si la clé API n'est pas configurée
    if (str_contains($msg, 'API key') || $httpCode === 400 || $httpCode === 403) {
        echo json_encode([
            'error' => 'Clé API YouTube non configurée. Veuillez configurer YOUTUBE_API_KEY dans SearchYoutubeController.php',
            'setup_required' => true,
        ]);
    } else {
        echo json_encode(['error' => $msg]);
    }
    exit;
}

// Vérifier qu'il y a des résultats
if (empty($data['items'])) {
    echo json_encode(['error' => 'Aucune vidéo trouvée pour "' . htmlspecialchars($nom) . '".']);
    exit;
}

// Prendre le premier résultat pertinent
$video   = $data['items'][0];
$videoId = $video['id']['videoId'] ?? '';

if (empty($videoId)) {
    echo json_encode(['error' => 'Identifiant vidéo introuvable.']);
    exit;
}

// Retourner les informations de la vidéo
echo json_encode([
    'video_id'  => $videoId,
    'title'     => $video['snippet']['title']        ?? '',
    'channel'   => $video['snippet']['channelTitle'] ?? '',
    'thumbnail' => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg',
    'embed_url' => 'https://www.youtube.com/embed/' . $videoId,
], JSON_UNESCAPED_UNICODE);
