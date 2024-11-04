<?php

namespace App\Entity\tik;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="TKI_Categorie")
 */
class TkiCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $idCategorie;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $description;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private \DateTimeInterface $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity=TKISousCategorie::class, mappedBy="categorie")
     */
    private Collection $sousCategories;

    public function __construct()
    {
        $this->sousCategories = new ArrayCollection();
    }

    // Getters and setters

    public function getIdCategorie(): int
    {
        return $this->idCategorie;
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

    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }
}
?>