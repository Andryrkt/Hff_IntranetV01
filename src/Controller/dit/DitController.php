<?php

namespace App\Controller\dit;



use App\Model\dit\DitModel;
use App\Entity\admin\Agence;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\utilisateur\User;
use App\Form\dit\demandeInterventionType;
use App\Service\genererPdf\GenererPdfDit;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        //recuperation de l'utilisateur connecter
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        /** Autorisation accées */
        $this->autorisationAcces($user);
        /** FIN AUtorisation acées */

        $demandeIntervention = new DemandeIntervention();
        
        //INITIALISATION DU FORMULAIRE
        $this->initialisationForm($demandeIntervention, self::$em);

        //AFFICHE LE FORMULAIRE
        $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            
            $dits = $this->infoEntrerManuel($form, self::$em, $user);
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
            $genererPdfDit = new GenererPdfDit();
            $genererPdfDit->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);
            
            //envoie des pièce jointe dans une dossier et la fusionner
            $this->envoiePieceJoint($form, $dits, $this->fusionPdf);
            
            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            $insertDemandeInterventions = $this->insertDemandeIntervention($dits, $demandeIntervention, self::$em);
            self::$em->persist($insertDemandeInterventions);
            self::$em->flush();
            
            //ENVOYER le PDF DANS DOXCUWARE
        
                $genererPdfDit->copyInterneToDOXCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));
            

            $this->sessionService->set('notification',['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("dit_index");
            
        }

        self::$twig->display('dit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function autorisationApp($user): bool
    {
        //id pour DIT est 4
        $AppIds = $user->getApplicationsIds();
        return in_array(4, $AppIds) ;
    }

    private function autorisationAcces($user)
    {
        if(!$this->autorisationApp($user)) {
            $message = "vous n'avez pas l'autorisation";
            $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
            $this->redirectToRoute("profil_acceuil");
            exit();
        }
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
        $ditModel = new DitModel();
        // Récupérer les données depuis le modèle
        $data = $ditModel->findAll($idMateriel, $numParc, $numSerie);

        // Vérifiez si les données existent
        if (!$data) {
            return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
        }
        header("Content-type:application/json");

        $jsonData = json_encode($data);

        $this->testJson($jsonData);
    }


}
