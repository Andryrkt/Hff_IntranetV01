<?php

namespace App\Controller\dit;


use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Form\dit\DitSearchType;
use App\Form\dit\DocDansDwType;
use App\Model\dit\DitListModel;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\dit\DitListTrait;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DitRiSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\DossierInterventionAtelierModel;

class DitListeController extends Controller
{
    use DitListTrait;

    /**
     * @Route("/dit", name="dit_index")
     *
     * @return void
     */
    public function index( Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);
        //recuperation agence et service autoriser
        $agenceIds = $user->getAgenceAutoriserIds();
        $serviceIds = $user->getServiceAutoriserIds();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        
        $autorisationRoleEnergie = $this->autorisationRoleEnergie(self::$em); 
        //FIN AUTORISATION

        $ditListeModel = new DitListModel();
        $ditSearch = new DitSearch();
        $agenceServiceIps= $this->agenceServiceIpsObjet();

        $this->initialisationRechercheDit($ditSearch, self::$em, $agenceServiceIps, $autoriser);


        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            //'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId(),
            'autorisationRoleEnergie' => $autorisationRoleEnergie
        ])->getForm();

        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid()) {
            
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                if(!empty($idMateriel)){
                    $this->ajoutDonnerRecherche($form, $ditSearch);
                    $ditSearch->setIdMateriel($idMateriel[0]['num_matricule']);
                } elseif(empty($idMateriel)) {
                    $empty = true;
                }
            } else {
                $this->ajoutDonnerRecherche($form, $ditSearch);
                $ditSearch->setIdMateriel($form->get('idMateriel')->getData());
            }
        } 
        
        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $ditSearch->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->sessionService->set('dit_search_criteria', $criteria);
    

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 10;

        $agenceServiceEmetteur = $this->agenceServiceEmetteur($agenceServiceIps, $autoriser);
    
        $option = $this->Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds);

        
        //recupère les donnees de option dans la session
        $this->sessionService->set('dit_search_option', $option);

        //recupération des données filtrée
        $paginationData = $this->donnerAAfficher($ditListeModel, $ditSearch, $option, $page, $limit, self::$em);

        /** 
         * Docs à intégrer dans DW 
         * */
        $formDocDansDW = self::$validator->createBuilder(DocDansDwType::class, null, [
            'method' => 'GET',
        ])->getForm();

        $this->dossierDit($request, $formDocDansDW);

        //variable pour tester s'il n'y pas de donner à afficher
        $empty = false;

        self::$twig->display('dit/list.html.twig', [
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages' =>$paginationData['lastPage'],
            'resultat' => $paginationData['totalItems'],
            'statusCounts' => $paginationData['statusCounts'],
            'empty' => $empty,
            'form' => $form->createView(),
            'criteria' => $criteria,
            'formDocDansDW' => $formDocDansDW->createView()
        ]);
    }

    
    /**
     * @Route("/export-excel", name="export_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('dit_search_criteria', []);
          //recupère les critères dans la session 
        $options = $this->sessionService->get('dit_search_option', []);

        //crée une objet à partir du tableau critère reçu par la session
        $ditSearch = $this->transformationEnObjet($criteria);
        
        $entities = $this->DonnerAAjouterExcel($ditSearch, $options, self::$em);

        // Convertir les entités en tableau de données
        $data = $this->transformationEnTableauAvecEntet($entities);
        //creation du fichier excel
        $this->excelService->createSpreadsheet($data);
    }


    /**
     * @Route("/cloturer-annuler/{id}", name="cloturer_annuler_dit_liste")
     */
    public function clotureStatut($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);
        $statutCloturerAnnuler = self::$em->getRepository(StatutDemande::class)->find(52);
        $dit->setIdStatutDemande($statutCloturerAnnuler);
        self::$em->persist($dit);
        self::$em->flush();

        $message = "La DIT a été clôturé avec succès.";
        $this->notification($message);

        $this->redirectToRoute("dit_index");
    }

    /**
     * @Route("/dw-intervention-atelier-avec-dit/{numDit}", name="dw_interv_ate_avec_dit")
     */
    public function dwintervAteAvecDit($numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $dwModel = new DossierInterventionAtelierModel();
    
        // Récupérer les données de la demande d'intervention et de l'ordre de réparation
        $dwDit = $dwModel->findDwDit($numDit) ?? [];
        foreach ($dwDit as $key =>$value) {
            $dwDit[$key]['nomDoc'] = 'Demande d\'intervention';
        }
        // dump($dwDit);
        $dwOr = $dwModel->findDwOr($numDit) ?? [];
        // dump($dwOr);
        $dwfac = [];
        $dwRi = [];
        $dwCde = [];

        // Si un ordre de réparation est trouvé, récupérer les autres données liées
        if (!empty($dwOr)) {
            $dwfac = $dwModel->findDwFac($dwOr[0]['numero_doc']) ?? [];
            $dwRi = $dwModel->findDwRi($dwOr[0]['numero_doc']) ?? [];
            $dwCde = $dwModel->findDwCde($dwOr[0]['numero_doc']) ?? [];

            foreach ($dwOr as $key =>$value) {
                $dwOr[$key]['nomDoc'] = 'Ordre de réparation';
            }
            
            foreach ($dwfac as $key =>$value) {
                $dwfac[$key]['nomDoc'] = 'Facture';
            }
            
            foreach ($dwRi as $key =>$value) {
                $dwRi[$key]['nomDoc'] = 'Rapport d\'intervention';
            }
            foreach ($dwCde as $key =>$value) {
                $dwCde[$key]['nomDoc'] = 'Commande';
            }
        }

        // Fusionner toutes les données dans un tableau associatif
        $data = array_merge($dwDit, $dwOr, $dwfac, $dwRi, $dwCde);

        self::$twig->display('dw/dwIntervAteAvecDit.html.twig', [
            'data' => $data,
        ]);
    }

    

}