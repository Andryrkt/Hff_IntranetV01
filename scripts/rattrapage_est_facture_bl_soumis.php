<?php

/**
 * Rattrapage de da_afficher.est_facture_bl_soumis pour les soumissions facture BL / BAP
 * faites pendant que getRefDesiSavLor ne retrouvait aucun article (hotfix 311d76358).
 *
 * Reproduit la logique de modificationDaAfficher() (TraitementSoumissionfacBlService / BAP) :
 * articles de la livraison dans IPS (sav_lor pour les DA avec DIT / réappro, frn_cdl sinon),
 * puis ligne da_afficher de la version max (DA, CDE, société, référence, désignation).
 *
 * Usage :
 *   php scripts/rattrapage_est_facture_bl_soumis.php --depuis=2026-10-14              (simulation, ne modifie rien)
 *   php scripts/rattrapage_est_facture_bl_soumis.php --depuis=2026-10-14 --executer   (met à jour da_afficher)
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable($root)->load();

$options  = getopt('', ['depuis:', 'executer']);
$depuis   = $options['depuis'] ?? null;
$executer = isset($options['executer']);

if (!$depuis || !\DateTime::createFromFormat('Y-m-d', $depuis)) {
    exit("Usage : php scripts/rattrapage_est_facture_bl_soumis.php --depuis=AAAA-MM-JJ [--executer]\n");
}

// types de DA dont les articles livrés sont lus dans sav_lor (cf. modificationDaAfficher)
const TYPES_DA_SAV_LOR = [0, 2, 3]; // TYPE_DA_AVEC_DIT, TYPE_DA_REAPPRO_MENSUEL, TYPE_DA_REAPPRO_PONCTUEL

$sql = odbc_connect($_ENV['DB_DNS_SQLSERV'], $_ENV['DB_USERNAME_SQLSERV'], $_ENV['DB_PASSWORD_SQLSERV']);
if (!$sql) exit("Connexion SQL Server impossible : " . odbc_errormsg() . "\n");
$ips = new \App\Model\da\DaSoumissionFacBlModel();

echo "Base SQL Server : {$_ENV['DB_DNS_SQLSERV']} / IPS : {$_ENV['DB_DNS_INFORMIX']}\n";
echo $executer ? "MODE EXÉCUTION : da_afficher sera mis à jour\n\n" : "MODE SIMULATION : aucune modification (ajouter --executer pour appliquer)\n\n";

function lignes($conn, string $requete, array $params = []): array
{
    // les colonnes SQL Server sont en Windows-1252 (désignations accentuées)
    $params = array_map(fn($v) => is_string($v) ? mb_convert_encoding($v, 'Windows-1252', 'UTF-8') : $v, $params);
    $stmt = odbc_prepare($conn, $requete);
    if (!$stmt || !odbc_execute($stmt, $params)) exit("Erreur SQL : " . odbc_errormsg($conn) . "\n$requete\n");
    $resultat = [];
    while ($ligne = odbc_fetch_array($stmt)) {
        $resultat[] = array_map(fn($v) => is_string($v) ? trim(mb_convert_encoding($v, 'UTF-8', 'Windows-1252')) : $v, $ligne);
    }
    return $resultat;
}

$soumissions = lignes($sql, "SELECT DISTINCT numero_demande_appro, numero_cde, numero_livraison, code_societe
    FROM da_soumission_facture_bl WHERE date_creation >= ? ORDER BY numero_demande_appro, numero_cde, numero_livraison", ["$depuis 00:00:00"]);

$idsAMettreAJour = [];
$totaux = ['soumissions' => 0, 'articles' => 0, 'deja' => 0, 'a_maj' => 0, 'non_trouves' => 0];

foreach ($soumissions as $s) {
    [$numDa, $numCde, $numLiv, $soc] = [$s['numero_demande_appro'], $s['numero_cde'], $s['numero_livraison'], $s['code_societe'] ?: 'HF'];
    $totaux['soumissions']++;

    $info = lignes($sql, "SELECT MAX(numero_version) AS version, MAX(da_type_id) AS type_da
        FROM da_afficher WHERE numero_demande_appro = ? AND code_societe = ?", [$numDa, $soc])[0] ?? [];
    $version = $info['version'] ?? null;
    $typeDa  = $info['type_da'] ?? null;

    $articles = in_array((int) $typeDa, TYPES_DA_SAV_LOR, true)
        ? $ips->getRefDesiSavLor($numLiv, $soc)
        : $ips->getRefDesiFrnCdl($numLiv, $soc);

    $deja = 0; $aMaj = 0; $nonTrouves = [];
    foreach ($articles as $article) {
        // même critère que findOneBy() dans modificationDaAfficher
        $ligne = lignes($sql, "SELECT TOP 1 id, est_facture_bl_soumis FROM da_afficher
            WHERE numero_demande_appro = ? AND numero_version = ? AND numero_cde = ? AND code_societe = ? AND art_refp = ? AND art_desi = ?
            ORDER BY id", [$numDa, $version, $numCde, $soc, $article['reference'], $article['designation']])[0] ?? null;

        if (!$ligne) {
            $nonTrouves[] = $article['designation'];
        } elseif ((int) $ligne['est_facture_bl_soumis'] === 1) {
            $deja++;
        } else {
            $aMaj++;
            $idsAMettreAJour[(int) $ligne['id']] = true;
        }
    }

    $totaux['articles']    += count($articles);
    $totaux['deja']        += $deja;
    $totaux['a_maj']       += $aMaj;
    $totaux['non_trouves'] += count($nonTrouves);

    if ($aMaj > 0 || $nonTrouves || !$articles) {
        echo "$numDa / CDE $numCde / LIV $numLiv (version $version, type $typeDa) : "
            . count($articles) . " article(s), $deja déjà à 1, $aMaj à mettre à jour"
            . ($nonTrouves ? ", NON TROUVÉ(S) : " . implode(' | ', $nonTrouves) : '')
            . (!$articles ? " - AUCUN ARTICLE DANS IPS" : '') . "\n";
    }
}

echo "\n{$totaux['soumissions']} soumission(s) depuis le $depuis : {$totaux['articles']} article(s) livré(s), "
    . "{$totaux['deja']} déjà à 1, {$totaux['a_maj']} ligne(s) da_afficher à mettre à jour, {$totaux['non_trouves']} non trouvé(s).\n";

if (!$executer || !$idsAMettreAJour) {
    exit($executer ? "Rien à mettre à jour.\n" : "Simulation terminée : relancer avec --executer pour appliquer.\n");
}

odbc_autocommit($sql, false);
foreach (array_chunk(array_keys($idsAMettreAJour), 500) as $lot) {
    $marqueurs = implode(',', array_fill(0, count($lot), '?'));
    $stmt = odbc_prepare($sql, "UPDATE da_afficher SET est_facture_bl_soumis = 1, date_modification = GETDATE()
        WHERE est_facture_bl_soumis = 0 AND id IN ($marqueurs)");
    if (!$stmt || !odbc_execute($stmt, $lot)) {
        odbc_rollback($sql);
        exit("Erreur, aucune modification appliquée : " . odbc_errormsg($sql) . "\n");
    }
}
odbc_commit($sql);
echo count($idsAMettreAJour) . " ligne(s) da_afficher passée(s) à est_facture_bl_soumis = 1.\n";
