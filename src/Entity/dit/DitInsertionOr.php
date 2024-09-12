<?php

namespace App\Entity\dit;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\dit\DitInsertionOrRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\dit\DitHistoriqueOperationDocument;

/**
 * @ORM\Entity(repositoryClass=DitInsertionOrRepository::class)
 * @ORM\Table(name="ors_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitInsertionOr
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private ?string $numeroDit = "";

    /**
     * @ORM\Column(type="string", length=8)
     */
    private string $numeroOR;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $dateSoumission;

    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroItv;

    /**
     * @ORM\Column(type="integer")
     */
    private int $nombrePieceItv;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantItv;

    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroModification;

    /**
     * @ORM\OneToMany(targetEntity=DitHistoriqueOperationDocument::class, mappedBy="idOrSoumisAValidation")
     */
    private $ditHistoriqueOperationDoc;


    private $file;
    //==========================================================================================
    

    public function __construct()
    {
        $this->ditHistoriqueOperationDoc = new ArrayCollection();
    }

    
    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDit
     */ 
    public function getNumeroDit()
    {
        return $this->numeroDit;
    }

    /**
     * Set the value of numeroDit
     *
     * @return  self
     */ 
    public function setNumeroDit($numeroDit)
    {
        $this->numeroDit = $numeroDit;

        return $this;
    }

    /**
     * Get the value of numeroOR
     */ 
    public function getNumeroOR()
    {
        return $this->numeroOR;
    }

    /**
     * Set the value of numeroOR
     *
     * @return  self
     */ 
    public function setNumeroOR($numeroOR)
    {
        $this->numeroOR = $numeroOR;

        return $this;
    }

    /**
     * Get the value of dateSoumission
     */ 
    public function getDateSoumission()
    {
        return $this->dateSoumission;
    }

    /**
     * Set the value of dateSoumission
     *
     * @return  self
     */ 
    public function setDateSoumission($dateSoumission)
    {
        $this->dateSoumission = $dateSoumission;

        return $this;
    }

    /**
     * Get the value of numeroItv
     */ 
    public function getNumeroItv()
    {
        return $this->numeroItv;
    }

    /**
     * Set the value of numeroItv
     *
     * @return  self
     */ 
    public function setNumeroItv($numeroItv)
    {
        $this->numeroItv = $numeroItv;

        return $this;
    }

    /**
     * Get the value of nombrePieceItv
     */ 
    public function getNombrePieceItv()
    {
        return $this->nombrePieceItv;
    }

    /**
     * Set the value of nombrePieceItv
     *
     * @return  self
     */ 
    public function setNombrePieceItv($nombrePieceItv)
    {
        $this->nombrePieceItv = $nombrePieceItv;

        return $this;
    }

    /**
     * Get the value of montantItv
     */ 
    public function getMontantItv()
    {
        return $this->montantItv;
    }

    /**
     * Set the value of montantItv
     *
     * @return  self
     */ 
    public function setMontantItv($montantItv)
    {
        $this->montantItv = $montantItv;

        return $this;
    }

    /**
     * Get the value of numeroModification
     */ 
    public function getNumeroModification()
    {
        return $this->numeroModification;
    }

    /**
     * Set the value of numeroModification
     *
     * @return  self
     */ 
    public function setNumeroModification($numeroModification)
    {
        $this->numeroModification = $numeroModification;

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
            $ditHistoriqueOperationDoc->setIdOrSoumisAValidation($this);
        }

        return $this;
    }

    public function removeDitHistoriqueOperationDoc(DitHistoriqueOperationDocument $ditHistoriqueOperationDoc): self
    {
        if ($this->ditHistoriqueOperationDoc->contains($ditHistoriqueOperationDoc)) {
            $this->ditHistoriqueOperationDoc->removeElement($ditHistoriqueOperationDoc);
            if ($ditHistoriqueOperationDoc->getIdOrSoumisAValidation() === $this) {
                $ditHistoriqueOperationDoc->setIdOrSoumisAValidation(null);
            }
        }
        
        return $this;
    }

    public function setDitHistoriqueOperationDoc($ditHistoriqueOperationDoc)
    {
        $this->ditHistoriqueOperationDoc = $ditHistoriqueOperationDoc;

        return $this;
    }

    /**
     * Get the value of file
     */ 
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @return  self
     */ 
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }
}