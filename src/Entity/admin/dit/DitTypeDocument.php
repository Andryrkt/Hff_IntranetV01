<?php

namespace App\Entity\admin\dit;

use App\Entity\dit\DitHistoriqueOperationDocument;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\admin\dit\DitTypeDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=DitTypeDocumentRepository::class)
 * @ORM\Table(name="type_document")
 * @ORM\HasLifecycleCallbacks
 */
class DitTypeDocument
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
     * @ORM\OneToMany(targetEntity=DitHistoriqueOperationDocument::class, mappedBy="idTypeDocument")
     */
    private $ditHistoriqueOperationDoc;
    //==========================================================================================
    

    public function __construct()
    {
        $this->ditHistoriqueOperationDoc = new ArrayCollection();
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
     * Get the value of demandeIntervention
     */ 
    public function getDitHistoriqueOperationDoc()
    {
        return $this->ditHistoriqueOperationDoc;
    }

    public function addDitHistoriqueOperationDoc(DitHistoriqueOperationDocument $ditHistoriqueOperationDoc): self
    {
        if (!$this->ditHistoriqueOperationDoc->contains($ditHistoriqueOperationDoc)) {
            $this->ditHistoriqueOperationDoc[] = $ditHistoriqueOperationDoc;
            $ditHistoriqueOperationDoc->setIdTypeDocument($this);
        }

        return $this;
    }

    public function removeDitHistoriqueOperationDoc(DitHistoriqueOperationDocument $ditHistoriqueOperationDoc): self
    {
        if ($this->ditHistoriqueOperationDoc->contains($ditHistoriqueOperationDoc)) {
            $this->ditHistoriqueOperationDoc->removeElement($ditHistoriqueOperationDoc);
            if ($ditHistoriqueOperationDoc->getIdTypeDocument() === $this) {
                $ditHistoriqueOperationDoc->setIdTypeDocument(null);
            }
        }
        
        return $this;
    }

    public function setDitHistoriqueOperationDoc($ditHistoriqueOperationDoc)
    {
        $this->ditHistoriqueOperationDoc = $ditHistoriqueOperationDoc;

        return $this;
    }
}