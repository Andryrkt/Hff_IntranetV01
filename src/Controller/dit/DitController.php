<?php

namespace App\Controller\dit;



use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Form\dit\demandeInterventionType;
use App\Service\genererPdf\GenererPdfDit;
use App\Service\historiqueOperation\HistoriqueOperationDITService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Controller;
use App\Service\FusionPdf;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitController extends Controller
{
    use DitTrait;
    use FormatageTrait;
    use AutorisationTrait;


    private $historiqueOperation;
    private $fusionPdf;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
        $this->fusionPdf = new FusionPdf();
    }

    /**
     * @Route("/new", name="dit_new")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DIT);
        /** FIN AUtorisation acées */

        $demandeIntervention = new DemandeIntervention();

        //INITIALISATION DU FORMULAIRE
        $this->initialisationForm($demandeIntervention, $this->getEntityManager());

        //AFFICHAGE ET TRAITEMENT DU FORMULAIRE
        $form = $this->getFormFactory()->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();
        $this->traitementFormulaire($form, $request, $demandeIntervention, $this->getUser());

        $this->logUserVisit('dit_new'); // historisation du page visité par l'utilisateur

        return $this->render('dit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function traitementFormulaire($form, Request $request, $demandeIntervention, $user)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dit = $form->getData();

            if (empty($dit->getIdMateriel())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du matériel.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
            }

            if ($dit->getInternetExterne() === "EXTERNE" && empty($dit->getNomClient()) && empty($dit->getNumeroClient())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du client.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
            }

            if ($dit->getInternetExterne() === "EXTERNE" && empty($dit->getNomClient()) && empty($dit->getNumeroClient())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du client.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
            }

            $dits = $this->infoEntrerManuel($dit, $this->getEntityManager(), $user);

            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $this->modificationDernierIdApp($dits);

            /**CREATION DU PDF*/
            //recupération des donners dans le formulaire
            $pdfDemandeInterventions = $this->pdfDemandeIntervention($dits, $demandeIntervention);

            if (!in_array((int)$pdfDemandeInterventions->getIdMateriel(), [14571, 7669, 7670, 7671, 7672, 7673, 7674, 7675, 7677, 9863])) {
                //récupération des historique de materiel (informix)
                $historiqueMateriel = $this->historiqueInterventionMateriel($dits);
            } else {
                $historiqueMateriel = [];
            }

            //genere le PDF
            $genererPdfDit = new GenererPdfDit();
            $genererPdfDit->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);

            //envoie des pièce jointe dans une dossier et la fusionner
            $this->envoiePieceJoint($form, $dits, $this->fusionPdf);

            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            $insertDemandeInterventions = $this->insertDemandeIntervention($dits, $demandeIntervention, $this->getEntityManager());
            $this->getEntityManager()->persist($insertDemandeInterventions);
            $this->getEntityManager()->flush();

            //ENVOYER le PDF DANS DOXCUWARE
            $genererPdfDit->copyInterneToDOCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(), str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $pdfDemandeInterventions->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }

    private function modificationDernierIdApp($dits)
    {
        $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
        $application->setDerniereId($dits->getNumeroDemandeIntervention());
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }
}
