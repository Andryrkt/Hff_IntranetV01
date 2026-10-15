<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Constants\da\StatutDaConstant;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Orchestre les actions d'une proposition (DA avec DIT et DA directe) :
 * lignes + statut + observation + DaAfficher + PDF/Excel + mail.
 * Chaque méthode renvoie la notification ['type', 'message'] ; la redirection reste au contrôleur.
 */
class DaPropositionService
{
    private EntityManagerInterface $em;
    private DaPropositionLigneService $daPropositionLigneService;
    private DaObservationService $daObservationService;
    private DaAfficherService $daAfficherService;
    private DaValidationService $daValidationService;
    private EmailDaService $emailDaService;

    public function __construct(
        EntityManagerInterface $em,
        DaPropositionLigneService $daPropositionLigneService,
        DaObservationService $daObservationService,
        DaAfficherService $daAfficherService,
        DaValidationService $daValidationService,
        EmailDaService $emailDaService
    ) {
        $this->em                        = $em;
        $this->daPropositionLigneService = $daPropositionLigneService;
        $this->daObservationService      = $daObservationService;
        $this->daAfficherService         = $daAfficherService;
        $this->daValidationService       = $daValidationService;
        $this->emailDaService            = $emailDaService;
    }

    /**
     * Enregistrer provisoirement la proposition
     */
    public function enregistrerBrouillon(DemandeAppro $da, $dalrList, ?string $observation, ?string $refsString): array
    {
        $dals = $da->getDAL();
        $refs = $this->daPropositionLigneService->parserRefs($refsString);

        $notification = $this->traiterProposition(
            $da,
            $dalrList,
            $observation,
            $refs,
            "La proposition a été enregistré avec succès",
            true,
            StatutDaConstant::STATUT_EN_COURS_PROPOSITION
        );

        $this->daPropositionLigneService->modifierChoixEtLigneDal($refs, $dals);

        $this->daAfficherService->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro());

