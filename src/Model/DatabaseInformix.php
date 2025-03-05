<?php

namespace App\Model;

use App\Controller\Controller;
use App\Service\GlobalVariablesService;

class DatabaseInformix
{
    private $dsn;
    private $user;
    private $password;
    private $conn;

    // Constructeur
    public function __construct()
    {
        try {
            // Récupération des variables d'environnement
            $this->dsn = $_ENV['DB_DNS_INFORMIX'] ;
            $this->user = $_ENV['DB_USERNAME_INFORMIX'];
            $this->password = $_ENV['DB_PASSWORD_INFORMIX'];
            
            if (empty($this->dsn) || empty($this->user) || empty($this->password)) {
                throw new \Exception("Les informations de connexion à la base de données sont incomplètes.");
            }

            // Établissement de la connexion
            $this->conn = odbc_connect($this->dsn, $this->user, $this->password);
            if (!$this->conn) {
                throw new \Exception("ODBC Connection failed: " . odbc_errormsg()."\n");
            }
        } catch (\Exception $e) {
            // Capture de l'erreur et enregistrement dans un fichier de log
            //$this->logError($e->getMessage());
            $this->redirectToErrorPage($e->getMessage());
        }
    }

    // Méthode pour établir la connexion à la base de données
    private function connect()
    {
        return $this->conn ;
    }

    // Méthode pour exécuter une requête SQL
    public function executeQuery($query)
    {
        try {
        if (!$this->conn) {
            throw new \Exception("La connexion à la base de données n'est pas établie.");
        }

        $result = odbc_exec($this->conn, $query);
        if (!$result) {
            throw new \Exception("ODBC Query failed: " . odbc_errormsg($this->conn));
        }
        return $result;
    } catch (\Exception $e) {
        // Capture de l'erreur et redirection vers la page d'erreur
        $this->redirectToErrorPage($e->getMessage());
        $this->logError($e->getMessage());
    }
    }

    // Méthode pour récupérer les résultats d'une requête
    public function fetchResults($result)
    {
        $rows = array();
        if ($result) {
            while ($row = odbc_fetch_array($result)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    // Méthode pour fermer la connexion à la base de données
    public function close()
{
    if ($this->conn) {
        odbc_close($this->conn);
        $this->logError("Connexion fermée.");
    } else {
        $this->logError("La connexion à la base de données n'est pas établie.");
    }
}


    private function logError($message)
    {
        $formattedMessage = sprintf("[%s] %s\n", date("Y-m-d H:i:s"), $message);
        error_log($formattedMessage, 3, GlobalVariablesService::get('chemin_log')."/log/app_errors.log");
    }

    // Méthode pour rediriger vers la page d'erreur
    private function redirectToErrorPage($errorMessage)
    {
        $this->redirectToRoute('utilisateur_non_touver', ["message" => $errorMessage]);
    }
   
    protected function redirectToRoute(string $routeName, array $params = []) {
        $url = Controller::getGenerator()->generate($routeName, $params);
        header("Location: $url");
        exit();
    }
}
