<?php

namespace App\Service\historiqueOperation\suivi;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Enregistre les requêtes SQL (SQL Server, connexion Doctrine) exécutées pendant une opération suivie,
 * avec leur durée, pour les rattacher aux étapes du suivi (voir SuiviOperationService).
 *
 * Le texte SQL est conservé avec les valeurs liées aux "?" (affichées dans l'ordre). Les requêtes du suivi lui-même ne sont pas
 * retenues. Un logger déjà installé continue d'être appelé.
 */
class CollecteurRequetesSql implements SQLLogger
{
    private const TABLES_IGNOREES = ['historique_operation_etape'];
    private const NOMBRE_MAX = 500;

    private ?SQLLogger $loggerPrecedent;

    /** @var array<int, array{sql: string, duree: float, source?: string}> source : "SQL" (SQL Server, par défaut) ou "IPS" */
    private array $requetes = [];
    private ?string $sqlEnCours = null;
    private array $paramsEnCours = [];
    private float $debut = 0.0;

    public function __construct(?SQLLogger $loggerPrecedent = null)
    {
        $this->loggerPrecedent = $loggerPrecedent;
    }

    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->sqlEnCours = $sql;
        $this->paramsEnCours = $params ?? [];
        $this->debut = microtime(true);

        if ($this->loggerPrecedent !== null) {
            $this->loggerPrecedent->startQuery($sql, $params, $types);
        }
    }

    public function stopQuery()
    {
        if ($this->sqlEnCours !== null && $this->estARetenir($this->sqlEnCours) && count($this->requetes) < self::NOMBRE_MAX) {
            $this->requetes[] = ['sql' => $this->sqlEnCours, 'duree' => (microtime(true) - $this->debut) * 1000, 'params' => $this->paramsEnCours];
        }
        $this->sqlEnCours = null;

        if ($this->loggerPrecedent !== null) {
            $this->loggerPrecedent->stopQuery();
        }
    }

    /**
     * Enregistre une requête exécutée hors de Doctrine (ex. IPS / Informix par ODBC)
     *
     * @return int|null position de la requête, pour y ajouter ensuite le temps de lecture des résultats
     */
    public function enregistrerExterne(string $source, string $sql, float $dureeMs): ?int
    {
        if (count($this->requetes) >= self::NOMBRE_MAX) {
            return null;
        }
        // certaines requêtes IPS sont construites en ISO-8859-1 (ex. désignation accentuée pour Informix) :
        // le texte est reconverti en UTF-8, sinon SQL Server refuse l'enregistrement du suivi
        if (!mb_check_encoding($sql, 'UTF-8')) {
            $sql = mb_convert_encoding($sql, 'UTF-8', 'ISO-8859-1');
        }
        $this->requetes[] = ['sql' => $sql, 'duree' => $dureeMs, 'source' => $source];

        return count($this->requetes) - 1;
    }

    /**
     * Ajoute un temps (ex. lecture des lignes de résultat) à une requête déjà enregistrée
     */
    public function ajouterDuree(int $position, float $dureeMs): void
    {
        if (isset($this->requetes[$position])) {
            $this->requetes[$position]['duree'] += $dureeMs;
        }
    }

    /**
     * Position actuelle : les requêtes d'une étape sont celles enregistrées entre son ouverture et sa fermeture
     */
    public function position(): int
    {
        return count($this->requetes);
    }

    /**
     * @return array<int, array{sql: string, duree: float}>
     */
    public function requetesDepuis(int $position): array
    {
        return array_slice($this->requetes, $position);
    }

    /**
     * Résumé d'une liste de requêtes : "3 requête(s) SQL (552 ms) · UPDATE demande_intervention (numero_or, id_statut_demande)"
     */
    public static function resume(array $requetes): ?string
    {
        if (empty($requetes)) {
            return null;
        }

        // un compteur par base : SQL Server ("SQL") et IPS / Informix ("IPS")
        $parSource = [];
        foreach ($requetes as $requete) {
            $source = $requete['source'] ?? 'SQL';
            $parSource[$source]['nombre'] = ($parSource[$source]['nombre'] ?? 0) + 1;
            $parSource[$source]['duree'] = ($parSource[$source]['duree'] ?? 0) + $requete['duree'];
        }
        $resume = implode(' · ', array_map(function ($source, $total) {
            return sprintf('%d requête(s) %s (%d ms)', $total['nombre'], $source, round($total['duree']));
        }, array_keys($parSource), $parSource));

        $ecritures = [];
        foreach ($requetes as $requete) {
            $ecriture = self::analyserEcriture($requete['sql']);
            if ($ecriture === null) {
                continue;
            }
            [$operation, $table, $colonnes] = $ecriture;
            $cle = "$operation $table";
            $ecritures[$cle] = array_unique(array_merge($ecritures[$cle] ?? [], $colonnes));
        }

        foreach ($ecritures as $cle => $colonnes) {
            $resume .= ' · ' . $cle . (empty($colonnes) ? '' : ' (' . implode(', ', $colonnes) . ')');
        }

        return $resume;
    }

    /**
     * Détail des requêtes pour la partie "Détail technique" : durée et texte SQL, dans l'ordre d'exécution
     */
    public static function detail(array $requetes): ?string
    {
        if (empty($requetes)) {
            return null;
        }

        $lignes = ['Requêtes :'];
        foreach ($requetes as $i => $requete) {
            $sql = preg_replace('/\s+/', ' ', trim($requete['sql']));
            $lignes[] = sprintf('%d. [%s %d ms] %s', $i + 1, $requete['source'] ?? 'SQL', round($requete['duree']), mb_strlen($sql) > 400 ? mb_substr($sql, 0, 400) . '...' : $sql)
                . (empty($requete['params']) ? '' : ' ⇐ valeurs : ' . implode(', ', array_map([self::class, 'formaterValeur'], $requete['params'])));
        }

        return implode("\n", $lignes);
    }

    /**
     * Valeur d'un paramètre lié, lisible et courte, dans l'ordre des "?"
     */
    private static function formaterValeur($valeur): string
    {
        if ($valeur === null) {
            return 'NULL';
        }
        if (is_bool($valeur)) {
            return $valeur ? '1' : '0';
        }
        if (is_int($valeur) || is_float($valeur)) {
            return (string) $valeur;
        }
        if ($valeur instanceof \DateTimeInterface) {
            return "'" . $valeur->format('Y-m-d H:i:s') . "'";
        }
        if (is_array($valeur)) {
            return '(' . implode(', ', array_map([self::class, 'formaterValeur'], $valeur)) . ')';
        }
        if (is_object($valeur) && !method_exists($valeur, '__toString')) {
            return get_class($valeur);
        }
        $texte = (string) $valeur;
        // même raison que pour les requêtes IPS : un texte non UTF-8 empêcherait l'enregistrement du suivi
        if (!mb_check_encoding($texte, 'UTF-8')) {
            $texte = mb_convert_encoding($texte, 'UTF-8', 'ISO-8859-1');
        }

        return "'" . (mb_strlen($texte) > 100 ? mb_substr($texte, 0, 100) . '...' : $texte) . "'";
    }

    /**
     * Table et colonnes écrites par une requête INSERT / UPDATE / DELETE, null pour une lecture
     *
     * @return array{0: string, 1: string, 2: string[]}|null
     */
    private static function analyserEcriture(string $sql): ?array
    {
        $sql = trim($sql);

        if (preg_match('/^UPDATE\s+\[?(\w+)\]?\s+(?:\w+\s+)?SET\s+(.+?)(?:\s+WHERE\s+|$)/is', $sql, $m)) {
            preg_match_all('/\[?(\w+)\]?\s*=/', $m[2], $colonnes);
            return ['UPDATE', $m[1], $colonnes[1]];
        }
        if (preg_match('/^INSERT\s+INTO\s+\[?(\w+)\]?\s*\(([^)]*)\)/is', $sql, $m)) {
            return ['INSERT', $m[1], []];
        }
        if (preg_match('/^DELETE\s+FROM\s+\[?(\w+)\]?/is', $sql, $m)) {
            return ['DELETE', $m[1], []];
        }

        return null;
    }

    private function estARetenir(string $sql): bool
    {
        // les instructions de transaction ("START TRANSACTION", "COMMIT"...) sont gardées : un COMMIT peut être lent
        foreach (self::TABLES_IGNOREES as $table) {
            if (stripos($sql, $table) !== false) {
                return false;
            }
        }

        return true;
    }
}
