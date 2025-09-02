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
            if (!$this->conn || !is_resource($this->conn)) {
                $err = odbc_errormsg() ?: 'Connexion invalide ou échouée';
                throw new \Exception("ODBC Connection failed: " . $err);
            }
        } catch (\Exception $e) {
            $this->logError("Connexion échouée: " . $e->getMessage());
            $this->redirectToErrorPage("Connexion ODBC impossible");
            exit; // <- obligatoire pour bloquer l'exécution
        }
    }

    public function getConnexion(bool $retry = true)
    {
        if (!is_resource($this->conn)) {
            $this->logError("Connexion ODBC invalide. Tentative de reconnexion ... à " . date('Y-m-d H:i:s'));
            try {
                $this->conn = odbc_connect($this->DB, $this->User, $this->pswd);
                if (!is_resource($this->conn)) {
                    throw new \Exception("Reconnexion ODBC échouée.");
                }
            } catch (\Exception $e) {
                $this->logError("Erreur lors de la reconnexion : " . $e->getMessage());
                if ($retry) {
                    throw new \Exception("Connexion ODBC non valide et reconnexion impossible.");
                }
            }
        }

        return $this->conn;
    }


    public function query($sql)
    {
        try {
            $conn = $this->getConnexion();
            $result = odbc_exec($conn, $sql);

            // Échec → tentative de reconnexion unique
            if (!$result) {
                $this->logError("Première exécution échouée. Tentative de reconnexion...");
                $conn = $this->getConnexion(false); // Retente sans boucle
                $result = odbc_exec($conn, $sql);
            }

            if (!$result) {
                $this->logError("ODBC Query failed: " . odbc_errormsg($conn) . "\nSQL: $sql");
                throw new \Exception("ODBC Query failed: " . odbc_errormsg($conn));
            }
            return $result;
        } catch (\Exception $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError("Exception capturée dans query(): " . $e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
            exit; // Assure l'arrêt
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

    // public function __destruct()
    // {
    //     if ($this->conn && is_resource($this->conn)) {
    //         odbc_close($this->conn);
    //     }
    // }

    private function logError($message)
    {
        error_log($message, 3, $_ENV['BASE_PATH_LOG'] . "/log/app_errors.log");
    }

    // Méthode pour rediriger vers la page d'erreur
    private function redirectToErrorPage($errorMessage)
    {
        $this->redirectToRoute('utilisateur_non_touver', ["message" => $errorMessage]);
    }

    protected function redirectToRoute(string $routeName, array $params = [])
    {
        global $container;
        if ($container && $container->has('router')) {
            $urlGenerator = $container->get('router');
            $url = $urlGenerator->generate($routeName, $params);
        } else {
            // Fallback si le conteneur n'est pas disponible
            $url = '/' . $routeName;
        }
        header("Location: $url");
        exit();
    }
}
