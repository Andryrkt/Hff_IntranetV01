<?php

/**
 * Script de test de performance de l'application web
 */

echo "=== TEST DE PERFORMANCE WEB ===\n\n";

// Test 1: Temps de chargement de la page de login
echo "1. Test de chargement de la page de login...\n";
$start = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/Hffintranet/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$end = microtime(true);
$loginTime = ($end - $start) * 1000;
echo "   Temps de chargement de la page de login: " . number_format($loginTime, 2) . " ms\n";
echo "   Code HTTP: " . $httpCode . "\n\n";

// Test 2: Temps de connexion
echo "2. Test de connexion...\n";
$start = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/Hffintranet/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['Username' => 'admin', 'Pswd' => 'admin']));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$end = microtime(true);
$authTime = ($end - $start) * 1000;
echo "   Temps de connexion: " . number_format($authTime, 2) . " ms\n";
echo "   Code HTTP: " . $httpCode . "\n\n";

// Test 3: Temps total
$totalTime = $loginTime + $authTime;
echo "3. TEMPS TOTAL: " . number_format($totalTime, 2) . " ms\n\n";

// Recommandations
echo "=== RECOMMANDATIONS ===\n";
if ($totalTime > 5000) {
    echo "❌ PERFORMANCE CRITIQUE: L'application met plus de 5 secondes à répondre\n";
    echo "   - Le cache du conteneur n'est pas activé\n";
    echo "   - Tous les services sont rechargés à chaque requête\n";
} elseif ($totalTime > 2000) {
    echo "⚠️  PERFORMANCE MOYENNE: L'application met plus de 2 secondes à répondre\n";
    echo "   - Considérez l'activation du cache\n";
} else {
    echo "✅ PERFORMANCE BONNE: L'application répond rapidement\n";
}

echo "\n=== DÉTAILS ===\n";
echo "Page de login: " . number_format($loginTime, 2) . " ms\n";
echo "Connexion: " . number_format($authTime, 2) . " ms\n";
echo "Total: " . number_format($totalTime, 2) . " ms\n";

// Nettoyer
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
