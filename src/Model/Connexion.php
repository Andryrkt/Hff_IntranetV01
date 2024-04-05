<?php

namespace App\Model;

class Connexion
{
    private $DB = "HFF_INTRANET_V01_TEST";
    private $User = "sa";
    private $pswd = "Hff@sql2024";
    private $conn;
    public function __construct()
    {
        $this->conn = odbc_connect($this->DB, $this->User, $this->pswd);
        if (!$this->conn) {
            throw new \Exception("ODBC Conenction failed:" . odbc_error());
        }
    }


    public function connect()
    {
        return $this->conn;
    }


    public function query($sql)
    {
        $result = odbc_exec($this->conn, $sql);
        if (!$result) {
            throw new \Exception("ODBC Conenction failed:" . odbc_error());
        }
        return $result;
    }

    public function prepareAndExecute($sql, $params)
    {
        $stmt = odbc_prepare($this->conn, $sql);
        if (!$stmt) {
            throw new \Exception("ODBC Prepare failed: " . odbc_errormsg($this->conn));
        }
        if (!odbc_execute($stmt, $params)) {
            throw new \Exception("ODBC Execute failed: " . odbc_errormsg($this->conn));
        }
        return $stmt;
    }
}
