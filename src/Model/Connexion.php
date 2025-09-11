<?php

namespace App\Model;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

class Connexion
{
    private $DB;
    private $User;
    private $pswd;
    private $conn;
    private $logger;
    private $isConnected = false;
    private $inTransaction = false;

    /**
     * Constructeur de la classe Connexion
     * @param string $dbDns DSN de la base de données
     * @param string $dbUsername Nom d'utilisateur
     * @param string $dbPassword Mot de passe
     * @param PsrLoggerInterface $logger Logger pour les logs
     * @throws \InvalidArgumentException Si les paramètres sont invalides
     * @throws \RuntimeException Si la connexion échoue
     */
    public function __construct(
        string $dbDns,
        string $dbUsername,
        string $dbPassword,
        PsrLoggerInterface $logger
    ) {
        $this->DB = $dbDns;
        $this->User = $dbUsername;
        $this->pswd = $dbPassword;
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
        if (empty($this->DB)) {
            throw new \InvalidArgumentException("Le DSN de la base de données ne peut pas être vide.");
        }

        if (empty($this->User)) {
            throw new \InvalidArgumentException("Le nom d'utilisateur ne peut pas être vide.");
        }

        if (empty($this->pswd)) {
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
            $this->logger->info("Tentative de connexion à la base de données ODBC", [
                'dsn' => $this->DB,
                'user' => $this->User
            ]);

            $this->conn = odbc_connect($this->DB, $this->User, $this->pswd);

            if (!$this->conn || !is_resource($this->conn)) {
                $error = odbc_errormsg() ?: 'Connexion invalide ou échouée';
                $this->logger->error("Échec de la connexion ODBC", [
                    'error' => $error,
                    'dsn' => $this->DB,
                    'user' => $this->User
                ]);
                throw new \RuntimeException("Échec de la connexion ODBC: " . $error);
            }

            $this->isConnected = true;
            $this->logger->info("Connexion à la base de données ODBC établie avec succès");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la connexion", [
                'message' => $e->getMessage(),
                'dsn' => $this->DB,
                'user' => $this->User
            ]);
            throw new \RuntimeException("Impossible de se connecter à la base de données ODBC: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Vérifie si la connexion est active
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->isConnected && is_resource($this->conn);
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
     * @param bool $retry Indique si on doit retenter en cas d'échec
     * @throws \RuntimeException
     */
    private function ensureConnection(bool $retry = true): void
    {
        if (!$this->isConnected() || !$this->ping()) {
            $this->logger->info("Tentative de reconnexion à la base de données");
            try {
                $this->connect();
            } catch (\Exception $e) {
                $this->logger->error("Erreur lors de la reconnexion", ['message' => $e->getMessage()]);
                if ($retry) {
                    throw new \RuntimeException("Connexion ODBC non valide et reconnexion impossible.", 0, $e);
                }
            }
        }
    }

    /**
     * Obtient la connexion ODBC
     * @param bool $retry Indique si on doit retenter en cas d'échec
     * @return resource La connexion ODBC
     * @throws \RuntimeException
     */
    public function getConnexion(bool $retry = true)
    {
        $this->ensureConnection($retry);
        return $this->conn;
    }

    /**
     * Exécute une requête SQL simple
     * @param string $sql La requête SQL à exécuter
     * @return resource|false Le résultat de la requête
     * @throws \RuntimeException
     */
    public function query(string $sql)
    {
        try {
            $conn = $this->getConnexion();

            $this->logger->debug("Exécution de la requête SQL", ['query' => $sql]);

            $result = odbc_exec($conn, $sql);

            // Échec → tentative de reconnexion unique
            if (!$result) {
                $this->logger->warning("Première exécution échouée. Tentative de reconnexion...");
                $conn = $this->getConnexion(false); // Retente sans boucle
                $result = odbc_exec($conn, $sql);
            }

            if (!$result) {
                $errorMsg = odbc_errormsg($conn);
                $this->logger->error("Échec de l'exécution de la requête ODBC", [
                    'query' => $sql,
                    'error' => $errorMsg
                ]);
                throw new \RuntimeException("Échec de l'exécution de la requête ODBC: " . $errorMsg);
            }

            $this->logger->debug("Requête exécutée avec succès");
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de l'exécution de la requête", [
                'query' => $sql,
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException("Erreur lors de l'exécution de la requête: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Prépare et exécute une requête avec paramètres
     * @param string $sql La requête SQL à préparer
     * @param array $params Les paramètres à passer à la requête
     * @return resource|false Le résultat de la requête
     * @throws \RuntimeException
     */
    public function prepareAndExecute(string $sql, array $params = [])
    {
        try {
            $this->ensureConnection();

            $this->logger->debug("Préparation et exécution de la requête", [
                'query' => $sql,
                'params' => $params
            ]);

            $stmt = odbc_prepare($this->conn, $sql);
            if (!$stmt) {
                $errorMsg = odbc_errormsg($this->conn);
                $this->logger->error("Échec de la préparation ODBC", [
                    'query' => $sql,
                    'error' => $errorMsg
                ]);
                throw new \RuntimeException("Échec de la préparation ODBC: " . $errorMsg);
            }

            if (!odbc_execute($stmt, $params)) {
                $errorMsg = odbc_errormsg($this->conn);
                $this->logger->error("Échec de l'exécution ODBC", [
                    'query' => $sql,
                    'params' => $params,
                    'error' => $errorMsg
                ]);
                throw new \RuntimeException("Échec de l'exécution ODBC: " . $errorMsg);
            }

            $this->logger->debug("Requête préparée exécutée avec succès");
            return $stmt;
        } catch (\Exception $e) {
            $this->logger->error("Exception lors de la préparation/exécution de la requête", [
                'query' => $sql,
                'params' => $params,
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException("Erreur lors de la préparation/exécution de la requête: " . $e->getMessage(), 0, $e);
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

            $this->query("BEGIN WORK");
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

            $this->query("COMMIT WORK");
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

            $this->query("ROLLBACK WORK");
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
                $this->logger->info("Connexion ODBC fermée");
            } else {
                $this->logger->warning("Tentative de fermer une connexion ODBC non établie");
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
            'dsn' => $this->DB,
            'user' => $this->User,
            'isConnected' => $this->isConnected,
            'inTransaction' => $this->inTransaction
        ];
    }
}
