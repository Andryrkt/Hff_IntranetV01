<?php

namespace App\Service\historiqueOperation\suivi;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Enregistre les requêtes SQL (SQL Server, connexion Doctrine) exécutées pendant une opération suivie,
 * avec leur durée, pour les rattacher aux étapes du suivi (voir SuiviOperationService).
 *
 * Seul le texte SQL est conservé (les valeurs restent des "?"). Les requêtes du suivi lui-même ne sont pas
 * retenues. Un logger déjà installé continue d'être appelé.
 */
class CollecteurRequetesSql implements SQLLogger
{
    private const TABLES_IGNOREES = ['historique_operation_etape'];
    private const NOMBRE_MAX = 500;

    private ?SQLLogger $loggerPrecedent;

    /** @var array<int, array{sql: string, duree: float}> */
    private array $requetes = [];
    private ?string $sqlEnCours = null;
    private float $debut = 0.0;

    public function __construct(?SQLLogger $loggerPrecedent = null)
    {
        $this->loggerPrecedent = $loggerPrecedent;
    }

    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->sqlEnCours = $sql;
        $this->debut = microtime(true);

        if ($this->loggerPrecedent !== null) {
            $this->loggerPrecedent->startQuery($sql, $params, $types);
        }
    }

    public function stopQuery()
    {
        if ($this->sqlEnCours !== null && $this->estARetenir($this->sqlEnCours) && count($this->requetes) < self::NOMBRE_MAX) {
            $this->requetes[] = ['sql' => $this->sqlEnCours, 'duree' => (microtime(true) - $this->debut) * 1000];
        }
        $this->sqlEnCours = null;

        if ($this->loggerPrecedent !== null) {
            $this->loggerPrecedent->stopQuery();
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

        $dureeTotale = array_sum(array_column($requetes, 'duree'));
        $resume = sprintf('%d requête(s) SQL (%d ms)', count($requetes), round($dureeTotale));

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

        $lignes = ['Requêtes SQL :'];
        foreach ($requetes as $i => $requete) {
            $sql = preg_replace('/\s+/', ' ', trim($requete['sql']));
            $lignes[] = sprintf('%d. [%d ms] %s', $i + 1, round($requete['duree']), mb_strlen($sql) > 400 ? mb_substr($sql, 0, 400) . '...' : $sql);
        }

        return implode("\n", $lignes);
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
