<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use Symfony\Component\Routing\Annotation\Route;


class DomDuplicationController extends Controller
{
    use Transformation;

    /**
     * @Route("/duplifierForm/{numDom}/{id}/{matricule}", name="domDuplication_duplificationForm")
     */
    public function duplificationFormController($numDom, $id, $matricule)
    {
            $datesyst = $this->duplicata->getDatesystem();

            $data = $this->duplicata->DuplicaftionFormModel($numDom, $id);

            $pattern = '/^\d{4}/';
            if (preg_match($pattern, $matricule)) {
                $statutSalarier = 'Interne';
                $cin = '';
            } else {
                $statutSalarier = 'Externe';
                $cin = explode('-', $data[0]['Matricule'])[2];
            }

            if ($data[0]['Emetteur'] === null) {
                $agentEmetteur = $data[0]['Code_agence'] . ' ' . $data[0]['Libelle_agence'];
                $serviceEmetteur = $data[0]['Code_Service'] . ' ' . $data[0]['Libelle_service'];
            } else {
                $agentEmetteur = explode('-', $data[0]['Emetteur'])[0];
                $serviceEmetteur = explode('-', $data[0]['Emetteur'])[1];
            }

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

            $agenceDebiteurs = $this->transformEnSeulTableau($this->DomModel->agenceDebiteur());
       
           $serviceDebiteurs = $this->transformEnSeulTableau($this->DomModel->serviceDebiteur($agentEmetteur));


            self::$twig->display(
                'dom/FormCompleDOM.html.twig',
                [
                    'data' => $data,
                    'numDom' => $numDom,
                    'idDom' => $id,
                    'check' => $statutSalarier,
                    'datesyst' => $datesyst,
                    'agentEmetteur' => $agentEmetteur,
                    'serviceEmetteur' => $serviceEmetteur,
                    'cin' => $cin,
                    'modePaiement' => $modePaiement,
                    'modePaiementNumero' => $modePaiementNumero,
                    'idemnityDepl' => $idemnityDepl,
                    'nombreJour' => $nombreJour,
                    'agenceDebiteur' => $agenceDebiteurs,
                    'serviceDebiteur' => $serviceDebiteurs,
                    'code_service' => $agentEmetteur,//agence emetteru externe
                    'service' => $serviceEmetteur, // agence emetteur externe
                    'codeServ' => $agentEmetteur,//agence emetteur interne
                    'servLib' => $serviceEmetteur,//service emetteur interne
                ]
            );
    }

   
}
