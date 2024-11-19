<?php

namespace App\Model\dom;

use App\Model\Model;


class DomModel extends Model
{


    public function filterstatut($LibStatut)
    {
        $sqlStatut = "SELECT  
     Demande_ordre_mission.ID_Demande_Ordre_Mission, 
    Statut_demande.Description AS Statut,
    Demande_ordre_mission.Sous_type_document,
    Demande_ordre_mission.Numero_Ordre_Mission,
    Demande_ordre_mission.Date_Demande,
    Demande_ordre_mission.Motif_Deplacement,
    Demande_ordre_mission.Matricule,
    Demande_ordre_mission.Nom, 
    Demande_ordre_mission.Prenom,
    Demande_ordre_mission.Mode_Paiement,
   ( SELECT  Agence_Service_Irium.nom_agence_i100 + ' - ' + Agence_Service_Irium.nom_service_i100 FROM Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur)AS LibelleCodeAgence_Service, 
    Demande_ordre_mission.Date_Debut, 
    Demande_ordre_mission.Date_Fin,   
    Demande_ordre_mission.Nombre_Jour, 
    Demande_ordre_mission.Client,
    Demande_ordre_mission.Fiche,
    Demande_ordre_mission.Lieu_Intervention,
    Demande_ordre_mission.NumVehicule,
    Demande_ordre_mission.Total_Autres_Depenses,
    Demande_ordre_mission.Total_General_Payer,
    Demande_ordre_mission.Devis
            FROM Demande_ordre_mission, Statut_demande
            WHERE Demande_ordre_mission.Code_Statut = Statut_demande.Code_Statut
            AND Statut_demande.Description ='" . $LibStatut . "'
     ORDER BY ID_Demande_Ordre_Mission DESC        
            ";
        $excec = $this->connexion->query($sqlStatut);
        $ListstatutDom = array();
        while ($tabliststatutDOM = odbc_fetch_array($excec)) {
            $ListstatutDom[] = $tabliststatutDOM;
        }
        return $ListstatutDom;
    }


    private function CompleteChaineCaractere($ChaineComplet, $LongerVoulu, $Caracterecomplet, $PositionComplet)
    {
        for ($i = 1; $i < $LongerVoulu; $i++) {
            if (strlen($ChaineComplet) < $LongerVoulu) {
                if ($PositionComplet = "G") {
                    $ChaineComplet = $Caracterecomplet . $ChaineComplet;
                } else {
                    $ChaineComplet = $Caracterecomplet . $Caracterecomplet;
                }
            }
        }
        return $ChaineComplet;
    }


