<?php

namespace App\Controller\dit;


use App\Entity\DitSearch;
use App\Form\DitSearchType;
use App\Controller\Controller;
use App\Entity\DemandeIntervention;
use App\Controller\Traits\DitListTrait;
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
       
        //variable pour tester s'il n'y pas de donner à afficher
        $empty = false;
    
        if($form->isSubmitted() && $form->isValid()) {
            
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                if(!empty($idMateriel)){
                    $this->ajoutDonnerRecherche($form, $ditSearch);
                    $ditSearch ->setIdMateriel($idMateriel[0]['num_matricule']);
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

        $this->ajoutNbrPj($paginationData['data'], self::$em);

        
        //recuperation de numero de serie et parc pour l'affichage
        $idMat = [];
        $numSerieParc = [];
        if (!empty($paginationData['data'])) {
            $idMateriels = $this->recupIdMaterielEnChaine($paginationData['data']);
            $numSerieParc = $this->ditModel->recuperationNumSerieNumParc($idMateriels);
            
            foreach ($numSerieParc as  $value) {
                $idMat[] = $value['num_matricule'];
            }
        } else {
            $empty = true;
        }

        self::$twig->display('dit/list.html.twig', [
            'data' => $paginationData['data'],
            'numSerieParc' => $numSerieParc,
            'idMat' => $idMat,
            'empty' => $empty,
            'form' => $form->createView(),
            'currentPage' => $paginationData['currentPage'],
            'totalPages' =>$paginationData['lastPage'],
            'criteria' => $criteria,
            'resultat' => $paginationData['totalItems'],
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
            ->setDitRattacherOr($criteria["ditRattacherOr"])
            ->setCategorie($criteria["categorie"])
            ->setUtilisateur($criteria["utilisateur"])
        ;
        

        $entities = self::$em->getrepository(DemandeIntervention::class)->findAndFilteredExcel($ditSearch, $options);
       
    // Convertir les entités en tableau de données
    $data = [];
    $data[] = ['N° DIT', 'Type Document', 'type de Réparation', 'Réalisé par', 'Catégorie de Demande', 'I/E', 'Débiteur', 'Emetteur', 'nom Client', 'N° Tel', 'Date de travaux', 'Devis', 'Niveau d\'urgence', 'Avis de recouvrement', 'Client sous contrat', 'Objet', 'Detail', 'Livraison Partiel', 'Id matériel', 'mail demandeur', 'date demande', 'statut Demande']; // En-têtes des colonnes
    foreach ($entities as $entity) {
        $data[] = [
            $entity->getNumeroDemandeIntervention(), 
            $entity->getTypeDocument()->getDescription(),
            $entity->getTypeReparation(),
            $entity->getReparationRealise(),
            $entity->getCategorieDemande()->getLibelleCategorieAteApp(),
            $entity->getInternetExterne(),
            $entity->getAgenceServiceDebiteur(),
            $entity->getAgenceServiceEmetteur(),
            $entity->getNomClient(),
            $entity->getNumeroTel(),
            $entity->getDatePrevueTravaux(),
            $entity->getDemandeDevis(),
            $entity->getIdNiveauUrgence()->getDescription(),
            $entity->getAvisRecouvrement(),
            $entity->getClientSousContrat(),
            $entity->getObjetDemande(),
            $entity->getDetailDemande(),
            $entity->getLivraisonPartiel(),
            $entity->getIdMateriel(),
            $entity->getMailDemandeur(),
            $entity->getDateDemande(),
            $entity->getIdStatutDemande()->getDescription()
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

}