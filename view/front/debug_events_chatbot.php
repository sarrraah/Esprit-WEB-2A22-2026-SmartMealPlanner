<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config.php';

echo "<style>body{font-family:monospace;padding:20px;background:#0f172a;color:#94a3b8;}
h2{color:#ce1212;margin-top:20px;}
.ok{color:#4ade80;} .err{color:#f87171;} .warn{color:#fbbf24;}
pre{background:#1e293b;padding:12px;border-radius:8px;overflow-x:auto;}
</style>";

echo "<h1 style='color:#fff'>Event Chatbot + Activity Debug</h1>";

// 1. Config checks
echo "<h2>1. Config</h2>";
echo "GROQ_API_KEY defined: ";
if (defined('GROQ_API_KEY') && GROQ_API_KEY !== '' && GROQ_API_KEY !== 'gsk_placeholder_replace_with_real_groq_key') {
    echo "<span class='ok'>YES — key starts with: " . substr(GROQ_API_KEY, 0, 8) . "...</span><br>";
} else {
    echo "<span class='err'>NO or placeholder — chatbot will return Invalid API Key</span><br>";
    echo "<span class='warn'>→ Set a real Groq API key at https://console.groq.com/keys</span><br>";
}

// 2. DB connection
echo "<h2>2. Database</h2>";
try {
    $pdo = config::getConnexion();
    echo "<span class='ok'>Connected ✓</span><br>";

    $evCount = $pdo->query("SELECT COUNT(*) FROM evenement")->fetchColumn();
    echo "Events in DB: <span class='ok'>$evCount</span><br>";

    $hasParticipation = (bool)$pdo->query("SHOW TABLES LIKE 'participation'")->fetchColumn();
    echo "participation table: " . ($hasParticipation ? "<span class='ok'>EXISTS ✓</span>" : "<span class='warn'>MISSING</span>") . "<br>";

    if ($hasParticipation) {
        $cols = $pdo->query("DESCRIBE participation")->fetchAll(PDO::FETCH_COLUMN);
        echo "participation columns: <span class='ok'>" . implode(', ', $cols) . "</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='err'>DB Error: " . $e->getMessage() . "</span><br>";
}

// 3. File paths
echo "<h2>3. Backend File Paths</h2>";
$files = [
    'chatbot.php'                  => __DIR__ . '/../back/chatbot.php',
    'getMyActivity.php'            => __DIR__ . '/../back/getMyActivity.php',
    'getAIEventRecommendations.php'=> __DIR__ . '/../back/getAIEventRecommendations.php',
    'getEventProgress.php'         => __DIR__ . '/../back/getEventProgress.php',
    'likeEvenement.php'            => __DIR__ . '/../back/likeEvenement.php',
    'getCommentaires.php'          => __DIR__ . '/../back/getCommentaires.php',
    'addCommentaire.php'           => __DIR__ . '/../back/addCommentaire.php',
    'getReactions.php'             => __DIR__ . '/../back/getReactions.php',
    'addReaction.php'              => __DIR__ . '/../back/addReaction.php',
];
foreach ($files as $label => $path) {
    $ok = file_exists($path);
    echo "$label: " . ($ok ? "<span class='ok'>✓ exists</span>" : "<span class='err'>✗ MISSING — $path</span>") . "<br>";
}

// 4. Live test getMyActivity
echo "<h2>4. getMyActivity.php Live Test</h2>";
$testEmail = $_SESSION['email'] ?? 'harrabibalkis99@gmail.com';
$url = "http://localhost/integration/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/back/getMyActivity.php?email=" . urlencode($testEmail);
echo "Testing with email: <span class='ok'>$testEmail</span><br>";
$resp = @file_get_contents($url);
if ($resp === false) {
    echo "<span class='err'>✗ Could not reach endpoint</span><br>";
} else {
    $json = json_decode($resp, true);
    if (isset($json['error'])) {
        echo "<span class='err'>Error: " . $json['error'] . "</span><br>";
    } else {
        echo "<span class='ok'>✓ Response OK</span><br>";
        echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
    }
}

// 5. Live test getAIEventRecommendations
echo "<h2>5. getAIEventRecommendations.php Live Test</h2>";
$url2 = "http://localhost/integration/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/back/getAIEventRecommendations.php";
$resp2 = @file_get_contents($url2);
if ($resp2 === false) {
    echo "<span class='err'>✗ Could not reach endpoint</span><br>";
} else {
    $json2 = json_decode($resp2, true);
    if (isset($json2['error'])) {
        echo "<span class='err'>Error: " . $json2['error'] . "</span><br>";
    } else {
        echo "<span class='ok'>✓ Returns " . count($json2) . " recommendations</span><br>";
    }
}

// 6. Chatbot API key test
echo "<h2>6. Groq API Key Test</h2>";
if (!defined('GROQ_API_KEY') || GROQ_API_KEY === '' || GROQ_API_KEY === 'gsk_placeholder_replace_with_real_groq_key') {
    echo "<span class='err'>✗ No valid API key — set GROQ_API_KEY in config.php</span><br>";
    echo "<span class='warn'>Get a free key at: https://console.groq.com/keys</span><br>";
} else {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'model'    => 'llama-3.3-70b-versatile',
            'messages' => [['role' => 'user', 'content' => 'Say OK']],
            'max_tokens' => 5,
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_TIMEOUT => 10,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo "<span class='err'>cURL error: $err</span><br>";
    } elseif ($code === 200) {
        echo "<span class='ok'>✓ API key valid — Groq responded 200 OK</span><br>";
    } else {
        $body = json_decode($res, true);
        echo "<span class='err'>✗ HTTP $code — " . ($body['error']['message'] ?? $res) . "</span><br>";
    }
}

echo "<br><hr style='border-color:#334155'><p style='color:#475569'>Debug complete — " . date('H:i:s') . "</p>";
?>
