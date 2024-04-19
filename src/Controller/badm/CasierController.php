<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\Transformation;
use App\Model\badm\CasierModel;

class CasierController extends Controller
{
    private $casier;

    public function __construct()
    {
        parent::__construct();
        $this->casier = new CasierModel();
    }

    use Transformation;
    use ConversionTrait;

    public function AffichageListeCasier()
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $this->twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
            ]
        );
    }

    public function NouveauCasier()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $dateDemande = $this->getDatesystem();

            $data = $this->casier->findAll($_POST['idMateriel'],  $_POST['numeroParc'], $_POST['numeroSerie']);


            $agence = $this->casier->recupAgence();
            $agenceDestinataire = [];
            foreach ($agence as $values) {
                foreach ($values as $value) {
                    $agenceDestinataire[] = $value;
                }
            }

            $this->twig->display(
                'badm/casier/formulaireCasier.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'dateDemande' => $dateDemande,
                    'items' => $data,
                    'agenceDestinataire' => $agenceDestinataire
                ]
            );
        } else {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $Code_AgenceService_Sage = $this->casier->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->casier->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);

            $this->twig->display(
                'badm/casier/nouveauCasier.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'CodeServiceofCours' => $CodeServiceofCours
                ]
            );
        }
    }


    public function FormulaireCasier()
    {
        $this->SessionStart();
        $NumCAS = $this->autoINcriment('CAS');
        $dateDemande = $this->getDatesystem();
        $MailUser = $this->casier->getmailUserConnect($_SESSION['user']);

        // var_dump($_POST);
        // die();
        $data = $this->casier->findAll($_POST['idMateriel']);

        if (isset($_POST['numParc'])) {
            $numParc = $_POST['numParc'];
        } else {
            $numParc = $data[0]['num_parc'];
        }

        $agenceRattacher = explode(' ', $_POST['agenceRattachementCasier'])[0];
        $motifCreation = $_POST['motifCreation'];
        $client = $_POST['client'];
        $chantier = $_POST['chantier'];

        $casier = $client . ' ' . $chantier;

        $agenceEmetteur = $data[0]['agence'];
        $serviceEmetteur = $data[0]['code_service'];

        $insertDbCasier = [
            'Agence' => $agenceRattacher,
            'Casier' => $casier,
            'Nom_Session_Utilisateur' => $_SESSION['user'],
            'Date_Creation' => $dateDemande,
            'Numero_CAS' => $NumCAS

        ];
        foreach ($insertDbCasier as $cle => $valeur) {
            $insertDbCasier[$cle] = strtoupper($valeur);
        }

        $generPdfCasier = [

            'Num_CAS' => $NumCAS,
            'Date_Demande' => implode('/', array_reverse(explode('-', $dateDemande))),
            'Designation' => $data[0]['designation'],
            'Num_ID' => $data[0]['num_matricule'],
            'Num_Serie' => $data[0]['num_serie'],
            'Groupe' => $data[0]['famille'],
            'Num_Parc' => $numParc,
            'Affectation' => $data[0]['affectation'],
            'Constructeur' => $data[0]['constructeur'],
            'Date_Achat' => implode('/', array_reverse(explode('-', $data[0]['date_achat']))),
            'Annee_Model' => $data[0]['annee'],
            'Modele' => $data[0]['modele'],
            'Agence' => $agenceRattacher,
            'Motif_Creation' => $motifCreation,
            'Client' => $client,
            'Chantier' => $chantier,
            'Email_Emetteur' => $MailUser,
            'Agence_Service_Emetteur_Non_separer' => $agenceEmetteur . $serviceEmetteur
        ];

        $insertDbBadm = $this->convertirEnUtf8($insertDbCasier);
        $this->casier->insererDansBaseDeDonnees($insertDbBadm);
        $this->genererPdf->genererPdfCasier($generPdfCasier);
        $this->genererPdf->copyInterneToDOXCUWARE($NumCAS, $agenceEmetteur . $serviceEmetteur);
        header('Location: /Hffintranet/index.php?action=listCasier');
        exit();
    }
}
