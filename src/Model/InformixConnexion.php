<?php

namespace App\Model;

class InformixConnexion extends AbstractDatabaseConnexion
{
    public function __construct()
    {
        $dsn = $_ENV['DB_DNS_INFORMIX_PDO_ODBC'];
        $username = $_ENV['DB_USERNAME_INFORMIX_PDO_ODBC'];
        $password = $_ENV['DB_PASSWORD_INFORMIX_PDO_ODBC'];

        parent::__construct($dsn, $username, $password);
    }
}
