<?php

namespace App\Dto\Dit;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\Societte;

class DemandeInterventionDto
{
    public ?string $objetDemande = null;
    public ?string $detailDemande = null;
    public ?string $typeDocument = null;
    public ?string $categorieDemande = null;
    public ?string $livraisonPartiel = null;
    public ?string $demandeDevis = null;
    public ?string $avisRecouvrement = null;
    public ?string $agenceEmetteur = null;
    public ?string $serviceEmetteur = null;
    public ?Agence $agence = null;
    public ?Service $service = null;
    public ?WorNiveauUrgence $idNiveauUrgence = null;
    public ?DateTime $datePrevueTravaux = null;
    public ?string $typeReparation = null;
    public ?string $reparationRealise = null;
    public ?string $internetExterne = null;
    // INFO CLIENT
    public ?string $numeroClient = null;
    public ?string $nomClient = null;
    public ?string $numeroTel = null;
    public ?string $mailClient = null;
    public ?string $clientSousContrat = null;
    // INFO MATERIEL
    public ?string $idMateriel = null;
    public ?string $numParc = null;
    public ?string $numSerie = null;
    // PIECE JOINTE
    public $pieceJoint01 = null;
    public $pieceJoint02 = null;
    public $pieceJoint03 = null;

    public ?StatutDemande $idStatutDemande = null;
    public ?string $numeroDemandeIntervention = null;
    public ?string $mailDemandeur = null;
    public ?DateTime $dateDemande = null;
    public ?string $heureDemande = null;
    public ?string $utilisateurDemandeur = null;

    public bool $estDitAvoir = false;
    public bool $estDitRefacturation = false;

    public bool $estAtePolTana = false;

    public ?Societte $societe = null;


    // Cette méthode peut être utilisée pour hydrater le DTO depuis l'entité/formulaire
    public static function createFromEntity($dits): self
    {
        $dto = new self();
        $dto->objetDemande = $dits->getObjetDemande();
        $dto->detailDemande = $dits->getDetailDemande();
        $dto->typeDocument = $dits->getTypeDocument();
        $dto->categorieDemande = $dits->getCategorieDemande();
        $dto->livraisonPartiel = $dits->getLivraisonPartiel();
        $dto->demandeDevis = $dits->getDemandeDevis();
        $dto->avisRecouvrement = $dits->getAvisRecouvrement();
        $dto->agenceEmetteur = $dits->getAgenceEmetteur();
        $dto->serviceEmetteur = $dits->getServiceEmetteur();
        $dto->agence = $dits->getAgence();
        $dto->service = $dits->getService();
        $dto->idNiveauUrgence = $dits->getIdNiveauUrgence();
        $dto->datePrevueTravaux = $dits->getDatePrevueTravaux();
        $dto->typeReparation = $dits->getTypeReparation();
        $dto->reparationRealise = $dits->getReparationRealise();
        $dto->internetExterne = $dits->getInternetExterne();
        $dto->numeroClient = $dits->getNumeroClient();
        $dto->nomClient = $dits->getNomClient();
        $dto->numeroTel = $dits->getNumeroTel();
        $dto->clientSousContrat = $dits->getClientSousContrat();
        $dto->idMateriel = $dits->getIdMateriel();
        $dto->numParc = $dits->getNumParc();
        $dto->numSerie = $dits->getNumSerie();
        $dto->pieceJoint01 = $dits->getPieceJoint01();
        $dto->pieceJoint02 = $dits->getPieceJoint02();
        $dto->pieceJoint03 = $dits->getPieceJoint03();
        $dto->idStatutDemande = $dits->getIdStatutDemande();
        $dto->numeroDemandeIntervention = $dits->getNumeroDemandeIntervention();
        $dto->mailDemandeur = $dits->getMailDemandeur();
        $dto->dateDemande = $dits->getDateDemande();
        $dto->heureDemande = $dits->getHeureDemande();
        $dto->utilisateurDemandeur = $dits->getUtilisateurDemandeur();
        $dto->estDitAvoir = $dits->getEstDitAvoir();
        $dto->estDitRefacturation = $dits->getEstDitRefacturation();
        $dto->mailClient = $dits->getMailClient();
        $dto->estAtePolTana = $dits->getEstAtePolTana();

        return $dto;
    }
}
