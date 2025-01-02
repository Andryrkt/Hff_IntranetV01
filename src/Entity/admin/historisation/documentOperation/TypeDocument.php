<?php

namespace App\Entity\admin\historisation\documentOperation;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\historisation\documentOperation\TypeDocumentRepository;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocument;

/**
 * @ORM\Entity(repositoryClass=TypeDocumentRepository::class)
 * @ORM\Table(name="type_document")
 * @ORM\HasLifecycleCallbacks
 */
class TypeDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     *
     * @var string
     */
    private string $typeDocument;

    /**
     * @ORM\Column(type="string", length=255, name="libelle_document")
     *
     * @var string
     */
    private string $libelleDocument;

    /**
     * @ORM\Column(type="string", length=10, name="heure_creation")
     */
    private $heureCreation;

    /**
     * @ORM\Column(type="string", length=10, name="heure_modification")
     */
    private $heureModification;

    /**
     * @ORM\OneToMany(targetEntity=HistoriqueOperationDocument::class, mappedBy="idTypeDocument")
     */
    private $historiqueOperationDoc;
    //==========================================================================================


    public function __construct()
    {
        $this->historiqueOperationDoc = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of typeDocument
     *
     * @return  string
     */
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * Set the value of typeDocument
     *
     * @param  string  $typeDocument
     *
     * @return  self
     */
    public function setTypeDocument(string $typeDocument)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get the value of libelleDocument
     *
     * @return  string
     */
    public function getLibelleDocument()
    {
        return $this->libelleDocument;
    }

    /**
     * Set the value of libelleDocument
     *
     * @param  string  $libelleDocument
     *
     * @return  self
     */
    public function setLibelleDocument(string $libelleDocument)
    {
        $this->libelleDocument = $libelleDocument;

        return $this;
    }

    /**
     * Get the value of heureCreation
     */
    public function getHeureCreation()
    {
        return $this->heureCreation;
    }

    /**
     * Set the value of heureCreation
     *
     * @return  self
     */
    public function setHeureCreation($heureCreation)
    {
        $this->heureCreation = $heureCreation;

        return $this;
    }

    /**
     * Get the value of heureModification
     */
    public function getHeureModification()
    {
        return $this->heureModification;
    }

    /**
     * Set the value of heureModification
     *
     * @return  self
     */
    public function setHeureModification($heureModification)
    {
        $this->heureModification = $heureModification;

        return $this;
    }

    /**
     * Get the value of demandeIntervention
     */
    public function getHistoriqueOperationDoc()
    {
        return $this->historiqueOperationDoc;
    }

    public function addHistoriqueOperationDoc(HistoriqueOperationDocument $historiqueOperationDoc): self
    {
        if (!$this->historiqueOperationDoc->contains($historiqueOperationDoc)) {
            $this->historiqueOperationDoc[] = $historiqueOperationDoc;
            $historiqueOperationDoc->setIdTypeDocument($this);
        }

        return $this;
    }

    public function removeHistoriqueOperationDoc(HistoriqueOperationDocument $historiqueOperationDoc): self
    {
        if ($this->historiqueOperationDoc->contains($historiqueOperationDoc)) {
            $this->historiqueOperationDoc->removeElement($historiqueOperationDoc);
            if ($historiqueOperationDoc->getIdTypeDocument() === $this) {
                $historiqueOperationDoc->setIdTypeDocument(null);
            }
        }

        return $this;
    }

    public function setHistoriqueOperationDoc($historiqueOperationDoc)
    {
        $this->historiqueOperationDoc = $historiqueOperationDoc;

        return $this;
    }
}
