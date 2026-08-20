<?php

namespace App\Dto\Dit;

use App\Entity\dit\DemandeIntervention;

class DitDetailDto
{
    private const MOTS_A_SUPPRIMER_SECTION = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];

    public ?string $numDit = null;
    public ?string $dateDemande = null;
    public ?string $statutDit = null;
    public ?string $objetDemande = null;
    public ?string $detailDemande = null;
    public ?string $typeDocument = null;
    public ?string $avisRecouvrement = null;
    public ?string $demandeDevis = null;
    public ?string $categorieDemandeLibelle = null;
    public ?string $livraisonPartiel = null;
    public ?string $internetExterne = null;
    public ?string $numeroOr = null;
    public ?string $statutOr = null;
    public ?string $sectionAffectee = null;
    public ?string $agenceServiceDebiteur = null;
    public ?string $agenceServiceEmetteur = null;
    public ?string $nomClient = null;
    public ?string $numeroTel = null;
    public ?string $clientSousContrat = null;
    public ?string $niveauUrgence = null;
    public ?string $datePrevueTravauxFormatee = null;
    public ?string $chiffreAffaireFormate = null;
    public ?string $chargeEntretientFormate = null;
    public ?string $chargeLocativeFormate = null;
    public ?string $resultatExploitationFormate = null;
    public ?string $coutAcquisitionFormate = null;
    public ?string $amortissementFormate = null;
    public ?string $valeurNetComptableFormatee = null;
    public ?string $idMateriel = null;
    public ?string $numSerie = null;
    public ?string $numParc = null;
    public ?string $constructeur = null;
    public ?string $designation = null;
    public $km = null;
    public ?string $modele = null;
    public ?string $casier = null;
    public ?string $heure = null;
    public ?string $typeReparation = null;
    public ?string $reparationRealise = null;
    public $pieceJoint01 = null;
    public $pieceJoint02 = null;
    public $pieceJoint03 = null;
    /** @var DitCommandeDto[] */
    public array $commandes = [];

    public static function fromEntity(DemandeIntervention $dit, array $commandes): self
    {
        $dto = new self();

        $dto->numDit                      = $dit->getNumeroDemandeIntervention();
        $dto->dateDemande                 = $dit->getDateDemande() ? $dit->getDateDemande()->format('d/m/Y') : null;
        $dto->statutDit                   = $dit->getIdStatutDemande() ? $dit->getIdStatutDemande()->getDescription() : "-";
        $dto->objetDemande                = $dit->getObjetDemande();
        $dto->detailDemande               = $dit->getDetailDemande();
        $dto->typeDocument                = $dit->getTypeDocument();
        $dto->avisRecouvrement            = $dit->getAvisRecouvrement();
        $dto->demandeDevis                = $dit->getDemandeDevis();
        $dto->categorieDemandeLibelle     = $dit->getCategorieDemande() ? $dit->getCategorieDemande()->getLibelleCategorieAteApp() : "-";
        $dto->livraisonPartiel            = $dit->getLivraisonPartiel();
        $dto->internetExterne             = $dit->getInternetExterne();
        $dto->numeroOr                    = $dit->getNumeroOR();
        $dto->statutOr                    = $dit->getStatutOr();
        $dto->sectionAffectee             = self::supprimerMots($dit->getSectionAffectee(), self::MOTS_A_SUPPRIMER_SECTION);
        $dto->agenceServiceDebiteur       = $dit->getAgenceServiceDebiteur();
        $dto->agenceServiceEmetteur       = $dit->getAgenceServiceEmetteur();
        $dto->nomClient                   = $dit->getNomClient();
        $dto->numeroTel                   = $dit->getNumeroTel();
        $dto->clientSousContrat           = $dit->getClientSousContrat();
        $dto->niveauUrgence               = $dit->getIdNiveauUrgence() ? $dit->getIdNiveauUrgence()->getDescription() : "-";
        $dto->datePrevueTravauxFormatee   = $dit->getDatePrevueTravaux() ? $dit->getDatePrevueTravaux()->format('d/m/Y') : null;
        $dto->chiffreAffaireFormate       = self::formaterMontant($dit->getChiffreAffaire());
        $dto->chargeEntretientFormate     = self::formaterMontant($dit->getChargeEntretient());
        $dto->chargeLocativeFormate       = self::formaterMontant($dit->getChargeLocative());
        $dto->resultatExploitationFormate = self::formaterMontant($dit->getResultatExploitation());
        $dto->coutAcquisitionFormate      = self::formaterMontant($dit->getCoutAcquisition());
        $dto->amortissementFormate        = self::formaterMontant($dit->getAmortissement());
        $dto->valeurNetComptableFormatee  = self::formaterMontant($dit->getValeurNetComptable());
        $dto->idMateriel                  = $dit->getIdMateriel();
        $dto->numSerie                    = $dit->getNumSerie();
        $dto->numParc                     = $dit->getNumParc();
        $dto->constructeur                = $dit->getConstructeur();
        $dto->designation                 = $dit->getDesignation();
        $dto->km                          = $dit->getKm();
        $dto->modele                      = $dit->getModele();
        $dto->casier                      = $dit->getCasier();
        $dto->heure                       = $dit->getHeure();
        $dto->typeReparation              = $dit->getTypeReparation();
        $dto->reparationRealise           = $dit->getReparationRealise();
        $dto->pieceJoint01                = $dit->getPieceJoint01();
        $dto->pieceJoint02                = $dit->getPieceJoint02();
        $dto->pieceJoint03                = $dit->getPieceJoint03();
        $dto->commandes                   = array_map([DitCommandeDto::class, 'fromRow'], $commandes);

        return $dto;
    }

    private static function formaterMontant(?float $montant): string
    {
        return number_format($montant ?? 0, 2, ',', '.');
    }

    private static function supprimerMots(?string $texte, array $mots): string
    {
        if ($texte === null) {
            return '';
        }
        foreach ($mots as $mot) {
            $texte = preg_replace('/\b' . preg_quote($mot, '/') . '[sS]?\b/u', '', $texte);
        }

        return trim(preg_replace('/\s+/', ' ', $texte));
    }
}
