<?php

namespace App\Model;

use App\Model\Traits\ConversionModel;



class Model
{
    use ConversionModel;

    protected $connexion;
    protected $connect;
    protected $sqlServer;
    protected $informix;
    protected $connexion04;
    protected $connexion04Gcot;
    protected string $dbIrium;
    protected string $dbIps;


    public function __construct()
    {
        global $container;
        $logger = ($container && $container->has('logger')) ? $container->get('logger') : null;

        $this->connexion = new Connexion();
        $this->connect = new DatabaseInformix($logger);
        
        // On force la connexion pour ne pas casser les modèles enfants existants
        try {
            $this->connect->connect();
        } catch (\Exception $e) {
            // On laisse l'erreur se logguer dans DatabaseInformix, 
            // mais on ne bloque pas forcément l'instanciation ici 
            // pour rester proche de l'ancien comportement.
        }

        $this->connexion04 = new ConnexionDote4();
        $this->connexion04Gcot = new connexionDote4Gcot();

        // Récupération dynamique des noms de bases
        $this->dbIrium = $_ENV['DB_NAME_IRIUM'] ?? 'ir_prod108';
        $this->dbIps = $_ENV['DB_NAME_IPS'] ?? 'ips_hffprod';
    }


    /**
     * recuperation Mail de l'utilisateur connecter
     */
    public function getmailUserConnect($Userconnect)
    {
        $sqlMail = "SELECT Mail FROM Profil_User WHERE Utilisateur = '" . $Userconnect . "'";
        $exSqlMail = $this->connexion->query($sqlMail);
        return $exSqlMail ? odbc_fetch_array($exSqlMail)['Mail'] : false;
    }


    // Agence Sage to Irium
    /**
     *recuperation agenceService(Base PAiE) de l'utilisateur connecter
     */
    public function getAgence_SageofCours($Userconnect)
    {
        $sql_Agence = "SELECT Code_AgenceService_Sage
                            FROM Personnel, Profil_User
                            WHERE Personnel.Matricule = Profil_User.Matricule
                            AND Profil_User.utilisateur = '" . $Userconnect . "'";
        $exec_Sql_Agence = $this->connexion->query($sql_Agence);
        return $exec_Sql_Agence ? odbc_fetch_array($exec_Sql_Agence)['Code_AgenceService_Sage'] : false;
    }

    /**
     * recuperation agence service dans iRium selon agenceService(Base PAIE) de l'utilisateur connecter 
     * @param $CodeAgenceSage : Agence Service dans le BAse PAIE  $Userconnect: Utilisateur Connecter 
     */
    public function getAgenceServiceIriumofcours($CodeAgenceSage, $Userconnect)
    {
        $sqlAgence_Service_Irim = "SELECT  agence_ips, 
                                            nom_agence_i100,
                                            service_ips,
                                            nom_service_i100
                                    FROM Agence_Service_Irium, personnel,Profil_User
                                    WHERE Agence_Service_Irium.service_sage_paie = personnel.Code_AgenceService_Sage
                                    AND personnel.Code_AgenceService_Sage = '" . $CodeAgenceSage . "'
                                    AND Personnel.Matricule = Profil_User.Matricule
                                    AND Profil_User.utilisateur = '" . $Userconnect . "' ";
        $exec_sqlAgence_Service_Irium = $this->connexion->query($sqlAgence_Service_Irim);
        $Tab_AgenceServiceIrium = array();
        while ($row_Irium = odbc_fetch_array($exec_sqlAgence_Service_Irium)) {
            $Tab_AgenceServiceIrium[] = $row_Irium;
        }
        return $Tab_AgenceServiceIrium;
    }

