<?php

namespace App\Entity;

use App\Traits\DateTrait;
use App\Entity\SousTypeDocument;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CatgRepository;
use Doctrine\Common\Collections\ArrayCollection;


  /**
 *   @ORM\Table(name="Catg")
 * @ORM\Entity(repositoryClass=CatgRepository::class)
 * @ORM\HasLifecycleCallbacks
 */

class Catg
{
    use DateTrait;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private string $description;


    /**
     * @ORM\OneToMany(targetEntity="SousTypeDocument::class", mappedBy="catg")
     */
    private  ArrayCollection $sousTypeDocument;

    

    public function __construct()
    {

        $this->sousTypeDocument = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getSousTypeDocument(): ArrayCollection
    {
        return $this->sousTypeDocument;
    }

    public function addSousTypeDocument(SousTypeDocument $sousTypeDocument): self
    {
        if (!$this->sousTypeDocument->contains($sousTypeDocument)) {
            $this->sousTypeDocument[] = $sousTypeDocument;
            $sousTypeDocument->setCatg($this);
        }

        return $this;
    }

    public function removeSousTypeDocument(SousTypeDocument $sousTypeDocument): self
    {
        if ($this->sousTypeDocument->contains($sousTypeDocument)) {
            $this->sousTypeDocument->removeElement($sousTypeDocument);
            if ($sousTypeDocument->getCatg() === $this) {
                $sousTypeDocument->setCatg(null);
            }
        }

        return $this;
    }
    public function setsousTypeDocument($demandeInterventions)
    {
        $this->sousTypeDocument = $demandeInterventions;

        return $this;
    }
}