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

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewDirectTrait();
    }

    /**
     * @Route("/new-da-direct", name="da_new_direct")
     */
    public function newDADirect(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin() || $this->estCreateurDeDADirecte());
        /** FIN AUtorisation accès */

        $demandeAppro = $this->initialisationDemandeApproDirect();

        $form = $this->getFormFactory()->createBuilder(DemandeApproDirectFormType::class, $demandeAppro)->getForm();
        $this->traitementFormDirect($form, $request, $demandeAppro);

        return $this->render('da/new-da-direct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormDirect($form, Request $request, DemandeAppro $demandeAppro): void
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

                $this->traitementFichiers($demandeApproL, $files); // traitement des fichiers uploadés pour chaque ligne DAL
                $this->getEntityManager()->persist($demandeApproL);
            }

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeAppro);

            /** Modifie la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->getEntityManager());
            $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation()) $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);

            $this->getEntityManager()->flush();

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro);

            $this->emailDaService->envoyerMailcreationDa($demandeAppro, $this->getUser());

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
