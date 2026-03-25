<?php

namespace App\Model;

use App\Contract\DatabaseConnectionInterface;
use Psr\Log\LoggerInterface;

class DatabaseInformix implements DatabaseConnectionInterface
{
    private $dsn;
    private $user;
    private $password;
    private $conn;
    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->dsn = $_ENV['DB_DNS_INFORMIX'] ?? null;
        $this->user = $_ENV['DB_USERNAME_INFORMIX'] ?? null;
        $this->password = $_ENV['DB_PASSWORD_INFORMIX'] ?? null;
        $this->logger = $logger;
    }

    /** 
     * Méthode pour établir la connexion à la base de données
     * */
    public function connect()
    {
        try {
            if (!$this->dsn || !$this->user || !$this->password) {
                throw new \Exception("Les variables d'environnement DB_DNS_INFORMIX, DB_USERNAME_INFORMIX ou DB_PASSWORD_INFORMIX ne sont pas définies.");
            }

            if (!is_resource($this->conn)) {
                $this->conn = odbc_connect($this->dsn, $this->user, $this->password);
                if (!is_resource($this->conn)) {
                    throw new \Exception("ODBC Connection failed: " . odbc_errormsg());
                }
            }
            return $this->conn;
        } catch (\Exception $e) {
            $this->logMessage('error', $e->getMessage());
            throw $e; // Relance l'exception pour être gérée par Symfony
        }
    }

    /** 
     * Méthode pour exécuter une requête SQL avec support optionnel des paramètres
     * */
    public function executeQuery(string $query, array $params = [])
    {
        try {
            if (!is_resource($this->conn)) {
                $this->connect(); // Tentative de connexion si pas déjà établie
            }

            if (empty($params)) {
                $this->logMessage('info', "Executing Query: " . $query);
                // Le @ supprime l'affichage du warning PHP d'odbc_exec()
                $result = @odbc_exec($this->conn, $query);
            } else {
                // Préparation de la requête pour les paramètres
                // Regex améliorée : ne matche les :nom que s'ils ne sont pas précédés d'un caractère de nom d'objet (ex: base:table)
                $orderedParams = [];
                $queryWithPlaceholders = preg_replace_callback('/(?<![a-zA-Z0-9_]):([a-zA-Z0-9_]+)/', function($matches) use ($params, &$orderedParams) {
                    $key = $matches[1];
                    if (array_key_exists($key, $params)) {
                        $value = $params[$key];
                        // Conversion des booléens pour ODBC/Informix
                        if (is_bool($value)) {
                            $value = $value ? 1 : 0;
                        }
                        $orderedParams[] = $value;
                        return '?';
                    }
                    return $matches[0]; // Laisser tel quel si pas dans les params
                }, $query);

                $this->logMessage('info', "Executing Prepared Query: " . $queryWithPlaceholders . " with params: " . json_encode($orderedParams));

                $stmt = odbc_prepare($this->conn, $queryWithPlaceholders);
                if (!$stmt) {
                    throw new \Exception("ODBC Prepare failed: " . odbc_errormsg());
                }

                $result = odbc_execute($stmt, $orderedParams);
                if (!$result) {
                    throw new \Exception("ODBC Execute failed: " . odbc_errormsg());
                }
                
                // Pour ODBC, odbc_execute retourne un booléen, mais on a besoin du statement pour fetcher
                $result = $stmt;
            }

            if (!$result) {
                throw new \Exception("ODBC Query failed: " . odbc_errormsg());
            }
            return $result;
        } catch (\Exception $e) {
            $this->logMessage('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Méthode pour récupérer les résultats d'une requête
     * sous forme d'un tableau associatif
     */
    public function fetchResults($result)
    {
        $rows = [];
        if ($result) {
            while ($row = odbc_fetch_array($result)) {
                $rows[] = $this->convertToUtf8($row);
            }
        }
        return $rows;
    }

    /**
     * Méthode pour récupérer les résultats d'une requête
     * sous forme d'une seule ligne (tableau associatif)
     */
    public function fetchScalarResults($result)
    {
        if ($result) {
            $row = odbc_fetch_array($result);
            if ($row !== false) {
                return $this->convertToUtf8($row);
            }
        }
        return [];
    }

    /**
     * Convertit récursivement les données en UTF-8
     */
    private function convertToUtf8($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertToUtf8($value);
            }
        } elseif (is_string($data)) {
            // Détection et conversion intelligente vers UTF-8
            return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1, UTF-8, ASCII');
        }

        return $data;
    }

    /**
     * Méthode pour fermer la connexion à la base de données
     */
    public function close()
    {
        if ($this->conn) {
            odbc_close($this->conn);
            $this->conn = null;
            $this->logMessage('info', "Connexion Informix fermée.");
        }
    }

    /**
     * Centralisation des logs pour supporter l'ancien et le nouveau mode
     */
    private function logMessage(string $level, string $message)
    {
        if ($this->logger) {
            $this->logger->$level($message);
        } else {
            // Fallback si aucun logger n'est injecté (ancien mode)
            $formattedMessage = sprintf("[%s][%s] %s\n", date("Y-m-d H:i:s"), strtoupper($level), $message);
            $logPath = $_ENV['BASE_PATH_LOG'] ?? 'var/log/app_errors.log';
            
            // Assure que le chemin finit par le fichier de log si ce n'est pas déjà le cas
            if (strpos($logPath, '.log') === false) {
                $logPath = rtrim($logPath, '/') . '/app_errors.log';
            }

            // Création du répertoire si nécessaire
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }

            @error_log($formattedMessage, 3, $logPath);
        }
    }
}
