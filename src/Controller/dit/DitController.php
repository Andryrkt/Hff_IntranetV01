<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\DemandeIntervention;
use App\Form\demandeInterventionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DitController extends Controller
{
    public function index(){
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
    
        $data = self::$em->getRepository(DemandeIntervention::class)->findBy([], ['id'=>'DESC']);
    
    
        self::$twig->display('dit/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data
        ]);
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

    

        $form = self::$validator->createBuilder(demandeInterventionType::class)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $utilisateur= $form->getData(); 

            $selectedApplications = $form->get('applications')->getData();

            foreach ($selectedApplications as $application) {
                $utilisateur->addApplication($application);
            }


            self::$em->persist($utilisateur);
            self::$em->flush();


            $this->redirectToRoute("utilisateur_index");
        }

        self::$twig->display('dit/new.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'form' => $form->createView()
        ]);
    }
}
