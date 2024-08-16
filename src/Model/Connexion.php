<?php

namespace App\Model;

class Connexion
{
    private $DB;
    private $User;
    private $pswd;
    private $conn;

    public function __construct()
    {
        $this->DB = $_ENV['DB_DNS_SQLSERV'];
        $this->User = $_ENV['DB_USERNAME_SQLSERV'];
        $this->pswd = $_ENV['DB_PASSWORD_SQLSERV'];
        
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
        $result = odbc_exec($this->conn, $sql);
        if (!$result) {
            $this->logError("ODBC Query failed: " . odbc_errormsg($this->conn));
            throw new \Exception("ODBC Query failed: " . odbc_errormsg($this->conn));
        }
        return $result;
    }

    public function prepareAndExecute($sql, $params)
    {
        $stmt = odbc_prepare($this->conn, $sql);
        if (!$stmt) {
            $this->logError("ODBC Prepare failed: " . odbc_errormsg($this->conn));
            throw new \Exception("ODBC Prepare failed: " . odbc_errormsg($this->conn));
        }
        if (!odbc_execute($stmt, $params)) {
            $this->logError("ODBC Execute failed: " . odbc_errormsg($this->conn));
            throw new \Exception("ODBC Execute failed: " . odbc_errormsg($this->conn));
        }
        return $stmt;
    }

    public function __destruct()
    {
        if ($this->conn && is_resource($this->conn)) {
            odbc_close($this->conn);
        }
    }

    private function logError($message)
    {
        error_log($message, 3, "/var/log/app_errors.log");
    }
}
