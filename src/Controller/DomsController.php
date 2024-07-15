<?php

namespace App\Controller;

use App\Entity\Dom;
use App\Entity\Personnel;
use App\Entity\SousTypeDocument;
use App\Form\DomForm1Type;
use App\Form\DomForm2Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomsController extends Controller
{
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

        $form1Data = $this->sessionService->get('form1Data', []);
        dd($form1Data);
        $form =self::$validator->createBuilder(DomForm2Type::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ici, vous pouvez enregistrer les données dans la base de données si nécessaire

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('some_success_route');
        }

        self::$twig->display('form/secondForm.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
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
    {
        $sousTypedocument = self::$em->getRepository(SousTypeDocument::class)->find($id);
     

        $catg = $sousTypedocument->getCatg();

        $categories = [];
        foreach ($catg as  $value) {
          $categories[] = [
              'value' => $value->getId(),
              'text' => $value->getDescription() 
          ];
        }

        header("Content-type:application/json");

        echo json_encode($categories);
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
}