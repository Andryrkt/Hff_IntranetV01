<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Entity\BadmSearch;
use App\Form\BadmSearchType;
use App\Controller\Controller;
use App\Controller\Traits\BadmListTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class BadmListeController extends Controller
{
    use BadmListTrait;

    /**
     * @Route("/listBadm", name="badmListe_AffichageListeBadm")
     */
    public function AffichageListeBadm(Request $request)
    {
         $autoriser = $this->autorisationRole(self::$em);
        
         $badmSearch = new BadmSearch();

         $agenceServiceIps= $this->agenceServiceIpsObjet();

         /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        $this->initialisation($badmSearch, self::$em, $agenceServiceIps, $autoriser);
   
        $form = self::$validator->createBuilder(BadmSearchType::class, $badmSearch , [
            'method' => 'GET',
            'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId()
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        if($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
          
            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                
                if(!empty($idMateriel)){
                    $this->recuperationCriterie($badmSearch, $form);
                    $badmSearch->setIdMateriel($idMateriel[0]['num_matricule']);
                } elseif(empty($idMateriel)) {
                    $empty = true;
                }
            } else {
                $this->recuperationCriterie($badmSearch, $form);
                $badmSearch->setIdMateriel($form->get('idMateriel')->getData());
            }
        } 

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $badmSearch->toArray();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $agenceServiceEmetteur = $this->agenceServiceEmetteur($autoriser);

        $option = [
            'boolean' => $autoriser,
            'codeAgence' => $agenceServiceEmetteur['agence'] === null ? null : $agenceServiceEmetteur['agence']->getCodeAgence(),
            'codeService' =>$agenceServiceEmetteur['service'] === null ? null : $agenceServiceEmetteur['service']->getCodeService()
        ];
       
        $repository= self::$em->getRepository(Badm::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $criteria, $option);

        //enregistre le critère dans la session
        $this->sessionService->set('badm_search_criteria', $criteria);
        $this->sessionService->set('badm_search_option', $option);

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
    public function exportExcel()
{
    // Récupère les critères dans la session
    $criteria = $this->sessionService->get('badm_search_criteria', []);
    $option = $this->sessionService->get('badm_search_option', []);

    // Récupère les entités filtrées
    $entities = self::$em->getRepository(Badm::class)->findAndFilteredExcel($criteria, $option);

    // Convertir les entités en tableau de données
    $data = [];
    $data[] = [
        "Statut", "N°BADM", "Date demande", "Mouvement", "Id matériel", "Ag/Serv émetteur", "N° Parc", "Casier émetteur", "Casier destinataire"
    ];

    foreach ($entities as $entity) {
        if($entity->getCasierDestinataire() === null){
            $casierDestinataire = '';
        } elseif ($entity->getCasierDestinataire()->getId() == 0 ||  $entity->getCasierDestinataire()->getId() == '' || $entity->getCasierDestinataire()->getId() == null) {
            $casierDestinataire = '';
        } else {
            $casierDestinataire = $entity->getCasierDestinataire()->getCasier();
        }
        $data[] = [
            $entity->getStatutDemande() ? $entity->getStatutDemande()->getDescription() : '',
            $entity->getNumBadm(),
            $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
            $entity->getTypeMouvement() ? $entity->getTypeMouvement()->getDescription() : '',
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

/**
 * @Route("/badm-list-annuler", name="badm_list_annuler")
 *
 * @param Request $request
 * @return void
 */
public function listAnnuler(Request $request){
    $autoriser = $this->autorisationRole(self::$em);
        
         $badmSearch = new BadmSearch();
         $agenceServiceIps= $this->agenceServiceIpsObjet();
         /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        $this->initialisation($badmSearch, self::$em, $agenceServiceIps, $autoriser);
   
        $form = self::$validator->createBuilder(BadmSearchType::class, $badmSearch , [
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
                    $this->recuperationCriterie($badmSearch, $form);
                    $badmSearch->setIdMateriel($idMateriel[0]['num_matricule']);
                } elseif(empty($idMateriel)) {
                    $empty = true;
                }
            } else {
                $this->recuperationCriterie($badmSearch, $form);
                $badmSearch->setIdMateriel($form->get('idMateriel')->getData());
            }
        } 

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $badmSearch->toArray();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $agenceServiceEmetteur = $this->agenceServiceEmetteur($autoriser);

        $option = [
            'boolean' => $autoriser,
            'codeAgence' => $agenceServiceEmetteur['agence'] === null ? null : $agenceServiceEmetteur['agence']->getCodeAgence(),
            'codeService' =>$agenceServiceEmetteur['service'] === null ? null : $agenceServiceEmetteur['service']->getCodeService()
        ];
       
        $repository= self::$em->getRepository(Badm::class);
        $paginationData = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria, $option);

        //enregistre le critère dans la session
        $this->sessionService->set('badm_search_criteria', $criteria);
        $this->sessionService->set('badm_search_option', $option);

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


}
