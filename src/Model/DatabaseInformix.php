<?php

namespace App\Model;

class DatabaseInformix
{
    private $conn;

    public function __construct($hostname, $port, $database, $username, $password)
    {
        // Construire la chaîne de connexion ODBC
        $dsn = "Driver={Informix};Server=$hostname;Port=$port;Database=$database;PROTOCOL=onsoctcp;UID=$username;PWD=$password";

        // Établir la connexion à la base de données
        $this->conn = odbc_connect($dsn, 'informix', 'informix');

        if (!$this->conn) {
            die("Impossible de se connecter à la base de données Informix");
        }
    }

    public function query($sql)
    {
        // Exécuter la requête
        $result = odbc_exec($this->conn, $sql);

        // Vérifier si la requête a réussi
        if ($result) {
            $data = array();
            while ($row = odbc_fetch_array($result)) {
                $data[] = $row;
            }
            return $data;
        } else {
            return false;
        }
    }

    public function close()
    {
        // Fermer la connexion à la base de données
        odbc_close($this->conn);
    }
}
