<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Form\BadmSearchType;
use App\Entity\TypeMouvement;
use App\Controller\Controller;
use App\Entity\StatutDemande;
use App\Form\ExcelExportType;
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
               
        if($request->query->get('page') !== null){
            if($request->query->get('typeMouvement') !==null){
                $idTypeMouvement = self::$em->getRepository(TypeMouvement::class)->findBy(['description' => $request->query->get('typeMouvement')], [])[0]->getId();
                $typeMouvement = self::$em->getRepository(TypeMouvement::class)->find($idTypeMouvement) ;
            } else {
                $typeMouvement = $request->query->get('typeMouvement', null);
            }

            if($request->query->get('statut') !==null){
                $idStatut = self::$em->getRepository(StatutDemande::class)->findBy(['description' => $request->query->get('statut')], [])[0]->getId();
                $statut = self::$em->getRepository(StatutDemande::class)->find($idStatut) ;
            } else {
                $statut = $request->query->get('statut', null);
            }
            
        } else {
            $typeMouvement = $request->query->get('typeMouvement', null);
            $statut = $request->query->get('statut', null);
        }
        
        if($request->query->get('badm_search') !== null) {
            if($request->query->get('badm_search')['typeMouvement'] !== null){
                $idTypeMouvement = $request->query->get('badm_search')['typeMouvement'];
                $typeMouvement = self::$em->getRepository(TypeMouvement::class)->find($idTypeMouvement);
            } else {
                $typeMouvement = $request->query->get('typeMouvement', null);
            }

            if($request->query->get('badm_search')['statut'] !== null){
                $idStatut = $request->query->get('badm_search')['statut'];
                $statut = self::$em->getRepository(StatutDemande::class)->find($idStatut);
            } else {
                $statut = $request->query->get('statut', null);
            }
            
        } else {
            $typeMouvement = $request->query->get('typeMouvement', null);
            $statut = $request->query->get('statut', null);
        }
    
        $criteria = [
            'statut' => $statut,
            'typeMouvement' => $typeMouvement,
            'idMateriel'=> $request->query->get('idMateriel'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin')
        ];

        // dd($request->query->get('badm_search')['typeMouvement']);
        //$typeMouvements = $this->badmRech->recupTypeMouvement();
//  dd($criteria);
       
       
        $form = self::$validator->createBuilder(BadmSearchType::class, $criteria, [
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

        
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository= self::$em->getRepository(Badm::class);
        $data = $repository->findPaginatedAndFiltered($page, $limit, $criteria);

        //enregistre le critère dans la session
        $this->sessionService->set('badm_search_criteria', $criteria);

        for ($i=0 ; $i < count($data)  ; $i++ ) { 
            $badms = $this->badmRech->findDesiSerieParc($data[$i]->getIdMateriel());
            $data[$i]->setDesignation($badms[0]['designation']);
            $data[$i]->setNumSerie($badms[0]['num_serie']);
            $data[$i]->setNumParc($badms[0]['num_parc']);
        }

        $totalBadms = $repository->countFiltered($criteria);

        $totalPages = ceil($totalBadms / $limit);

        if(empty($data)){
            $empty = true;
        }
        
      

        if($request->query->get("envoyer") === "listAnnuler") {
        
            $data = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria);
            $totalBadms = $repository->countFilteredListAnnuller($criteria);

            $totalPages = ceil($totalBadms / $limit);
        }
        


        self::$twig->display(
            'badm/listBadm.html.twig',
            [
                'form' => $form->createView(),
                'data' => $data,
                'empty' => $empty,
                'currentPage' => $page,
                'totalPages' =>$totalPages,
                'criteria' => $criteria,
               'resultat' => $totalBadms,
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
