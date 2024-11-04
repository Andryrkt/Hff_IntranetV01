<?php

namespace App\Entity\tik;

use App\Entity\tik\TkiCategorie;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="TKI_Sous_Categorie")
 */
class TkiSousCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $idSousCategorie;

    /**
     * @ORM\ManyToOne(targetEntity=TKICategorie::class, inversedBy="sousCategories")
     * @ORM\JoinColumn(nullable=false, name="ID_Categorie", referencedColumnName="idCategorie")
     */
    private TkiCategorie $categorie;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $description;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private \DateTimeInterface $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity=TKIAutresCategorie::class, mappedBy="sousCategorie")
     */
    private Collection $autresCategories;

    public function __construct()
    {
        $this->autresCategories = new ArrayCollection();
    }

    // Getters and setters

    public function getIdSousCategorie(): int
    {
        return $this->idSousCategorie;
    }

    public function getCategorie(): TkiCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(TkiCategorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getAutresCategories(): Collection
    {
        return $this->autresCategories;
    }
}
