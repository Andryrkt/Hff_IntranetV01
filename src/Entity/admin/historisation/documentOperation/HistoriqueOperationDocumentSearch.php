<?php

namespace App\Entity\admin\historisation\documentOperation;

class HistoriqueOperationDocumentSearch
{
    private ?string $numeroDocument = '';

    private ?string $utilisateur = '';

    private ?string $statutOperation = '';

    private ?TypeOperation $typeOperation = null;

    private ?TypeDocument $typeDocument = null;

    private ?\DateTime $dateOperationDebut = null;

    private ?\Datetime $dateOperationFin = null;

    /**=====================================================================================
     * GETTERS and SETTERS
     * =====================================================================================*/

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
     * Get the value of typeOperation
     */
    public function getTypeOperation()
    {
        return $this->typeOperation;
    }

    /**
     * Set the value of typeOperation
     *
     * @return  self
     */
    public function setTypeOperation($typeOperation)
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    /**
     * Get the value of typeDocument
     */
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * Set the value of typeDocument
     *
     * @return  self
     */
    public function setTypeDocument($typeDocument)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get the value of dateOperationDebut
     */
    public function getDateOperationDebut()
    {
        return $this->dateOperationDebut;
    }

    /**
     * Set the value of dateOperationDebut
     *
     * @return  self
     */
    public function setDateOperationDebut($dateOperationDebut)
    {
        $this->dateOperationDebut = $dateOperationDebut;

        return $this;
    }

    /**
     * Get the value of dateOperationFin
     */
    public function getDateOperationFin()
    {
        return $this->dateOperationFin;
    }

    /**
     * Set the value of dateOperationFin
     *
     * @return  self
     */
    public function setDateOperationFin($dateOperationFin)
    {
        $this->dateOperationFin = $dateOperationFin;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'numeroDocument'    => $this->numeroDocument,
            'utilisateur'       => $this->utilisateur,
            'statutOperation'   => $this->statutOperation,
            'typeOperation'     => $this->typeOperation === null ? null : $this->typeOperation->getId(),
            'typeDocument'      => $this->typeDocument === null ? null : $this->typeDocument->getId(),
            'dateOperationDebut' => $this->dateOperationDebut,
            'dateOperationFin'  => $this->dateOperationFin,
        ];
    }
}
