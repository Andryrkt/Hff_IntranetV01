<?php

namespace App\Entity\admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page de documentation utilisateur d'un module (ex: "ddp-magasin"),
 * affichée à tous et modifiable par les administrateurs (HFF-ADMIN).
 *
 * @ORM\Entity
 * @ORM\Table(name="documentation_utilisateur")
 */
class DocumentationUtilisateur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $titre = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $contenu = null;

    /**
     * Rubrique du sommaire (mêmes intitulés que le menu principal : Compta, Magasin...)
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $rubrique = null;

    /**
     * Ordre d'affichage dans la rubrique
     *
     * @ORM\Column(type="integer")
     */
    private int $ordre = 0;

    /**
     * Brouillon (false) visible des seuls administrateurs
     *
     * @ORM\Column(type="boolean")
     */
    private bool $publie = false;

    /**
     * @ORM\Column(type="datetime", name="date_modification", nullable=true)
     */
    private ?\DateTime $dateModification = null;

    /**
     * @ORM\Column(type="string", length=100, name="modifie_par", nullable=true)
     */
    private ?string $modifiePar = null;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = (string) $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getRubrique(): ?string
    {
        return $this->rubrique;
    }

    public function setRubrique(?string $rubrique): self
    {
        $this->rubrique = $rubrique;

        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = (int) $ordre;

        return $this;
    }

    public function isPublie(): bool
    {
        return $this->publie;
    }

    public function setPublie(bool $publie): self
    {
        $this->publie = $publie;

        return $this;
    }

    public function getDateModification(): ?\DateTime
    {
        return $this->dateModification;
    }

    public function getModifiePar(): ?string
    {
        return $this->modifiePar;
    }

    /**
     * Trace de la dernière modification
     */
    public function marquerModifie(string $utilisateur): self
    {
        $this->dateModification = new \DateTime();
        $this->modifiePar = $utilisateur;

        return $this;
    }
}
