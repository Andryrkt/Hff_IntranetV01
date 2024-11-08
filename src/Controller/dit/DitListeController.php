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
        $ditListeModel = new DitListModel();
        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);

        $autorisationRoleEnergie = $this->autorisationRoleEnergie(self::$em); 
        //FIN AUTORISATION

        $ditSearch = new DitSearch();
        $agenceServiceIps= $this->agenceServiceIpsObjet();

        $this->initialisationRechercheDit($ditSearch, self::$em, $agenceServiceIps, $autoriser);


        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId(),
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
    
        $option = [
            'boolean' => $autoriser,
            'autorisationRoleEnergie' => $autorisationRoleEnergie,
            'codeAgence' => $agenceServiceEmetteur['agence'] === null ? null : $agenceServiceEmetteur['agence']->getCodeAgence(),
            'codeService' =>$agenceServiceEmetteur['service'] === null ? null : $agenceServiceEmetteur['service']->getCodeService()
        ];

        
        //recupère les donnees de option dans la session
        $this->sessionService->set('dit_search_option', $option);

        //recupération des données filtrée
        $paginationData = self::$em->getRepository(DemandeIntervention::class)->findPaginatedAndFiltered($page, $limit, $ditSearch, $option);
        //dump($paginationData);
        //ajout de donner du statut achat piece dans data
        $this->ajoutStatutAchatPiece($paginationData['data']);

        //ajout de donner du statut achat locaux dans data
        $this->ajoutStatutAchatLocaux($paginationData['data']);

        //ajout nombre de pièce joint
        $this->ajoutNbrPj($paginationData['data'], self::$em);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data']);

        $this->ajoutQuatreStatutOr($paginationData['data']);

        $this->ajoutConditionOrEqDit($paginationData['data']);
    
        $this->ajoutri($paginationData['data'], $ditListeModel, self::$em);

        

        /** 
         * Docs à intégrer dans DW 
         * */
        $formDocDansDW = self::$validator->createBuilder(DocDansDwType::class, null, [
            'method' => 'GET',
        ])->getForm();


        $formDocDansDW->handleRequest($request);
            
        //variable pour tester s'il n'y pas de donner à afficher
        $empty = false;
    
        if($formDocDansDW->isSubmitted() && $formDocDansDW->isValid()) {
            if($formDocDansDW->getData()['docDansDW'] === 'OR'){
                $this->redirectToRoute("dit_insertion_or", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            } else if($formDocDansDW->getData()['docDansDW'] === 'FACTURE'){
                $this->redirectToRoute("dit_insertion_facture", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            } elseif ($formDocDansDW->getData()['docDansDW'] === 'RI') {
                $this->redirectToRoute("dit_insertion_ri", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
            }
        } 


        self::$twig->display('dit/list.html.twig', [
            'data' => $paginationData['data'],
            'empty' => $empty,
            'form' => $form->createView(),
            'currentPage' => $paginationData['currentPage'],
            'totalPages' =>$paginationData['lastPage'],
            'criteria' => $criteria,
            'resultat' => $paginationData['totalItems'],
            'statusCounts' => $paginationData['statusCounts'],
            'formDocDansDW' => $formDocDansDW->createView()
        ]);
    }


    /**
     * @Route("/export-excel", name="export_excel")
     */
    public function exportExcel()
    {
        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('dit_search_criteria', []);
          //recupère les critères dans la session 
        $options = $this->sessionService->get('dit_search_option', []);

        //crée une objet à partir du tableau critère reçu par la session
        $ditSearch = new DitSearch();
        $ditSearch
            ->setTypeDocument($criteria["typeDocument"])
            ->setNiveauUrgence($criteria["niveauUrgence"])
            ->setStatut($criteria["statut"])
            ->setInternetExterne($criteria["interneExterne"])
            ->setDateDebut($criteria["dateDebut"])
            ->setDateFin($criteria["dateFin"])
            ->setIdMateriel($criteria["idMateriel"])
            ->setNumParc($criteria["numParc"])
            ->setNumSerie($criteria["numSerie"])
            ->setAgenceEmetteur($criteria["agenceEmetteur"])
            ->setServiceEmetteur($criteria["serviceEmetteur"])
            ->setAgenceDebiteur($criteria["agenceDebiteur"])
            ->setServiceDebiteur($criteria["serviceDebiteur"])
            ->setNumDit($criteria["numDit"])
            ->setNumOr($criteria["numOr"])
            ->setStatutOr($criteria["statutOr"])
            ->setDitSansOr($criteria["ditSansOr"])
            ->setCategorie($criteria["categorie"])
            ->setUtilisateur($criteria["utilisateur"])
        ;
        

        $entities = self::$em->getrepository(DemandeIntervention::class)->findAndFilteredExcel($ditSearch, $options);
        $this->ajoutStatutAchatPiece($entities);

        //ajout de donner du statut achat locaux dans data
        $this->ajoutStatutAchatLocaux($entities);

        $this->ajoutNbrPj($entities, self::$em);

          //recuperation de numero de serie et parc pour l'affichage
            $this->ajoutNumSerieNumParc($entities); 

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['Statut', 'N° DIT', 'Type Document','Niveau', 'Catégorie de Demande', 'N°Serie', 'N°Parc', 'date demande','Int/Ext', 'Emetteur', 'Débiteur',  'Objet', 'sectionAffectee', 'N°Or', 'Statut Or DW', 'Statut Livraison pièces', 'Statut Achats Locaux', 'Nbre Pj', 'utilisateur']; // En-têtes des colonnes
        foreach ($entities as $entity) {
            $data[] = [
                $entity->getIdStatutDemande()->getDescription(),
                $entity->getNumeroDemandeIntervention(), 
                $entity->getTypeDocument()->getDescription(),
                $entity->getIdNiveauUrgence()->getDescription(),
                $entity->getCategorieDemande()->getLibelleCategorieAteApp(),
                $entity->getNumSerie(),
                $entity->getNumParc(),
                $entity->getDateDemande(),
                $entity->getInternetExterne(),
                $entity->getAgenceServiceEmetteur(),
                $entity->getAgenceServiceDebiteur(),
                $entity->getObjetDemande(),
                $entity->getSectionAffectee(),
                $entity->getNumeroOr(),
                $entity->getStatutOr(),
                $entity->getStatutAchatPiece(),
                $entity->getStatutAchatLocaux(),
                $entity->getNbrPj(),
                $entity->getUtilisateurDemandeur()
            ];
        }

        $this->excelService->createSpreadsheet($data);
    }


    /**
     * @Route("/cloturer-annuler/{id}", name="cloturer_annuler_dit_liste")
     */
    public function clotureStatut($id)
    {
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

    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'success', 'message' => $message]);
        $this->redirectToRoute("dit_index");
    }

}