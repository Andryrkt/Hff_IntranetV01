<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\creation\DaNewAchatTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use App\Form\da\DemandeApproAchatFormType;
use App\Service\application\ApplicationService;
use App\Service\da\FileUploaderForDAService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/demande-appro") */
class DaNewAchatController extends Controller
{
    use DaNewAchatTrait, AutorisationTrait;
    const STATUT_DAL = [
        'enregistrerBrouillon' => DemandeAppro::STATUT_EN_COURS_CREATION,
        'soumissionAppro'      => DemandeAppro::STATUT_SOUMIS_APPRO,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewAchatTrait();
    }

    /**
     * @Route("/new-da-achat/{id<\d+>}", name="da_new_achat")
     */
    public function newDaAchat(int $id, Request $request)
    {
        // verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin() || $this->estCreateurDeDADirecte());
        /** FIN AUtorisation accès */

        $demandeApproParentRepository = $this->getEntityManager()->getRepository(DemandeApproParent::class);

        $demandeApproParent = $id === 0 ? $this->initialisationDemandeApproAchat() : $demandeApproParentRepository->find($id);

        $form = $this->getFormFactory()->createBuilder(DemandeApproAchatFormType::class, $demandeApproParent)->getForm();
        $this->traitementFormAchat($form, $request);

        return $this->render('da/new-da-achat.html.twig', [
            'form'        => $form->createView(),
            'codeCentrale' => $this->estAdmin() || in_array($demandeApproParent->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
        ]);
    }

    private function gererAgenceServiceDebiteur(DemandeApproParent $demandeApproParent)
    {
        $demandeApproParent
            ->setAgenceDebiteur($demandeApproParent->getDebiteur()['agence'])
            ->setServiceDebiteur($demandeApproParent->getDebiteur()['service'])
            ->setAgenceServiceDebiteur($demandeApproParent->getAgenceDebiteur()->getCodeAgence() . '-' . $demandeApproParent->getServiceDebiteur()->getCodeService());
    }

    private function traitementFormAchat(FormInterface $form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeApproParent $demandeApproParent */
            $demandeApproParent = $form->getData();
            $this->gererAgenceServiceDebiteur($demandeApproParent);

            $firstCreation = $demandeApproParent->getNumeroDemandeAppro() === null;
            $numDa = $firstCreation ? $this->autoDecrement('DAP') : $demandeApproParent->getNumeroDemandeAppro();
            $demandeApproParent->setNumeroDemandeAppro($numDa);
            $formDemandeApproLines = $form->get('demandeApproParentLines');

            // Récupérer le nom du bouton cliqué
            $clickedButtonName = $this->getButtonName($request);
            $demandeApproParent->setStatutDal(self::STATUT_DAL[$clickedButtonName]);

            foreach ($formDemandeApproLines as $subFormDapL) {
                /** @var DemandeApproParentLine $demandeApproParentLine */
                $demandeApproParentLine = $subFormDapL->getData();

                if ($demandeApproParentLine->isDeleted()) {
                    $this->getEntityManager()->remove($demandeApproParentLine);
                } else {
                    // Récupérer les données
                    $filesToDelete = $subFormDapL->get('filesToDelete')->getData();
                    $existingFileNames = $subFormDapL->get('existingFileNames')->getData();
                    $newFiles = $subFormDapL->get('fileNames')->getData();

                    // Supprimer les fichiers
                    if ($filesToDelete) {
                        $this->daFileUploader->deleteFiles(
                            explode(',', $filesToDelete),
                            $numDa
                        );
                    }

                    // Gérer l'upload et obtenir la liste finale
                    $allFileNames = $this->daFileUploader->handleFileUpload(
                        $newFiles,
                        $existingFileNames,
                        $numDa,
                        FileUploaderForDAService::FILE_TYPE["DEVIS"]
                    );

                    $demandeApproParentLine
                        ->setNumeroDemandeAppro($numDa)
                        ->setStatutDal(self::STATUT_DAL[$clickedButtonName])
                        ->setJoursDispo($this->getJoursRestants($demandeApproParentLine))
                        ->setFileNames($allFileNames)
                    ;

                    $this->getEntityManager()->persist($demandeApproParentLine);
                }
            }

            // si c'est la première création, on met à jour la colonne dernière_id dans la table applications
            if ($firstCreation) {
                /** Modifie la colonne dernière_id dans la table applications */
                $applicationService = new ApplicationService($this->getEntityManager());
                $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);
            }

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeApproParent);
            $this->getEntityManager()->flush();

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeApproParent->getObservation()) $this->insertionObservation($numDa, $demandeApproParent->getObservation());

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichageParent($demandeApproParent);

            if ($clickedButtonName === "soumissionAppro") $this->emailDaService->envoyerMailCreationDaParent($demandeApproParent, $this->getUser());

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }
}
