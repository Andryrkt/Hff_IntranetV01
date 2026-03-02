<?php

namespace App\Entity\magasin\devis;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\magasin\devis\PointageRelanceRepository;

/**
 * @ORM\Entity(repositoryClass=PointageRelanceRepository::class)
 * @ORM\Table(name="pointage_relance")
 * @ORM\HasLifecycleCallbacks
 */
class PointageRelance
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=11, name="numero_devis", nullable=false)
     */
    private ?string $numeroDevis = null;

    /**
     * @ORM\Column(type="integer", name="numero_version", nullable=false)
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="datetime", name="date_de_relance", nullable=true)
     */
    private ?\DateTimeInterface $dateDeRelance = null;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur", nullable=false)
     */
    private ?string $utilisateur = null;

    /**
     * @ORM\Column(type="string", length=5, name="societe", nullable=true)
     */
    private ?string $societe = null;

    /**
     * @ORM\Column(type="string", length=2, name="agence", nullable=true)
     */
    private ?string $agence = null;

    /**
     * @ORM\Column(type="integer", name="numero_relance", nullable=true)
     */
    private ?int $numeroRelance = null;

    /**
     * @ORM\Column(type="boolean", name="stop_progression_niveau", options={"default": false}, nullable=true)
     */
    private $stopProgressionNiveau;

    /**
     * @ORM\Column(type="datetime", name="date_stop_niveau", nullable=true)
     */
    private $dateStopNiveau;

    /**
     * @ORM\Column(type="string", length=255, name="motif_stop_niveau", nullable=true)
     */
    private $motifStopNiveau;

    /** =========================================
     * GETTERS & SETTERS
     *============================================*/
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDevis
     */
    public function getNumeroDevis(): ?string
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     */
    public function setNumeroDevis(?string $numeroDevis): self
    {
        $this->numeroDevis = $numeroDevis;

        return $this;
    }

    /**
     * Get the value of numeroVersion
     */
    public function getNumeroVersion(): ?int
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     */
    public function setNumeroVersion(?int $numeroVersion): self
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }

    /**
     * Get the value of dateDeRelance
     */
    public function getDateDeRelance(): ?\DateTimeInterface
    {
        return $this->dateDeRelance;
    }

    /**
     * Set the value of dateDeRelance
     */
    public function setDateDeRelance(?\DateTimeInterface $dateDeRelance): self
    {
        $this->dateDeRelance = $dateDeRelance;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur(): ?string
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     */
    public function setUtilisateur(?string $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of societe
     */
    public function getSociete(): ?string
    {
        return $this->societe;
    }

    /**
     * Set the value of societe
     */
    public function setSociete(?string $societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get the value of agence
     */
    public function getAgence(): ?string
    {
        return $this->agence;
    }

    /**
     * Set the value of agence
     */
    public function setAgence(?string $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * Get the value of numeroRelance
     */
    public function getNumeroRelance(): ?int
    {
        return $this->numeroRelance;
    }

    /**
     * Set the value of numeroRelance
     */
    public function setNumeroRelance(?int $numeroRelance): self
    {
        $this->numeroRelance = $numeroRelance;

        return $this;
    }

    /**
     * Get the value of stopProgressionNiveau
     */
    public function getStopProgressionNiveau()
    {
        return $this->stopProgressionNiveau;
    }

    /**
     * Set the value of stopProgressionNiveau
     */
    public function setStopProgressionNiveau($stopProgressionNiveau): self
    {
        $this->stopProgressionNiveau = $stopProgressionNiveau;

        return $this;
    }

    /**
     * Get the value of dateStopNiveau
     */
    public function getDateStopNiveau()
    {
        return $this->dateStopNiveau;
    }

    /**
     * Set the value of dateStopNiveau
     */
    public function setDateStopNiveau($dateStopNiveau): self
    {
        $this->dateStopNiveau = $dateStopNiveau;

        return $this;
    }

    /**
     * Get the value of motifStopNiveau
     */
    public function getMotifStopNiveau()
    {
        return $this->motifStopNiveau;
    }

    /**
     * Set the value of motifStopNiveau
     */
    public function setMotifStopNiveau($motifStopNiveau): self
    {
        $this->motifStopNiveau = $motifStopNiveau;

        return $this;
    }
}
