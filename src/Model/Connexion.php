<?php

namespace App\Model;

use App\Controller\Controller;

class Connexion
{
    private $DB;
    private $User;
    private $pswd;
    private $conn;

    public function __construct()
    {
        try {
            $this->DB = $_ENV['DB_DNS_SQLSERV'];
            $this->User = $_ENV['DB_USERNAME_SQLSERV'];
            $this->pswd = $_ENV['DB_PASSWORD_SQLSERV'];


            $this->conn = odbc_connect($this->DB, $this->User, $this->pswd);
            if (!$this->conn) {
                throw new \Exception("ODBC Connection failed:" . odbc_error());
            }
        } catch (\Exception $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function getConnexion()
    {
        return $this->conn;
    }

    public function query($sql)
    {
        try {
            $result = odbc_exec($this->conn, $sql);
            if (!$result) {
                $this->logError("ODBC Query failed: " . odbc_errormsg($this->conn));
                throw new \Exception("ODBC Query failed: " . odbc_errormsg($this->conn));
            }
            return $result;
        } catch (\Exception $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function prepareAndExecute($sql, $params)
    {
        try {
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
        } catch (\Exception $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function __destruct()
    {
        if ($this->conn && is_resource($this->conn)) {
            odbc_close($this->conn);
        }
    }

    private function logError($message)
    {
        error_log($message, 3, $_ENV['BASE_PATH_LOG']."/log/app_errors.log");
    }

    // Méthode pour rediriger vers la page d'erreur
    private function redirectToErrorPage($errorMessage)
    {
        $this->redirectToRoute('utilisateur_non_touver', ["message" => $errorMessage]);
    }

    protected function redirectToRoute(string $routeName, array $params = [])
    {
        $url = Controller::getGenerator()->generate($routeName, $params);
        header("Location: $url");
        exit();
    }
}
