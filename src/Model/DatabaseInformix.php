<?php

namespace App\Model;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

class DatabaseInformix
{
    private $dsn;
    private $user;
    private $password;
    private $conn;
    private $logger;
    private $isConnected = false;
    private $inTransaction = false;

    // Constructeur
    public function __construct(
        string $dsn,
        string $user,
        string $password,
        PsrLoggerInterface $logger
    ) {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $this->logger = $logger;

        $this->validateConnectionParameters();
        $this->connect();
    }

    /**
     * Valide les paramètres de connexion
     * @throws \InvalidArgumentException
     */
    private function validateConnectionParameters(): void
    {
        if (empty($this->dsn)) {
            throw new \InvalidArgumentException("Le DSN de connexion ne peut pas être vide.");
        }

        if (empty($this->user)) {
            throw new \InvalidArgumentException("Le nom d'utilisateur ne peut pas être vide.");
        }

        if (empty($this->password)) {
            throw new \InvalidArgumentException("Le mot de passe ne peut pas être vide.");
        }
    }

    /**
     * Établit la connexion à la base de données
     * @throws \RuntimeException
     */
    private function connect(): void
    {
        try {
            $this->logger->info("Tentative de connexion à la base de données Informix", [
                'dsn' => $this->dsn,
                'user' => $this->user
            ]);

            $this->conn = odbc_connect($this->dsn, $this->user, $this->password);

            if (!$this->conn) {
                $error = odbc_errormsg();
                $this->logger->error("Échec de la connexion ODBC", [
                    'error' => $error,
                    'dsn' => $this->dsn,
                    'user' => $this->user
                ]);
                throw new \RuntimeException("Échec de la connexion ODBC: " . $error);
            }

            $this->isConnected = true;
            $this->logger->info("Connexion à la base de données Informix établie avec succès");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la connexion", [
                'message' => $e->getMessage(),
                'dsn' => $this->dsn,
                'user' => $this->user
            ]);
            throw new \RuntimeException("Impossible de se connecter à la base de données Informix: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Vérifie si la connexion est active
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->isConnected && $this->conn !== false;
    }

    /**
     * Vérifie la santé de la connexion
     * @return bool
     */
    public function ping(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $result = odbc_exec($this->conn, "SELECT 1");
            if ($result) {
                odbc_free_result($result);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->warning("Échec du ping de la base de données", ['error' => $e->getMessage()]);
            $this->isConnected = false;
            return false;
        }
    }

    /**
     * Reconnexion automatique si nécessaire
     * @throws \RuntimeException
     */
    private function ensureConnection(): void
    {
        if (!$this->isConnected() || !$this->ping()) {
            $this->logger->info("Tentative de reconnexion à la base de données");
            $this->connect();
        }
    }

