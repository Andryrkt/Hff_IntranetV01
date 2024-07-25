<?php

namespace App\Model;

class connexionDote4
{
    private $DB = "HFF_INTRANET_V04";
    private $User = "sa";
    private $pswd = "catkpi";
    private $conn;
    public function __construct()
    {
        $this->conn = odbc_connect($this->DB, $this->User, $this->pswd);
        if (!$this->conn) {
            throw new \Exception("ODBC Connection failed:" . odbc_error());
        }
    }


    public function getConnexion() {
        return $this->conn; 
    }


    public function query($sql)
    {
        echo "Executing query: $sql\n";
        $result = odbc_exec($this->conn, $sql);
        if (!$result) {
            throw new \Exception("ODBC Query failed: " . odbc_errormsg($this->conn));
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