        return $notification;
    }

    /**
     * Envoyer la proposition à l'atelier (DA avec DIT) ou au service (DA directe)
     */
    public function envoyerProposition(DemandeAppro $da, $dalrList, ?string $observation, ?string $refsString): array
    {
        $dals = $da->getDAL();
        $refs = $this->daPropositionLigneService->parserRefs($refsString);

        $notification = $this->traiterProposition(
            $da,
            $dalrList,
            $observation,
            $refs,
            $this->estDirect($da) ? "La proposition a été soumis au service" : "La proposition a été soumis à l'atelier",
            true
        );

        $this->daPropositionLigneService->modifierChoixEtLigneDal($refs, $dals);

        $this->daAfficherService->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro());

        $this->emailDaService->envoyerMailPropositionDa($da);

        return $notification;
    }

    /**
     * Valider les articles par l'atelier (DA avec DIT) ou le créateur (DA directe)
     */
    public function validerParDemandeur(DemandeAppro $da, $dalrList, ?string $observation, ?string $refsString): array
    {
        $dals = $da->getDAL();
        $refs = $this->daPropositionLigneService->parserRefs($refsString);

        /** MODIFICATION de choix de reference */
        $notification = $this->traiterProposition(
            $da,
            $dalrList,
            $observation,
            $refs,
            "Le choix de la proposition a été changé avec succès"
        );

        $this->daPropositionLigneService->modifierChoixEtLigneDal($refs, $dals);

        $this->validerEtNotifier($da);

        return $notification;
    }

    /**
     * Valider les articles par l'appro
     */
    public function validerParAppro(DemandeAppro $da, $dalrList, ?string $observation, ?string $refsString): array
    {
        $dals = $da->getDAL();
        $refs = $this->daPropositionLigneService->parserRefs($refsString);

        $notification = $this->traiterProposition(
            $da,
            $dalrList,
            $observation,
            $refs,
            "Les articles ont été validés avec succès",
            true,
            StatutDaConstant::STATUT_VALIDE
        );

        $this->daPropositionLigneService->modifierChoixEtLigneDal($refs, $dals);

        $this->validerEtNotifier($da);

        return $notification;
    }

    /**
     * Envoyer une observation depuis le bouton « observation » du formulaire de proposition
     */
    public function envoyerObservation(DemandeAppro $da, ?string $observation, $statutChange, bool $estAppro): array
    {
        if ($observation === null) {
            return $this->notification('danger', 'Echec: Pas d\'observation.');
        }

        $numDa = $da->getNumeroDemandeAppro();

        $this->daObservationService->insertionObservation($numDa, $observation);
        if ($statutChange) {
            $this->modifierStatut($da, StatutDaConstant::STATUT_SOUMIS_APPRO);

            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé
        }

        $this->emailDaService->envoyerMailObservationDa($da, $observation, $estAppro);

        return $this->notification('success', 'Votre observation a été enregistré avec succès.');
    }

    /**
     * Envoyer une observation (avec pièces jointes) depuis le formulaire d'observation
     */
    public function envoyerObservationAvecPj(DemandeAppro $da, DaObservation $daObservation, bool $estAppro): array
    {
        $numDa = $da->getNumeroDemandeAppro();

        $this->daObservationService->insertionObservation($numDa, $daObservation->getObservation(), $daObservation->getFileNames());

        if ($estAppro && $daObservation->getStatutChange()) {
            $this->modifierStatut($da, StatutDaConstant::STATUT_AUTORISER_EMETTEUR);

            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé
        }

        $this->emailDaService->envoyerMailObservationDa($da, $daObservation->getObservation(), $estAppro);

        return $this->notification('success', 'Votre observation a été enregistré avec succès.');
    }

    private function estDirect(DemandeAppro $da): bool
    {
        return $da->getDaTypeId() === DemandeAppro::TYPE_DA_DIRECT;
    }

    private function traiterProposition(DemandeAppro $da, $dalrList, ?string $observation, array $refs, string $messageSuccess, bool $doSaveDb = false, string $statut = StatutDaConstant::STATUT_SOUMIS_ATE): array
    {
        $numDa = $da->getNumeroDemandeAppro();

        if ($dalrList->isEmpty() && empty($refs)) {
            return $this->notification('danger', "Aucune modification n'a été effectuée");
        }

        if ($doSaveDb) {
            $this->daPropositionLigneService->enregistrerLignes($da->getDAL(), $dalrList, $statut, !$this->estDirect($da));
        }

        if ($observation !== null) {
            $this->daObservationService->insertionObservation($numDa, $observation);
        }

        $this->modifierStatut($da, $statut);

        return $this->notification('success', $messageSuccess);
    }

    /**
     * Statut des lignes (dernière version) puis statut de la DA
     */
    private function modifierStatut(DemandeAppro $da, string $statut): void
    {
        $this->daPropositionLigneService->modifierStatutLignes($da->getNumeroDemandeAppro(), $statut);

        $da->setStatutDal($statut);
        $this->em->persist($da);
        $this->em->flush();
    }

    /**
     * Validation de la proposition : DA validée, Excel/PDF, DaAfficher, (DocuWare pour la DA directe) et mail
     */
    private function validerEtNotifier(DemandeAppro $da): void
    {
        $numDa = $da->getNumeroDemandeAppro();
        $numeroVersionMax = $this->em->getRepository(DemandeApproL::class)->getNumeroVersionMax($numDa);

        $da = $this->daValidationService->validerDemandeApproAvecLignes($numDa, $numeroVersionMax);

        /** CREATION EXCEL */
        $nomEtChemin = $this->estDirect($da)
            ? $this->daValidationService->exporterDaDirectEnExcelEtPdf($numDa, $numeroVersionMax)
            : $this->daValidationService->exporterDaAvecDitEnExcelEtPdf($numDa, $numeroVersionMax);

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($nomEtChemin['fileName']);
        $this->em->flush();

        if ($this->estDirect($da)) {
            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa, true, StatutDaConstant::STATUT_DW_A_VALIDE);

            $this->daValidationService->soumettreDaDirecteADocuware($da);
        } else {
            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa, true);
        }

        $this->emailDaService->envoyerMailValidationDa($da, $nomEtChemin);
    }

    private function notification(string $type, string $message): array
    {
        return [
            'type'    => $type,
            'message' => $message,
        ];
    }
}
