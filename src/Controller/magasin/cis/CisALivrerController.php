<?php

namespace App\Controller\magasin\cis;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Model\magasin\cis\CisALivrerModel;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\magasin\cis\ALivrerSearchtype;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;

class CisALivrerController extends Controller
{
    use ALivrerTrait;

    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisALivrerModel();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = self::$validator->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();



        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orValide" => true,
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $data = $this->recupData($cisATraiterModel, $criteria);

        //enregistrer les critère de recherche dans la session
        $this->sessionService->set('cis_a_Livrer_search_criteria', $criteria);

        $this->logUserVisit('cis_liste_a_livrer'); // historisation du page visité par l'utilisateur

        self::$twig->display('magasin/cis/listALivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/export-excel-cis-a-livrer", name="export_excel_cis_a_livrer")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisALivrerModel();

        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('cis_a_Livrer_search_criteria', []);

        $entities = $this->recupData($cisATraiterModel, $criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° OR', 'Date OR', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté cde', 'Qté à liv', 'Qté liv', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['num_dit'],
                $entity['num_cis'],
                $entity['date_cis'],
                $entity['agence_service_travaux'],
                $entity['num_or'],
                $entity['date_or'],
                $entity['agence_service_debiteur_ou_client'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['quantitercommander'],
                $entity['quantiteralivrer'],
                $entity['quantiterlivrer'],
                $entity['idMateriel'],
                $entity['marque'],
                $entity['casier']
            ];
        }

        $this->excelService->createSpreadsheet($data);
    }

    private function recupData($cisATraiterModel, $criteria)
    {
        $ditOrsSoumisRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $numORItvValides = $this->orEnString($ditOrsSoumisRepository->findNumOrItvValide());
        $data = $cisATraiterModel->listOrALivrer($criteria, $numORItvValides);

        for ($i = 0; $i < count($data); $i++) {

            $numeroOr = $data[$i]['num_or'];
            $ditRepository = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);
            $idMateriel = $ditRepository->getIdMateriel();
            $marqueCasier = $this->ditModel->recupMarqueCasierMateriel($idMateriel);
            $data[$i]['idMateriel'] = $idMateriel;
            $data[$i]['marque'] = $marqueCasier[0]['marque'];
            $data[$i]['casier'] = $marqueCasier[0]['casier'];
        }
        return $data;
    }
}
