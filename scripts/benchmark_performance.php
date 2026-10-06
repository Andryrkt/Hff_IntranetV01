<?php

use App\Entity\da\DaAfficher;
use App\Service\da\CdeFrnPresenter;

/**
 * Script de benchmark pour identifier la source de lenteur
 * Usage: php scripts/benchmark_performance.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require_once __DIR__ . '/../doctrineBootstrap.php';

echo "--- DEBUT DU BENCHMARK ---\n";

// 1. Benchmark de la requête Repository (SQL Server)
$start = microtime(true);
$repository = $entityManager->getRepository(DaAfficher::class);

// On simule les critères par défaut
$criteria = [];
$page = 1;
$limit = 20;

echo "[1/3] Exécution de la requête de pagination (SQL Server)... ";
$paginationData = $repository->findValidatedPaginatedDas($criteria, $page, $limit);
$sqlTime = microtime(true) - $start;
echo sprintf("%.3f s (%d lignes récupérées)\n", $sqlTime, count($paginationData['data']));

// 2. Benchmark du Presenter (Logic PHP + IPS)
$start = microtime(true);
// On a besoin d'un router factice pour le presenter
$router = new class implements \Symfony\Component\Routing\Generator\UrlGeneratorInterface {
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH): string { return "/fake-url"; }
    public function setContext(\Symfony\Component\Routing\RequestContext $context) {}
    public function getContext(): \Symfony\Component\Routing\RequestContext { return new \Symfony\Component\Routing\RequestContext(); }
};

$presenter = new CdeFrnPresenter($router);

echo "[2/3] Transformation des données (Presenter + IPS)... \n";
$loopStart = microtime(true);
$rowCount = 0;
foreach ($paginationData['data'] as $item) {
    $rowStart = microtime(true);
    $presenter->present([$item]);
    $rowTime = microtime(true) - $rowStart;
    $rowCount++;
    if ($rowCount <= 5) {
        echo sprintf("   - Ligne %d : %.3f s\n", $rowCount, $rowTime);
    }
}
$presenterTime = microtime(true) - $loopStart;
echo sprintf("Total Presenter : %.3f s\n", $presenterTime);

// 3. Analyse finale
echo "\n--- ANALYSE FINALE ---\n";
$totalTime = $sqlTime + $presenterTime;
echo sprintf("Temps Total : %.3f s\n", $totalTime);

if ($sqlTime > $presenterTime) {
    echo "VERDICT : La lenteur vient principalement de la REQUETE SQL (SQL Server).\n";
    echo "ACTION : Il est impératif d'exécuter les CREATE INDEX donnés précédemment.\n";
} else {
    echo "VERDICT : La lenteur vient principalement du PRESENTER (Appels réseau IPS/Informix).\n";
    echo "ACTION : Il faut optimiser les appels à DaModel (Batch fetching ou Cache agressif).\n";
}

echo "--- FIN DU BENCHMARK ---\n";
