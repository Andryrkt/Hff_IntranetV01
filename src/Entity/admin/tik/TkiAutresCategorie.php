<?php

namespace App\Entity\admin\tik;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\tik\TkiSousCategorie;

/**
 * @ORM\Entity
 * @ORM\Table(name="TKI_Autres_Categorie")
 */
class TkiAutresCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $idAutresCategorie;

    /**
     * @ORM\ManyToOne(targetEntity=TKISousCategorie::class, inversedBy="autresCategories")
     * @ORM\JoinColumn(nullable=false, name="ID_Sous_Categorie", referencedColumnName="idSousCategorie")
     */
    private TkiSousCategorie $sousCategorie;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $description;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private \DateTimeInterface $dateCreation;


    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    public function getIdAutresCategorie(): int
    {
        return $this->idAutresCategorie;
    }

    public function getSousCategorie(): TkiSousCategorie
    {
        return $this->sousCategorie;
    }

    public function setSousCategorie(TkiSousCategorie $sousCategorie): self
    {
        $this->sousCategorie = $sousCategorie;
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
}
