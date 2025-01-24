<?php

namespace App\Entity\admin\dom;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="Idemnity")
 * @ORM\HasLifecycleCallbacks
 */
 class Idemnity {

    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Idemnity")
     */
    private $id;

     /**
     * @ORM\Column(type="string", length=50, name="Catg",nullable=true)
     */
     private ?string $catg = null;


 /**
     * @ORM\Column(type="string", length=100, name="Destination",nullable=true)
     */
    private ?string $destination = null;


     /**
     * @ORM\Column(type="string", length=50, name="Rmq",nullable=true)
     */
    private ?string $rmq = null;


    /**
     * @ORM\Column(type="string", length=50, name="Type",nullable=true)
     */
    private ?string $type = null;


/**
     * @ORM\Column(type="string", length=50, name="Montant_idemnite",nullable=true)
     */
    private ?string $montantIdemnite = null;


    public function getId()
    {
        return $this->id;
    }


    public function getCatg(): string
    {
        return $this->catg;
    }

   
    public function setCatg(string $catg): self
    {
        $this->catg = $catg;

        return $this;
    }


    public function getDestination(): string
    {
        return $this->destination;
    }

   
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getRmq(): string
    {
        return $this->rmq;
    }

   
    public function setRmq(string $rmq): self
    {
        $this->rmq = $rmq;

        return $this;
    }


    public function getType(): string
    {
        return $this->type;
    }

   
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMontantIdemnite(): string
    {
        return $this->montantIdemnite;
    }

   
    public function setMontantIdemnite(string $montantIdemnite): self
    {
        $this->montantIdemnite = $montantIdemnite;

        return $this;
    }
 }