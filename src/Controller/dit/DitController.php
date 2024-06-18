<?php

namespace App\Controller\dit;

use App\Entity\Agence;
use App\Controller\Controller;
use App\Entity\DemandeIntervention;
use App\Entity\Service;
use App\Form\demandeInterventionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);

        $demandeIntervention = new DemandeIntervention();
        $demandeIntervention->setAgenceEmetteur($CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']) );
        $demandeIntervention->setServiceEmetteur($CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']));
        
        // dd(self::$em->getRepository(Agence::class)->findOneBy(['id' => 1])->getId());

        //dd(self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAg ]));
         $demandeIntervention->setAgence(self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $CodeServiceofCours[0]['agence_ips'] ]));
        //dd($demandeIntervention->getAgence());
        $demandeIntervention->setService(self::$em->getRepository(Service::class)->findOneBy(['codeService' => $CodeServiceofCours[0]['service_ips'] ]));

        $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();


  
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $dits= $form->getData(); 

           dd($dits);


            self::$em->persist($dits);
            self::$em->flush();


            $this->redirectToRoute("utilisateur_new");
        }

        self::$twig->display('dit/new.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'form' => $form->createView()
        ]);
    }

/**
 * @Route("/agence-fetch/{id}", name="fetch_agence", methods={"GET"})
 *
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
