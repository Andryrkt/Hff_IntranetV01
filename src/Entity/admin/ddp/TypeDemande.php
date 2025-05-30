<?php

namespace App\Entity\admin\ddp;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\ddp\DemandePaiement;
use App\Repository\admin\ddp\TypeDemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=TypeDemandeRepository::class)
 * @ORM\Table(name="type_demande")
 * @ORM\HasLifecycleCallbacks
 */
class TypeDemande
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=3, name="code_type_demande")
     *
     * @var string|null
     */
    private ?string $code;

    /**
     * @ORM\Column(type="string", length=100, name="libelle_type_demande")
     *
     * @var string|null
     */
    private ?string $libelle;

    /**
     * @ORM\Column(type="string", length=255, name="description")
     *
     * @var string|null
     */
    private ?string $description;


    /**
     * @ORM\OneToMany(targetEntity=DemandePaiement::class, mappedBy="typeDemandeId")
     */
    private $demandePaiement;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    public function __construct()
    {
        $this->demandePaiement = new ArrayCollection();
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of code
     *
     * @return  string|null
     */ 
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @param  string|null  $code
     *
     * @return  self
     */ 
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of libelle
     *
     * @return  string|null
     */ 
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set the value of libelle
     *
     * @param  string|null  $libelle
     *
     * @return  self
     */ 
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return  string|null
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param  string|null  $description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDemandePaiement()
    {
        return $this->demandePaiement;
    }

    public function addDemandePaiement($demandePaiement)
    {
        if(!$this->demandePaiement->contains($demandePaiement)) {
            $this->demandePaiement[] = $demandePaiement;
            $demandePaiement->setTypeDemandeId($this);
        }

        return $this;
    }

    public function removeDemandePaiement($demandePaiement)
    {
        if($this->demandePaiement->contains($demandePaiement)) {
            $this->demandePaiement->removeElement($demandePaiement);
            if($demandePaiement->getTypeDemandeId() === $this ) {
                $demandePaiement->setTypeDemandeId(null);
            }
        }
        return $this;
    }

    public function setDemandePaiement($demandePaiement)
    {
        $this->demandePaiement = $demandePaiement;

        return $this;
    }
}