    /**
     * Date Système
     */
    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }

    public function has_permission($nomUtilisateur, $permission_name)
    {

        // Définir la requête SQL
        $query = "SELECT COUNT(*) as nombre FROM users
        JOIN roles ON users.role_id = roles.id
        JOIN role_permissions ON roles.id = role_permissions.role_id
        JOIN permissions ON role_permissions.permission_id = permissions.id
        WHERE users.nom_utilisateur = ? AND permissions.permission_name = ?";

        // Préparer la requête
        $stmt = odbc_prepare($this->connexion->getConnexion(), $query);

        if (!$stmt) {
            // Gérer les erreurs de préparation
            echo "Query preparation failed: " . odbc_errormsg($this->connexion->getConnexion());
            odbc_close($this->connexion->getConnexion());
            return false;
        }

        // Exécuter la requête avec les paramètres
        $params = array($nomUtilisateur, $permission_name);
        $result = odbc_execute($stmt, $params);

        if (!$result) {
            // Gérer les erreurs d'exécution
            echo "Query execution failed: " . odbc_errormsg($this->connexion->getConnexion());
            odbc_close($this->connexion->getConnexion());
            return false;
        }

        // Récupérer le résultat
        $row = odbc_fetch_array($stmt);
        odbc_close($this->connexion->getConnexion());

        return $row['nombre'] > 0;  // Le COUNT(*) est retourné sans nom de colonne spécifique
    }

    public function modificationDernierIdApp(string $numApp, string $codeApp)
    {
        $statement = "UPDATE applications SET derniere_id = '{$numApp}' WHERE code_app = '{$codeApp}'";

        // Exécution de la requête
        $result = odbc_exec($this->connexion->getConnexion(), $statement);

        // if ($result) {
        //     echo "Record updated successfully.";
        // } else {
        //     echo "Error updating record: " . odbc_errormsg($this->connexion->getConnexion());
        // }

        // Fermeture de la connexion
        odbc_close($this->connexion->getConnexion());
    }

    public function recuperationDerniereIdApp(string $codeApp)
    {
        $statement = "SELECT derniere_id FROM applications WHERE code_app = '{$codeApp}'";
    }

    public function retournerResult28($sql)
    {
        $statement = $this->connexion->query($sql);
        $data = [];
        while ($tabType = odbc_fetch_array($statement)) {
            $data[] = $tabType;
        }
        return $data;
    }

    public function retournerResultGcot04($sql)
    {
        $statement = $this->connexion04Gcot->query($sql);
        $data = [];
        while ($tabType = odbc_fetch_array($statement)) {
            $data[] = $tabType;
        }
        return $data;
    }

    /**
     * Exécute une requête GCOT (SQL Server) contenant un "IN (...)" potentiellement
     * très long en la découpant en lots : au-delà de quelques milliers de valeurs,
     * SQL Server échoue avec "The query processor ran out of internal resources".
     *
     * @param string   $listeIn    valeurs déjà quotées et séparées par des virgules ("'a','b'")
     * @param callable $sqlBuilder fn(string $lotIn): string, construit la requête pour un lot
     * @return array lignes fusionnées de tous les lots, sans doublon
     */
    protected function retournerResultGcot04ParLots(string $listeIn, callable $sqlBuilder, int $tailleLot = 500): array
    {
        $valeurs = array_filter(
            array_map('trim', explode(',', $listeIn)),
            fn($el) => $el !== '' && $el !== "''"
        );
        if (empty($valeurs)) {
            return [];
        }

        $data = [];
        foreach (array_chunk(array_values(array_unique($valeurs)), $tailleLot) as $lot) {
            foreach ($this->retournerResultGcot04($sqlBuilder(implode(',', $lot))) as $ligne) {
                $data[serialize($ligne)] = $ligne;
            }
        }

        return array_values($data);
    }

    /**
     * Trie des lignes (tableaux associatifs) sur plusieurs colonnes, dans l'ordre donné.
     * Utile après retournerResultGcot04ParLots(), chaque lot n'étant trié que séparément.
     */
    protected function trierLignesParColonnes(array $data, array $colonnes): array
    {
        usort($data, function ($a, $b) use ($colonnes) {
            foreach ($colonnes as $col) {
                $cmp = strcmp((string) ($a[$col] ?? ''), (string) ($b[$col] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });

        return $data;
    }

    public function retournerResult04($sql)
    {
        $statement = $this->connexion04->query($sql);
        $data = [];
        while ($tabType = odbc_fetch_array($statement)) {
            $data[] = $tabType;
        }
        return $data;
    }
}
