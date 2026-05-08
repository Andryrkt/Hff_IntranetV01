<?php

namespace App\Controller\Traits\da\validation;

use App\Constants\da\StatutDaConstant;
use App\Entity\da\DaObservation;
use App\Entity\da\DaSoumisAValidation;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DaSoumisAValidationRepository;
use App\Service\autres\VersionService;
use App\Service\genererPdf\da\GenererPdfDaReappro;
use DateTime;

trait DaValidationReapproTrait
{
    use DaValidationTrait;
    private GenererPdfDaReappro $genererPdfDaReappro;
    private DaObservationRepository $daObservationRepository;
    private DaSoumisAValidationRepository $daSoumisAValidationRepository;
    private string $cheminDeBase;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationReapproTrait(): void
    {
        $this->initDaTrait();
        $em = $this->getEntityManager();
        $this->genererPdfDaReappro = new GenererPdfDaReappro();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->daSoumisAValidationRepository = $em->getRepository(DaSoumisAValidation::class);
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
    }
    //==================================================================================================

    private function modifierStatut(DemandeAppro $demandeAppro, string $statut)
    {
        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal($statut);
            if ($statut === StatutDaConstant::STATUT_VALIDE) {
                $demandeApproL->setEstValidee(true);
                $demandeApproL->setValidePar($this->getUser()->getNomUtilisateur());
            }

            $this->getEntityManager()->persist($demandeApproL);
        }

        $demandeAppro->setStatutDal($statut);
        $this->getEntityManager()->persist($demandeAppro);
        $this->getEntityManager()->flush();
    }

    /** 
     * Création du PDF pour une DA Reapproe
     * 
     * @param DemandeAppro $demandeAppro
     * @return void
     */
    private function creationPDFReappro(DemandeAppro $demandeAppro, iterable $observations, array $monthsList, array $dataHistoriqueConsommation): void
    {
        $this->genererPdfDaReappro->genererPdfBonAchatValide($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);
    }

    /** 
     * Fonction pour mettre la DA à valider dans DW
     * 
     * @param string $numDa le numero de la demande appro pour laquelle on génère le PDF
     */
    private function copyPDFToDW(string $numDa)
    {
        $this->genererPdfDaReappro->copyToDWDaAValiderReapproMensuel($numDa, "");
    }

    /**
     * Ajoute les données d'une Demande de Réappro dans la table `DaSoumisAValidation`
     *
     * @param DemandeAppro $demandeAppro  Objet de la demande de réappro à traiter
     */
    private function ajouterDansDaSoumisAValidation(DemandeAppro $demandeAppro): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion($numeroVersion)
            ->setStatut(StatutDaConstant::STATUT_DW_A_VALIDE)
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->getEntityManager()->persist($daSoumisAValidation);
        $this->getEntityManager()->flush();
    }

    public function validerDemande(DemandeAppro $demandeAppro)
    {
        $this->modifierStatut($demandeAppro, StatutDaConstant::STATUT_VALIDE);
        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro(), true, StatutDaConstant::STATUT_DW_A_VALIDE);
    }

    public function refuserDemande(DemandeAppro $demandeAppro)
    {
        $this->modifierStatut($demandeAppro, StatutDaConstant::STATUT_REFUSE_APPRO);
        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
    }
}
