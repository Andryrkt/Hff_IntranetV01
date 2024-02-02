<?php

use setasign\Fpdi\Tcpdf\Fpdi;

require_once('TCPDF-main/tcpdf.php');
require_once('FPDI-2.6.0/src/autoload.php');
class DomModel
{
    private $connexion;
    public function __construct(Connexion $connexion)
    {
        $this->connexion = $connexion;
    }

    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
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
    //TypeDOc 
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
    public function getAgence_SageofCours($Userconnect)
    {
        $sql_Agence = "SELECT Code_AgenceService_Sage
                            FROM Personnel, Profil_User
                            WHERE Personnel.Matricule = Profil_User.Matricule
                            AND Profil_User.utilisateur = '" . $Userconnect . "'";
        $exec_Sql_Agence = $this->connexion->query($sql_Agence);
        return $exec_Sql_Agence ? odbc_fetch_array($exec_Sql_Agence)['Code_AgenceService_Sage'] : false;
    }
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
    public function getserviceofcours($Usernames)
    {
        $serviceofcours = "SELECT 
                                Code_Service_Agence_IRIUM
                                FROM Personnel, Profil_User 
                                WHERE Personnel.Matricule = Profil_User.Matricule
                                AND Profil_User.utilisateur = '" . $Usernames . "'";
        $excServofCours = $this->connexion->query($serviceofcours);
        return $excServofCours ? odbc_fetch_array($excServofCours)['Code_Service_Agence_IRIUM'] : false;
    }
    public function getInfoUserMservice($ServiceofCours)
    {
        $QueryService = "SELECT  Matricule,
                        Nom,
                        Prenoms
                        FROM Personnel 
                        WHERE Code_Service_Agence_IRIUM = '" . $ServiceofCours . "' ";
        $execService = $this->connexion->query($QueryService);
        $ResUserAllService = array();
        while ($tab = odbc_fetch_array($execService)) {
            $ResUserAllService[] = $tab;
        }
        return $ResUserAllService;
    }
    //
    public function getInfoTelCompte($userSelect)
    {
        $QueryCompte  = "SELECT Nom,
                            Prenoms,
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

    //Insert DOM 
    public function InsertDom(
        $NumDom,
        $dateS,
        $typMiss,
        $autrTyp,
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
        $LibCodeAg_serv
    ) {
        $Insert_DOM = "INSERT INTO Demande_ordre_mission(Numero_Ordre_Mission, Date_Demande, Type_Document, Sous_Type_Document, Autre_Type_Document, Matricule,
                        Nom_Session_Utilisateur, Code_AgenceService_Debiteur, Date_Debut, Heure_Debut, Date_Fin, Heure_Fin,Nombre_Jour, Motif_Deplacement, Client, Lieu_Intervention,Vehicule_Societe,
                        Indemnite_Forfaitaire,Total_Indemnite_Forfaitaire,Motif_Autres_depense_1,Autres_depense_1,Motif_Autres_depense_2,Autres_depense_2,Motif_Autres_depense_3,Autres_depense_3,
                        Total_Autres_Depenses, Total_General_Payer,Mode_Paiement,Numero_Tel, Code_Statut, Nom, Prenom, Devis, Piece_Jointe_1, Piece_Jointe_2, Utilisateur_Creation, LibelleCodeAgence_Service)
                       VALUES('" . $NumDom . "','" . $dateS . "','ORM','" . $typMiss . "','" . $autrTyp . "','" . $matr . "','" . $usersession . "','" . $codeAg_serv . "','" . $DateDebut . "','" . $heureD . "','" . $DateFin . "',
                       '" . $heureF . "','" . $NbJ . "','" . $motif . "','" . $Client . "','" . $lieu . "','" . $vehicule . "','" . $idemn . "','" . $totalIdemn . "','" . $motifdep01 . "','" . $montdep01 . "',
                       '" . $motifdep02 . "','" . $montdep02 . "','" . $motifdep03 . "','" . $montdep03 . "','" . $totaldep . "','" . $AllMontant . "','" . $modeDB . "','" . $valModemob . "','O', 
                       '" . $Nom . "','" . $Prenoms . "','" . $Devis . "','" . $filename01 . "','" . $filename02 . "','" . $usersession . "','" . $LibCodeAg_serv . "')";
        $excec_insertDOM = $this->connexion->query($Insert_DOM);
    }

