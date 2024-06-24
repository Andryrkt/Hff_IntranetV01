<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="idemnity")
 * @ORM\HasLifecycleCallbacks
 */



 class Idemnity {

    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_idemnity")
     */
    private $id;

     /**
     * @ORM\Column(type="string", length=50, name="catg",nullable=true)
     */
     private ?string $catg;


 /**
     * @ORM\Column(type="string", length=100, name="destination",nullable=true)
     */
    private ?string $destination;


     /**
     * @ORM\Column(type="string", length=50, name="rmq",nullable=true)
     */
    private ?string $rmq;


    /**
     * @ORM\Column(type="string", length=50, name="type",nullable=true)
     */
    private ?string $type;


/**
     * @ORM\Column(type="string", length=50, name="montantIdemnite",nullable=true)
     */
    private ?string $montantIdemnite;


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