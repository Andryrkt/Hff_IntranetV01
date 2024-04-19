<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Model\dom\DomDuplicationModel;

class DomDuplicationController extends Controller
{
    private $duplicata;

    public function __construct()
    {
        parent::__construct();
        $this->duplicata = new DomDuplicationModel();
    }

    public function duplificationFormController()
    {
        $this->SessionStart();

        if ($_SERVER['REQUEST_METHOD']  === 'GET') {
            $numDom = $_GET['NumDOM'];
            $idDom = $_GET['IdDOM'];

            // var_dump($numDom, $idDom, $matricule, $check);
            // die();
            $datesyst = $this->duplicata->getDatesystem();

            // $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            // $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
            //include 'Views/Principe.php';
            $data = $this->duplicata->DuplicaftionFormModel($numDom, $idDom);

            $matricule = $_GET['check'];
            $pattern = '/^\d{4}/';
            if (preg_match($pattern, $matricule)) {
                $statutSalarier = 'Interne';
                $cin = '';
            } else {
                $statutSalarier = 'Externe';
                $cin = explode('-', $data[0]['Matricule'])[2];
            }

            if ($data[0]['Debiteur'] === null) {
                $agentDebiteur = '';
                $serviceDebiteur = '';
            } else {
                $agentDebiteur = explode('-', $data[0]['Debiteur'])[0];
                $serviceDebiteur = explode('-', $data[0]['Debiteur'])[1];
            }

            if ($data[0]['Emetteur'] === null) {
                $agentEmetteur = $data[0]['Code_agence'] . ' ' . $data[0]['Libelle_agence'];
                $serviceEmetteur = $data[0]['Code_Service'] . ' ' . $data[0]['Libelle_service'];
            } else {
                $agentEmetteur = explode('-', $data[0]['Emetteur'])[0];
                $serviceEmetteur = explode('-', $data[0]['Emetteur'])[1];
            }



            $dateDemande = $data[0]['Date_Demande'];
            $dateDebut = date("d/m/Y", strtotime($data[0]['Date_Debut']));
            $dateFin = date("d/m/Y", strtotime($data[0]['Date_Debut']));


            if (trim($data[0]['Mode_Paiement']) === 'ESPECES') {
                if (!isset(explode(' ', trim($data[0]['Mode_Paiement']))[1]) || explode(' ', trim($data[0]['Mode_Paiement']))[1] === null || explode(' ', trim($data[0]['Mode_Paiement']))[1] === '') {
                    //$modePaiement = explode(' ', trim($data[0]['Mode_Paiement']));
                    $modePaiement = 'ESPECES';
                    $modePaiementNumero = '';
                    // var_dump('01 :' . $modePaiement);
                    // die();
                } else {

                    $modePaiement = explode(' ', trim($data[0]['Mode_Paiement']))[0];
                    $modePaiementNumero = explode(' ', trim($data[0]['Mode_Paiement']))[1];
                    // var_dump('02 :' . $modePaiement);
                    // die();
                }
            } else {
                $modePaiement = explode(':', trim($data[0]['Mode_Paiement']))[0];
                $modePaiementNumero = explode(':', trim($data[0]['Mode_Paiement']))[1];
                // var_dump('03 :' . $modePaiement);
                // die();
            }
            $idemnityDepl = (int)$data[0]['idemnity_depl'];
            $nombreJour = (int)$data[0]['Nombre_Jour'];

            foreach ($data as $key => $value) {
                $data = $value;
            }

            // var_dump($data);
            // die();

            $this->twig->display(
                'dom/FormCompleDOM.html.twig',
                [
                    'data' => $data,
                    'numDom' => $numDom,
                    'idDom' => $idDom,
                    'statutSalarier' => $statutSalarier,
                    'datesyst' => $datesyst,
                    'agentEmetteur' => $agentEmetteur,
                    'serviceEmetteur' => $serviceEmetteur,
                    'cin' => $cin,
                    'modePaiement' => $modePaiement,
                    'modePaiementNumero' => $modePaiementNumero,
                    'idemnityDepl' => $idemnityDepl,
                    'nombreJour' => $nombreJour

                ]
            );
            // $_FILES['file01']['name'] = $data[0]['Piece_Jointe_1'];
            // $_FILES['file02']['name'] = $data[0]['Piece_Jointe_1'];

            //var_dump($statutSalarier);
            // var_dump(trim($data[0]['Mode_Paiement']) === 'ESPECES');
            // var_dump($data[0]);
            // die();
            //include 'Views/DOM/FormCompleDOM.php';
        }
    }

    public function duplificationFormJsonController()
    {

        $data1 = $this->duplicata->DuplicaftionFormJsonModel();

        header("Content-type:application/json");

        echo json_encode($data1);
    }
}
