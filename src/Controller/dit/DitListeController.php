<?php

namespace App\Controller\dit;

use App\Entity\User;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\DitSearch;
use App\Form\DitSearchType;
use App\Entity\StatutDemande;
use App\Controller\Controller;
use App\Entity\WorTypeDocument;
use App\Entity\WorNiveauUrgence;
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
        $userId = $this->sessionService->get('user_id');
        $userConnecter = self::$em->getRepository(User::class)->find($userId);
        $roleNames = [];
        foreach ($userConnecter->getRoles() as $role) {
            $roleNames[] = $role->getRoleName();
        }
            //dd($this->accessControl);
        $autoriser = in_array('ADMINISTRATEUR', $roleNames);
        //FIN AUTORISATION

        $ditSearch = new DitSearch();
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        $idAgence = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $CodeServiceofCours[0]['agence_ips'] ])->getId();
        //initialisation agence et service
        if($autoriser){
            $agence = null;
            $service = null;
        } else {
            $agence = self::$em->getRepository(Agence::class)->find($idAgence);
            $service = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $CodeServiceofCours[0]['service_ips'] ]);
        }
        
        $this->initialisationRechercheDit($ditSearch, self::$em, $request, $agence, $service);

        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'idAgenceEmetteur' => $idAgence
        ])->getForm();

        $form->handleRequest($request);
        //recupération du repository demande d'intervention
        $repository= self::$em->getRepository(DemandeIntervention::class);
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
     
        $option = [
            'boolean' => $autoriser,
            'codeAgence' => $agence === null ? null : $agence->getCodeAgence(),
            'codeService' =>$service === null ? null : $service->getCodeService()
        ];
        //recupère les donnees de option dans la session
        $this->sessionService->set('dit_search_option', $option);

        $totalBadms = $repository->countFiltered($ditSearch, $option);
        //nombre total de page
        $totalPages = ceil($totalBadms / $limit);
       
        
        //recupération des données filtrée
        $data = $repository->findPaginatedAndFiltered($page, $limit, $ditSearch, $option);
     
        //recuperation de numero de serie et parc pour l'affichage
        $idMat = [];
        $numSerieParc = [];
        if (!empty($data)) {
            $idMateriels = $this->recupIdMaterielEnChaine($data);
            $numSerieParc = $this->ditModel->recuperationNumSerieNumParc($idMateriels);
            
            foreach ($numSerieParc as  $value) {
                $idMat[] = $value['num_matricule'];
            }
        } else {
           
            $empty = true;
        }


        self::$twig->display('dit/list.html.twig', [
            'data' => $data,
            'numSerieParc' => $numSerieParc,
            'idMat' => $idMat,
            'empty' => $empty,
            'form' => $form->createView(),
            'currentPage' => $page,
            'totalPages' =>$totalPages,
            'criteria' => $criteria,
            'resultat' => $totalBadms,
        ]);
    }

    
    /**
     * @Route("/export-excel", name="export_excel")
     */
    public function exportExcel(Request $request)
    {
        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('dit_search_criteria', []);

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
        ;
        
        //recupère les critères dans la session 
        $options = $this->sessionService->get('dit_search_option', []);

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
     * TODO: MBOLA MILA ATAO
     *
     * AFFICHAGE LISTE ANNULLER
     * @return void
     */
    public function listeAnnuler(Request $request)
    {
        // $this->SessionStart();
        // $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        // $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        // $text = file_get_contents($fichier);
        // $boolean = strpos($text, $_SESSION['user']);

        // //recuperation des critère à partir de session
        // $criteria = $this->sessionService->get('dit_search_criteria', []);

        // $repository= self::$em->getRepository(DemandeIntervention::class);
        //  //recupère le numero de page
        //  $page = $request->query->getInt('page', 1);
        //  //nombre de ligne par page
        //  $limit = 10;
      
        //  $option = [
        //      'boolean' => $boolean,
        //      'codeAgence' => $agence === null ? null : $agence->getCodeAgence(),
        //      'codeService' =>$service === null ? null : $service->getCodeService()
        //  ];
        // $data = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria);
        //     $idMateriels = $this->recupIdMaterielEnChaine($data);
        //     $numSerieParc = $this->ditModel->recuperationNumSerieNumParc($idMateriels);
        //     $totalBadms = $repository->countFilteredListAnnuller($criteria);

        //     $totalPages = ceil($totalBadms / $limit);


        //     self::$twig->display('dit/list.html.twig', [
        //         'infoUserCours' => $infoUserCours,
        //         'boolean' => $boolean,
        //         'data' => $data,
        //         'numSerieParc' => $numSerieParc,
        //         'idMat' => $idMat,
        //         'empty' => $empty,
        //         'form' => $form->createView(),
        //         'currentPage' => $page,
        //         'totalPages' =>$totalPages,
        //         'criteria' => $criteria,
        //         'resultat' => $totalBadms,
        //     ]);
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