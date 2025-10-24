<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\creation\DaNewReapproTrait;
use App\Form\da\DemandeApproReapproFormType;

/**
 * @Route("/demande-appro")
 */
class DaNewReApproController extends Controller
{
    use DaNewReapproTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewReapproTrait();
    }

    /**
     * @Route("/new-da-reappro", name="da_new_reappro")
     */
    public function newDAReappro(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin() || $this->estCreateurDeDADirecte());
        /** FIN AUtorisation accès */

        $demandeAppro = $this->initialisationDemandeApproReappro();

        $form = $this->getFormFactory()->createBuilder(DemandeApproReapproFormType::class, $demandeAppro)->getForm();
        $this->traitementFormReappro($form, $request, $demandeAppro);

        return $this->render('da/new-da-direct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormReappro($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** 
             * @var DemandeAppro $demandeAppro
             */
            $demandeAppro = $form->getData();

            $numDa = $demandeAppro->getNumeroDemandeAppro();
            $formDAL = $form->get('DAL');

            foreach ($formDAL as $subFormDAL) {
                /** 
                 * @var DemandeApproL $demandeApproL
                 * On récupère les données du formulaire DAL
                 */
                $demandeApproL = $subFormDAL->getData();
                $files = $subFormDAL->get('fileNames')->getData(); // Récupération des fichiers

                $demandeApproL
                    ->setNumeroFournisseur($demandeApproL->getNumeroFournisseur() ?? '-')
                    ->setNomFournisseur($demandeApproL->getNomFournisseur() ?? '-')
                    ->setNumeroDemandeAppro($numDa)
                    ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
                    ->setJoursDispo($this->getJoursRestants($demandeApproL));

                $this->getEntityManager()->persist($demandeApproL);
            }

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeAppro);

            /** Modifie la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->getEntityManager());
            $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            $this->getEntityManager()->flush();

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro);

            $this->emailDaService->envoyerMailcreationDaDirect($demandeAppro, [
                'service'       => $demandeAppro->getServiceEmetteur()->getLibelleService(),
                'observation'   => $demandeAppro->getObservation() ?? '-',
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
            ]);

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }
}
