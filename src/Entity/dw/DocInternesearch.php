<?php

namespace App\Entity\dw;

class DocInternesearch
{
    private $dateDocument;
    private $nomDocument;
    private $typeDocument;
    private $perimetre;
    private $processusLie;
    private $nomResponsable;

    public function toArray() : array
    {
        return [
            "dateDocument" => $this->dateDocument,
            "nomDocument" => $this->nomDocument,
            "typeDocument" => $this->typeDocument,
            "perimetre" => $this->perimetre,
            "processusLie" => $this->processusLie,
            "nomResponsable" => $this->nomResponsable,
        ];
    }

    /**
     * Get the value of nomResponsable
     */
    public function getNomResponsable() {
        return $this->nomResponsable;
    }

    /**
     * Set the value of nomResponsable
     */
    public function setNomResponsable($nomResponsable): self {
        $this->nomResponsable = $nomResponsable;
        return $this;
    }

    /**
     * Get the value of processusLie
     */
    public function getProcessusLie() {
        return $this->processusLie;
    }

    /**
     * Set the value of processusLie
     */
    public function setProcessusLie($processusLie): self {
        $this->processusLie = $processusLie;
        return $this;
    }

    /**
     * Get the value of perimetre
     */
    public function getPerimetre() {
        return $this->perimetre;
    }

    /**
     * Set the value of perimetre
     */
    public function setPerimetre($perimetre): self {
        $this->perimetre = $perimetre;
        return $this;
    }

    /**
     * Get the value of typeDocument
     */
    public function getTypeDocument() {
        return $this->typeDocument;
    }

    /**
     * Set the value of typeDocument
     */
    public function setTypeDocument($typeDocument): self {
        $this->typeDocument = $typeDocument;
        return $this;
    }

    /**
     * Get the value of nomDocument
     */
    public function getNomDocument() {
        return $this->nomDocument;
    }

    /**
     * Set the value of nomDocument
     */
    public function setNomDocument($nomDocument): self {
        $this->nomDocument = $nomDocument;
        return $this;
    }

    /**
     * Get the value of dateDocument
     */
    public function getDateDocument() {
        return $this->dateDocument;
    }

    /**
     * Set the value of dateDocument
     */
    public function setDateDocument($dateDocument): self {
        $this->dateDocument = $dateDocument;
        return $this;
    }
}