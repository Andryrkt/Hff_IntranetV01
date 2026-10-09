<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use App\Service\UserData\UserDataService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Affectation d'une DA parent (Achat) : subdivision en DA directes / réappro ponctuel.
 */
class DaAffectationService
{
    private EntityManagerInterface $em;
    private UserDataService $userDataService;
    private DaService $daService;
    private DaAfficherService $daAfficherService;
    private DaSoumissionValidationService $daSoumissionValidationService;

    public function __construct(
        EntityManagerInterface $em,
        UserDataService $userDataService,
        DaService $daService,
        DaAfficherService $daAfficherService,
        DaSoumissionValidationService $daSoumissionValidationService
    ) {
        $this->em = $em;
        $this->userDataService = $userDataService;
        $this->daService = $daService;
        $this->daAfficherService = $daAfficherService;
        $this->daSoumissionValidationService = $daSoumissionValidationService;
    }

    /**
     * Traite les lignes d'une demande parent
     *
     * @param Collection         $daParentLines  Collection des lignes de la demande parent
     * @param DemandeApproParent $daParent       Objet de la demande parent
     * @param int                $daType         Type de la demande
     */
    public function traitementDaParentLines(Collection $daParentLines, DemandeApproParent $daParent, int $daType): void
    {
        $demandeAppro = $this->createDemandeAppro($daParent, $daType);
        $numeroDemandeAppro = $demandeAppro->getNumeroDemandeAppro();

        $numLigne = 0;

        $rejectedLines = [];
        $notRejectedLines = [];

        /** @var DemandeApproParentLine $daParentLine */
        foreach ($daParentLines as $daParentLine) {
            $demandeApproLine = new DemandeApproL();

            $demandeApproLine
                ->duplicateDaParentLine($daParentLine)
                ->setNumeroDemandeAppro($numeroDemandeAppro)
                ->setNumeroLigne(++$numLigne)
                ->setStatutDal($demandeAppro->getStatutDal())
                ->setEstValidee($demandeAppro->getEstValidee())
                ->setValidePar($demandeAppro->getValidePar())
            ;

            $this->handleOldFiles($numeroDemandeAppro, $daParent->getNumeroDemandeAppro(), $daParentLine->getFileNames());

            // ajouter dans la collection des DAL de la nouvelle DA
            $demandeAppro->addDAL($demandeApproLine);

            $this->em->persist($demandeApproLine);

            // ajout de ligne à supprimer pour les lignes de DA parent dans da_afficher
            if ($daParentLine->isDeleted()) $rejectedLines[] = $daParentLine->getNumeroLigne();
            else $notRejectedLines[] = $daParentLine->getNumeroLigne();
        }
        $this->em->persist($demandeAppro);
        $this->em->flush();

        $this->handleOldObservation($numeroDemandeAppro, $daParent->getNumeroDemandeAppro()); // copier les observations de la DA parent

        if ($daParent->getObservation()) $this->daService->insertionObservation($numeroDemandeAppro, $daParent->getObservation(), $this->userDataService->getUserName()); // observation du formulaire dans la nouvelle DA

        $validationDA = $daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL;
        $statutDW = $validationDA ? StatutDaConstant::STATUT_DW_A_VALIDE : '';

        // Supprimer les lignes de DA Parent dans la table da_afficher
        $daAfficherRepository = $this->em->getRepository(DaAfficher::class);
        $daAfficherRepository->markAsDeletedByNumeroLigne($daParent->getNumeroDemandeAppro(), $notRejectedLines, '__Subdivision-DA__', true);
        $daAfficherRepository->markAsDeletedByNumeroLigne($daParent->getNumeroDemandeAppro(), $rejectedLines, '__Rejected-DA__', true);

        // Ajouter les nouveaux données dans la table da_afficher
        $this->daAfficherService->ajouterDansTableAffichageParNumDa($numeroDemandeAppro, $validationDA, $statutDW, $daParent->getDateCreation());

        if ($validationDA) {
            // création de PDF
            $daConsumptionHistory = new DaConsumptionHistory();
            $dateRange = $daConsumptionHistory->getLast13MonthsDateRange();
            $monthsList = $daConsumptionHistory->getMonthsList($dateRange['start'], $dateRange['end']);
            $dataHistoriqueConsommation = $daConsumptionHistory->getHistoriqueConsommation($demandeAppro, $dateRange, $monthsList);
            $observations = $this->daService->getObservations($numeroDemandeAppro);

            $this->daSoumissionValidationService->creationPDFReappro($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);

            // Dépôt du document dans DocuWare
            $this->daSoumissionValidationService->copyPDFToDWReapproPonctuel($numeroDemandeAppro);

            // Enregistrement dans la table de Soumission
            $this->daSoumissionValidationService->ajouterDansDaSoumisAValidation($demandeAppro);
        }
    }

    /** Crée une DA à partir d'une DA parent et du type de DA */
    private function createDemandeAppro(DemandeApproParent $daParent, int $daType): DemandeAppro
    {
        $demandeAppro = new DemandeAppro();

        $prefix = [
            DemandeAppro::TYPE_DA_DIRECT           => 'DAPD',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'DAPP',
        ];

        $statut = [
            DemandeAppro::TYPE_DA_DIRECT           => StatutDaConstant::STATUT_SOUMIS_APPRO,
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => StatutDaConstant::STATUT_VALIDE,
        ];

        $numDa = str_replace('DAP', $prefix[$daType], $daParent->getNumeroDemandeAppro());

        $demandeAppro
            ->duplicateDaParent($daParent)
            ->setDaTypeId($daType)
            ->setNumeroDemandeAppro($numDa)
            ->setStatutDal($statut[$daType])
        ;

        if ($daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL) {
            $demandeAppro
                ->setEstValidee(true)
                ->setValidateur($this->userDataService->getUser())
                ->setValidePar($this->userDataService->getUser()->getNomUtilisateur())
            ;
        }
        return $demandeAppro;
    }

    /** Copie les observations de la DA parent vers la nouvelle DA */
    private function handleOldObservation(string $numDa, string $numDaParent): void
    {
        $observations = $this->daService->getObservations($numDaParent);

        if (empty($observations)) return;

        /** @var DaObservation $observation */
        foreach ($observations as $observation) {
            $newObservation = clone $observation;
            $newObservation->setNumDa($numDa);
            $this->em->persist($newObservation);
        }

        $this->em->flush();
    }

    /** Copie les fichiers de la DA parent vers le dossier de la nouvelle DA */
    private function handleOldFiles(string $numeroDemandeAppro, string $numeroDemandeApproParent, array $fileNames): void
    {
        if (empty($fileNames)) return;

        $baseFichier    = $_ENV['BASE_PATH_FICHIER'] . "/da/$numeroDemandeApproParent";
        $baseFichierNew = $_ENV['BASE_PATH_FICHIER'] . "/da/$numeroDemandeAppro";

        foreach ($fileNames as $fileName) {
            $cheminComplet    = $baseFichier . "/$fileName";
            $cheminCompletNew = $baseFichierNew . "/$fileName";

            if (file_exists($cheminComplet)) {
                if (!is_dir($baseFichierNew)) mkdir($baseFichierNew, 0777, true);
                if (!file_exists($cheminCompletNew)) copy($cheminComplet, $cheminCompletNew);
            }
        }
    }
}