    public function getListDom()
    {
        $ListDOM = "SELECT  ID_Demande_Ordre_Mission,
                            LibelleCodeAgence_Service, 
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

                    FROM Demande_ordre_mission, Statut_demande
                    WHERE Demande_ordre_mission.Code_Statut = Statut_demande.Code_Statut
                    ORDER BY ID_Demande_Ordre_Mission DESC";
        $exec_ListDOM = $this->connexion->query($ListDOM);
        $DomList = array();
        while ($row_ListDom = odbc_fetch_array($exec_ListDOM)) {
            $DomList[] = $row_ListDom;
        }
        return $DomList;
    }

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
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hff_IntranetV01/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, 10, 10, 30, '', 'jpg');

        $pdf->SetFont('pdfatimesbi', 'B', 16);
        $pdf->Cell(0, 10, 'ORDRE DE MISSION ', 0, 1, 'C');
        $pdf->SetFont('pdfatimesbi', '', 12);
        $pdf->Cell(0, 10, $NumDom, 0, 1, 'R');
        $pdf->Ln(10);
        $pdf->SetFont('pdfatimesbi', '', 12);

        $pdf->setY(30);
        $pdf->Cell(80, 10, 'Type  : ' . $typMiss, 0, 0);
        $pdf->Cell(80, 10,  $autrTyp, 0, 0, 'L');
        $pdf->Cell(40, 10, 'Le: ' . $dateS, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Agence: ' . $Code_serv, 0, 1);
        $pdf->Cell(0, 10, 'Service: ' . $serv, 0, 1);
        $pdf->Cell(60, 10, 'Matricule : ' . $matr, 0, 1);

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
        $pdf->Cell(80, 10, 'Indemnité Forfaitaire: ' . $idemn . ' ' . $Devis . '/j', 0, 0);
        $pdf->Cell(60, 10, 'Total indemnité: ' . $totalIdemn . ' ' . $Devis, 0, 1, 'L');

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
        $pdf->Cell(60, 8, 'VISA DIRECTION TECHNIQUE', 1, 1, 'C');


        $pdf->Cell(60, 20, ' ', 1, 0, 'C');
        $pdf->Cell(60, 20, '  ', 1, 0, 'C');
        $pdf->Cell(60, 20, ' ', 1, 1, 'C');


        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Upload/';
        $pdf->Output($Dossier . $NumDom . '_' . $matr . '_' . $Code_serv . '.pdf', 'F');



        $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\ORDERE DE MISSION\\' . $NumDom . '_' . $matr . '_' . $Code_serv . '.pdf';
        //$cheminFichierDistant = 'C:\xampp\\' . $NumDom . '_' . $matr . '_' . $Code_serv . '.pdf';

        $cheminDestinationLocal = $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Upload/' . $NumDom . '_' . $matr . '_' . $Code_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
        } else {
            echo "sorry";
        }
    }
    public function genererFusion($FichierDom, $FichierAttache)
    {
        $pdf01 = new Fpdi();
        $chemin01 = $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Upload/'.$FichierDom;
        $pdf01->setSourceFile($chemin01);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin02 = $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Controler/pdf/'.$FichierAttache;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin02);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        // Sauvegarder le PDF fusionné
        $pdf01->Output( $_SERVER['DOCUMENT_ROOT'] . '/Hff_INtranetV01/Fusion/'.$FichierDom.'.pdf', 'F');
    }
}
