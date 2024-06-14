<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SocietteRepository")
 * @ORM\Table(name="societe")
 * @ORM\HasLifecycleCallbacks
 */
class TypeReparation
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_type_reparation")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;


    /**
     * @ORM\ManyToMany(targetEntity=Societte::class, mappedBy="typeReparations")
     */
    private $societtes;

    public function __construct()
    {
        $this->societtes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

   
    public function getType()
    {
        return $this->type;
    }

    
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }


    
    public function getSociettes(): Collection
    {
        return $this->societtes;
    }

    public function addSociette(Societte $societte): self
    {
        if(!$this->societtes->contains($societte)){
            $this->societtes[] = $societte;
            $societte->addTypeReparation($this);
        }
        return $this;
    }

    public function removeSociette(Societte $societte): self
    {
        if($this->societtes->contains($societte)) {
            $this->societtes->removeElement($societte);
          $societte->removeTypeReparation($this);
        }
        return $this;
    }
}

