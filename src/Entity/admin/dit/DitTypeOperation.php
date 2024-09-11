<?php

namespace App\Entity\admin\dit;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Repository\admin\dit\DitTypeOperationRepository;

/**
 * @ORM\Entity(repositoryClass=DitTypeOperationRepository::class)
 * @ORM\Table(name="type_operation")
 * @ORM\HasLifecycleCallbacks
 */
class DitOperation
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
    private string $typeOperation;

    /**
     * @ORM\OneToMany(targetEntity=DitHistoriqueOperationDocument::class, mappedBy="idTypeOperation")
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

  
    public function getTypeOperation()
    {
        return $this->typeOperation;
    }

 
    public function setTypeOperation( $typeOperation): self
    {
        $this->typeOperation = $typeOperation;

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
            $ditHistoriqueOperationDoc->setIdTypeOperation($this);
        }

        return $this;
    }

    public function removeDitHistoriqueOperationDoc(DitHistoriqueOperationDocument $ditHistoriqueOperationDoc): self
    {
        if ($this->ditHistoriqueOperationDoc->contains($ditHistoriqueOperationDoc)) {
            $this->ditHistoriqueOperationDoc->removeElement($ditHistoriqueOperationDoc);
            if ($ditHistoriqueOperationDoc->getIdTypeOperation() === $this) {
                $ditHistoriqueOperationDoc->setIdTypeOperation(null);
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