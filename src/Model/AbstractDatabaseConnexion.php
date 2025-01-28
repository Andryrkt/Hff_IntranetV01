<?php

namespace App\Model;

use PDO;
use PDOException;
use App\Controller\Controller;

abstract class AbstractDatabaseConnexion
{
    protected $dsn;
    protected $username;
    protected $password;
    protected $conn;

    public function __construct(string $dsn, string $username, string $password)
    {
        try {
            $this->dsn = $dsn;
            $this->username = $username;
            $this->password = $password;

            // Connexion via PDO
            $this->conn = new PDO($this->dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function getConnexion()
    {
        return $this->conn;
    }

    public function query(string $sql)
    {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retourne les résultats sous forme de tableau associatif
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function prepareAndExecute(string $sql, array $params)
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retourne les résultats sous forme de tableau associatif
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function __destruct()
    {
        // Libération de la connexion PDO
        $this->conn = null;
    }

    private function logError(string $message)
    {
        error_log($message, 3, "C:\wamp64\www\Hffintranet/var/log/app_errors.log");
    }

    // Méthode pour rediriger vers la page d'erreur
    private function redirectToErrorPage(string $errorMessage)
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
