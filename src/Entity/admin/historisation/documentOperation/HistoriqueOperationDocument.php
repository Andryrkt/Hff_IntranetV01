<?php

namespace App\Entity\admin\historisation\documentOperation;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\historisation\documentOperation\TypeDocument;
use App\Entity\admin\historisation\documentOperation\TypeOperation;
use App\Repository\admin\historisation\documentOperation\HistoriqueOperationDocumentRepository;

/**
 * @ORM\Entity(repositoryClass=HistoriqueOperationDocumentRepository::class)
 * @ORM\Table(name="historique_operation_document")
 * @ORM\HasLifecycleCallbacks
 */
class HistoriqueOperationDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",  length=50)
     */
    private string $numeroDocument;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateOperation;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=TypeOperation::class, inversedBy="historiqueOperationDoc")
     * @ORM\JoinColumn(name="idTypeOperation", referencedColumnName="id")
     */
    private $idTypeOperation;

    /**
     * @ORM\ManyToOne(targetEntity=TypeDocument::class, inversedBy="historiqueOperationDoc")
     * @ORM\JoinColumn(name="idTypeDocument", referencedColumnName="id")
     */
    private $idTypeDocument;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $pathPieceJointe;

    /**
     * @ORM\Column(type="string", length=10, name="heure_operation")
     */
    private $heureOperation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statutOperation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelleOperation;

    //========================================================================================================================================================

    public function __construct()
    {
        $this->dateOperation = new \DateTime(); // Date courante
        $this->heureOperation = (new \DateTime())->format('H:i:s'); // Heure courante
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    public function getNumeroDocument()
    {
        return $this->numeroDocument;
    }

    public function setNumeroDocument($numeroDocument): self
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

    /**
     * Get the value of heureOperation
     */
    public function getHeureOperation()
    {
        return $this->heureOperation;
    }

    /**
     * Set the value of heureOperation
     *
     * @return  self
     */
    public function setHeureOperation($heureOperation)
    {
        $this->heureOperation = $heureOperation;

        return $this;
    }

    /**
     * Get the value of statutOperation
     */
    public function getStatutOperation()
    {
        return $this->statutOperation;
    }

    /**
     * Set the value of statutOperation
     *
     * @return  self
     */
    public function setStatutOperation($statutOperation)
    {
        $this->statutOperation = $statutOperation;

        return $this;
    }

    /**
     * Get the value of libelleOperation
     */
    public function getLibelleOperation()
    {
        return $this->libelleOperation;
    }

    /**
     * Set the value of libelleOperation
     *
     * @return  self
     */
    public function setLibelleOperation($libelleOperation)
    {
        $this->libelleOperation = $libelleOperation;

        return $this;
    }
}
