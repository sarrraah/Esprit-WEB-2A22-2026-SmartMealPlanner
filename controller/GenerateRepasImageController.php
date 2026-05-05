<?php
/**
 * GenerateRepasImageController.php — Génération automatique de photo de repas
 *
 * Sources en cascade :
 *   1. TheMealDB  — vraie photo du plat par nom (sans clé)
 *   2. Foodish    — photo par catégorie (sans clé)
 *   3. DuckDuckGo — recherche image par nom (sans clé)
 *   4. GD local   — image colorée si GD activé
 *   5. SVG local  — fallback absolu sans aucune dépendance
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

try {

$input = json_decode(file_get_contents('php://input'), true);
$nom   = trim($input['nom'] ?? '');

if ($nom === '') {
    ob_clean();
    echo json_encode(['error' => 'Le nom du repas est requis.']);
    exit;
}

$nomLower = mb_strtolower($nom, 'UTF-8');

// Mapping FR → catégorie Foodish + mot-clé anglais
$foodishCat = null;
$keywordEn  = $nom . ' food recipe';

$mapping = [
    'poulet'    => ['foodish' => 'butter-chicken', 'en' => $nom . ' chicken recipe'],
    'chicken'   => ['foodish' => 'butter-chicken', 'en' => $nom . ' chicken'],
    'burger'    => ['foodish' => 'burger',          'en' => $nom . ' burger'],
    'pizza'     => ['foodish' => 'pizza',           'en' => $nom . ' pizza'],
    'pâtes'     => ['foodish' => 'pasta',           'en' => $nom . ' pasta'],
    'pates'     => ['foodish' => 'pasta',           'en' => $nom . ' pasta'],
    'pasta'     => ['foodish' => 'pasta',           'en' => $nom . ' pasta'],
    'spaghetti' => ['foodish' => 'pasta',           'en' => 'spaghetti ' . $nom],
    'riz'       => ['foodish' => 'rice',            'en' => $nom . ' rice dish'],
    'risotto'   => ['foodish' => 'rice',            'en' => 'risotto ' . $nom],
    'biryani'   => ['foodish' => 'biryani',         'en' => 'biryani'],
    'dessert'   => ['foodish' => 'dessert',         'en' => $nom . ' dessert'],
    'gâteau'    => ['foodish' => 'dessert',         'en' => $nom . ' cake'],
    'gateau'    => ['foodish' => 'dessert',         'en' => $nom . ' cake'],
    'tarte'     => ['foodish' => 'dessert',         'en' => $nom . ' pie tart'],
    'curry'     => ['foodish' => 'butter-chicken',  'en' => $nom . ' curry'],
    'lasagne'   => ['foodish' => 'pasta',           'en' => 'lasagna ' . $nom],
    'samosa'    => ['foodish' => 'samosa',          'en' => 'samosa'],
    'salade'    => ['foodish' => null,              'en' => $nom . ' salad'],
    'soupe'     => ['foodish' => null,              'en' => $nom . ' soup'],
    'saumon'    => ['foodish' => null,              'en' => $nom . ' salmon'],
    'bœuf'      => ['foodish' => null,              'en' => $nom . ' beef'],
    'boeuf'     => ['foodish' => null,              'en' => $nom . ' beef'],
    'steak'     => ['foodish' => null,              'en' => $nom . ' steak'],
    'tajine'    => ['foodish' => null,              'en' => $nom . ' tagine'],
    'couscous'  => ['foodish' => null,              'en' => 'couscous ' . $nom],
    'omelette'  => ['foodish' => null,              'en' => $nom . ' omelette egg'],
    'gratin'    => ['foodish' => null,              'en' => $nom . ' gratin'],
    'quiche'    => ['foodish' => null,              'en' => $nom . ' quiche'],
    'crêpe'     => ['foodish' => null,              'en' => $nom . ' crepe'],
    'crepe'     => ['foodish' => null,              'en' => $nom . ' crepe'],
];

foreach ($mapping as $fr => $data) {
    if (mb_strpos($nomLower, $fr) !== false) {
        $foodishCat = $data['foodish'];
        $keywordEn  = $data['en'];
        break;
    }
}

// ── Helper cURL ───────────────────────────────────────────────────────────────
function safeFetch(string $url, array $headers = [], int $timeout = 12): array {
    if (!function_exists('curl_init')) {
        return ['body' => '', 'code' => 0, 'error' => 'cURL non disponible'];
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => array_merge([
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
        ], $headers),
    ]);
    $body  = curl_exec($ch);
    $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return ['body' => ($body ?: ''), 'code' => $code, 'error' => $error];
}

// ── Helper : sauvegarder une image binaire ────────────────────────────────────
function saveImageData(string $data): ?string {
    if (strlen($data) < 500) return null;

    $sig = substr($data, 0, 8);
    $ext = null;
    if (substr($sig, 0, 3) === "\xFF\xD8\xFF")                              $ext = 'jpg';
    elseif (substr($sig, 0, 8) === "\x89PNG\r\n\x1a\n")                     $ext = 'png';
    elseif (substr($sig, 0, 4) === 'RIFF' && substr($data, 8, 4) === 'WEBP') $ext = 'webp';
    elseif (substr($sig, 0, 6) === 'GIF87a' || substr($sig, 0, 6) === 'GIF89a') $ext = 'gif';

    if (!$ext || strlen($data) < 2000) return null;

    $dir = __DIR__ . '/../uploads/repas';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);

    $filename = 'repas_auto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (@file_put_contents($dir . '/' . $filename, $data) === false) return null;
    return 'uploads/repas/' . $filename;
}

// ── URL de base ───────────────────────────────────────────────────────────────
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$savedPath = null;
$source    = '';
$keywords  = [$nom];

// ══ SOURCE 1 : TheMealDB — recherche par nom exact ════════════════════════════
$r1 = safeFetch('https://www.themealdb.com/api/json/v1/1/search.php?s=' . urlencode($nom));
if ($r1['code'] === 200 && !empty($r1['body'])) {
    $d1 = json_decode($r1['body'], true);
    if (!empty($d1['meals'][0]['strMealThumb'])) {
        $ri = safeFetch($d1['meals'][0]['strMealThumb']);
        if ($ri['code'] === 200) {
            $savedPath = saveImageData($ri['body']);
            if ($savedPath) { $source = 'TheMealDB'; $keywords = [$d1['meals'][0]['strMeal']]; }
        }
    }
}

// ══ SOURCE 2 : Foodish — photo par catégorie ══════════════════════════════════
if (!$savedPath && $foodishCat) {
    $r2 = safeFetch('https://foodish-api.com/api/images/' . $foodishCat);
    if ($r2['code'] === 200) {
        $d2 = json_decode($r2['body'], true);
        if (!empty($d2['image'])) {
            $ri = safeFetch($d2['image']);
            if ($ri['code'] === 200) {
                $savedPath = saveImageData($ri['body']);
                if ($savedPath) { $source = 'Foodish'; $keywords = [$foodishCat]; }
            }
        }
    }
}

// ══ SOURCE 3 : DuckDuckGo Image Search — vraie photo selon le nom ═════════════
if (!$savedPath) {
    // DuckDuckGo retourne des résultats JSON via son endpoint vqd
    $query   = urlencode($keywordEn . ' plat cuisine');
    $ddgHtml = safeFetch('https://duckduckgo.com/?q=' . $query . '&iax=images&ia=images');

    // Extraire le token vqd nécessaire pour la recherche d'images
    $vqd = '';
    if ($ddgHtml['code'] === 200 && preg_match('/vqd=([^&"]+)/', $ddgHtml['body'], $m)) {
        $vqd = $m[1];
    }

    if ($vqd) {
        $imgSearch = safeFetch(
            'https://duckduckgo.com/i.js?q=' . $query . '&vqd=' . urlencode($vqd) . '&f=,,,,,&p=1',
            ['Accept: application/json', 'Referer: https://duckduckgo.com/']
        );

        if ($imgSearch['code'] === 200) {
            $imgData = json_decode($imgSearch['body'], true);
            $results = $imgData['results'] ?? [];

            // Essayer les 5 premiers résultats jusqu'à en trouver un téléchargeable
            foreach (array_slice($results, 0, 5) as $result) {
                $imgUrl = $result['image'] ?? '';
                if (empty($imgUrl)) continue;

                // Filtrer les URLs problématiques
                if (strpos($imgUrl, 'data:') === 0) continue;

                $ri = safeFetch($imgUrl, [], 8);
                if ($ri['code'] === 200) {
                    $savedPath = saveImageData($ri['body']);
                    if ($savedPath) {
                        $source   = 'DuckDuckGo Images';
                        $keywords = [$keywordEn];
                        break;
                    }
                }
            }
        }
    }
}

// ══ SOURCE 4 : GD local (si extension=gd activée dans php.ini) ════════════════
if (!$savedPath && function_exists('imagecreatetruecolor')) {
    $w   = 800;
    $h   = 500;
    $img = imagecreatetruecolor($w, $h);

    $palettes = [
        [206,18,18,  150,8,8],
        [230,126,34, 180,80,10],
        [39,174,96,  20,120,60],
        [41,128,185, 20,80,140],
        [142,68,173, 90,30,130],
        [22,160,133, 10,110,90],
    ];
    $pi = abs(crc32($nom)) % count($palettes);
    $p  = $palettes[$pi];

    for ($y = 0; $y < $h; $y++) {
        $t = $y / $h;
        $c = imagecolorallocate($img,
            (int)($p[0]+($p[3]-$p[0])*$t),
            (int)($p[1]+($p[4]-$p[1])*$t),
            (int)($p[2]+($p[5]-$p[2])*$t)
        );
        imageline($img, 0, $y, $w, $y, $c);
    }

    $w80 = imagecolorallocatealpha($img, 255,255,255, 30);
    $w60 = imagecolorallocatealpha($img, 255,255,255, 50);
    imagefilledellipse($img, $w/2, $h/2-20, 280, 280, $w80);
    imagefilledellipse($img, $w/2, $h/2-20, 200, 200, $w60);

    $dark = imagecolorallocatealpha($img, 0,0,0, 40);
    imagefilledrectangle($img, 0, $h-100, $w, $h, $dark);

    $white = imagecolorallocate($img, 255,255,255);
    $label = mb_strlen($nom) > 35 ? mb_substr($nom,0,32).'...' : $nom;
    $fw    = imagefontwidth(5);
    imagestring($img, 5, max(20,(int)(($w-$fw*strlen($label))/2)), $h-70, $label, $white);
    imagestring($img, 3, (int)(($w-$fw*strlen('SmartMeal Planner'))/2), $h-40, 'SmartMeal Planner', $white);

    $dir = __DIR__ . '/../uploads/repas';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $fn = 'repas_auto_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    if (@imagejpeg($img, $dir.'/'.$fn, 88)) {
        $savedPath = 'uploads/repas/' . $fn;
        $source    = 'Généré (GD)';
    }
    imagedestroy($img);
}

// ══ SOURCE 5 : SVG inline — fallback absolu, aucune dépendance ════════════════
if (!$savedPath) {
    $colors = ['#ce1212','#e67e22','#27ae60','#2980b9','#8e44ad','#16a085'];
    $ci     = abs(crc32($nom)) % count($colors);
    $c1     = $colors[$ci];
    $c2     = $colors[($ci+1) % count($colors)];
    $label  = htmlspecialchars(mb_strlen($nom)>30 ? mb_substr($nom,0,27).'...' : $nom, ENT_XML1, 'UTF-8');

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="500" viewBox="0 0 800 500">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$c1}"/>
      <stop offset="100%" stop-color="{$c2}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="500" fill="url(#g)"/>
  <circle cx="400" cy="220" r="140" fill="rgba(255,255,255,0.12)"/>
  <circle cx="400" cy="220" r="95"  fill="rgba(255,255,255,0.18)"/>
  <circle cx="400" cy="220" r="50"  fill="rgba(255,255,255,0.22)"/>
  <text x="400" y="228" font-family="Arial" font-size="48" fill="rgba(255,255,255,0.7)" text-anchor="middle" dominant-baseline="middle">🍽</text>
  <rect x="0" y="390" width="800" height="110" fill="rgba(0,0,0,0.45)"/>
  <text x="400" y="435" font-family="Arial,sans-serif" font-size="30" font-weight="bold" fill="white" text-anchor="middle">{$label}</text>
  <text x="400" y="472" font-family="Arial,sans-serif" font-size="14" fill="rgba(255,255,255,0.75)" text-anchor="middle">SmartMeal Planner</text>
</svg>
SVG;

    $dir = __DIR__ . '/../uploads/repas';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $fn = 'repas_auto_' . time() . '_' . bin2hex(random_bytes(4)) . '.svg';
    if (@file_put_contents($dir.'/'.$fn, $svg) !== false) {
        $savedPath = 'uploads/repas/' . $fn;
        $source    = 'Généré (SVG)';
    }
}

// ── Réponse ───────────────────────────────────────────────────────────────────
if (!$savedPath) {
    ob_clean();
    echo json_encode(['error' => 'Impossible de générer une image. Activez GD dans php.ini (décommentez extension=gd).']);
    exit;
}

ob_clean();
echo json_encode([
    'path'     => $savedPath,
    'url'      => $baseUrl . '/' . $savedPath,
    'source'   => $source,
    'keywords' => $keywords,
], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Erreur PHP : ' . $e->getMessage()]);
}
