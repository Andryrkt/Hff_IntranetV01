<?php

namespace App\Entity\dit;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DitHistoriqueOperationDocumentRepository::class)
 * @ORM\Table(name="historique_operation_document")
 * @ORM\HasLifecycleCallbacks
 */
class DitHistoriqueOperationDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

/**
     * @ORM\ManyToOne(targetEntity=DitInsertionOr::class, inversedBy="ditHistoriqueOperationDoc")
     * @ORM\JoinColumn(name="idOrSoumisAValidation", referencedColumnName="id")
     */
    private $idOrSoumisAValidation;

    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroDocument;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateOperation;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=DitTypeOperation::class, inversedBy="ditHistoriqueOperationDoc")
     * @ORM\JoinColumn(name="idTypeOperation", referencedColumnName="id")
     */
    private $idTypeOperation;

    /**
     * @ORM\ManyToOne(targetEntity=DitTypeDocument::class, inversedBy="ditHistoriqueOperationDoc")
     * @ORM\JoinColumn(name="idTypeDocument", referencedColumnName="id")
     */
    private $idTypeDocument;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $pathPieceJointe;

    //========================================================================================================================================================
    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of idOrSoumisAValidation
     */ 
    public function getIdOrSoumisAValidation()
    {
        return $this->idOrSoumisAValidation;
    }

    /**
     * Set the value of idOrSoumisAValidation
     *
     * @return  self
     */ 
    public function setIdOrSoumisAValidation($idOrSoumisAValidation)
    {
        $this->idOrSoumisAValidation = $idOrSoumisAValidation;

        return $this;
    }

    /**
     * Get the value of numeroDocument
     */ 
    public function getNumeroDocument()
    {
        return $this->numeroDocument;
    }

    /**
     * Set the value of numeroDocument
     *
     * @return  self
     */ 
    public function setNumeroDocument($numeroDocument)
    {
        $this->numeroDocument = $numeroDocument;

        return $this;
    }

    /**
     * Get the value of dateOperation
     */ 
    public function getDateOperation()
    {
        return $this->dateOperation;
    }

    /**
     * Set the value of dateOperation
     *
     * @return  self
     */ 
    public function setDateOperation($dateOperation)
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */ 
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */ 
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of idTypeOperation
     */ 
    public function getIdTypeOperation()
    {
        return $this->idTypeOperation;
    }

    /**
     * Set the value of idTypeOperation
     *
     * @return  self
     */ 
    public function setIdTypeOperation($idTypeOperation)
    {
        $this->idTypeOperation = $idTypeOperation;

        return $this;
    }

    /**
     * Get the value of idTypeDocument
     */ 
    public function getIdTypeDocument()
    {
        return $this->idTypeDocument;
    }

    /**
     * Set the value of idTypeDocument
     *
     * @return  self
     */ 
    public function setIdTypeDocument($idTypeDocument)
    {
        $this->idTypeDocument = $idTypeDocument;

        return $this;
    }

    /**
     * Get the value of pathPieceJointe
     */ 
    public function getPathPieceJointe()
    {
        return $this->pathPieceJointe;
    }

    /**
     * Set the value of pathPieceJointe
     *
     * @return  self
     */ 
    public function setPathPieceJointe($pathPieceJointe)
    {
        $this->pathPieceJointe = $pathPieceJointe;

        return $this;
    }
}