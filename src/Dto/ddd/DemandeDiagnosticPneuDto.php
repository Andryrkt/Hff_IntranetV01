<?php

namespace App\Dto\ddd;

use DateTime;
use App\Entity\ddd\Chantier;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Entity\dit\DemandeIntervention;
use App\Service\Admin\UrlIdCipher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DemandeDiagnosticPneuDto
{
    public ?int $id = null;

    public ?string $numeroDemande = null;

    public ?Chantier $chantier = null;
    public ?int $idChantier = null;
    public ?string $codeChantier = null;
    public ?string $nomChantier = null;

    public ?int $idMateriel = null;
    public ?string $numeroParcMateriel = null;
    public ?string $marqueMateriel = null;
    public ?string $typeMateriel = null;
    public ?string $designationMateriel = null;

    public ?DateTime $dateDepartChantier = null;

    public ?string $livraison = null;

    public ?int $nbPneuSurMachine = null;
    public ?int $nbPneuSecours = null;
    public ?int $nbPneuADiagnostiquer = null;

    public ?string $observation = null;

    /**
     * @var string[]
     */
    public array $motifs = [];

    public ?string $demandeur = null;

    public ?DateTime $dateCreation = null;

    public ?string $statut = null;

    public ?string $numeroDit = null;
    public ?string $numeroOr = null;
    public ?array $piecesJointes = [];

    public ?string $urlDetailDit = null;

    // Méthode de fabrique pour créer le DTO à partir des entités
    public static function fromEntities(DemandeDiagnosticPneu $demande, ?DemandeIntervention $intervention = null, ?UrlGeneratorInterface $urlGenerator = null, ?UrlIdCipher $urlIdCipher = null): self
    {
        $dto = new self();
        // Remplir les données de DemandeDiagnosticPneu
        $dto->id = $demande->getId();
        $dto->numeroDemande = $demande->getNumeroDemande();

        // Chantier
        $chantier = $demande->getChantier();
        if ($chantier) {
            $dto->chantier = $chantier;
            $dto->idChantier = $chantier->getId();
            // Adaptez les noms de méthodes selon votre entité Chantier
            $dto->codeChantier = $chantier->getCodeChantier() ?? "-";
            $dto->nomChantier = $chantier->getNomChantier() ?? "-";
        }

        // Matériel (les champs sont directement dans l'entité)
        $dto->idMateriel = $demande->getIdMateriel();
        $dto->numeroParcMateriel = $demande->getNumeroParcMateriel();
        $dto->marqueMateriel = $demande->getMarqueMateriel();
        $dto->typeMateriel = $demande->getTypeMateriel();
        $dto->designationMateriel = $demande->getDesignationMateriel();

        $dto->dateDepartChantier = $demande->getDateDepartChantier();
        $dto->livraison = $demande->getLivraison();
        $dto->nbPneuSurMachine = $demande->getNbPneuSurMachine();
        $dto->nbPneuSecours = $demande->getNbPneuSecours();
        $dto->nbPneuADiagnostiquer = $demande->getNbPneuADiagnostiquer();
        $dto->observation = $demande->getObservation();
        $dto->motifs = $demande->getMotifs() ?? [];
        $dto->demandeur = $demande->getDemandeur();
        $dto->dateCreation = $demande->getDateCreation();
        $dto->statut = $demande->getStatut();

        $dto->piecesJointes = $demande->getPiecesJointes() ?? [];

        // Remplir les données de DemandeIntervention si disponible
        if ($intervention) {
            $dto->numeroDit = $demande->getNumeroDit();
            $dto->numeroOr = $demande->getNumeroOr();
            $dto->urlDetailDit = $urlGenerator->generate('dit_validationDit', ['token' => $urlIdCipher->encrypt($intervention->getId(), "DIT")]);
        }

        return $dto;
    }
}
