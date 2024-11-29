<?php

namespace App\Controller\magasin\cis;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\cis\CisATraiterModel;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;

class CisATraiterController extends Controller
{
    use AtraiterTrait;
    /**
     * @Route("/cis-liste-a-traiter", name="cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisATraiterModel();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = self::$validator->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            'orValide' => true
        ];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 
        
        $data = $this->recupData($cisATraiterModel, $criteria);

        //enregistrer les critère de recherche dans la session
        $this->sessionService->set('cis_a_traiter_search_criteria', $criteria);

        self::$twig->display('magasin/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' =>$form->createView()
        ]);
    }
    
    /**
     * @Route("/export-excel-a-traiter-cis", name="export_excel_a_traiter_cis")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $cisATraiterModel = new CisATraiterModel();

        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('cis_a_traiter_search_criteria', []);

        $entities = $this->recupData($cisATraiterModel, $criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° Or', 'Date Or', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem', 'ID Materiel', 'Marque', 'Casier']; 
        foreach ($entities as $entity) {
            $data[] = [
                $entity['numdit'],
                $entity['numcis'],
                $entity['datecis'],
                $entity['agenceservicetravaux'],
                $entity['numor'],
                $entity['dateor'],
                $entity['agenceservicedebiteur'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['qte_dem'],
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
        
        $data = $cisATraiterModel->listOrATraiter($criteria, $numORItvValides);

        for ($i=0; $i < count($data) ; $i++) { 
    
            $numeroOr = $data[$i]['numor'];
            $ditRepository = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);
            if($ditRepository != null){
                $idMateriel = $ditRepository->getIdMateriel();
                $marqueCasier = $this->ditModel->recupMarqueCasierMateriel($idMateriel);
                $data[$i]['idMateriel'] = $idMateriel;
                $data[$i]['marque'] = $marqueCasier[0]['marque'];
                $data[$i]['casier'] = $marqueCasier[0]['casier'];
            } else {
                $data[$i]['idMateriel'] = 0;
                $data[$i]['marque'] = '';
                $data[$i]['casier'] = '';
            }
        }

        return $data;
    }
}