    /**
     * Exécute une requête SQL simple
     * @param string $query La requête SQL à exécuter
     * @return resource|false Le résultat de la requête
     * @throws \RuntimeException
     */
    public function executeQuery(string $query)
    {
        try {
            $this->ensureConnection();

            $this->logger->debug("Exécution de la requête SQL", ['query' => $query]);

            $result = odbc_exec($this->conn, $query);
            if (!$result) {
                $errorMsg = odbc_errormsg($this->conn);
                $this->logger->error("Échec de l'exécution de la requête ODBC", [
                    'query' => $query,
                    'error' => $errorMsg
                ]);
                throw new \RuntimeException("Échec de l'exécution de la requête ODBC: " . $errorMsg);
            }

            $this->logger->debug("Requête exécutée avec succès");
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de l'exécution de la requête", [
                'query' => $query,
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException("Erreur lors de l'exécution de la requête: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Exécute une requête préparée (simulation avec échappement)
     * @param string $query La requête SQL avec des placeholders
     * @param array $params Les paramètres à échapper et insérer
     * @return resource|false Le résultat de la requête
     * @throws \RuntimeException
     */
    public function executePreparedQuery(string $query, array $params = [])
    {
        try {
            $this->ensureConnection();

            // Échappement basique des paramètres (à améliorer selon les besoins)
            $escapedParams = array_map(function ($param) {
                if (is_string($param)) {
                    return "'" . addslashes($param) . "'";
                }
                return $param;
            }, $params);

            $preparedQuery = vsprintf($query, $escapedParams);

            $this->logger->debug("Exécution de la requête préparée", [
                'query' => $preparedQuery,
                'params' => $params
            ]);

            return $this->executeQuery($preparedQuery);
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de l'exécution de la requête préparée", [
                'query' => $query,
                'params' => $params,
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException("Erreur lors de l'exécution de la requête préparée: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Récupère tous les résultats d'une requête sous forme de tableau
     * @param resource $result Le résultat de la requête
     * @return array Les résultats sous forme de tableau associatif
     */
    public function fetchResults($result): array
    {
        $rows = [];

        if (!$result) {
            $this->logger->warning("Tentative de récupération des résultats d'une requête invalide");
            return $rows;
        }

        try {
            while ($row = odbc_fetch_array($result)) {
                $rows[] = $row;
            }

            $this->logger->debug("Récupération des résultats", ['count' => count($rows)]);
            return $rows;
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la récupération des résultats", ['message' => $e->getMessage()]);
            throw new \RuntimeException("Erreur lors de la récupération des résultats: " . $e->getMessage(), 0, $e);
        } finally {
            odbc_free_result($result);
        }
    }

    /**
     * Récupère un seul résultat d'une requête
     * @param resource $result Le résultat de la requête
     * @return array|null Le premier résultat ou null si aucun résultat
     */
    public function fetchOne($result): ?array
    {
        if (!$result) {
            return null;
        }

        try {
            $row = odbc_fetch_array($result);
            odbc_free_result($result);
            return $row ?: null;
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la récupération d'un résultat", ['message' => $e->getMessage()]);
            throw new \RuntimeException("Erreur lors de la récupération d'un résultat: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Démarre une transaction
     * @throws \RuntimeException
     */
    public function beginTransaction(): void
    {
        try {
            $this->ensureConnection();

            if ($this->inTransaction) {
                throw new \RuntimeException("Une transaction est déjà en cours");
            }

            $this->executeQuery("BEGIN WORK");
            $this->inTransaction = true;
            $this->logger->info("Transaction démarrée");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors du démarrage de la transaction", ['message' => $e->getMessage()]);
            throw new \RuntimeException("Impossible de démarrer la transaction: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Valide une transaction
     * @throws \RuntimeException
     */
    public function commit(): void
    {
        try {
            if (!$this->inTransaction) {
                throw new \RuntimeException("Aucune transaction en cours");
            }

            $this->executeQuery("COMMIT WORK");
            $this->inTransaction = false;
            $this->logger->info("Transaction validée");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la validation de la transaction", ['message' => $e->getMessage()]);
            throw new \RuntimeException("Impossible de valider la transaction: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Annule une transaction
     * @throws \RuntimeException
     */
    public function rollback(): void
    {
        try {
            if (!$this->inTransaction) {
                $this->logger->warning("Tentative d'annulation d'une transaction inexistante");
                return;
            }

            $this->executeQuery("ROLLBACK WORK");
            $this->inTransaction = false;
            $this->logger->info("Transaction annulée");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'annulation de la transaction", ['message' => $e->getMessage()]);
            throw new \RuntimeException("Impossible d'annuler la transaction: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Ferme la connexion à la base de données
     */
    public function close(): void
    {
        try {
            if ($this->inTransaction) {
                $this->rollback();
            }

            if ($this->conn && $this->isConnected) {
                odbc_close($this->conn);
                $this->isConnected = false;
                $this->logger->info("Connexion Informix fermée");
            } else {
                $this->logger->warning("Tentative de fermer une connexion Informix non établie");
            }
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la fermeture de la connexion", ['message' => $e->getMessage()]);
        }
    }

    /**
     * Destructeur - ferme automatiquement la connexion
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Obtient des informations sur la connexion
     * @return array
     */
    public function getConnectionInfo(): array
    {
        return [
            'dsn' => $this->dsn,
            'user' => $this->user,
            'isConnected' => $this->isConnected,
            'inTransaction' => $this->inTransaction
        ];
    }
}
