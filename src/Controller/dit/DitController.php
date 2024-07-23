<?php

namespace App\Controller\dit;


use App\Entity\Agence;
use App\Entity\Application;
use App\Controller\Controller;
use App\Controller\Traits\DitTrait;
use App\Entity\DemandeIntervention;
use App\Form\demandeInterventionType;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class DitController extends Controller
{
    use DitTrait;
    use FormatageTrait;

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
           
           
           if($demandeIntervention->getIdMateriel() === null){
                $message = 'Renseigner l\'information matériel';
                $this->alertRedirection($message);
                dd('Renseigner l\'information matériel');
           } else {

               $dits = $this->infoEntrerManuel($form, self::$em);
               
               //RECUPERATION de la dernière NumeroDemandeIntervention 
               $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
               $application->setDerniereId($dits->getNumeroDemandeIntervention());
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();
            
            
            
            /**CREATION DU PDF*/
            //recupération des donners dans le formulaire
            $pdfDemandeInterventions = $this->pdfDemandeIntervention($dits, $demandeIntervention);
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($dits);
            //genere le PDF
            $this->genererPdf->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);
            
            //envoie des pièce jointe dans une dossier et le fusionner
            $this->envoiePieceJoint($form, $dits, $this->fusionPdf);
            
            
            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            $insertDemandeInterventions = $this->insertDemandeIntervention($dits, $demandeIntervention);
            self::$em->persist($insertDemandeInterventions);
            self::$em->flush();
            
            //ENVOYER le PDF DANS DOXCUWARE
            if($dits->getAgence()->getCodeAgence() === "91" || $dits->getAgence()->getCodeAgence() === "92") {
                $this->genererPdf->copyInterneToDOXCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));
            }
            
            $this->redirectToRoute("dit_index");
        }
            
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
