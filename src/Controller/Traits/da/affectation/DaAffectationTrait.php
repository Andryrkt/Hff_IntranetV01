<?php

namespace App\Controller\Traits\da\affectation;

use DateTime;
use App\Entity\da\DemandeAppro;
use App\Constants\da\StatutDaConstant;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Model\da\DaReapproModel;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DaSoumisAValidation;
use App\Service\autres\VersionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\da\DemandeApproParentLine;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Repository\da\DaObservationRepository;
use Doctrine\Common\Collections\Collection;
use App\Repository\da\DemandeApproParentRepository;
use App\Repository\da\DaSoumisAValidationRepository;
use App\Service\genererPdf\da\GenererPdfDaReappro;
use App\Traits\DaConsumtionHistoryTrait;

trait DaAffectationTrait
{
    use DaAfficherTrait;
    use DaConsumtionHistoryTrait;
    private ?array $oldObservations = null;

    //=====================================================================================
    private EntityManagerInterface $em;
    private DaObservationRepository $daObservationRepository;
    private DemandeApproParentRepository $demandeApproParentRepository;
    private DaSoumisAValidationRepository $daSoumisAValidationRepository;
    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaAffectationTrait(): void
    {
        $this->initDaTrait();
        $this->em = $this->getEntityManager();
        $this->demandeApproParentRepository  = $this->em->getRepository(DemandeApproParent::class);
        $this->daSoumisAValidationRepository = $this->em->getRepository(DaSoumisAValidation::class);
        $this->daObservationRepository       = $this->em->getRepository(DaObservation::class);
    }
    //=====================================================================================

    /**
     * Traite les lignes d'une demande parent
     *
     * @param Collection         $daParentLines  Collection des lignes de la demande parent
     * @param DemandeApproParent $daParent       Objet de la demande parent
     * @param int                $daType         Type de la demande
     */
    private function traitementDaParentLines(Collection $daParentLines, DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = $this->createDemandeAppro($daParent, $daType);
        $numeroDemandeAppro = $demandeAppro->getNumeroDemandeAppro();

        $numLigne = 0;

        $linesToDelete = []; // lignes à supprimer pour les lignes de DA parent dans da_afficher

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
            $linesToDelete[] = $daParentLine->getNumeroLigne();
        }
        $this->em->persist($demandeAppro);
        $this->em->flush();

        $this->handleOldObservation($numeroDemandeAppro, $daParent->getNumeroDemandeAppro()); // copier les observations de la DA parent

        if ($daParent->getObservation()) $this->insertionObservation($numeroDemandeAppro, $daParent->getObservation()); // insertion d'observation du formulaire dans le nouveau DA

        $validationDA = $daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL;
        $statutDW = $validationDA ? StatutDaConstant::STATUT_DW_A_VALIDE : '';

        // Supprimer les lignes de DA Parent dans la table da_afficher
        $this->daAfficherRepository->markAsDeletedByNumeroLigne($daParent->getNumeroDemandeAppro(), $linesToDelete, '__Subdivision-DA__', true);

        // Ajouter les nouveaux données dans la table da_afficher
        $this->ajouterDansTableAffichageParNumDa($numeroDemandeAppro, $validationDA, $statutDW, $daParent->getDateCreation());

        if ($validationDA) {
            // création de PDF
            $genererPdfReappro = new GenererPdfDaReappro();
            $dateRange = $this->getLast13MonthsDateRange();
            $monthsList = $this->getMonthsList($dateRange['start'], $dateRange['end']);
            $dataHistoriqueConsommation = $this->getHistoriqueConsommation($demandeAppro, $dateRange, $monthsList);
            $observations = $this->daObservationRepository->findBy(
                ['numDa' => $numeroDemandeAppro],
                ['dateCreation' => 'ASC']
            );
            $genererPdfReappro->genererPdfBonAchatValide($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);

            // Dépôt du document dans DocuWare
            $genererPdfReappro->copyToDWDaAValiderReapproPonctuel($numeroDemandeAppro, "");

            // Enregistrement dans la table de Soumission
            $this->ajouterDansDaSoumisAValidation($numeroDemandeAppro, $demandeAppro->getDemandeur());
        }
    }

    /**
     * Crée une DA à partir d'une DA parent et du type de DA
     *
     * @param DemandeApproParent $daParent Objet de la demande parent
     * @param int                $daType   Type de la demande
     *
     * @return DemandeAppro
     */
    private function createDemandeAppro(DemandeApproParent $daParent, int $daType)
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
                ->setValidateur($this->getUser())
                ->setValidePar($this->getUser()->getNomUtilisateur())
            ;
        }
        return $demandeAppro;
    }

    /**
     * Ajoute les données d'une Demande de Réappro dans la table `DaSoumisAValidation`
     *
     * @param string $numeroDemandeAppro  Numéro de la demande de réappro à traiter
     * @param string $demandeur           Demandeur de la demande de réappro
     */
    private function ajouterDansDaSoumisAValidation(string $numeroDemandeAppro, string $demandeur): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($numeroDemandeAppro);
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($numeroDemandeAppro)
            ->setNumeroVersion($numeroVersion)
            ->setStatut(StatutDaConstant::STATUT_DW_A_VALIDE)
            ->setUtilisateur($demandeur)
        ;

        $this->em->persist($daSoumisAValidation);
        $this->em->flush();
    }

    private function handleOldObservation(string $numDa, string $numDaParent): void
    {
        $observations = $this->getOldObservations($numDaParent);

        if (empty($observations)) return;

        /** @var DaObservation $observation */
        foreach ($observations as $observation) {
            $newObservation = clone $observation;
            $newObservation->setNumDa($numDa);
            $this->em->persist($newObservation);
        }

        $this->em->flush();
    }

    private function getOldObservations(string $numeroDemandeAppro): array
    {
        if ($this->oldObservations !== null) return $this->oldObservations;

        $this->oldObservations = $this->daObservationRepository->findBy(
            ['numDa' => $numeroDemandeAppro],
            ['dateCreation' => 'ASC']
        );

        return $this->oldObservations;
    }

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
