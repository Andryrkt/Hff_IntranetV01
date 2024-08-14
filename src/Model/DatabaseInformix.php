<?php

namespace App\Model;

class DatabaseInformix
{
    private $dsn;
    private $user;
    private $password;
    private $conn;

    // Constructeur
    public function __construct()
    {
        // Récupération des variables d'environnement
        $this->dsn = $_ENV['DB_DNS_INFORMIX'] ;
        $this->user = $_ENV['DB_USERNAME_INFORMIX'];
        $this->password = $_ENV['DB_PASSWORD_INFORMIX'];
        
        // Établissement de la connexion
        $this->connect();
    }

    // Méthode pour établir la connexion à la base de données
    private function connect()
    {
        $this->conn = odbc_connect($this->dsn, $this->user, $this->password);
        if (!$this->conn) {
            throw new \Exception("ODBC Connection failed: " . odbc_errormsg());
        }
    }

    // Méthode pour exécuter une requête SQL
    public function executeQuery($query)
    {
        if (!$this->conn) {
            throw new \Exception("La connexion à la base de données n'est pas établie.");
        }

        $result = odbc_exec($this->conn, $query);
        if (!$result) {
            throw new \Exception("ODBC Query failed: " . odbc_errormsg($this->conn));
        }
        return $result;
    }

    // Méthode pour récupérer les résultats d'une requête
    public function fetchResults($result)
    {
        $rows = array();
        if ($result) {
            while ($row = odbc_fetch_array($result)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    // Méthode pour fermer la connexion à la base de données
    public function close()
    {
        if ($this->conn) {
            odbc_close($this->conn);
            echo "Connexion fermée.\n";
        } else {
            echo "La connexion à la base de données n'est pas établie.";
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
