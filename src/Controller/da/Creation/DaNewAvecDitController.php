<?php

namespace App\Controller\da\Creation;

use App\Constants\admin\ApplicationConstant;
use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\dit\DemandeIntervention;
use App\Form\da\DemandeApproFormType;
use App\Service\application\ApplicationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\creation\DaNewAvecDitTrait;
use App\Service\da\FileUploaderForDAService;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaNewAvecDitController extends Controller
{
    use DaNewAvecDitTrait;
    const STATUT_DAL = [
        'enregistrerBrouillon' => StatutDaConstant::STATUT_EN_COURS_CREATION,
        'soumissionAppro'      => StatutDaConstant::STATUT_SOUMIS_APPRO,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewAvecDitTrait();
    }

    /**
     * @Route("/new-avec-dit/{daId<\d+>}/{ditId}", name="da_new_avec_dit")
     */
    public function new(int $daId, int $ditId, Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        /** 
         * @var DemandeIntervention $dit DIT correspondant à l'id $ditId
         */
        $dit = $this->ditRepository->find($ditId);

        $demandeAppro = $daId === 0 ? $this->initialisationDemandeApproAvecDit($dit) : $this->demandeApproRepository->find($daId);
        $demandeAppro
            ->setDit($dit)
            ->setCodeSociete($codeSociete)
            ->setDateFinSouhaite($this->dateLivraisonPrevueDA($dit->getNumeroDemandeIntervention(), $dit->getIdNiveauUrgence()->getDescription()))
        ;

        $form = $this->getFormFactory()->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();
        $this->traitementForm($form, $request, $demandeAppro, $dit);

        return $this->render('da/new-avec-dit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro, DemandeIntervention $dit): void
    {
        $dateFinSouhaite = $demandeAppro->getDateFinSouhaite();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $form->getData();
            // suivi étape par étape de l'enregistrement (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_CREATION_AVEC_DIT, $demandeAppro->getNumeroDemandeAppro());

            if ($demandeAppro->getDateFinSouhaite() < $dateFinSouhaite) {
                $message = 'La date fin souhaitée ne peut pas être antérieure à la date initiale prévue (' . $dateFinSouhaite->format('d/m/Y') . ')';
                $this->getSessionService()->set('notification', ['type' => 'error', 'message' => $message]);
                $suivi->echecOperation($message);
            } else {
                $firstCreation = $demandeAppro->getNumeroDemandeAppro() === null;
                $numDa = $firstCreation
                    ? $suivi->etape('Numérotation', function () {
                        return $this->autoDecrement(ApplicationConstant::CODE_DAP);
                    })
                    : $demandeAppro->getNumeroDemandeAppro();
                $suivi->setNumeroDocument($numDa);
                $demandeAppro->setNumeroDemandeAppro($numDa)->setNumeroDemandeApproMere($numDa);
                $formDAL = $form->get('DAL');

                // Récupérer le nom du bouton cliqué
                $clickedButtonName = $this->getButtonName($request);
                $demandeAppro->setStatutDal(StatutDaConstant::STATUT_DAL[$clickedButtonName]);

                $suivi->etape('Lignes de DA (fichiers, prix IPS)', function () use ($formDAL, $numDa, $clickedButtonName, $demandeAppro) {
                    foreach ($formDAL as $subFormDAL) {
                        /** 
                         * @var DemandeApproL $demandeApproL
                         * On récupère les données du formulaire DAL
                         */
                        $demandeApproL = $subFormDAL->getData();

                        if ($demandeApproL->getDeleted() == 1) {
                            $this->getEntityManager()->remove($demandeApproL);
                        } else {
                            // Récupérer les données
                            $filesToDelete = $subFormDAL->get('filesToDelete')->getData();
                            $existingFileNames = $subFormDAL->get('existingFileNames')->getData();
                            $newFiles = $subFormDAL->get('fileNames')->getData();

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

                            /** 
                             * @var DemandeApproL $demandeApproL
                             */
                            $demandeApproL
                                ->setNumeroDemandeAppro($numDa)
                                ->setStatutDal(StatutDaConstant::STATUT_DAL[$clickedButtonName])
                                ->setPrixUnitaire($this->daModel->getPrixUnitaire($demandeApproL->getArtRefp())[0])
                                ->setNumeroDit($demandeAppro->getNumeroDemandeDit())
                                ->setJoursDispo($this->getJoursRestants($demandeApproL))
                                ->setFileNames($allFileNames)
                            ;

                            if ($demandeApproL->getNumeroFournisseur() == 0) {
                                $demandeApproL->setNumeroFournisseur($this->fournisseurs[$demandeApproL->getNomFournisseur()] ?? 0); // définir le numéro du fournisseur
                            }

                            $this->getEntityManager()->persist($demandeApproL);
                        }
                    }
                });

                // si c'est la première création, on met à jour la colonne dernière_id dans la table applications
                if ($firstCreation) {
                    /** Modifie la colonne dernière_id dans la table applications */
                    $suivi->etape('Mise à jour du dernier numéro', function () use ($numDa) {
                        $applicationService = new ApplicationService($this->getEntityManager());
                        $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);
                    });
                }

                /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
                $suivi->etape('Base de données', function () use ($demandeAppro) {
                    $this->getEntityManager()->persist($demandeAppro);
                    $this->getEntityManager()->flush();
                });

                /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
                if ($demandeAppro->getObservation()) {
                    $suivi->etape('Observation', function () use ($numDa, $demandeAppro) {
                        $this->insertionObservation($numDa, $demandeAppro->getObservation());
                    });
                }

                // ajout des données dans la table DaAfficher
                $suivi->etape('Mise à jour da_afficher', function () use ($demandeAppro, $firstCreation, $dit) {
                    $this->ajouterDaDansTableAffichage($demandeAppro, $firstCreation, $dit);
                });

                if ($clickedButtonName === "soumissionAppro") {
                    $suivi->etape('Mail de création', function () use ($demandeAppro) {
                        $this->emailDaService->envoyerMailCreationDa($demandeAppro, $this->getUser());
                    });
                }

                $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
                $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
            }
        }
    }
}
