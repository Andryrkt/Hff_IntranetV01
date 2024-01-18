<?php
require_once('TCPDF-main/tcpdf.php');
class DomModel
{
    private $connexion;
    public function __construct(Connexion $connexion)
    {
        $this->connexion = $connexion;
    }

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

    //personnel
    public function getInfoAgenceUserofCours($Usernames)
    {
        $QueryAgence = "SELECT Utilisateur,
                    Code_AgenceService_Sage,
                    Libelle_AgenceService_Sage
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
    public function getInfoUserMservice($ServiceofCours)
    {
        $QueryService = "SELECT  Matricule,
                        Noms_Prenoms
                        FROM Personnel 
                        WHERE Code_AgenceService_Sage = '" . $ServiceofCours . "' ";
        $execService = $this->connexion->query($QueryService);
        $ResUserAllService = array();
        while ($tab = odbc_fetch_array($execService)) {
            $ResUserAllService[] = $tab;
        }
        return $ResUserAllService;
    }
    public function getInfoTelCompte($userSelect)
    {
        $QueryCompte  = "SELECT Noms_Prenoms,
                            Numero_Telephone,
                            Numero_Compte_Bancaire
                            FROM Personnel
                            WHERE Matricule = '" . $userSelect . "'";
        $execCompte = $this->connexion->query($QueryCompte);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }
    public function getName($Matricule)
    {
        $Queryname  = "SELECT Noms_Prenoms
                        FROM Personnel
                        WHERE Matricule = '" . $Matricule . "'";
        $execCname = $this->connexion->query($Queryname);
        return $execCname ? odbc_fetch_array($execCname)['Noms_Prenoms'] : false;
    }
    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }

    public function InsertDom()
    {
    }

    public function genererPDF(
        $NumDom,
        $serv,
        $matr,
        $typMiss,
        $autrTyp,
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
        $mode
    ) {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('pdfatimesbi', 'B', 16);
        $pdf->Cell(0, 10, 'ORDRE DE MISSION ', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('pdfatimesbi', '', 12);

        $pdf->setY(30);
        $pdf->Cell(0, 10, 'TYPE DE MISSION : ' . $typMiss, 0, 1);
        $pdf->Cell(0, 10, 'SERVICE : ' . $serv, 0, 1);
        $pdf->Cell(60, 10, 'MATRICULE : ' . $matr, 0, 0);
        $pdf->Cell(60, 10, 'NOM et PRENOMS: ' . $Nom, 0, 1);
        $pdf->Cell(40, 10, 'PERIODE: ' . $NbJ . ' Jour(s)', 0, 0);
        $pdf->Cell(50, 10, 'SOIT DU ' . $dateD, 0, 0, 'C');
        $pdf->Cell(30, 10, 'A  ' . $heureD . ' Heures ', 0, 0);
        $pdf->Cell(30, 10, ' AU  ' . $dateF, 0, 0);
        $pdf->Cell(30, 10, '  A ' . $heureF . ' Heures ', 0, 1);
        $pdf->Cell(0, 10, 'MOTIF : ' . $motif, 0, 1);
        $pdf->Cell(80, 10, 'CLIENT : ' . $Client, 0, 0);
        $pdf->Cell(30, 10, 'N° FICHE : ' . $fiche, 0, 1);
        $pdf->Cell(0, 10, 'LIEU D INTERVENTION : ' . $lieu, 0, 1);
        $pdf->Cell(80, 10, 'VEHICULE DE SOCIETE : ' . $vehicule, 0, 0);
        $pdf->Cell(60, 10, 'N° DU VEHICULE: ' . $numvehicul, 0, 1);
        $pdf->Cell(80, 10, 'INDEMNITE FORFAITAIRE: ' . $idemn . '/j', 0, 0);
        $pdf->Cell(60, 10, 'TOTAL INDEMNITE: ' . $totalIdemn, 0, 1, 'L');

        $pdf->setY(130);
        $pdf->Cell(20, 10, 'AUTRES: ', 0, 1, 'R');
        $pdf->setY(140);
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'MOTIF', 1, 0, 'C');
        $pdf->Cell(80, 10, '' . 'MONTANT', 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  $motifdep01, 1, 0, 'C');
        $pdf->Cell(80, 10, '' . $montdep01, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  $motifdep02, 1, 0, 'C');
        $pdf->Cell(80, 10, '' . $montdep02, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  $motifdep03, 1, 0, 'C');
        $pdf->Cell(80, 10, '' . $montdep03, 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'TOTAL ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $totaldep, 1, 1, 'C');

        $pdf->setY(200);
        $pdf->Cell(60, 10, 'MODE DE PAIEMENT : ', 0, 0);
        $pdf->Cell(60, 10, $libmodepaie, 0, 0);
        $pdf->Cell(60, 10, $mode, 0, 1);

        $pdf->setY(220);
        $pdf->Cell(0, 10, 'Je soussigné(e), reconnais avoir lu et approuvé le code de conduite et de moralité en mission.', 0, 1);

        $pdf->SetFont('pdfatimesbi', '', 10);
        $pdf->setY(230);
        $pdf->setX(10);
        $pdf->Cell(40, 10, 'LE DEMANDEUR', 1, 0, 'C');
        $pdf->Cell(40, 10, 'CHEF DE SERVICE', 1, 0, 'C');
        $pdf->Cell(50, 10, 'VISA RESP. PERSONNEL ', 1, 0, 'C');
        $pdf->Cell(60, 10, 'VISA DIRECTION TECHNIQUE', 1, 1, 'C');

        $pdf->Cell(40, 20, ' ', 1, 0, 'C');
        $pdf->Cell(40, 20, ' ', 1, 0, 'C');
        $pdf->Cell(50, 20, '  ', 1, 0, 'C');
        $pdf->Cell(60, 20, ' ', 1, 1, 'C');
        $ipadr = '192.168.0.15';
        /*9$Dossier = '\\\\'.$ipadr.'\hff_pdf\\DOCUWARE\\ORDERE DE MISSION\\';
        //$Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Upload/';
        $pdf->Output( $Dossier.$serv . '.pdf', 'F');*/

        // Adresse IP de l'ordinateur distant
        $adresseIP = '192.168.0.15';

        // Chemin du fichier distant
        $cheminFichierDistant = '\\hff_pdf\\DOCUWARE\\ORDERE DE MISSION\\';

        // Chemin local pour enregistrer le fichier PDF
        $cheminDestinationLocal = $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Upload/ADMINISTRATION - INFORMATIQUE.pdf';

        // URL cURL pour le fichier distant
        $urlFichierDistant = 'file://' . $adresseIP . $cheminFichierDistant;
          
        // Initialisation de cURL
        $ch = curl_init($urlFichierDistant);

        // Paramètres cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Exécution de la requête cURL
        $resultat = curl_exec($ch);

        // Fermeture de la ressource cURL
        curl_close($ch);

        // Vérification si le téléchargement a réussi
        if ($resultat) {
            // Enregistrement du fichier localement
            file_put_contents($cheminDestinationLocal, $resultat);
            echo 'Fichier téléchargé avec succès sur le serveur local.';
        } else {
            echo 'Erreur lors du téléchargement du fichier distant.';
        }
    }
}
