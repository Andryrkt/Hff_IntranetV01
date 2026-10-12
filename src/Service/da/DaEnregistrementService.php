<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproParent;
use App\Entity\dit\DemandeIntervention;
use App\Service\application\ApplicationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Orchestre l'enregistrement d'une DA à la création : lignes, n° DAP, persist, observation, DaAfficher, mail.
 * Le contrôle de la date, le n° de DA (autoDecrement) et la redirection restent au contrôleur.
 */
class DaEnregistrementService
{
    private const NOTIFICATION = ['type' => 'success', 'message' => 'Votre demande a été enregistrée'];

    private EntityManagerInterface $em;
    private DaCreationLigneService $daCreationLigneService;
    private DaObservationService $daObservationService;
    private DaAfficherService $daAfficherService;
    private EmailDaService $emailDaService;

    public function __construct(
        EntityManagerInterface $em,
        DaCreationLigneService $daCreationLigneService,
        DaObservationService $daObservationService,
        DaAfficherService $daAfficherService,
        EmailDaService $emailDaService
    ) {
        $this->em = $em;
        $this->daCreationLigneService = $daCreationLigneService;
        $this->daObservationService = $daObservationService;
        $this->daAfficherService = $daAfficherService;
        $this->emailDaService = $emailDaService;
    }

    /** DA avec DIT. */
    public function enregistrerDaAvecDit(DemandeAppro $demandeAppro, FormInterface $form, DemandeIntervention $dit, string $numDa, bool $firstCreation, string $bouton, string $username, User $user): array
    {
        $statutDal = StatutDaConstant::STATUT_DAL[$bouton];
        $demandeAppro->setNumeroDemandeAppro($numDa)->setNumeroDemandeApproMere($numDa)->setStatutDal($statutDal);

        $this->daCreationLigneService->traiterLignesAvecDit($form->get('DAL'), $demandeAppro, $numDa, $statutDal);
        $this->persisterDa($demandeAppro, $demandeAppro->getObservation(), $numDa, $firstCreation, $username);

        $this->daAfficherService->generateDaAfficherOnCreationDa($demandeAppro, $firstCreation, $dit);

        if ($bouton === "soumissionAppro") $this->emailDaService->envoyerMailCreationDa($demandeAppro, $user);

        return self::NOTIFICATION;
    }

    /** DA reappro mensuel. */
    public function enregistrerDaReapproMensuel(DemandeAppro $demandeAppro, string $numDa, bool $firstCreation, string $bouton, string $username, User $user): array
    {
        $this->appliquerAgenceServiceDebiteur($demandeAppro);

        $statutDal = StatutDaConstant::STATUT_DAL[$bouton];
        $demandeAppro
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeApproMere($numDa)
            ->setDetailDal($demandeAppro->getDetailDal() ?? '-')
            ->setStatutDal($statutDal);

        $this->daCreationLigneService->traiterLignesReappro($demandeAppro, $numDa, $statutDal);
        $this->persisterDa($demandeAppro, $demandeAppro->getObservation(), $numDa, $firstCreation, $username);

        $this->daAfficherService->generateDaAfficherOnCreationDa($demandeAppro, $firstCreation);

        if ($bouton === "soumissionAppro") $this->emailDaService->envoyerMailCreationDa($demandeAppro, $user);

        return self::NOTIFICATION;
    }

    /** DA achat (parent). */
    public function enregistrerDaParent(DemandeApproParent $demandeApproParent, FormInterface $form, string $numDa, bool $firstCreation, string $bouton, string $username, User $user): array
    {
        $this->appliquerAgenceServiceDebiteur($demandeApproParent);

        $statutDal = StatutDaConstant::STATUT_DAL[$bouton];
        $demandeApproParent
            ->setNumeroDemandeAppro($numDa)
            ->setDetailDal($this->normaliserDetail($demandeApproParent->getDetailDal()))
            ->setStatutDal($statutDal);

        $this->daCreationLigneService->traiterLignesParent($form->get('demandeApproParentLines'), $numDa, $statutDal);
        $this->persisterDa($demandeApproParent, $demandeApproParent->getObservation(), $numDa, $firstCreation, $username);

        $this->daAfficherService->generateDaAfficherOnCreationDaParent($demandeApproParent, $firstCreation);

        if ($bouton === "soumissionAppro") $this->emailDaService->envoyerMailCreationDaParent($demandeApproParent, $user);

        return self::NOTIFICATION;
    }

    /** @param DemandeAppro|DemandeApproParent $da */
    private function appliquerAgenceServiceDebiteur($da): void
    {
        $da
            ->setAgenceDebiteur($da->getDebiteur()['agence'])
            ->setServiceDebiteur($da->getDebiteur()['service'])
            ->setAgenceServiceDebiteur($da->getAgenceDebiteur()->getCodeAgence() . '-' . $da->getServiceDebiteur()->getCodeService());
    }

    /** Normalise \r\n et \r isolés vers \n, puis réduit 3+ sauts de ligne consécutifs à 2. */
    private function normaliserDetail(?string $detail): string
    {
        $detail = preg_replace('/\r\n|\r/', "\n", (string) $detail);

        return preg_replace('/\n{3,}/', "\n\n", trim($detail));
    }

    /**
     * Met à jour la dernière id (1re création), persiste la DA et insère l'observation si elle existe.
     *
     * @param DemandeAppro|DemandeApproParent $da
     */
    private function persisterDa($da, ?string $observation, string $numDa, bool $firstCreation, string $username): void
    {
        if ($firstCreation) {
            (new ApplicationService($this->em))->mettreAJourDerniereIdApplication('DAP', $numDa);
        }

        $this->em->persist($da);
        $this->em->flush();

        if ($observation) $this->daObservationService->insertionObservation($numDa, $observation, $username);
    }
}
