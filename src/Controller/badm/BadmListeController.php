<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Form\BadmSearchType;
use App\Entity\StatutDemande;
use App\Entity\TypeMouvement;
use App\Form\ExcelExportType;
use App\Controller\Controller;
use Illuminate\Pagination\Paginator;
use App\Service\ExcelExporterService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class BadmListeController extends Controller
{
    

    /**
     * @Route("/listBadm", name="badmListe_AffichageListeBadm")
     */
    public function AffichageListeBadm(Request $request)
    {
        $criteria = $this->sessionService->get('badm_search_criteria', []);
// $criteria = [];
        $form = self::$validator->createBuilder(BadmSearchType::class, $criteria , [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        if($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();

            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                
                if(!empty($idMateriel)){
                    $criteria['statut'] = $form->get('statut')->getData();
                    $criteria['typeMouvement'] = $form->get('typeMouvement')->getData();
                    $criteria['idMateriel'] = $idMateriel[0]['num_matricule'];
                    $criteria['dateDebut'] = $form->get('dateDebut')->getData();
                    $criteria['dateFin'] = $form->get('dateFin')->getData();
                } elseif(empty($idMateriel)) {
                    $empty = true;
                }
            } else {
                $criteria['statut'] = $form->get('statut')->getData();
                $criteria['typeMouvement'] = $form->get('typeMouvement')->getData();
                $criteria['idMateriel'] = $form->get('idMateriel')->getData();
                $criteria['dateDebut'] = $form->get('dateDebut')->getData();
                $criteria['dateFin'] = $form->get('dateFin')->getData();
            }
        } 

        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $repository= self::$em->getRepository(Badm::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $criteria);


        //enregistre le critère dans la session
        $this->sessionService->set('badm_search_criteria', $criteria);

        for ($i=0 ; $i < count($paginationData['data'])  ; $i++ ) { 

            $badms = $this->badmRech->findDesiSerieParc($paginationData['data'][$i]->getIdMateriel());

            $paginationData['data'][$i]->setDesignation($badms[0]['designation']);
            $paginationData['data'][$i]->setNumSerie($badms[0]['num_serie']);
            $paginationData['data'][$i]->setNumParc($badms[0]['num_parc']);
        }


        if(empty($paginationData['data'])){
            $empty = true;
        }

        self::$twig->display(
            'badm/listBadm.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
                'empty' => $empty,
                'criteria' => $criteria,
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems']
            ]
        );
    }



     /**
     * @Route("/export-badm-excel", name="export_badm_excel")
     */
    public function exportExcel(Request $request)
{
    // Récupère les critères dans la session
    $criteria = $this->sessionService->get('badm_search_criteria', []);

    // Récupère les entités filtrées
    $entities = self::$em->getRepository(Badm::class)->findAndFilteredExcel($criteria);

    // Convertir les entités en tableau de données
    $data = [];
    $data[] = [
        "Statut", "N°BADM", "Date demande", "Mouvement", "Id matériel", "Ag/Serv émetteur", "N° Parc", "Casier émetteur", "Casier destinataire"
    ];

    foreach ($entities as $entity) {
        if($entity->getCasierDestinataire() === null){
            $casierDestinataire = 'N/A';
        } elseif ($entity->getCasierDestinataire()->getId() == 0 ||  $entity->getCasierDestinataire()->getId() == '' || $entity->getCasierDestinataire()->getId() == null) {
            $casierDestinataire = 'N/A';
        } else {
            $casierDestinataire = $entity->getCasierDestinataire()->getCasier();
        }
        $data[] = [
            $entity->getStatutDemande() ? $entity->getStatutDemande()->getDescription() : 'N/A',
            $entity->getNumBadm(),
            $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : 'N/A',
            $entity->getTypeMouvement() ? $entity->getTypeMouvement()->getDescription() : 'N/A',
            $entity->getIdMateriel(),
            $entity->getAgenceServiceEmetteur(),
            $entity->getNumParc(),
            $entity->getCasierEmetteur(),
           $casierDestinataire
        ];
    }

    // Crée le fichier Excel
    $this->excelService->createSpreadsheet($data);
}



}
