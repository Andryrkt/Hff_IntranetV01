<?php

namespace App\Model;


class InformixConnect extends DatabasePdoOdbc
{
    public function __construct()
    {
        parent::__construct($_ENV['DB_DNS_PDO_INFORMIX'], $_ENV['DB_USERNAME_PDO_INFORMIX'], $_ENV['DB_PASSWORD_PDO_INFORMIX']);
    }
}
