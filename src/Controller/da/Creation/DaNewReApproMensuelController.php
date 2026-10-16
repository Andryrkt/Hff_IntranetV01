<?php

namespace App\Controller\da\Creation;

use App\Constants\da\StatutDaConstant;
use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Controller\Traits\da\creation\DaNewReapproMensuelTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproReapproMensuelFormType;
use Symfony\Component\HttpFoundation\Request;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaNewReApproMensuelController extends Controller
{
    use DaNewReapproMensuelTrait;

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewReapproMensuelTrait();
    }

    /**
     * @Route("/new-da-reappro-mensuel/{id<\d+>}", name="da_new_reappro_mensuel")
     */
    public function newDAReapproMensuel(int $id, Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $demandeAppro     = $id === 0 ? $this->initialisationDemandeApproReapproMensuel($codeSociete) : $this->demandeApproRepository->find($id);
        $this->generateDemandApproLinesFromReappros($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproReapproMensuelFormType::class, $demandeAppro, [
            'em' => $this->getEntityManager()
        ])->getForm();
        $this->traitementFormReappro($form, $request);

        return $this->render('da/new-da-reappro-mensuel.html.twig', [
            'form'         => $form->createView(),
            'codeCentrale' => $this->estAdmin() || $this->estEnergie(),
        ]);
    }

    private function gererAgenceServiceDebiteur(DemandeAppro $demandeAppro)
    {
        $demandeAppro->setAgenceDebiteur($demandeAppro->getDebiteur()['agence'])
            ->setServiceDebiteur($demandeAppro->getDebiteur()['service'])
            ->setAgenceServiceDebiteur($demandeAppro->getAgenceDebiteur()->getCodeAgence() . '-' . $demandeAppro->getServiceDebiteur()->getCodeService());
    }
    private function traitementFormReappro($form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $form->getData();
            // suivi étape par étape de l'enregistrement (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_CREATION_REAPPRO_MENSUEL, $demandeAppro->getNumeroDemandeAppro());

            if ($demandeAppro->getDateFinSouhaite() < new \DateTime()) {
                $message = 'La date fin souhaitée ne peut pas être antérieure à la date du jour.';
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

                $suivi->etape('Agence / service débiteur', function () use ($demandeAppro) {
                    $this->gererAgenceServiceDebiteur($demandeAppro);
                });

                // Récupérer le nom du bouton cliqué
                $clickedButtonName = $this->getButtonName($request);
                $statutDa = StatutDaConstant::STATUT_DAL[$clickedButtonName];

                $demandeAppro
                    ->setNumeroDemandeAppro($numDa)
                    ->setNumeroDemandeApproMere($numDa)
                    ->setDetailDal($demandeAppro->getDetailDal() ?? '-')
                    ->setStatutDal($statutDa);

                /** @var DemandeApproL $dal */
                $suivi->etape('Lignes de DA', function () use ($demandeAppro, $numDa, $statutDa) {
                    foreach ($demandeAppro->getDAL() as $dal) {
                        if ($dal->getQteDem()) {
                            $dal
                                ->setNumeroDemandeAppro($numDa)
                                ->setDemandeAppro($demandeAppro)
                                ->setDateFinSouhaite($demandeAppro->getDateFinSouhaite())
                                ->setJoursDispo($this->getJoursRestants($dal))
                                ->setStatutDal($statutDa);
                            $this->getEntityManager()->persist($dal);
                        } else {
                            $demandeAppro->removeDAL($dal); // ne pas persister les DAL avec qteDem vide
                            $this->getEntityManager()->remove($dal);
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
                    $suivi->etape('Observation', function () use ($demandeAppro) {
                        $this->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $demandeAppro->getObservation());
                    });
                }

                // ajout des données dans la table DaAfficher
                $suivi->etape('Mise à jour da_afficher', function () use ($demandeAppro, $firstCreation) {
                    $this->ajouterDaDansTableAffichage($demandeAppro, $firstCreation);
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
