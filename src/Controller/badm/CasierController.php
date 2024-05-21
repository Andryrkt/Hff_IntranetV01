<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\IncrementationTrait;
use Symfony\Component\Routing\Annotation\Route;


class CasierController extends Controller
{

    use Transformation;
    use ConversionTrait;
    use IncrementationTrait;
    use FormatageTrait;


    /**
     * @Route("/nouveauCasier", name="casier_nouveau")
     */
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


            $agenceLibele = $this->casier->recupAgence();
            $agenceDestinataire = [];
            foreach ($agenceLibele as $values) {
                foreach ($values as $value) {
                    $agenceDestinataire[] = $value;
                }
            }
            if ($_POST['idMateriel'] === '' &&  $_POST['numeroParc'] === '' && $_POST['numeroSerie'] === '') {
                $message = " Renseigner l\'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->alertRedirection($message);
            } elseif (empty($data)) {
                $message = "Matériel déjà vendu";
                $this->alertRedirection($message);
            } else {
                self::$twig->display(
                    'badm/casier/formulaireCasier.html.twig',
                    [
                        'infoUserCours' => $infoUserCours,
                        'boolean' => $boolean,
                        'dateDemande' => $dateDemande,
                        'items' => $data,
                        'agenceDestinataire' => $agenceDestinataire
                    ]
                );
            }
        } else {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $Code_AgenceService_Sage = $this->casier->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->casier->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);

            self::$twig->display(
                'badm/casier/nouveauCasier.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'CodeServiceofCours' => $CodeServiceofCours
                ]
            );
        }
    }

    /**
     * @Route("/createCasier", name="casiser_formulaireCasier", methods={"POST"})
     */
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
        $serviceRattacher = explode(' ', $_POST['agenceRattachementCasier'])[1];
        $agenceServiceRattacher = $agenceRattacher . '-' . $serviceRattacher;

        $motifCreation = $_POST['motifCreation'];
        $client = $_POST['client'];
        $chantier = $_POST['chantier'];

        $casier = $client . ' - ' . $chantier;

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
            'Date_Demande' => $this->formatageDate($dateDemande),
            'Designation' => $data[0]['designation'],
            'Num_ID' => $data[0]['num_matricule'],
            'Num_Serie' => $data[0]['num_serie'],
            'Groupe' => $data[0]['famille'],
            'Num_Parc' => $numParc,
            'Affectation' => $data[0]['affectation'],
            'Constructeur' => $data[0]['constructeur'],
            'Date_Achat' => $this->formatageDate($data[0]['date_achat']),
            'Annee_Model' => $data[0]['annee'],
            'Modele' => $data[0]['modele'],
            'Agence' => $agenceServiceRattacher,
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
        header('Location: /Hffintranet/listTemporaireCasier');
        exit();
    }



    
    /**
     * @Route("/casierDestinataire", name="badm_casierDestinataire")
     */
    public function casierDestinataire()
    {
        $casierDestinataireInformix = $this->badm->recupeCasierDestinataireInformix();
        $casierDestinataireSqlServer = $this->badm->recupeCasierDestinataireSqlServer();

        // Combinaison des deux tableaux
        $resultat = [];

        foreach ($casierDestinataireInformix as $agence) {
            foreach ($casierDestinataireSqlServer as $casier) {

                if ($casier['Agence_Rattacher'] == $agence['code_agence']) {

                    $resultat[$agence['agence']][] = $casier['Casier'];
                }
            }

            //Assurez-vous que chaque agence est présente même si elle n'a pas de casiers
            if (!array_key_exists($agence['agence'], $resultat)) {
                $resultat[$agence['agence']] = [];
            }
        }



        header("Content-type:application/json");

        $jsonData = json_encode($resultat);

        $this->testJson($jsonData);
    }


    private function alertRedirection(string $message, string $chemin = "/Hffintranet/nouveauCasier")
    {
        echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
    }

    private function testJson($jsonData)
    {
        if ($jsonData === false) {
            // L'encodage a échoué, vérifions pourquoi
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    echo 'Aucune erreur';
                    break;
                case JSON_ERROR_DEPTH:
                    echo 'Profondeur maximale atteinte';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo 'Inadéquation des états ou mode invalide';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo 'Caractère de contrôle inattendu trouvé';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo 'Erreur de syntaxe, JSON malformé';
                    break;
                case JSON_ERROR_UTF8:
                    echo 'Caractères UTF-8 malformés, possiblement mal encodés';
                    break;
                default:
                    echo 'Erreur inconnue';
                    break;
            }
        } else {
            // L'encodage a réussi
            echo $jsonData;
        }
    }
}