    /**
     * Incrimentation de Numero_DOM (DOMAnnéeMoisNuméro)
     */
    public function DOM_autoINcriment()
    {
        //NumDOM auto
        // include('../FunctionChaine.php');
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        // dernier NumDOM dans la base
        $NumDOM_Max = "SELECT  MAX(Numero_Ordre_Mission) FROM Demande_ordre_mission ";
        $exec_NumDOM_Max = $this->connexion->query($NumDOM_Max);
        if ($exec_NumDOM_Max === null) {
            echo "null";
        }
        odbc_fetch_row($exec_NumDOM_Max);
        $Max_NumDOM = odbc_result($exec_NumDOM_Max, 1);
        //num_sequentielless
        $vNumSequential =  substr($Max_NumDOM, -4); // lay 4chiffre msincrimente
        $DateAnneemoisnum = substr($Max_NumDOM, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential + 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 1;
            }
        }
        strlen($vNumSequential);
        $Result_Num_DOM = "DOM" . $AnneMoisOfcours . $this->CompleteChaineCaractere($vNumSequential, 4, "0", "G");
        return $Result_Num_DOM;
    }

    //TypeDOc 
    /**
     * recuperation type de document 
     */
    public function getTypeDoc()
    {
        $Sql_TypeDoc = "SELECT Code_Document,
                    Code_Sous_type 
                    FROM Sous_type_document
                    WHERE Code_Document = 'ORM' ";
        $execTypeDoc = $this->connexion->query($Sql_TypeDoc);
        $TypDoc = array();
        while ($TabTyp = odbc_fetch_array($execTypeDoc)) {
            $TypDoc[] = $TabTyp;
        }
        return $TypDoc;
    }
    //
    //personnel
    /***
     * recuperation Agence Service de l'utilisateur connecter 
     */
    public function getInfoAgenceUserofCours($Usernames)
    {
        $QueryAgence = "SELECT Utilisateur,
                    Code_AgenceService_IRIUM,
                    Libelle_Service_Agence_IRIUM
                    FROM Personnel, Profil_User 
                    WHERE Personnel.Matricule = Profil_User.Matricule
                    AND Profil_User.utilisateur = '" . $Usernames . "'";
        $execQueryAgence = $this->connexion->query($QueryAgence);
        $ResAgence = array();
        while ($row_agence = odbc_fetch_array($execQueryAgence)) {
            $ResAgence[] = $row_agence;
        }
        return $ResAgence;
    }

    //
    //code Service sage en cours
    /**
     * recuperation code AgenceService (BASE PAIE) de l'utilisateur Connecter
     */
    public function getserviceofcours($Usernames)
    {
        $serviceofcours = "SELECT 
                                Code_AgenceService_Sage
                                FROM Personnel, Profil_User 
                                WHERE Personnel.Matricule = Profil_User.Matricule
                                AND Profil_User.utilisateur = '" . $Usernames . "'";
        $excServofCours = $this->connexion->query($serviceofcours);
        return $excServofCours ? odbc_fetch_array($excServofCours)['Code_AgenceService_Sage'] : false;
    }
    // libel Agence Service 
    /**
     * recuperation Agence service de l'irium de l'utilisateur Connecter
     */
    public function getLibeleAgence_Service($CodeAgenceSage)
    {
        $LibelServ = " SELECT nom_agence_i100 + '-'+  libelle_service_ips as LibAgenceService
                        
                  FROM Agence_Service_Irium 
                WHERE service_sage_paie = '" . $CodeAgenceSage . "' ";
        $execLibserv = $this->connexion->query($LibelServ);
        return $execLibserv ? odbc_fetch_array($execLibserv)['LibAgenceService'] : false;
    }

    /**
     * recupere les informations des personnels selon l'agence autoriser de l'utilisateur connecter 
     */
    public function getInfoUserMservice($ConnectUser)
    {
        $QueryService = "SELECT  Matricule,
                        Nom+' '+Prenoms as Nom,
                        Agence_Service_Irium.agence_ips+Agence_Service_Irium.service_ips
                        FROM Personnel , Agence_Service_Irium
                        WHERE Personnel.Code_AgenceService_Sage = Agence_Service_Irium.service_sage_paie
                        AND Personnel.Code_AgenceService_Sage 
                        IN (SELECT service_sage_paie 
                        FROM Agence_Service_Irium WHERE  agence_ips+service_ips  IN (
                            SELECT  Code_AgenceService_IRIUM 
                            FROM Agence_service_autorise WHERE Session_Utilisateur = '" . $ConnectUser . "') )";
        $execService = $this->connexion->query($QueryService);
        $ResUserAllService = array();
        while ($tab = odbc_fetch_array($execService)) {
            $ResUserAllService[] = $tab;
        }
        return $ResUserAllService;
    }
    //
    /**
     * recuperation le dernier n° tel du personnel dans le DOM 
     *  */
    public function getInfoTelCompte($userSelect)
    {
        $QueryCompte = "SELECT
                                Nom,
                                Prenoms,
                                (
                                    SELECT TOP 1 Numero_Tel
                                    FROM Demande_ordre_mission
                                    WHERE Demande_ordre_mission.Matricule = Personnel.Matricule
                                    ORDER BY Date_demande DESC
                                ) AS NumeroTel_Recente,
                                Numero_Compte_Bancaire,
                                Agence_Service_Irium.agence_ips + ' ' + Agence_Service_Irium.nom_agence_i100 AS Code_serv,
                                Agence_Service_Irium.service_ips + ' ' + Agence_Service_Irium.libelle_service_ips AS Serv_lib
                        FROM Personnel
                        JOIN Agence_Service_Irium 
                        ON Personnel.Code_AgenceService_Sage = Agence_Service_Irium.service_sage_paie
                         WHERE  Personnel.Matricule = '" . $userSelect . "' ";

        $execCompte = $this->connexion->query($QueryCompte);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }

    /**
     * Recuperation du code service et nom de l'agence dans Agence_Service_Irium
     */
    // public function RecuperationCodeServiceIrium(): array
    // {
    //     $sql = "SELECT DISTINCT UPPER( CONCAT(ASI.agence_ips , ' ', ASI.nom_agence_i100))AS codeService
    //     FROM Agence_Service_Irium ASI
    //     WHERE societe_ios = 'HF'  ";

    //     $statement = $this->connexion->query($sql);
    //     $codeServices = array();
    //     while ($tab_compt = odbc_fetch_array($statement)) {
    //         $codeServices[] = $tab_compt;
    //     }
    //     return $codeServices;
    // }

    /**
     * Recuperation du code service, nom de l'agence, service de l'agence dans Agence_Service_Irium
     */
    public function RecuperationCodeEtServiceIrium(): array
    {
        $sql = "SELECT DISTINCT 
                UPPER(CONCAT(ASI.service_ips, ' ', ASI.libelle_service_ips)) AS service,
                UPPER(CONCAT(ASI.agence_ips , ' ', ASI.nom_agence_i100)) AS codeService
                FROM Agence_Service_Irium ASI
                WHERE societe_ios = 'HF'
                ORDER BY codeService ASC ";

        $statement = $this->connexion->query($sql);
        $services = [];

        while ($tab_compt = odbc_fetch_array($statement)) {
            $services[] = $tab_compt;
        }
        $nouveauTableau = [];

        foreach ($services as $element) {
            $codeService = $element['codeService'];
            $service = $element['service'];

            if (!isset($nouveauTableau[$codeService])) {
                $nouveauTableau[$codeService] = array();
            }

            $nouveauTableau[$codeService][] = $service;
        }
        return $nouveauTableau;
    }

    //TSY MAHAZO FAFANA
    /**
     * Chevauchement : recuperation la minimum de la date de mission et le maximum de la mission 
     */
    public function getInfoDOMMatrSelet($matricule)
    {
        // $SqlDate = "SELECT  min(Date_Debut) as DateDebutMin,
        //                      max(Date_Fin) as DateFinMax
        //             FROM Demande_ordre_mission
        //             WHERE  Matricule = '" . $Matri . "'  
        //             AND Code_Statut IN ('OUV', 'PAY')
        //             GROUP BY Matricule";
        $SqlDate = "SELECT  Date_Debut, Date_Fin
        FROM Demande_ordre_mission
        WHERE  Matricule = '4174'  
        AND ID_Statut_Demande IN (1, 6)";
        $execSqlDate = $this->connexion->query($SqlDate);
       
        $DateM = array();
        while ($tab_list = odbc_fetch_array($execSqlDate)) {
            $DateM[] = $tab_list;
        }

        return $DateM;
    }


    /**
     * recuperer le nom et prenoms du matricule 
     */
    public function getName($Matricule)
    {
        $Queryname  = "SELECT Nom, Prenoms
                        FROM Personnel
                        WHERE Matricule = '" . $Matricule . "'";
        $execCname = $this->connexion->query($Queryname);
        $Infopers = array();
        while ($tabInfo = odbc_fetch_array($execCname)) {
            $Infopers[] = $tabInfo;
        }
        return $Infopers;
    }
    // categorie
    /**
     * recuperation des catégories selon le type de mission et code agence 
     */
    public function CategPers($TypeMiss, $codeAg)
    {
        $SqlTypeMiss = "SELECT DISTINCT
                        Catg 
                        FROM Idemnity 
                        WHERE Type = '" . $TypeMiss . "' 
                        AND Rmq  in('STD','" . $codeAg . "')";
        $execSqlTypeMiss = $this->connexion->query($SqlTypeMiss);
        $ListCatg = array();
        while ($TabTYpeMiss = odbc_fetch_array($execSqlTypeMiss)) {
            $ListCatg[] = $TabTYpeMiss;
        }
        return $ListCatg;
    }
    /**
     * recuperation catégorie Rental 
     * @param default CodeR : 50 
     */
    public function catgeRental($CodeR)
    {
        $SqlRentacatg = "SELECT DISTINCT Catg FROM Idemnity WHERE Type = 'MUTATION' AND Rmq = '" . $CodeR . "' ";
        $exSql = $this->connexion->query($SqlRentacatg);
        $ListCatge = array();
        while ($tab_list = odbc_fetch_array($exSql)) {
            $ListCatge[] = $tab_list;
        }
        return $ListCatge;
    }
    
    /**
     * selection site (region ) 
     * @param  TypeM: type de mission 
     * @param catgPERs:  Catégorie du personnel selectionner 
     */
    public function SelectSite($TypeM, $catgPERs)
    {
        $Site = "SELECT DISTINCT Destination FROM Idemnity WHERE Type = '" . $TypeM . "' AND Catg='" . $catgPERs . "'  ";
        $exSite = $this->connexion->query($Site);
        $list = array();
        while ($tab = odbc_fetch_array($exSite)) {
            $list[] = $tab;
        }
        return $list;
    }


    /**
     * recuperation Prix des idemnité
     * @param TypeM: Type de mission
     * @param CategPers: Catgégorie du personnel selectionner 
     * @param Dest : site (region) selectionner
     * @param AgCode: Code agence 
     */
    public function SelectMUTPrixRental($TypeM, $CategPers, $Dest, $AgCode)
    {
        $PrixRental = "SELECT DISTINCT Montant_idemnite FROM Idemnity WHERE Type = '" . $TypeM . "' 
                    AND Catg = '" . $CategPers . "' AND Destination = '" . $Dest . "' AND Rmq = '" . $AgCode . "' ";
        $exPrixRental = $this->connexion->query($PrixRental);
        $Prix = array();
        while ($tab_prix = odbc_fetch_array($exPrixRental)) {
            $Prix[] = $tab_prix;
        }
        return $Prix;
    }


    //count si 50 catg 
    /**
     * test si le catgérie appartion à l'agence 50
     */
    public function SiRentalCatg($catg)
    {
        $sqlcount = "SELECT count(*) as nbCount FROM Idemnity WHERE Catg ='" . $catg . "' and Rmq = '50' ";
        $exsqlcount = $this->connexion->query($sqlcount);
        return $exsqlcount ? odbc_fetch_array($exsqlcount)['nbCount'] : false;
    }
    //

    //Insert DOM 


    /**
     * insertion des données dans la base de donnée
     */
    public function InsertDom(array $tab)
    {
        $Insert_DOM = "INSERT INTO Demande_ordre_mission(Numero_Ordre_Mission, Date_Demande, Type_Document, Sous_Type_Document, Matricule,
                        Nom_Session_Utilisateur, Code_AgenceService_Debiteur, Date_Debut, Heure_Debut, Date_Fin, Heure_Fin,Nombre_Jour, Motif_Deplacement, Client, Lieu_Intervention,Vehicule_Societe,
                        Indemnite_Forfaitaire,Total_Indemnite_Forfaitaire,Motif_Autres_depense_1,Autres_depense_1,Motif_Autres_depense_2,Autres_depense_2,Motif_Autres_depense_3,Autres_depense_3,
                        Total_Autres_Depenses, Total_General_Payer,Mode_Paiement,Numero_Tel, Code_Statut, Nom, Prenom, Devis, Piece_Jointe_1, Piece_Jointe_2, Utilisateur_Creation, LibelleCodeAgence_Service, Fiche, 
                        NumVehicule,Doit_indemnite, Categorie, Site,idemnity_depl, Emetteur, Debiteur, ID_Statut_Demande )
                       VALUES('" . $tab['NumDom'] . "','" . $tab['dateS'] . "','ORM','" . $tab['typMiss'] . "','" . $tab['matr'] . "','" . $tab['usersession'] . "','" . $tab['codeAg_serv'] . "','" . $tab['DateDebut'] . "','" . $tab['heureD'] . "','" . $tab['DateFin'] . "',
                       '" . $tab['heureF'] . "','" . $tab['NbJ'] . "','" . $tab['motif'] . "','" . $tab['Client'] . "','" . $tab['lieu'] . "','" . $tab['vehicule'] . "','" . $tab['idemn'] . "','" . $tab['totalIdemn'] . "','" . $tab['motifdep01'] . "','" . $tab['montdep01'] . "',
                       '" . $tab['motifdep02'] . "','" . $tab['montdep02'] . "','" . $tab['motifdep03'] . "','" . $tab['montdep03'] . "','" . $tab['totaldep'] . "','" . $tab['AllMontant'] . "','" . $tab['modeDB'] . "','" . $tab['valModemob'] . "', 'OUV' , 
                       '" . $tab['Nom'] . "','" . $tab['Prenoms'] . "','" . $tab['Devis'] . "','" . $tab['filename01'] . "','" . $tab['filename02'] . "','" . $tab['usersession'] . "','" . $tab['LibCodeAg_serv'] . "', '" . $tab['fiche'] . "', '" . $tab['Numvehicule'] . "',
                        '" . $tab['doitIdemn'] . "', '" . $tab['CategoriePers'] . "','" . $tab['Site'] . "','" . $tab['Idemn_depl'] . "','" . $tab['codeServEmeteur'] . "','" . $tab['codeServDebiteur'] . "', 1)";
        $excec_insertDOM = $this->connexion->query($Insert_DOM);
    }


    public function agenceDebiteur()
    {
        $statement = " SELECT 
        a.code_agence + ' ' + a.libelle_agence as agenceDebiteur 
         FROM agences a";

        $sql= $this->connexion->query($statement);
        $agences = array();
        while ($tab = odbc_fetch_array($sql)) {
            $agences[] = $tab;
        }
        return $agences;
    }
    
    public function serviceDebiteur($agenceId)
    {
        $statement = "SELECT DISTINCT 
        s.code_service + ' ' + s.libelle_service as serviceDebiteur
        FROM services s
        INNER JOIN agence_service ags ON s.id = ags.service_id
        INNER JOIN agences a ON a.id = ags.agence_id
        WHERE a.code_agence + ' ' + a.libelle_agence = '". $agenceId ."'
        ";
        $sql= $this->connexion->query($statement);
        $services = array();
        while ($tab = odbc_fetch_array($sql)) {
            $services[] = $tab;
        }
        return $services;
    }
}
