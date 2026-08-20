<?php

namespace App\Dto\Dit;

use App\Entity\dit\DemandeIntervention;
use App\Service\Admin\UrlIdCipher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DitListItemDto
{
    private const MOTS_A_SUPPRIMER_SECTION = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];

    private const QUATRE_STATUT_OR_CSS_CLASS = [
        'Tout livré'             => 'bg-success text-white',
        'Partiellement livré'    => 'bg-warning text-white',
        'Partiellement dispo'    => 'bg-info text-white',
        'Complet non livré'      => 'bg-primary text-white',
    ];

    public ?int $id = null;
    public ?string $numDit = null;
    public ?string $statutLibelle = null;
    public ?string $statutCssClass = null;
    public ?string $reparationRealise = null;
    public ?string $typeDocumentLibelle = null;
    public ?string $niveauUrgenceLibelle = null;
    public ?string $categorieDemandeLibelle = null;
    public ?string $numSerie = null;
    public ?string $numParc = null;
    public ?string $dateDemande = null;
    public ?string $internetExterne = null;
    public ?string $agenceServiceEmetteur = null;
    public ?string $agenceServiceDebiteur = null;
    public ?string $objetDemande = null;
    public ?string $sectionAffectee = null;
    public ?string $numeroDevisRattache = null;
    public ?string $statutDevis = null;
    public ?string $numeroOr = null;
    public ?string $statutOr = null;
    public ?string $quatreStatutOrCssClass = null;
    public ?string $montantTotalOrFormate = null;
    public ?string $dateSoumissionOrFormatee = null;
    public ?string $etatFacturation = null;
    public ?string $ri = null;
    public $nbrPj = null;
    public ?string $utilisateurDemandeur = null;
    public bool $estOrASoumi = false;
    public bool $estAnnulable = false;
    public string $urlFicheDit = '';

    public static function fromEntity(DemandeIntervention $item, UrlGeneratorInterface $urlGenerator, UrlIdCipher $urlIdCipher): self
    {
        $dto = new self();

        $dto->id                        = $item->getId();
        $dto->numDit                    = $item->getNumeroDemandeIntervention();
        $dto->statutLibelle             = trim($item->getIdStatutDemande()->getDescription());
        $dto->statutCssClass            = str_replace(' ', '_', strtolower($item->getIdStatutDemande()->getDescription()));
        $dto->reparationRealise         = $item->getReparationRealise();
        $dto->typeDocumentLibelle       = $item->getTypeDocument() ? $item->getTypeDocument()->getDescription() : null;
        $dto->niveauUrgenceLibelle      = $item->getIdNiveauUrgence() ? $item->getIdNiveauUrgence()->getDescription() : null;
        $dto->categorieDemandeLibelle   = $item->getCategorieDemande() ? $item->getCategorieDemande()->getLibelleCategorieAteApp() : null;
        $dto->numSerie                  = $item->getNumSerie();
        $dto->numParc                   = $item->getNumParc();
        $dto->dateDemande               = $item->getDateDemande() ? $item->getDateDemande()->format('d/m/Y') : null;
        $dto->internetExterne           = $item->getInternetExterne();
        $dto->agenceServiceEmetteur     = $item->getAgenceServiceEmetteur();
        $dto->agenceServiceDebiteur     = $item->getAgenceServiceDebiteur();
        $dto->objetDemande              = $item->getObjetDemande();
        $dto->sectionAffectee           = self::supprimerMots($item->getSectionAffectee(), self::MOTS_A_SUPPRIMER_SECTION);
        $dto->numeroDevisRattache       = $item->getNumeroDevisRattache();
        $dto->statutDevis               = $item->getStatutDevis();
        $dto->numeroOr                  = $item->getNumeroOR();
        $dto->statutOr                  = $item->getStatutOr();
        $dto->quatreStatutOrCssClass    = self::QUATRE_STATUT_OR_CSS_CLASS[$item->getQuatreStatutOr()] ?? '';
        $dto->montantTotalOrFormate     = $item->getMontantTotalOR() !== null ? number_format($item->getMontantTotalOR(), 2, ',', '.') : '';
        $dto->dateSoumissionOrFormatee  = $item->getDateSoumissionOR() !== null ? $item->getDateSoumissionOR()->format('d/m/Y') : '';
        $dto->etatFacturation           = $item->getEtatFacturation();
        $dto->ri                        = $item->getRi();
        $dto->nbrPj                     = $item->getNbrPj();
        $dto->utilisateurDemandeur      = $item->getUtilisateurDemandeur() ? strtoupper($item->getUtilisateurDemandeur()) : null;
        $dto->estOrASoumi               = (bool) $item->getEstOrASoumi();
        $dto->estAnnulable              = (bool) $item->getEstAnnulable();
        $dto->urlFicheDit               = $urlGenerator->generate('dit_fiche_detail', ['token' => $urlIdCipher->encrypt($item->getId(), "DIT")]);

        return $dto;
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
