<?php

namespace App\Controller;

use App\Entity\Dom;
use App\Entity\Rmq;
use App\Entity\Site;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Idemnity;
use App\Entity\Indemnite;
use App\Entity\Personnel;
use App\Form\DomForm1Type;
use App\Form\DomForm2Type;
use App\Entity\SousTypeDocument;
use App\Repository\IdemniteRepository;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomsController extends Controller
{
    use FormatageTrait;
    
    private $dom;

    public function __construct()
    {
        parent::__construct();
        $this->dom = new Dom();
    }
 
    /**
     * @Route("/dom-first-form", name="dom_first_form")
     */
    public function firstForm(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
    
        $this->dom->setAgenceEmetteur($CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']) );
        $this->dom->setServiceEmetteur($CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']));
        $this->dom->setSousTypeDocument(self::$em->getRepository(SousTypeDocument::class)->find(2));
        $this->dom->setSalarier('PERMANENT');

   
        $form =self::$validator->createBuilder(DomForm1Type::class, $this->dom)->getForm();

 
        
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
          
            $this->dom->setSalarier($form->get('salarie')->getData());
            $formData = $form->getData()->toArray();
        

            $this->sessionService->set('form1Data', $formData);

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }
        
        self::$twig->display('doms/firstForm.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
        ]);
    }

    /**
     * @Route("/dom-second-form", name="dom_second_form")
     */
    public function secondForm(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
//dd(self::$em->getRepository(Indemnite::class)->findAll());
 /** INITIALISATION des données qui vent de FirstFORM */
 $form1Data = $this->sessionService->get('form1Data', []);
 $this->dom->setMatricule($form1Data['matricule']);
 $this->dom->setSalarier($form1Data['salarier']);
 $this->dom->setSousTypeDocument($form1Data['sousTypeDocument']);
 $this->dom->setCategorie($form1Data['categorie']);
 if ($form1Data['salarier'] === "TEMPORAIRE") {
     $this->dom->setNom($form1Data['nom']);
     $this->dom->setPrenom($form1Data['prenom']);
     $this->dom->setCin($form1Data['cin']);
     
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        $agenceEmetteur = $CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']);
        $serviceEmetteur = $CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']);
        $codeAgenceEmetteur = $CodeServiceofCours[0]['agence_ips'] ;
        $codeServiceEmetteur = $CodeServiceofCours[0]['service_ips'] ;
        
 } else {
     $personnel = self::$em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
    
     $this->dom->setNom($personnel->getNom());
     $this->dom->setPrenom($personnel->getPrenoms());
 }
/** INITIALISATION AGENCE ET SERVICE Emetteur et Debiteur */
        $this->dom->setAgenceEmetteur($agenceEmetteur);
        $this->dom->setServiceEmetteur($serviceEmetteur);
        $idAgence = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgenceEmetteur])->getId();
        $this->dom->setAgence(self::$em->getRepository(Agence::class)->find($idAgence));
        $this->dom->setService(self::$em->getRepository(Service::class)->findOneBy(['codeService' => $codeServiceEmetteur]));
       
      
       
        //initialisation site
        $sousTypedocument = $form1Data['sousTypeDocument'];
            $catg = $form1Data['categorie'];
            if($CodeServiceofCours[0]['agence_ips'] === '50'){
                $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
               
           } else {
            $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
           }
           $criteria = [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq,
            'categorie' => $catg
            ];

            $indemites = self::$em->getRepository(Indemnite::class)->findBy($criteria);
            $sites = [];
            foreach ($indemites as $key => $value) {
                $sites[] = $value->getSite()->getId();
            }
            if(in_array(8, $sites)){
                $this->dom->setSite(self::$em->getRepository(Site::class)->find(8));
            } else {
                $this->dom->setSite(self::$em->getRepository(Site::class)->find(1));
            }


        $is_temporaire = $form1Data['salarier'];
        $form =self::$validator->createBuilder(DomForm2Type::class, $this->dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ici, vous pouvez enregistrer les données dans la base de données si nécessaire

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('some_success_route');
        }

        self::$twig->display('doms/secondForm.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'is_temporaire' => $is_temporaire
        ]);
    }

    /**
     * @Route("/categorie-fetch/{id}", name="fetch_categorie", methods={"GET"})
     * 
     * Cette fonction permet d'envoier les donner de categorie selon la sousType de document
     *
     * @param int $id
     * @return void
     */
    public function categoriefetch(int $id)
    {   $this->SessionStart();
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        $sousTypedocument = self::$em->getRepository(SousTypeDocument::class)->find($id);
        if($CodeServiceofCours[0]['agence_ips'] === '50'){
            $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
           
       } else {
        $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
       }
       // dump($sousTypedocument);
       // dump($rmq);
       $criteria = [
        'sousTypeDoc' => $sousTypedocument,
        'rmq' => $rmq
     ];
      //dump($criteria);
        
     $catg = self::$em->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);
    //dd($catg);

        header("Content-type:application/json");

        echo json_encode($catg);
    }

     /**
     * @Route("/matricule-fetch/{id}", name="fetch_matricule", methods={"GET"})
     * 
     * Cette fonction permet d'envoier les donner de categorie selon la sousType de document
     *
     * @param int $id
     * @return void
     */
    public function matriculeFetch(int $id)
    {
        $personnel = self::$em->getRepository(Personnel::class)->find($id)->toArray();
     

        header("Content-type:application/json");

        echo json_encode($personnel);
    }


    /**
     * @Route("/form1Data-fetch", name="fetch_form1Data", methods={"GET"})
     *permet d'envoyer les donnner du form1
     * @return void
     */
    public function form1DataFetch()
    {
        $form1Data = $this->sessionService->get('form1Data', []);
        header("Content-type:application/json");

        echo json_encode($form1Data);
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
 * @Route("/site-idemnite-fetch/{id}", name="fetch_siteIdemnite", methods={"GET"})
 *
 * @return void
 */
public function siteIndemniteFetch(int $id)
{
    $site = self::$em->getRepository(Site::class)->find($id);
    $montant = self::$em->getRepository(Indemnite::class)->findOneBy(['site' => $site])->getMontant();

    $montant = $this->formatNumber($montant);

    header("Content-type:application/json");

    echo json_encode(['montant' => $montant]);
}

/**
 * @Route("/personnel-fetch/{matricule}", name="fetch_personnel", methods={"GET"})
 *
 * @param [type] $matricule
 * @return void
 */
public function personnelFetch($matricule){
    $personne = self::$em->getRepository(Personnel::class)->findOneBy(['Matricule' => $matricule]);
    
    $tab = [
        'compteBancaire' => $personne->getNumeroCompteBancaire(),
    ];

    header("Content-type:application/json");

    echo json_encode($tab);
}
}