<?php

namespace App\Controller\dit;

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
    public function index( Request $request){
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
    
//dd($this->accessControl);
        if($request->query->get('page') !== null){
            if($request->query->get('typeDocument') !==null){
                $idTypeDocument = self::$em->getRepository(WorTypeDocument::class)->findBy(['description' => $request->query->get('typeDocument')], [])[0]->getId();
                $typeDocument = self::$em->getRepository(WorTypeDocument::class)->find($idTypeDocument) ;
            } else {
                $typeDocument = $request->query->get('typeDocument', null);
            }

            if($request->query->get('niveauUrgence') !==null){
                $idNiveauUrgence = self::$em->getRepository(WorNiveauUrgence::class)->findBy(['description' => $request->query->get('niveauUrgence')], [])[0]->getId();
                
                $niveauUrgence = self::$em->getRepository(WorNiveauUrgence::class)->find($idNiveauUrgence) ;
            } else {
                $niveauUrgence = $request->query->get('niveauUrgence', null);
            }
           
            if($request->query->get('statut') !==null){
                $idStatut = self::$em->getRepository(StatutDemande::class)->findBy(['description' => $request->query->get('statut')], [])[0]->getId();
                $statut = self::$em->getRepository(StatutDemande::class)->find($idStatut) ;
            } else {
                $statut = $request->query->get('statut', null);
            }
            
        } else {
            $typeDocument = $request->query->get('typeDocument', null);
            $niveauUrgence = $request->query->get('niveauUrgence', null);
            $statut = $request->query->get('statut', null);
            
            
            if($request->query->get('dit_search') !== null) {
                if($request->query->get('dit_search')['typeDocument'] !== null){
                    $idTypeDocument = $request->query->get('dit_search')['typeDocument'];
                    $typeDocument = self::$em->getRepository(WorTypeDocument::class)->find($idTypeDocument);
                } else {
                    $typeDocument = $request->query->get('typeDocument', null);
                }

                if($request->query->get('dit_search')['niveauUrgence'] !== null){
                    $idNiveauUrgence = $request->query->get('dit_search')['niveauUrgence'];
                    
                    $niveauUrgence = self::$em->getRepository(WorNiveauUrgence::class)->find($idNiveauUrgence);
                } else {
                    $niveauUrgence = $request->query->get('niveauUrgence', null);
                }
                
                if($request->query->get('dit_search')['statut'] !== null){
                    $idStatut = $request->query->get('dit_search')['statut'];
                    $statut = self::$em->getRepository(StatutDemande::class)->find($idStatut);
                } else {
                    $statut = $request->query->get('statut', null);
                }
            
            } else {
                $typeDocument = $request->query->get('typeDocument', null);
                $niveauUrgence = $request->query->get('niveauUrgence', null);
                $statut = $request->query->get('statut', null);
            }
    }
       
        
       

        $ditSearch = new DitSearch();

        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        $idAgence = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $CodeServiceofCours[0]['agence_ips'] ])->getId();
        $agence = self::$em->getRepository(Agence::class)->find($idAgence);
        $service = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $CodeServiceofCours[0]['service_ips'] ]);
        
        $ditSearch
        ->setStatut($statut)
        ->setNiveauUrgence($niveauUrgence)
        ->setTypeDocument($typeDocument)
        ->setIdMateriel($request->query->get('idMateriel'))
        ->setInternetExterne($request->query->get('internetExterne'))
        ->setDateDebut($request->query->get('dateDebut'))
        ->setDateFin($request->query->get('dateFin'))
        ->setAgenceEmetteur($agence)
        ->setServiceEmetteur($service)
        //->setAgenceDebiteur(self::$em->getRepository(Agence::class)->find(1))
        ;


        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'idAgenceEmetteur' => $idAgence
        ])->getForm();

        $form->handleRequest($request);

        $repository= self::$em->getRepository(DemandeIntervention::class);
        $empty = false;
        $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
           
            
            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                if(!empty($idMateriel)){
                    $ditSearch
                    ->setStatut($form->get('statut')->getData())
                    ->setNiveauUrgence($form->get('niveauUrgence')->getData())
                    ->setTypeDocument($form->get('typeDocument')->getData())
                    ->setIdMateriel($idMateriel[0]['num_matricule'])
                    ->setInternetExterne($form->get('internetExterne')->getData())
                    ->setDateDebut($form->get('dateDebut')->getData())
                    ->setDateFin($form->get('dateFin')->getData())
                    ->setAgenceEmetteur($form->get('agenceEmetteur')->getData())
                    ->setServiceEmetteur($form->get('serviceEmetteur')->getData())
                    ;
                    
                    if ($form->get('agenceDebiteur')->getData() === null  && $form->get('serviceDebiteur')->getData() === null) {
                        $ditSearch
                        ->setAgenceDebiteur(null)
                        ->setServiceDebiteur(null);
                    } else {
                        $ditSearch
                        ->setAgenceDebiteur($form->get('agenceDebiteur')->getData())
                        ->setServiceDebiteur($form->get('serviceDebiteur')->getData());

                    }
                } elseif(empty($idMateriel)) {
                    $empty = true;
                }
                
            } else {
                $ditSearch
                    ->setStatut($form->get('statut')->getData())
                    ->setNiveauUrgence($form->get('niveauUrgence')->getData())
                    ->setTypeDocument($form->get('typeDocument')->getData())
                    ->setIdMateriel($form->get('idMateriel')->getData())
                    ->setInternetExterne($form->get('internetExterne')->getData())
                    ->setDateDebut($form->get('dateDebut')->getData())
                  ->setDateFin($form->get('dateFin')->getData())
                  ->setAgenceEmetteur($form->get('agenceEmetteur')->getData())
                  ->setServiceEmetteur($form->get('serviceEmetteur')->getData())
                  ;
                  
                  if ($form->get('agenceDebiteur')->getData() === null  && $form->get('serviceDebiteur')->getData() === null) {
                      $ditSearch
                      ->setAgenceDebiteur(null)
                      ->setServiceDebiteur(null);
                  } else {
                      $ditSearch
                      ->setAgenceDebiteur($form->get('agenceDebiteur')->getData())
                      ->setServiceDebiteur($form->get('serviceDebiteur')->getData());
                  }

            }
            
        } 
       

        $criteria = $ditSearch->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->sessionService->set('dit_search_criteria', $criteria);
      
        $page = $request->query->getInt('page', 1);
        $limit = 10;
     
        $option = [
            'boolean' => $boolean,
            'codeAgence' => $agence->getCodeAgence(),
            'codeService' =>$service->getCodeService()
        ];

        $data = $repository->findPaginatedAndFiltered($page, $limit, $ditSearch, $option);
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
        
//         dump($data);    
// dump($idMateriels);
// dump($numSerieParc);
// dd($idMat);
        $totalBadms = $repository->countFiltered($ditSearch);

        $totalPages = ceil($totalBadms / $limit);


       
        
        if($request->query->get("envoyer") === "listAnnuler") {
        
            $data = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria);
            $idMateriels = $this->recupIdMaterielEnChaine($data);
            $numSerieParc = $this->ditModel->recuperationNumSerieNumParc($idMateriels);
            $totalBadms = $repository->countFilteredListAnnuller($criteria);

            $totalPages = ceil($totalBadms / $limit);
        }
    
        self::$twig->display('dit/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
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
        
        $criteria = $this->sessionService->get('dit_search_criteria', []);

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
        
    
        $entities = self::$em->getrepository(DemandeIntervention::class)->findAndFilteredExcel($ditSearch);
       
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
}