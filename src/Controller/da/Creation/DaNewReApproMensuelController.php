<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\creation\DaNewReapproMensuelTrait;
use App\Form\da\DemandeApproReapproMensuelFormType;

/**
 * @Route("/demande-appro")
 */
class DaNewReApproMensuelController extends Controller
{
    use DaNewReapproMensuelTrait;
    use AutorisationTrait;
    const STATUT_DAL = [
        'enregistrerBrouillon' => DemandeAppro::STATUT_EN_COURS_CREATION,
        'soumissionAppro'      => DemandeAppro::STATUT_SOUMIS_APPRO,
    ];

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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin() || $this->estCreateurDeDADirecte());
        /** FIN AUtorisation accès */

        $demandeAppro     = $id === 0 ? $this->initialisationDemandeApproReapproMensuel() : $this->demandeApproRepository->find($id);
        $this->generateDemandApproLinesFromReappros($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproReapproMensuelFormType::class, $demandeAppro, [
            'em' => $this->getEntityManager()
        ])->getForm();
        $this->traitementFormReappro($form, $request);

        return $this->render('da/new-da-reappro-mensuel.html.twig', [
            'form'         => $form->createView(),
            'codeCentrale' => $this->estAdmin() || in_array($demandeAppro->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
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
            $firstCreation = $demandeAppro->getNumeroDemandeAppro() === null;
            $numDa = $firstCreation ? $this->autoDecrement('DAP') : $demandeAppro->getNumeroDemandeAppro();

            $this->gererAgenceServiceDebiteur($demandeAppro);

            // Récupérer le nom du bouton cliqué
            $clickedButtonName = $this->getButtonName($request);
            $statutDa = self::STATUT_DAL[$clickedButtonName];

            $demandeAppro
                ->setNumeroDemandeAppro($numDa)
                ->setNumeroDemandeApproMere($numDa)
                ->setDetailDal($demandeAppro->getDetailDal() ?? '-')
                ->setStatutDal($statutDa);

            /** @var DemandeApproL $dal */
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

            // si c'est la première création, on met à jour la colonne dernière_id dans la table applications
            if ($firstCreation) {
                /** Modifie la colonne dernière_id dans la table applications */
                $applicationService = new ApplicationService($this->getEntityManager());
                $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);
            }

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeAppro);
            $this->getEntityManager()->flush();

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation()) $this->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $demandeAppro->getObservation());

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro, $firstCreation);

            if ($clickedButtonName === "soumissionAppro") $this->emailDaService->envoyerMailCreationDa($demandeAppro, $this->getUser());

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }
}
