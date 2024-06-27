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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeInterventionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DitController extends Controller
{
    use DitTrait;
    use FormatageTrait;

    // private DemandeIntervention $demandeIntervention;

    // public function __construct()
    // {
    //     $this->demandeIntervention = new DemandeIntervention();
    // }

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


        if($form->isSubmitted() && $form->isValid()) {
            $criteria['statut'] = $form->get('statut')->getData();
            $criteria['niveauUrgence'] = $form->get('niveauUrgence')->getData();
            $criteria['typeDocument'] = $form->get('typeDocument')->getData();
            $criteria['idMateriel'] = $form->get('idMateriel')->getData();
            $criteria['internetExterne'] = $form->get('internetExterne')->getData();
            $criteria['dateDebut'] = $form->get('dateDebut')->getData();
            $criteria['dateFin'] = $form->get('dateFin')->getData();
        } 

      
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository= self::$em->getRepository(DemandeIntervention::class);

      
        $data = $repository->findPaginatedAndFiltered($page, $limit, $criteria);
        $totalBadms = $repository->countFiltered($criteria);

        $totalPages = ceil($totalBadms / $limit);
        
        if($request->query->get("envoyer") === "listAnnuler") {
        
            $data = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria);
            $totalBadms = $repository->countFilteredListAnnuller($criteria);

            $totalPages = ceil($totalBadms / $limit);
        }
    
        self::$twig->display('dit/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data,
            'form' => $form->createView(),
                'currentPage' => $page,
                'totalPages' =>$totalPages,
                'criteria' => $criteria,
               'resultat' => $totalBadms,
        ]);
    }

 /**
     * @Route("/dit-excel/{idStatut}/{idNiveauUrgence}/{idTypeDocument}/{idMateriel}/{internetExterne}/{dateDebut}/{dateFin}", name="fetch_materiel", methods={"GET"})
     * cette fonctin permet d'envoyer les informations materiels en ajax
     */
    public function fetchExcel($idStatut,  $idNiveauUrgence, $idTypeDocument, $idMateriel, $internetExterne, $dateDebut, $dateFin)
    {
        $criteria = [
            'statut' => self::$em->getRepository(StatutDemande::class)->find($idStatut),
            'niveauUrgence' => self::$em->getRepository(WorNiveauUrgence::class)->find($idNiveauUrgence),
            'typeDocument' => self::$em->getRepository(WorTypeDocument::class)->find($idTypeDocument),
            'idMateriel' => $idMateriel,
            'internetExterne' => $internetExterne,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ];
        
        // Récupérer les données depuis le modèle
    $data = self::$em->getRepository(DemandeIntervention::class)->findAndFilteredExcel($criteria);

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


    private function infoEntrerManuel($form) 
    {
        $dits = $form->getData();
        
        $dits->setUtilisateurDemandeur($_SESSION['user']);
            $dits->setHeureDemande($this->getTime());
            $dits->setDateDemande(new \DateTime($this->getDatesystem()));
            $statutDemande = self::$em->getRepository(StatutDemande::class)->find(1);
            $dits->setIdStatutDemande($statutDemande);
            $dits->setNumeroDemandeIntervention($this->autoINcriment('DIT'));
            $email = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $_SESSION['user']])->getMail();
            $dits->setMailDemandeur($email);

         
            return $dits;
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

        //INITIALISATION DU FORMULAIRE
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        $demandeIntervention = new DemandeIntervention();
        $demandeIntervention->setAgenceEmetteur($CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']) );
        $demandeIntervention->setServiceEmetteur($CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']));
        $demandeIntervention->setIdNiveauUrgence(self::$em->getRepository(WorNiveauUrgence::class)->find(1));
        $idAgence = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $CodeServiceofCours[0]['agence_ips'] ])->getId();
        $demandeIntervention->setAgence(self::$em->getRepository(Agence::class)->find($idAgence));
        $demandeIntervention->setService(self::$em->getRepository(Service::class)->findOneBy(['codeService' => $CodeServiceofCours[0]['service_ips'] ]));

        //AFFICHE LE FORMULAIRE
        $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();

        $form->handleRequest($request);

        
        
        if($form->isSubmitted() && $form->isValid())
        {
            $dits = $this->infoEntrerManuel($form);
            
            if($form->get('pieceJoint03')->getData() !== null){
                $this->uplodeFile($form, $dits, 'pieceJoint03');
            $this->uplodeFile($form, $dits, 'pieceJoint02');
            $this->uplodeFile($form, $dits, 'pieceJoint01');
            }
            

        
        
        
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
            if($dits->getAgence()->getCodeAgence() === 91 || $dits->getAgence()->getCodeAgence() === 92) {
                $this->genererPdf->copyInterneToDOXCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));
            }
            
            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
            $application->setDerniereId($this->autoINcriment('DIT'));
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();

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

   
}
