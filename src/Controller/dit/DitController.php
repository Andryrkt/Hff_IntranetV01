<?php

namespace App\Controller\dit;


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\utilisateur\User;
use App\Form\dit\demandeInterventionType;
use App\Service\genererPdf\GenererPdfDit;
use App\Service\historiqueOperation\HistoriqueOperationDITService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DitController extends Controller
{
    use DitTrait;
    use FormatageTrait;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService;
    }

    /**
     * @Route("/dit/new", name="dit_new")
     *
     * @param Request $request
     * @return void
     */
    public function new(Request $request)
    {
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

        //AFFICHAGE ET TRAITEMENT DU FORMULAIRE
        $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();
        $this->traitementFormulaire($form, $request, $demandeIntervention, $user);

        $this->logUserVisit('dit_new'); // historisation du page visité par l'utilisateur

        self::$twig->display('dit/new.html.twig', [
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

            $dits = $this->infoEntrerManuel($dit, self::$em, $user);

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
            $insertDemandeInterventions = $this->insertDemandeIntervention($dits, $demandeIntervention, self::$em);
            self::$em->persist($insertDemandeInterventions);
            self::$em->flush();

            //ENVOYER le PDF DANS DOXCUWARE
            $genererPdfDit->copyInterneToDOCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(), str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $pdfDemandeInterventions->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }

    private function modificationDernierIdApp($dits)
    {
        $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
        $application->setDerniereId($dits->getNumeroDemandeIntervention());
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        self::$em->persist($application);
        self::$em->flush();
    }

    private function autorisationApp($user): bool
    {
        //id pour DIT est 4
        $AppIds = $user->getApplicationsIds();
        return in_array(4, $AppIds);
    }

    private function autorisationAcces($user)
    {
        if (!$this->autorisationApp($user)) {
            $message = "vous n'avez pas l'autorisation";

            $this->historiqueOperation->sendNotificationCreation($message, '-', 'profil_acceuil');
        }
    }
}
