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
        if(!isset($flashes)){
            $flashes = [];
        }
        
        if($form->isSubmitted() && $form->isValid())
        {
            $dits= $form->getData();
            
            $demandeIntervention = $this->demandeIntervention($dits);

            self::$em->persist($demandeIntervention);
            self::$em->flush();

            $this->flashManager->addFlash('sucess', 'demande ajouter');
            $flashes = $this->flashManager->getFlashes('success');
            $this->redirectToRoute("dit_new");
            
        }

        self::$twig->display('dit/new.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'form' => $form->createView(),
            'flashes' => $flashes,
        ]);
    }


    private function demandeIntervention($dits) : DemandeIntervention
    {
        $demandeIntervention = new DemandeIntervention();

            $demandeIntervention->setTypeDocument($dits->getTypeDocument());
            $demandeIntervention->setCodeSociete($dits->getCodeSociete());
            $demandeIntervention->setTypeReparation($dits->getTypeReparation());
            $demandeIntervention->setReparationRealise($dits->getReparationRealise());
            $demandeIntervention->setDatePrevueTravaux($dits->getDatePrevueTravaux());
            $demandeIntervention->setIdNiveauUrgence($dits->getIdNiveauUrgence());
            $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
            $demandeIntervention->setClientSousContrat($dits->getClientSousContrat());
            $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
            $demandeIntervention->setIdStatutDemande($dits->getIdStatutDemande());
            $demandeIntervention->setSecteur($dits->getSecteur());
            $demandeIntervention->setNomClient($dits->getNomClient());
            $demandeIntervention->setNumeroTel($dits->getNumeroTel());
            $demandeIntervention->setObjetDemande($dits->getObjetDemande());
            $demandeIntervention->setDetailDemande($dits->getDetailDemande());
            $demandeIntervention->setIdMateriel($dits->getIdMateriel());
            $demandeIntervention->setPieceJoint01($dits->getPieceJoint01());
            $demandeIntervention->setPieceJoint02($dits->getPieceJoint02());
            $demandeIntervention->setPieceJoint03($dits->getPieceJoint03());
            $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).''.substr($dits->getServiceEmetteur(), 0, 3));
            $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().''. $dits->getService()->getCodeService());
        return $demandeIntervention;
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
