<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="sous_type_document")
 * @ORM\HasLifecycleCallbacks
 */
class SousTypeDocument {
/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_type_sous_document")
     */
    private $id;

      
    /**
     * @ORM\Column(type="string", length=4, name="code_Document")
     */
    private string $codeDocument;

 /**
     * @ORM\Column(type="string", length=4, name="code_sous_type",nullable=true)
     */
    private ?string $codeSousType;

 /**
     * @ORM\Column(type="string", length=50, name="description",nullable=true)
     */
    private ?string $description;

/**
     * @ORM\Column(type="string", name="date_Creation")
     */
    private string $dateCreation;



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


    public function getCodeSousType(): string
    {
        return $this->codeSousType;
    }

   
    public function setCodeSousType(string $codeSousType): self
    {
        $this->codeSousType = $codeSousType;

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


    public function getDateCreation(): string
    {
        return $this->dateCreation;
    }

   
    public function setDateCreation(string $dateCreation): self
    {
        $this->dateCreation= $dateCreation;

        return $this;
    }

}