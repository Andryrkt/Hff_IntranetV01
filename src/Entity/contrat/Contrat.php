<?php

namespace App\Entity\contrat;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\contrat\ContratRepository")
 * @ORM\Table(name="contrat")
 */
class Contrat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $reference = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $objet = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $date_enregistrement = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $statut = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $agence = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $service = null;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private ?string $nom_partenaire = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $type_tiers = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $date_debut_contrat = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $date_fin_contrat = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $piece_jointe = null;

    // Champs non mappés pour le formulaire de recherche
    private ?string $referenceSearch = null;
    private ?\DateTimeInterface $date_enregistrement_debut = null;
    private ?\DateTimeInterface $date_enregistrement_fin = null;
    private ?string $agenceSearch = null;
    private ?string $serviceSearch = null;
    private ?string $nom_partenaireSearch = null;
    private ?string $type_tiersSearch = null;

    public function __construct()
    {
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(?string $objet): self
    {
        $this->objet = $objet;
        return $this;
    }

    public function getDateEnregistrement(): ?\DateTimeInterface
    {
        return $this->date_enregistrement;
    }

    public function setDateEnregistrement(?\DateTimeInterface $date_enregistrement): self
    {
        $this->date_enregistrement = $date_enregistrement;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getAgence(): ?string
    {
        return $this->agence;
    }

    public function setAgence(?string $agence): self
    {
        $this->agence = $agence;
        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getNomPartenaire(): ?string
    {
        return $this->nom_partenaire;
    }

    public function setNomPartenaire(?string $nom_partenaire): self
    {
        $this->nom_partenaire = $nom_partenaire;
        return $this;
    }

    public function getTypeTiers(): ?string
    {
        return $this->type_tiers;
    }

    public function setTypeTiers(?string $type_tiers): self
    {
        $this->type_tiers = $type_tiers;
        return $this;
    }

    public function getDateDebutContrat(): ?\DateTimeInterface
    {
        return $this->date_debut_contrat;
    }

    public function setDateDebutContrat(?\DateTimeInterface $date_debut_contrat): self
    {
        $this->date_debut_contrat = $date_debut_contrat;
        return $this;
    }

    public function getDateFinContrat(): ?\DateTimeInterface
    {
        return $this->date_fin_contrat;
    }

    public function setDateFinContrat(?\DateTimeInterface $date_fin_contrat): self
    {
        $this->date_fin_contrat = $date_fin_contrat;
        return $this;
    }

    public function getPieceJointe(): ?string
    {
        return $this->piece_jointe;
    }

    public function setPieceJointe(?string $piece_jointe): self
    {
        $this->piece_jointe = $piece_jointe;
        return $this;
    }

    // Getters and Setters pour les champs de recherche (non mappés)

    public function getReferenceSearch(): ?string
    {
        return $this->referenceSearch;
    }

    public function setReferenceSearch(?string $referenceSearch): self
    {
        $this->referenceSearch = $referenceSearch;
        return $this;
    }

    public function getDateEnregistrementDebut(): ?\DateTimeInterface
    {
        return $this->date_enregistrement_debut;
    }

    public function setDateEnregistrementDebut(?\DateTimeInterface $date_enregistrement_debut): self
    {
        $this->date_enregistrement_debut = $date_enregistrement_debut;
        return $this;
    }

    public function getDateEnregistrementFin(): ?\DateTimeInterface
    {
        return $this->date_enregistrement_fin;
    }

    public function setDateEnregistrementFin(?\DateTimeInterface $date_enregistrement_fin): self
    {
        $this->date_enregistrement_fin = $date_enregistrement_fin;
        return $this;
    }

    public function getAgenceSearch(): ?string
    {
        return $this->agenceSearch;
    }

    public function setAgenceSearch(?string $agenceSearch): self
    {
        $this->agenceSearch = $agenceSearch;
        return $this;
    }

    public function getServiceSearch(): ?string
    {
        return $this->serviceSearch;
    }

    public function setServiceSearch(?string $serviceSearch): self
    {
        $this->serviceSearch = $serviceSearch;
        return $this;
    }

    public function getNomPartenaireSearch(): ?string
    {
        return $this->nom_partenaireSearch;
    }

    public function setNomPartenaireSearch(?string $nom_partenaireSearch): self
    {
        $this->nom_partenaireSearch = $nom_partenaireSearch;
        return $this;
    }

    public function getTypeTiersSearch(): ?string
    {
        return $this->type_tiersSearch;
    }

    public function setTypeTiersSearch(?string $type_tiersSearch): self
    {
        $this->type_tiersSearch = $type_tiersSearch;
        return $this;
    }

    /**
     * Convertir l'objet en tableau pour la session
     */
    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'objet' => $this->objet,
            'date_enregistrement' => $this->date_enregistrement ? $this->date_enregistrement->format('Y-m-d') : null,
            'statut' => $this->statut,
            'agence' => $this->agence,
            'service' => $this->service,
            'nom_partenaire' => $this->nom_partenaire,
            'type_tiers' => $this->type_tiers,
            'date_debut_contrat' => $this->date_debut_contrat ? $this->date_debut_contrat->format('Y-m-d') : null,
            'date_fin_contrat' => $this->date_fin_contrat ? $this->date_fin_contrat->format('Y-m-d') : null,
        ];
    }
}
