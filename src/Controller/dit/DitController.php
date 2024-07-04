<?php

namespace App\Controller\dit;

use App\Entity\User;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Application;
use App\Form\DitSearchType;
use App\Entity\StatutDemande;
use App\Controller\Controller;
use App\Entity\WorTypeDocument;
use App\Entity\WorNiveauUrgence;
use App\Repository\DitRepository;
use App\Controller\Traits\DitTrait;
use App\Entity\DemandeIntervention;
use App\Form\demandeInterventionType;
use App\Controller\Traits\FormatageTrait;
use App\Entity\DitSearch;
use App\Form\DitValidationType;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeInterventionRepository;
use App\Service\EmailService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DitController extends Controller
{
    use DitTrait;
    use FormatageTrait;



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
    
        $ditSearch
        ->setStatut($statut)
        ->setNiveauUrgence($niveauUrgence)
        ->setTypeDocument($typeDocument)
        ->setIdMateriel($request->query->get('idMateriel'))
        ->setInternetExterne($request->query->get('internetExterne'))
        ->setDateDebut($request->query->get('dateDebut'))
        ->setDateFin($request->query->get('dateFin'))
        ->setAgenceEmetteur(self::$em->getRepository(Agence::class)->find($idAgence))
        ->setServiceEmetteur(self::$em->getRepository(Service::class)->findOneBy(['codeService' => $CodeServiceofCours[0]['service_ips'] ]))
        //->setAgenceDebiteur(self::$em->getRepository(Agence::class)->find(1))
        ;



        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
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
      
        $data = $repository->findPaginatedAndFiltered($page, $limit, $ditSearch);
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
   

    /**
     * @Route("/dit/new", name="dit_new")
     *
     * @param Request $request
     * @return void
     */
    public function new(Request $request){
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $demandeIntervention = new DemandeIntervention();
        //INITIALISATION DU FORMULAIRE
       $this->initialisationForm($demandeIntervention, self::$em);

        //AFFICHE LE FORMULAIRE
        $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();

        $form->handleRequest($request);

        
        
        if($form->isSubmitted() && $form->isValid())
        {
            $dits = $this->infoEntrerManuel($form, self::$em);

           

            //envoie des pièce jointe dans une dossier
            $this->envoiePieceJoint($form, $dits);
            
            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
            $application->setDerniereId($dits->getNumeroDemandeIntervention());
            
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();
        
            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            $insertDemandeInterventions = $this->insertDemandeIntervention($dits, $demandeIntervention);
            self::$em->persist($insertDemandeInterventions);
            self::$em->flush();

            /**CREATION DU PDF*/
            //recupération des donners dans le formulaire
            $pdfDemandeInterventions = $this->pdfDemandeIntervention($dits, $demandeIntervention);
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($dits);
            //genere le PDF
            $this->genererPdf->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);

            
            //ENVOYER le PDF DANS DOXCUWARE
            if($dits->getAgence()->getCodeAgence() === "91" || $dits->getAgence()->getCodeAgence() === "92") {
                $this->genererPdf->copyInterneToDOXCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));
            }
            
            
            

            $this->redirectToRoute("dit_index");
            
        }

        self::$twig->display('dit/new.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'form' => $form->createView()
        ]);
    }

   
   
/**
 * @Route("/agence-fetch/{id}", name="fetch_agence", methods={"GET"})
 * cette fonction permet d'envoyer les donner du service debiteur selon l'agence debiteur en ajax
 * @return void
 */
public function agence($id) {
    $agence = self::$em->getRepository(Agence::class)->find($id);
  
   $service = $agence->getServices();

//   $services = $service->getValues();
    $services = [];
  foreach ($service as $key => $value) {
    $services[] = [
        'value' => $value->getId(),
        'text' => $value->getCodeService() . ' ' . $value->getLibelleService()
    ];
  }

  
  //dd($services);
 header("Content-type:application/json");

 echo json_encode($services);

  //echo new JsonResponse($services);
}


/**
 * @Route("/fetch-materiel/{idMateriel?0}/{numParc?0}/{numSerie?}", name="fetch_materiel", methods={"GET"})
 * cette fonctin permet d'envoyer les informations materiels en ajax
 */
public function fetchMateriel($idMateriel,  $numParc, $numSerie)
{

    
    // Récupérer les données depuis le modèle
$data = $this->ditModel->findAll($idMateriel, $numParc, $numSerie);

// Vérifiez si les données existent
if (!$data) {
    return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
}
header("Content-type:application/json");

$jsonData = json_encode($data);

    $this->testJson($jsonData);
// Renvoyer les données en réponse JSON
 //echo new JsonResponse($data);
}

/**
 * @Route("/ditValidation/{id<\d+>}/{numDit<\w+>}", name="dit_validationDit")
 *
 * @return void
 */
   public function validationDit($numDit, $id, Request $request)
   {
    dd($numDit);
    $this->SessionStart();
    $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    $text = file_get_contents($fichier);
    $boolean = strpos($text, $_SESSION['user']);

    

    $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);

    $data = $this->ditModel->findAll($dit->getIdMateriel(), $dit->getNumParc(), $dit->getNumSerie());
            $dit->setNumParc($data[0]['num_parc']);
            $dit->setNumSerie($data[0]['num_serie']);
            $dit->setIdMateriel($data[0]['num_matricule']);
            $dit->setConstructeur($data[0]['constructeur']);
            $dit->setModele($data[0]['modele']);
            $dit->setDesignation($data[0]['designation']);
            $dit->setCasier($data[0]['casier_emetteur']);
            //Bilan financière
            $dit->setCoutAcquisition($data[0]['prix_achat']);
            $dit->setAmortissement($data[0]['amortissement']);
            $dit->setChiffreAffaire($data[0]['chiffreaffaires']);
            $dit->setChargeEntretient($data[0]['chargeentretien']);
            $dit->setChargeLocative($data[0]['chargelocative']);
            //Etat machine
            $dit->setKm($data[0]['km']);
            $dit->setHeure($data[0]['heure']);

            if($dit->getInternetExterne() === 'I'){
                $dit->setInternetExterne('INTERNE');
            } elseif($dit->getInternetExterne() === 'E') {
                $dit->setInternetExterne('EXTERNE');
            }
    /*
    $form = self::$validator->createBuilder(DitValidationType::class)->getForm();

    $form->handleRequest($request);

    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {

        $email = new EmailService();
        if ($request->request->has('refuser')) {
           // Définir l'expéditeur
            // $email->setFrom('hasimanjaka.ratompoarinandro@hff.mg', 'Different Sender');
            
            $to = 'hasina.andrianadison@hff.mg';
            $subject = 'Sujet de l\'email';
            $body = 'Ceci est le <b>contenu</b> de l\'email.';
            $altBody = 'Ceci est le contenu de l\'email en texte brut.';
    
            if ($email->sendEmail($to, $subject, $body, $altBody)) {
                dd( 'Email envoyé avec succès');
            } else {
                dd( 'L\'envoi de l\'email a échoué' );
            }
           
        } elseif ($request->request->has('valider')) {
           dd('valider');
            
        }

        dd('Okey');
        $this->redirectToRoute("dit_index");
        
    }
        */

    self::$twig->display('dit/validation.html.twig', [
        //'form' => $form->createView(),
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'dit' => $dit
    ]);
   }
}
