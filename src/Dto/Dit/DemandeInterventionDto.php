<?php

namespace App\Dto\Dit;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\WorNiveauUrgence;

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
    public ?string $codeSociete = null;
    public bool $estDitAvoir = false;
    public bool $estDitRefacturation = false;
    public bool $estAtePolTana = false;

    // Cette méthode peut être utilisée pour hydrater le DTO depuis l'entité/formulaire
    public static function createFromEntity(DemandeIntervention $dit): self
    {
        $dto = new self();
        $dto->objetDemande = $dit->getObjetDemande();
        $dto->detailDemande = $dit->getDetailDemande();
        $dto->typeDocument = $dit->getTypeDocument();
        $dto->categorieDemande = $dit->getCategorieDemande();
        $dto->livraisonPartiel = $dit->getLivraisonPartiel();
        $dto->demandeDevis = $dit->getDemandeDevis();
        $dto->avisRecouvrement = $dit->getAvisRecouvrement();
        $dto->agenceEmetteur = $dit->getAgenceEmetteur();
        $dto->serviceEmetteur = $dit->getServiceEmetteur();
        $dto->agence = $dit->getAgence();
        $dto->service = $dit->getService();
        $dto->idNiveauUrgence = $dit->getIdNiveauUrgence();
        $dto->datePrevueTravaux = $dit->getDatePrevueTravaux();
        $dto->typeReparation = $dit->getTypeReparation();
        $dto->reparationRealise = $dit->getReparationRealise();
        $dto->internetExterne = $dit->getInternetExterne();
        $dto->numeroClient = $dit->getNumeroClient();
        $dto->nomClient = $dit->getNomClient();
        $dto->numeroTel = $dit->getNumeroTel();
        $dto->clientSousContrat = $dit->getClientSousContrat();
        $dto->idMateriel = $dit->getIdMateriel();
        $dto->numParc = $dit->getNumParc();
        $dto->numSerie = $dit->getNumSerie();
        $dto->pieceJoint01 = $dit->getPieceJoint01();
        $dto->pieceJoint02 = $dit->getPieceJoint02();
        $dto->pieceJoint03 = $dit->getPieceJoint03();
        $dto->idStatutDemande = $dit->getIdStatutDemande();
        $dto->numeroDemandeIntervention = $dit->getNumeroDemandeIntervention();
        $dto->mailDemandeur = $dit->getMailDemandeur();
        $dto->dateDemande = $dit->getDateDemande();
        $dto->heureDemande = $dit->getHeureDemande();
        $dto->utilisateurDemandeur = $dit->getUtilisateurDemandeur();
        $dto->estDitAvoir = $dit->getEstDitAvoir();
        $dto->estDitRefacturation = $dit->getEstDitRefacturation();
        $dto->mailClient = $dit->getMailClient();
        $dto->estAtePolTana = $dit->getEstAtePolTana();
        $dto->codeSociete = $dit->getCodeSociete();

        return $dto;
    }
}
