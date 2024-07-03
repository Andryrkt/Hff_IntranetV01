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
       
        $criteria = [
            'statut' => $statut,
            'niveauUrgence' => $niveauUrgence,
            'typeDocument' => $typeDocument,
            'idMateriel' => $request->query->get('idMateriel'),
            'internetExterne' => $request->query->get('internetExterne'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin')
        ];


        $form = self::$validator->createBuilder(DitSearchType::class, $criteria, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        $repository= self::$em->getRepository(DemandeIntervention::class);
        $empty = false;
       
        if($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData() ;
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
            
            if(!empty($numParc) || !empty($numSerie)){
                
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                if(!empty($idMateriel)){
                    $criteria['statut'] = $form->get('statut')->getData();
                    $criteria['niveauUrgence'] = $form->get('niveauUrgence')->getData();
                    $criteria['typeDocument'] = $form->get('typeDocument')->getData();
                    $criteria['idMateriel'] = $idMateriel[0]['num_matricule'];
                    $criteria['internetExterne'] = $form->get('internetExterne')->getData();
                    $criteria['dateDebut'] = $form->get('dateDebut')->getData();
                    $criteria['dateFin'] = $form->get('dateFin')->getData();
                } elseif(empty($idMateriel)) {
                    $empty = true;
                }
                
            } else {
                
                $criteria['statut'] = $form->get('statut')->getData();
                $criteria['niveauUrgence'] = $form->get('niveauUrgence')->getData();
                $criteria['typeDocument'] = $form->get('typeDocument')->getData();
                $criteria['idMateriel'] = $form->get('idMateriel')->getData();
                $criteria['internetExterne'] = $form->get('internetExterne')->getData();
                $criteria['dateDebut'] = $form->get('dateDebut')->getData();
                $criteria['dateFin'] = $form->get('dateFin')->getData();
            }
            
        } 

      
        $page = $request->query->getInt('page', 1);
        $limit = 10;
      
        $data = $repository->findPaginatedAndFiltered($page, $limit, $criteria);
        $idMateriels = $this->recupIdMaterielEnChaine($data);
        $numSerieParc = $this->ditModel->recuperationNumSerieNumParc($idMateriels);
        $idMat = [];
        foreach ($numSerieParc as  $value) {
            $idMat[] = $value['num_matricule'];
        }
//         dump($data);    
// dump($idMateriels);
// dump($numSerieParc);
// dd($idMat);
        $totalBadms = $repository->countFiltered($criteria);

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
     * @Route("/api/excel-dit", name="dit_excel", methods={"GET", "POST"})
     * 
     */
    // public function fetchExcel(Request $request)
    // {
    //     ini_set('memory_limit', '1024M'); 

    //     $data = json_decode($request->getContent(), true);

    //     $idMateriel = $data['idMateriel'] ?? null;
    //     $idTypeDocument = $data['typeDocument'] ?? null;
    //     $idNiveauUrgence = $data['niveauUrgence'] ?? null;
    //     $idStatut = $data['statut'] ?? null;
    //     $interneExterne = $data['interExtern'] ?? null;
    //     $dateDebut = $data['dateDebut'] ?? null;
    //     $dateFin = $data['dateFin'] ?? null;
    
       
    //     $criteria = [
    //         'statut' => $idStatut === null ? '' : self::$em->getRepository(StatutDemande::class)->find($idStatut),
    //         'niveauUrgence' => $idNiveauUrgence === null ? '' : self::$em->getRepository(WorNiveauUrgence::class)->find($idNiveauUrgence),
    //         'typeDocument' => $idTypeDocument === null ? '' : self::$em->getRepository(WorTypeDocument::class)->find($idTypeDocument),
    //         'idMateriel' => $idMateriel === null ? '' : $idMateriel,
    //         'internetExterne' => $interneExterne === null ? '' : $interneExterne,
    //         'dateDebut' => $dateDebut === null ? '' : $dateDebut,
    //         'dateFin' => $dateFin === null ? '' : $dateFin
    //     ];
        
    //     // Récupérer les données depuis le modèle
    // $data = self::$em->getRepository(DemandeIntervention::class)->findAndFilteredExcel($criteria);

    // // Vérifiez si les données existent
    // if (!$data) {
    //     return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
    // }
    // // header("Content-type:application/json");

    // // $jsonData = json_encode($data);

    //     //$this->testJson($jsonData);
    // // Renvoyer les données en réponse JSON
    //  //echo new JsonResponse($data);
    //  //$jsonData = $this->serializer->serialize($data, 'json', ['groups' => 'intervention']);

    //  $batchSize = 100; // Taille du lot
    //     $jsonData = [];

    //     foreach (array_chunk($data, $batchSize) as $batch) {
    //         $jsonData = array_merge($jsonData, json_decode($this->serializer->serialize($batch, 'json'), true));
    //     }
    //  return new JsonResponse($jsonData, 200, [], true);
    // }


   
   

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
 * @Route("/ditValidation/{numDit}/{id}", name="dit_validationDit")
 *
 * @return void
 */
   public function validationDit($numDit, $id, Request $request)
   {
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
