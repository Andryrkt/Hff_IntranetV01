<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeMouvementRepository")
 * @ORM\Table(name="Type_Mouvement")
 * @ORM\HasLifecycleCallbacks
 */
class TypeMouvement
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Type_Mouvement")
     */
    private $id; 

    /**
     * @ORM\Column(type="string", length=3, name="Code_Mouvement")
     */
    private $codeMouvement;

    /**
     * @ORM\Column(type="string", length=50, name="Description")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Badm", mappedBy="typeMouvement")
     */
    private $badms;

    public function __construct()
    {
        $this->badms = new ArrayCollection();
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    public function getCodeMouvement(): string
    {
        return $this->codeMouvement;
    }

    public function setCodeMouvement(string $codeMouvement): self
    {
        $this->codeMouvement = $codeMouvement;
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

    public function getBadms(): Collection
    {
        return $this->badms;
    }

    public function addBadm(Badm $badm): self
    {
        if (!$this->badms->contains($badm)) {
            $this->badms[] = $badm;
            $badm->setTypeMouvement($this);
        }
        return $this;
    }

    public function removeBadm(Badm $badm): self
    {
        if ($this->badms->contains($badm)) {
            $this->badms->removeElement($badm);
            if ($badm->getTypeMouvement() === $this) {
                $badm->setTypeMouvement(null);
            }
        }
        return $this;
    }

    public function setBadms($badms): self
    {
        $this->badms = $badms;
        return $this;
    }

    public function __toString()
    {
        return $this->description; 
    }
}
