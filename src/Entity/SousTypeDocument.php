<?php

namespace App\Entity;


use App\Entity\Catg;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="Sous_type_document")
 * @ORM\HasLifecycleCallbacks
 */
class SousTypeDocument {
/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Sous_Type_Document")
     */
    private $id;

      
    /**
     * @ORM\Column(type="string", length=4, name="Code_Document")
     */
    private string $codeDocument;

 /**
     * @ORM\Column(type="string", length=4, name="Code_Sous_Type",nullable=true)
     */
    private ?string $codeSousType = null;

 /**
     * @ORM\Column(type="string", length=50, name="Description",nullable=true)
     */
    private ?string $description = null;

/**
     * @ORM\Column(type="string", name="Date_creation")
     */
    private string $dateCreation;


    /**
     * @ORM\ManyToOne(targetEntity="Catg::class", inversedBy="sousTypeDocument")
     * @ORM\JoinColumn(name="catg", referencedColumnName="id")
     */
    private ?Catg $catg;



    public function getId()
    {
        return $this->id;
    }



    public function getCodeDocument(): string
    {
        return $this->codeDocument;
    }

   
    public function setCodeDocument(string $codeDocument): self
    {
        $this->codeDocument = $codeDocument;

        return $this;
    }


    public function getCodeSousType(): ?string
    {
        return $this->codeSousType;
    }

   
    public function setCodeSousType(?string $codeSousType): self
    {
        $this->codeSousType = $codeSousType;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

   
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getDateCreation(): string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(string $dateCreation): self
    {
        $this->dateCreation= $dateCreation;

        return $this;
    }

    public function getCatg(): ?Catg
    {
        return $this->catg;
    }

    
    public function setCatg(?Catg $catg): self
    {
        $this->catg = $catg;

        return $this;
    }
}