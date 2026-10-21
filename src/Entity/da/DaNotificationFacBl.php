<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use App\Repository\da\DaNotificationFacBlRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notification enregistrée à chaque soumission de facture / BL (FacBl, BAP, DDPL)
 * avec le bilan de la mise à jour de est_facture_bl_soumis dans da_afficher.
 *
 * @ORM\Entity(repositoryClass=DaNotificationFacBlRepository::class)
 * @ORM\Table(name="da_notification_facture_bl")
 * @ORM\HasLifecycleCallbacks
 */
class DaNotificationFacBl
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12, name="numero_demande_appro", nullable=true)
     */
    private ?string $numeroDemandeAppro = null;

    /**
     * @ORM\Column(type="string", length=20, name="numero_cde", nullable=true)
     */
    private ?string $numeroCde = null;

    /**
     * @ORM\Column(type="string", length=20, name="numero_livraison", nullable=true)
     */
    private ?string $numeroLivraison = null;

    /**
     * @ORM\Column(type="string", length=20, name="numero_or", nullable=true)
     */
    private ?string $numeroOR = null;

    /**
     * @ORM\Column(type="integer", name="numero_version_da_afficher", nullable=true)
     */
    private ?int $numeroVersionDaAfficher = null;

    /**
     * @ORM\Column(type="string", length=20, name="type_soumission", nullable=true)
     */
    private ?string $typeSoumission = null;

    /**
     * @ORM\Column(type="float", name="montant", nullable=true)
     */
    private ?float $montant = null;

    /**
     * @ORM\Column(type="string", length=100, name="soumis_par", nullable=true)
     */
    private ?string $soumisPar = null;

    /**
     * @ORM\Column(type="integer", name="nb_articles_livres")
     */
    private int $nbArticlesLivres = 0;

    /**
     * @ORM\Column(type="integer", name="nb_lignes_maj")
     */
    private int $nbLignesMaj = 0;

    /**
     * @ORM\Column(type="text", name="articles_non_trouves", nullable=true)
     */
    private ?string $articlesNonTrouves = null;

    /**
     * @ORM\Column(type="boolean", name="est_anomalie")
     */
    private bool $estAnomalie = false;

    /**
     * @ORM\Column(type="string", length=1000, name="raison_anomalie", nullable=true)
     */
    private ?string $raisonAnomalie = null;

    /**
     * @ORM\Column(type="boolean", name="est_lu")
     */
    private bool $estLu = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroDemandeAppro(): ?string
    {
        return $this->numeroDemandeAppro;
    }

    public function setNumeroDemandeAppro(?string $numeroDemandeAppro): self
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;
        return $this;
    }

    public function getNumeroCde(): ?string
    {
        return $this->numeroCde;
    }

    public function setNumeroCde(?string $numeroCde): self
    {
        $this->numeroCde = $numeroCde;
        return $this;
    }

    public function getNumeroLivraison(): ?string
    {
        return $this->numeroLivraison;
    }

    public function setNumeroLivraison(?string $numeroLivraison): self
    {
        $this->numeroLivraison = $numeroLivraison;
        return $this;
    }

    public function getNumeroOR(): ?string
    {
        return $this->numeroOR;
    }

    public function setNumeroOR(?string $numeroOR): self
    {
        $this->numeroOR = $numeroOR;
        return $this;
    }

    public function getNumeroVersionDaAfficher(): ?int
    {
        return $this->numeroVersionDaAfficher;
    }

    public function setNumeroVersionDaAfficher(?int $numeroVersionDaAfficher): self
    {
        $this->numeroVersionDaAfficher = $numeroVersionDaAfficher;
        return $this;
    }

    public function getTypeSoumission(): ?string
    {
        return $this->typeSoumission;
    }

    public function setTypeSoumission(?string $typeSoumission): self
    {
        $this->typeSoumission = $typeSoumission;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getSoumisPar(): ?string
    {
        return $this->soumisPar;
    }

    public function setSoumisPar(?string $soumisPar): self
    {
        $this->soumisPar = $soumisPar;
        return $this;
    }

    public function getNbArticlesLivres(): int
    {
        return $this->nbArticlesLivres;
    }

    public function setNbArticlesLivres(int $nbArticlesLivres): self
    {
        $this->nbArticlesLivres = $nbArticlesLivres;
        return $this;
    }

    public function getNbLignesMaj(): int
    {
        return $this->nbLignesMaj;
    }

    public function setNbLignesMaj(int $nbLignesMaj): self
    {
        $this->nbLignesMaj = $nbLignesMaj;
        return $this;
    }

    public function getArticlesNonTrouves(): ?string
    {
        return $this->articlesNonTrouves;
    }

    public function setArticlesNonTrouves(?string $articlesNonTrouves): self
    {
        $this->articlesNonTrouves = $articlesNonTrouves;
        return $this;
    }

    public function getRaisonAnomalie(): ?string
    {
        return $this->raisonAnomalie;
    }

    public function setRaisonAnomalie(?string $raisonAnomalie): self
    {
        $this->raisonAnomalie = $raisonAnomalie !== null ? mb_substr($raisonAnomalie, 0, 1000) : null;
        return $this;
    }

    public function getEstAnomalie(): bool
    {
        return $this->estAnomalie;
    }

    public function setEstAnomalie(bool $estAnomalie): self
    {
        $this->estAnomalie = $estAnomalie;
        return $this;
    }

    public function getEstLu(): bool
    {
        return $this->estLu;
    }

    public function setEstLu(bool $estLu): self
    {
        $this->estLu = $estLu;
        return $this;
    }
}
