<?php

namespace App\Model;

class SqlServerConnexion extends AbstractDatabaseConnexion
{
    public function __construct()
    {
        $dsn = $_ENV['DB_DNS_SQLSERV_PDO_ODBC'];
        $username = $_ENV['DB_USERNAME_SQLSERV_PDO_ODBC'];
        $password = $_ENV['DB_PASSWORD_SQLSERV_PDO_ODBC'];

        parent::__construct($dsn, $username, $password);
    }
}
