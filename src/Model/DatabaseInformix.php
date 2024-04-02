<?php

namespace App\Model;

class DatabaseInformix
{

    private $dsn = 'IPS_HFFPROD';
    private $user = 'informix';
    private $password = 'informix';
    private $conn;

    // Constructeur
    public function __construct()
    {
        $this->conn = odbc_connect($this->dsn, $this->user, $this->password);
        if (!$this->conn) {
            throw new \Exception("ODBC Conenction failed:" . odbc_error());
        }
    }

    // Méthode pour établir la connexion à la base de données
    public function connect()
    {
        return $this->conn;
    }

    // Méthode pour exécuter une requête SQL
    public function executeQuery($query)
    {
        if ($this->conn) {
            $result = odbc_exec($this->conn, $query);
            return $result;
        } else {
            echo "La connexion à la base de données n'est pas établie.";
            return false;
        }
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
}
