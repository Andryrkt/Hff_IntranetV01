<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproDirectFormType;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\creation\DaNewDirectTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/demande-appro")
 */
class DaNewDirectController extends Controller
{
    use DaNewDirectTrait;
    use AutorisationTrait;
    const STATUT_DAL = [
        'enregistrerBrouillon' => DemandeAppro::STATUT_EN_COURS_CREATION,
        'soumissionAppro'      => DemandeAppro::STATUT_SOUMIS_APPRO,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewDirectTrait();
    }

    /**
     * @Route("/new-da-direct/{id<\d+>}", name="da_new_direct")
     */
    public function newDADirect(int $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin() || $this->estCreateurDeDADirecte());
        /** FIN AUtorisation accès */

        $demandeAppro = $id === 0 ? $this->initialisationDemandeApproDirect() : $this->demandeApproRepository->findAvecDernieresDALetLR($id);

        $form = $this->getFormFactory()->createBuilder(DemandeApproDirectFormType::class, $demandeAppro, [
            'em' => $this->getEntityManager()
        ])->getForm();
        $this->traitementFormDirect($form, $request, $demandeAppro);

        return $this->render('da/new-da-direct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function gererAgenceServiceDebiteur(DemandeAppro $demandeAppro)
    {
        $demandeAppro->setAgenceDebiteur($demandeAppro->getDebiteur()['agence'])
            ->setServiceDebiteur($demandeAppro->getDebiteur()['service'])
            ->setAgenceServiceDebiteur($demandeAppro->getAgenceDebiteur()->getCodeAgence() . '-' . $demandeAppro->getServiceDebiteur()->getCodeService());
    }

    private function traitementFormDirect($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** 
             * @var DemandeAppro $demandeAppro
             */
            $demandeAppro = $form->getData();
            $this->gererAgenceServiceDebiteur($demandeAppro);

            $numDa = $demandeAppro->getNumeroDemandeAppro();
            $formDAL = $form->get('DAL');

            // Récupérer le nom du bouton cliqué
            $clickedButtonName = $this->getButtonName($request);
            $demandeAppro->setStatutDal(self::STATUT_DAL[$clickedButtonName]);

            foreach ($formDAL as $subFormDAL) {
                /** 
                 * @var DemandeApproL $demandeApproL
                 * On récupère les données du formulaire DAL
                 */
                $demandeApproL = $subFormDAL->getData();
                $files = $subFormDAL->get('fileNames')->getData(); // Récupération des fichiers

                $demandeApproL
                    ->setNumeroDemandeAppro($numDa)
                    ->setStatutDal(self::STATUT_DAL[$clickedButtonName])
                    ->setNumeroFournisseur($demandeApproL->getNumeroFournisseur() ?? '-')
                    ->setNomFournisseur($demandeApproL->getNomFournisseur() ?? '-')
                    ->setJoursDispo($this->getJoursRestants($demandeApproL));

                $this->traitementFichiers($demandeApproL, $files); // traitement des fichiers uploadés pour chaque ligne DAL
                $this->getEntityManager()->persist($demandeApproL);
            }

            /** Modifie la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->getEntityManager());
            $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeAppro);
            $this->getEntityManager()->flush();

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation()) $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro);

            if ($clickedButtonName === "soumissionAppro") $this->emailDaService->envoyerMailCreationDa($demandeAppro, $this->getUser());

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD pour chaque ligne de la demande appro (DAL)
     */
    private function traitementFichiers(DemandeApproL $dal, $files): void
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->daFileUploader->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
    }
}
