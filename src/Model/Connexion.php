<?php

namespace App\Model;

use App\Controller\Controller;

class Connexion
{
    private $dsn;
    private $User;
    private $pswd;
    private $conn;

    public function __construct()
    {
        try {
            // Récupération des informations de connexion à partir des variables d'environnement
            $this->dsn = "mysql:host=localhost;dbname=ticketing";   // Exemple: 'mysql:host=localhost;dbname=testdb'
            $this->User = 'root';  // Nom d'utilisateur MySQL
            $this->pswd = '';  // Mot de passe MySQL

            // Connexion PDO à la base de données
            $this->conn = new \PDO($this->dsn, $this->User, $this->pswd);
            // Configuration pour gérer les erreurs
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    // Exécution d'une requête SQL simple (SELECT)
    public function query($sql)
    {
        try {
            // Exécution de la requête SQL
            $result = $this->conn->query($sql);
            if (!$result) {
                $this->logError("PDO Query failed: " . $this->conn->errorInfo()[2]);
                throw new \Exception("PDO Query failed: " . $this->conn->errorInfo()[2]);
            }
            return $result;
        } catch (\Exception $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    // Exécution d'une requête préparée avec des paramètres
    public function prepareAndExecute($sql, $params)
    {
        try {
            // Préparation de la requête
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $this->logError("PDO Prepare failed: " . $this->conn->errorInfo()[2]);
                throw new \Exception("PDO Prepare failed: " . $this->conn->errorInfo()[2]);
            }

            // Exécution <de></de> la requête avec les paramètres
            if (!$stmt->execute($params)) {
                $this->logError("PDO Execute failed: " . $stmt->errorInfo()[2]);
                throw new \Exception("PDO Execute failed: " . $stmt->errorInfo()[2]);
            }
            return $stmt;
        } catch (\Exception $e) {
            // Capture de l'erreur et redirection vers la page d'erreur
            $this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    // Méthode appelée lorsque l'objet est détruit (fermeture de la connexion PDO)
    public function __destruct()
    {
        // Fermeture de la connexion PDO
        if ($this->conn) {
            $this->conn = null;
        }
    }

    // Méthode de journalisation des erreurs
    private function logError($message)
    {
        error_log($message, 3, "C:\\wamp64\\www\\Hffintranet/var/log/app_errors.log");
    }

    // Méthode pour rediriger vers la page d'erreur
    private function redirectToErrorPage($errorMessage)
    {
        $this->redirectToRoute('utilisateur_non_touver', ["message" => $errorMessage]);
    }

    // Méthode pour rediriger vers une autre page
    protected function redirectToRoute(string $routeName, array $params = [])
    {
        $url = Controller::getGenerator()->generate($routeName, $params);
        header("Location: $url");
        exit();
    }
}
