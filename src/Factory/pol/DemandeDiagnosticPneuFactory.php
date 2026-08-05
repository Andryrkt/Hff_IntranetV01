<?php

namespace App\Factory\pol;

use App\Dto\ddd\DemandeDiagnosticPneuDto;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Model\ddd\DemandeDiagnosticPneuModel;
use App\Service\historiqueOperation\HistoriqueOperationDDDService;
use Doctrine\ORM\EntityManagerInterface;

class DemandeDiagnosticPneuFactory
{
    private $entityManager;
    private DemandeDiagnosticPneuModel $demandeDiagnosticPneuModel;
    private $historiqueOperation;
    public function __construct(
        EntityManagerInterface $entityManager,
        DemandeDiagnosticPneuModel $demandeDiagnosticPneuModel,
        HistoriqueOperationDDDService $historiqueOperation
    ) {
        $this->entityManager = $entityManager;
        $this->demandeDiagnosticPneuModel = $demandeDiagnosticPneuModel;
        $this->historiqueOperation = $historiqueOperation;
    }
    public function createFromDto(DemandeDiagnosticPneuDto $dto): DemandeDiagnosticPneu
    {
        $demandeDiagnosticPneu = new DemandeDiagnosticPneu();

        // Informations demande
        $demandeDiagnosticPneu->setNumeroDemande($dto->numeroDemande);
        $demandeDiagnosticPneu->setDemandeur($dto->demandeur);
        $demandeDiagnosticPneu->setDateCreation($dto->dateCreation);
        $demandeDiagnosticPneu->setStatut($dto->statut);

        // Chantier
        $demandeDiagnosticPneu->setChantier($dto->idChantier);

        // Matériel
        $demandeDiagnosticPneu->setIdMateriel($dto->idMateriel);
        $demandeDiagnosticPneu->setNumeroParcMateriel($dto->numeroParcMateriel);
        $demandeDiagnosticPneu->setMarqueMateriel($dto->marqueMateriel);
        $demandeDiagnosticPneu->setTypeMateriel($dto->typeMateriel);
        $demandeDiagnosticPneu->setDesignationMateriel($dto->designationMateriel);

        // Déplacement chantier
        $demandeDiagnosticPneu->setDateDepartChantier($dto->dateDepartChantier);
        $demandeDiagnosticPneu->setLivraison($dto->livraison);

        // Pneus
        $demandeDiagnosticPneu->setNbPneuSurMachine($dto->nbPneuSurMachine);
        $demandeDiagnosticPneu->setNbPneuSecours($dto->nbPneuSecours);
        $demandeDiagnosticPneu->setNbPneuADiagnostiquer($dto->nbPneuADiagnostiquer);

        // Observation
        $demandeDiagnosticPneu->setObservation($dto->observation);

        // Motifs
        // $demandeDiagnosticPneu->setMotifs($dto->motifs);

        // Références DIT / OR
        $demandeDiagnosticPneu->setNumeroDit($dto->numeroDit);
        $demandeDiagnosticPneu->setNumeroOr($dto->numeroOr);

        return $demandeDiagnosticPneu;
    }
}
