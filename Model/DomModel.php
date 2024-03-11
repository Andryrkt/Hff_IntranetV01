<?php



use setasign\Fpdi\Tcpdf\Fpdi;

require_once('TCPDF-main/tcpdf.php');
require_once('FPDI-2.6.0/src/autoload.php');
class DomModel
{
    private $connexion;
    /**
     * 
     */
    public function __construct(Connexion $connexion)
    {
        $this->connexion = $connexion;
    }
public function filterstatut($LibStatut){
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
            AND Statut_demande.Description ='".$LibStatut."'
     ORDER BY ID_Demande_Ordre_Mission DESC        
            ";
    $excec = $this->connexion->query($sqlStatut);
    $ListstatutDom = array();
    while($tabliststatutDOM = odbc_fetch_array($excec)){
        $ListstatutDom[] = $tabliststatutDOM;
    }      
    return $ListstatutDom;  
}

public function DuplicaftionDomSelect($numDom, $IdDom){
        $Sql = "
        SELECT 
        (select agence_ips from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Code_agence,
        (select nom_agence_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Libelle_agence,
        (select service_ips from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Code_Service,
        (select nom_service_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Libelle_service,
        Sous_type_document,
        Motif_Deplacement,
        Numero_Ordre_Mission,
        Date_Demande,
        Matricule,
        Nom,
        Prenom,
        Date_Debut,
        Date_Fin,
        Nombre_Jour,
        Client,
        Lieu_Intervention,
        Vehicule_Societe,
        NumVehicule,
        Indemnite_Forfaitaire,
        Doit_indemnite,
        Categorie,
        Site,
        Motif_Autres_depense_1,
        Autres_depense_1,
        Motif_Autres_depense_2,
        Autres_depense_2,
        Motif_Autres_depense_3,
        Autres_depense_3,
        Total_Indemnite_Forfaitaire,
        Total_Autres_Depenses,
        Total_General_Payer,
        Mode_Paiement
        FROM Demande_ordre_mission
        WHERE Numero_Ordre_Mission = '".$numDom."'
        AND ID_Demande_Ordre_Mission ='".$IdDom."' 
        ";
        $excecSelectDom = $this->connexion->query($Sql);
        $ListselectDom = array();
        while($tablistselectDom = odbc_fetch_array($excecSelectDom)){
            $ListselectDom[] = $tablistselectDom;
        }
        return $ListselectDom;
        
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
    /**
     * Date Système
     */
    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }
    /**
     * Incrimentation de Numero_DOM (DOMAnnéeMoisNuméro)
     */
    public function DOM_autoINcriment()
    {
        //NumDOM auto
        include('FunctionChaine.php');
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
        $Result_Num_DOM = "DOM" . $AnneMoisOfcours . CompleteChaineCaractere($vNumSequential, 4, "0", "G");
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
        $LibelServ = " SELECT nom_agence_i100 + '-'+  nom_service_i100 as LibAgenceService
                        
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
                        FROM Personnel, Agence_Service_Irium 
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
                                Agence_Service_Irium.service_ips + ' ' + Agence_Service_Irium.nom_service_i100 AS Serv_lib
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

    public function RecuperationCodeServiceIrium(): array
    {
        $sql = "SELECT DISTINCT  Agence_Service_Irium.agence_ips + ' ' + Agence_Service_Irium.nom_agence_i100 AS Code_serv
        FROM Agence_Service_Irium ";

        $statement = $this->connexion->query($sql);
        $codeServices = array();
        while ($tab_compt = odbc_fetch_array($statement)) {
            $codeServices[] = $tab_compt;
        }
        return $codeServices;
    }

    public function RecuperationCodeEtServiceIrium(): array
    {
        $sql = "SELECT Agence_Service_Irium.service_ips + ' ' + Agence_Service_Irium.nom_service_i100 As service,
                        Agence_Service_Irium.agence_ips + ' ' + Agence_Service_Irium.nom_agence_i100 As codeService
                FROM Agence_Service_Irium";

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

    //
    /**
     * Chevauchement : recuperation la minimum de la date de mission et le maximum de la mission 
     */
    public function getInfoDOMMatrSelet($Matri)
    {
        $SqlDate = "SELECT  min(Date_Debut) as DateDebutMin,
                             max(Date_Fin) as DateFinMax
                    FROM Demande_ordre_mission
                    WHERE  Matricule = '" . $Matri . "'  
                    AND Code_Statut ='OUV'
                    GROUP BY Matricule";
        $execSqlDate = $this->connexion->query($SqlDate);
        $DateM = array();
        while ($tab_list = odbc_fetch_array($execSqlDate)) {
            $DateM[] = $tab_list;
        }
        return $DateM;
    }

    //
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
    /* public function SelectMUTPrixSTD($TypeM,$CategPers,$Dest){
        $PrixRental = "SELECT DISTINCT Montant_idemnite FROM Idemnity WHERE Type = '".$TypeM."' 
                       AND Catg = '".$CategPers."' AND Destination = '".$Dest."' AND Rmq = 'STD' ";
        $exPrixRental = $this->connexion->query($PrixRental);
        return $exPrixRental ? odbc_fetch_array($exPrixRental)['Montant_idemnite'] : false;
       }*/
    //Insert DOM 
    /**
     * insertion dans la base 
     */
    public function InsertDom(
        $NumDom,
        $dateS,
        $typMiss,

        $matr,
        $usersession,
        $codeAg_serv,
        $DateDebut,
        $heureD,
        $DateFin,
        $heureF,
        $NbJ,
        $motif,
        $Client,
        $fiche,
        $lieu,
        $vehicule,
        $idemn,
        $totalIdemn,
        $motifdep01,
        $montdep01,
        $motifdep02,
        $montdep02,
        $motifdep03,
        $montdep03,
        $totaldep,
        $AllMontant,
        $modeDB,
        $valModemob,
        $Nom,
        $Prenoms,
        $Devis,
        $filename01,
        $filename02,
        $usersessionCre,
        $LibCodeAg_serv,
        $Numvehicule,
        $doitIdemn,
        $CategoriePers,
        $Site,
        $Idemn_depl
    ) {
        $Insert_DOM = "INSERT INTO Demande_ordre_mission(Numero_Ordre_Mission, Date_Demande, Type_Document, Sous_Type_Document, Matricule,
                        Nom_Session_Utilisateur, Code_AgenceService_Debiteur, Date_Debut, Heure_Debut, Date_Fin, Heure_Fin,Nombre_Jour, Motif_Deplacement, Client, Lieu_Intervention,Vehicule_Societe,
                        Indemnite_Forfaitaire,Total_Indemnite_Forfaitaire,Motif_Autres_depense_1,Autres_depense_1,Motif_Autres_depense_2,Autres_depense_2,Motif_Autres_depense_3,Autres_depense_3,
                        Total_Autres_Depenses, Total_General_Payer,Mode_Paiement,Numero_Tel, Code_Statut, Nom, Prenom, Devis, Piece_Jointe_1, Piece_Jointe_2, Utilisateur_Creation, LibelleCodeAgence_Service, Fiche, 
                        NumVehicule,Doit_indemnite, Categorie, Site,idemnity_depl )
                       VALUES('" . $NumDom . "','" . $dateS . "','ORM','" . $typMiss . "','" . $matr . "','" . $usersession . "','" . $codeAg_serv . "','" . $DateDebut . "','" . $heureD . "','" . $DateFin . "',
                       '" . $heureF . "','" . $NbJ . "','" . $motif . "','" . $Client . "','" . $lieu . "','" . $vehicule . "','" . $idemn . "','" . $totalIdemn . "','" . $motifdep01 . "','" . $montdep01 . "',
                       '" . $motifdep02 . "','" . $montdep02 . "','" . $motifdep03 . "','" . $montdep03 . "','" . $totaldep . "','" . $AllMontant . "','" . $modeDB . "','" . $valModemob . "','OUV', 
                       '" . $Nom . "','" . $Prenoms . "','" . $Devis . "','" . $filename01 . "','" . $filename02 . "','" . $usersession . "','" . $LibCodeAg_serv . "', '" . $fiche . "', '" . $Numvehicule . "',
                        '" . $doitIdemn . "', '" . $CategoriePers . "','" . $Site . "','" . $Idemn_depl . "')";
        $excec_insertDOM = $this->connexion->query($Insert_DOM);
    }
    /**
     * affiche  liste de Dom selon l'agence autoriser de l'utilisateur connecter 
     */
    public function getListDom($User)
    {
        $ListDOM = "SELECT  ID_Demande_Ordre_Mission,
                            (select nom_agence_i100+'-'+nom_service_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur ) as LibelleCodeAgence_Service, 
                            Nom_Session_Utilisateur,
                            Numero_Ordre_Mission,
                            Type_Document,
                            Sous_type_document,
                            Matricule, 
                            Date_Demande, 
                            Nombre_Jour, 
                            Date_Debut, 
                            Date_Fin, 
                            Motif_Deplacement,
                            Client, 
                            Lieu_Intervention,
                            Devis,
                            Statut_demande.Description as Statut,
                            Total_General_Payer
                    FROM Demande_ordre_mission, Statut_demande
                    WHERE Demande_ordre_mission.Code_Statut = Statut_demande.Code_Statut
                    AND Demande_ordre_mission.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
                                                                            FROM Agence_service_autorise 
                                                                            WHERE Session_Utilisateur = '" . $User . "' )
                    AND Demande_ordre_mission.Code_Statut in('OUV','CPT')                                                        
                    ORDER BY ID_Demande_Ordre_Mission DESC";
        $exec_ListDOM = $this->connexion->query($ListDOM);
        $DomList = array();
        while ($row_ListDom = odbc_fetch_array($exec_ListDOM)) {
            $DomList[] = $row_ListDom;
        }
        return $DomList;
    }
    //
    /**
     * affiche tous la liste (sans filtre d'agence autorise)
     */
    public function getListDomAll()
    {
        $ListDOMAll = "SELECT  ID_Demande_Ordre_Mission,
                            (select nom_agence_i100+'-'+nom_service_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur ) as LibelleCodeAgence_Service, 
                            Nom_Session_Utilisateur,
                            Numero_Ordre_Mission,
                            Type_Document,
                            Sous_type_document,
                            Matricule, 
                            Date_Demande, 
                            Nombre_Jour, 
                            Date_Debut, 
                            Date_Fin, 
                            Motif_Deplacement,
                            Client, 
                            Lieu_Intervention,
                            Devis,
                            Statut_demande.Description as Statut,
                            Total_General_Payer
                    FROM Demande_ordre_mission, Statut_demande
                    WHERE Demande_ordre_mission.Code_Statut = Statut_demande.Code_Statut
                    ORDER BY ID_Demande_Ordre_Mission DESC";
        $exec_ListDOMAll = $this->connexion->query($ListDOMAll);
        $DomListAll = array();
        while ($row_ListDomAll = odbc_fetch_array($exec_ListDOMAll)) {
            $DomListAll[] = $row_ListDomAll;
        }
        return $DomListAll;
    }
    /**
     * TODO liste DOM avec filtre date et statut 
     */
    public function getListDomRech($ConnectUser)
    {
        $rech = "SELECT  
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
                AND Demande_ordre_mission.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
                                                                        FROM Agence_service_autorise 
                                                                        WHERE Session_Utilisateur = '" . $ConnectUser . "' )
                                                                        
                ORDER BY ID_Demande_Ordre_Mission DESC";
        $exRech = $this->connexion->query($rech);
        $ListDomRech = array();
        while ($tab_listRech = odbc_fetch_array($exRech)) {
            $ListDomRech[] = $tab_listRech;
        }
        return $ListDomRech;
    }

    public function getListDomRechAll()
    {
        $rech = "SELECT  
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
                                           
                ORDER BY ID_Demande_Ordre_Mission DESC";
        $exRech = $this->connexion->query($rech);
        $ListDomRech = array();
        while ($tab_listRech = odbc_fetch_array($exRech)) {
            $ListDomRech[] = $tab_listRech;
        }
        return $ListDomRech;
    }
    /**
     * récupere le code Statut et libelle statut 
     */
    public function getListStatut()
    {
        $stat = "SELECT DISTINCT Code_Statut, 
                (SELECT Description from Statut_demande WHERE Statut_demande.Code_Statut =Demande_ordre_mission.Code_Statut)  as 'LibStatut'
                FROM Demande_ordre_mission ";
        $exstat = $this->connexion->query($stat);
        $ListStat = array();
        while ($tabStat = odbc_fetch_array($exstat)) {
            $ListStat[] = $tabStat;
        }
        return $ListStat;
    }
    //
    /**
     * affiche les informations correspond au NumDom selectionner et IDDom
     * @param NumDom : Numero d'Ordre de Mission
     * @param IDDOm : ID_demande d'ordre de mission
     */
    public function getDetailDOMselect($NumDOM, $IDDom)
    {
        $SqlDetail = "SELECT Numero_Ordre_Mission, Date_Demande,
                             Sous_Type_Document, 
                             Matricule, Nom_Session_Utilisateur,  
                             LibelleCodeAgence_Service, Matricule, 
                             Nom, Prenom, 
                             Date_Debut, Heure_Debut, 
                             Date_Fin, Heure_Fin,
                             Nombre_Jour, Motif_Deplacement, 
                             Client,Fiche,Lieu_Intervention,
                             Vehicule_Societe, NumVehicule,
                             Devis, Indemnite_Forfaitaire, 
                             Total_Indemnite_Forfaitaire, Motif_Autres_depense_1,
                             Autres_depense_1, Motif_Autres_depense_2,
                             Autres_depense_2, Motif_Autres_depense_3,
                             Autres_depense_3,Total_Autres_Depenses, 
                             Total_General_Payer, Mode_Paiement, 
                             Piece_Jointe_1, Piece_Jointe_2,
                             idemnity_depl,
                             Doit_indemnite,
                             ID_Demande_Ordre_Mission
                     FROM Demande_ordre_mission
                     WHERE Numero_Ordre_Mission = '" . $NumDOM . "'
                     AND ID_Demande_Ordre_Mission = '" . $IDDom . "'";
        $execSqlDetail = $this->connexion->query($SqlDetail);
        $listDetail = array();
        while ($TabDetail = odbc_fetch_array($execSqlDetail)) {
            $listDetail[] = $TabDetail;
        }
        return $listDetail;
    }

    /**
     * Genere le PDF 
     */
    //pdf
    public function genererPDF(
        $Devis,
        $Prenoms,
        $AllMontant,
        $Code_serv,
        $dateS,
        $NumDom,
        $serv,
        $matr,
        $typMiss,

        $Nom,
        $NbJ,
        $dateD,
        $heureD,
        $dateF,
        $heureF,
        $motif,
        $Client,
        $fiche,
        $lieu,
        $vehicule,
        $numvehicul,
        $idemn,
        $totalIdemn,
        $motifdep01,
        $montdep01,
        $motifdep02,
        $montdep02,
        $motifdep03,
        $montdep03,
        $totaldep,
        $libmodepaie,
        $mode,
        $codeAg_serv,
        $CategoriePers,
        $Site,
        $Idemn_depl,
        $MailUser,
        $Bonus,
        $codeServiceDebitteur,
        $serviceDebitteur
    ) {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, 10, 10, 30, '', 'jpg');

        $pdf->SetFont('pdfatimesbi', 'B', 16);
        $pdf->Cell(0, 10, 'ORDRE DE MISSION ', 0, 1, 'C');
        $pdf->SetFont('pdfatimesbi', '', 12);
        $pdf->Cell(0, 10, $NumDom, 0, 1, 'R');
        $pdf->Ln(10);
        $pdf->SetFont('pdfatimesbi', '', 12);

        $pdf->setY(30);
        $pdf->Cell(80, 10, 'Type  : ' . $typMiss, 0, 0);
        // $pdf->Cell(80, 10,  $autrTyp, 0, 0, 'L');
        $pdf->Cell(110, 10, 'Le: ' . $dateS, 0, 1, 'R');
        $pdf->Cell(80, 10, 'Agence: ' . $Code_serv, 0, 0);
        $pdf->Cell(110, 10, 'Catégorie : ' . $CategoriePers, 0, 1, 'R');
        $pdf->Cell(80, 10, 'Service: ' . $serv, 0, 0);
        $pdf->Cell(110, 10, 'Site : ' . $Site, 0, 1, 'R');

        $pdf->Cell(80, 10, 'Matricule : ' . $matr, 0, 0);
        $pdf->Cell(110, 10, 'Ideminté de déplacement: ' . $Idemn_depl, 0, 1, 'R');

        $pdf->Cell(0, 10, 'Nom : ' . $Nom, 0, 1);
        $pdf->Cell(0, 10, 'Prénoms: ' . $Prenoms, 0, 1);
        $pdf->Cell(40, 10, 'Période: ' . $NbJ . ' Jour(s)', 0, 0);
        $pdf->Cell(50, 10, 'Soit du ' . $dateD, 0, 0, 'C');
        $pdf->Cell(30, 10, 'à  ' . $heureD . ' Heures ', 0, 0);
        $pdf->Cell(30, 10, ' au  ' . $dateF, 0, 0);
        $pdf->Cell(30, 10, '  à ' . $heureF . ' Heures ', 0, 1);
        $pdf->Cell(0, 10, 'Motif : ' . $motif, 0, 1);
        $pdf->Cell(80, 10, 'Client : ' . $Client, 0, 0);
        $pdf->Cell(30, 10, 'N° fiche : ' . $fiche, 0, 1);
        $pdf->Cell(0, 10, 'Lieu d intervention : ' . $lieu, 0, 1);
        $pdf->Cell(80, 10, 'Véhicule société : ' . $vehicule, 0, 0);
        $pdf->Cell(60, 10, 'N° de véhicule: ' . $numvehicul, 0, 1);

        $pdf->Cell(70, 10, 'Indemnité Forfaitaire: ' . $idemn . ' ' . $Devis . '/j', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(35, 10, 'Supplément /jour: ', 0, 0, 'L');
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(35, 10,  $Bonus . ' ' . $Devis . '/j', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(55, 10, 'Total indemnité: ' . $totalIdemn . ' ' . $Devis, 0, 1, 'R');

        $pdf->setY(150);
        $pdf->Cell(20, 10, 'Autres: ', 0, 1, 'R');
        $pdf->setY(160);
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'MOTIF', 1, 0, 'C');
        $pdf->Cell(80, 10, '' . 'MONTANT', 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '   ' . $motifdep01, 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $montdep01 . ' ' . $Devis, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '  ' . $motifdep02, 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $montdep02 . ' ' . $Devis, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '   ' . $motifdep03, 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $montdep03 . ' ' . $Devis, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'Total autre ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $totaldep . ' ' . $Devis, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'MONTANT TOTAL A PAYER ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $AllMontant . ' ' . $Devis, 1, 1, 'C');

        $pdf->setY(230);
        $pdf->Cell(60, 10, 'Mode de paiement : ', 0, 0);
        $pdf->Cell(60, 10, $libmodepaie, 0, 0);
        $pdf->Cell(60, 10, $mode, 0, 1);


        $pdf->SetFont('pdfatimesbi', '', 10);
        $pdf->setY(240);
        $pdf->setX(10);
        //  $pdf->Cell(40, 10, 'LE DEMANDEUR', 1, 0, 'C');
        $pdf->Cell(60, 8, 'CHEF DE SERVICE', 1, 0, 'C');
        $pdf->Cell(60, 8, 'VISA RESP. PERSONNEL ', 1, 0, 'C');
        $pdf->Cell(60, 8, 'VISA COMPTABILITE', 1, 1, 'C');


        $pdf->Cell(60, 20, ' ', 1, 0, 'C');
        $pdf->Cell(60, 20, '  ', 1, 0, 'C');
        $pdf->Cell(60, 20, ' ', 1, 1, 'C');

        //pieds de page 
        $pdf->setY(0);
        $pdf->SetFont('pdfatimesbi', '', 8);
        $pdf->Cell(0, 8, $MailUser, 0, 1, 'R');
        $pdf->Cell(0, 8, $codeServiceDebitteur - $serviceDebitteur, 0, 1, 'L');
        //
        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/';
        $pdf->Output($Dossier . $NumDom . '_' . $codeAg_serv . '.pdf', 'F');
    }

    // copy interne vers DOCUWARE
    /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {


        $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\ORDERE DE MISSION\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';

        $cheminDestinationLocal = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "ok";
        } else {
            echo "sorry";
        }
    }
    /**
     * Fusion du Pdf avec les 2 Pièce Joints
     */
    public function genererFusion($FichierDom, $FichierAttache01, $FichierAttache02)
    {
        $pdf01 = new Fpdi();
        $chemin01 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . $FichierDom;
        $pdf01->setSourceFile($chemin01);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $FichierAttache01;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin02);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin03 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $FichierAttache02;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin03);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        // Sauvegarder le PDF fusionné
        $pdf01->Output($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Fusion/' . $FichierDom, 'F');
        // $pdf01->Output('C:/DOCUWARE/ORDRE_DE_MISSION/' . $FichierDom, 'F');
    }
    /**
     * Fusion du Pdf avec un Pièce Joint
     */
    public function genererFusion1($FichierDom, $FichierAttache01)
    {
        $pdf01 = new Fpdi();
        $chemin01 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . $FichierDom;
        $pdf01->setSourceFile($chemin01);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $FichierAttache01;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin02);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        // Sauvegarder le PDF fusionné
        $pdf01->Output($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Fusion/' . $FichierDom, 'F');
        // $pdf01->Output('C:/DOCUWARE/ORDRE_DE_MISSION/' . $FichierDom, 'F');
    }
}
