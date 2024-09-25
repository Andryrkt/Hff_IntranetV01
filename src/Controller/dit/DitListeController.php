<?php

namespace App\Controller\dit;


use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Form\dit\DitSearchType;
use App\Controller\Traits\DitListTrait;
use App\Entity\dit\DemandeIntervention;
use App\Form\dit\DocDansDwType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        
        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        //FIN AUTORISATION

        $ditSearch = new DitSearch();
        $agenceServiceIps= $this->agenceServiceIpsObjet();
   

        $this->initialisationRechercheDit($ditSearch, self::$em, $agenceServiceIps, $autoriser);


        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId()
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
     * @Route("/command-modal/{numOr}", name="liste_commandModal")
     *
     * @return void
     */
    public function commandModal($numOr)
    {
        //RECUPERATION DE LISTE COMMANDE 
        if ($numOr === '') {
            $commandes = [];
        } else {
            $commandes = $this->ditModel->RecupereCommandeOr($numOr);
        }

        header("Content-type:application/json");

        echo json_encode($commandes);
    }

    /**
     * @Route("/section-affectee-modal-fetch/{id}", name="section_affectee_modal")
     *
     * @return void
     */
    public function sectionAffecteeModal($id)
    {
        $motsASupprimer = ['Chef section', 'Chef de section', 'Responsable section'];

        // Récupération des données
        $sectionSupportAffectee = self::$em->getRepository(DemandeIntervention::class)->findSectionSupport($id);
        
        // Parcourir chaque élément du tableau et supprimer les mots
        foreach ($sectionSupportAffectee as &$value) {
            foreach ($value as &$texte) {
                // Vérification si c'est bien une chaîne de caractères avant d'effectuer le remplacement
                if (is_string($texte)) {
                    $texte = str_replace($motsASupprimer, '', $texte);
                    $texte = trim($texte); // Supprimer les espaces en trop après remplacement
                }
            }
        }
        

        header("Content-type:application/json");

        echo json_encode($sectionSupportAffectee);
    }

}