<?php

namespace App\Entity\dw;

class DocInternesearch
{
    private $dateDocumentDebut;
    private $dateDocumentFin;
    private $nomDocument;
    private $typeDocument;
    private $perimetre;
    private $processusLie;
    private $nomResponsable;
    private $motCle;

    public function toArray(): array
    {
        return [
            "dateDocumentDebut" => $this->dateDocumentDebut,
            "dateDocumentFin" => $this->dateDocumentFin,
            "nomDocument" => $this->nomDocument,
            "typeDocument" => $this->typeDocument,
            "perimetre" => $this->perimetre,
            "processusLie" => $this->processusLie,
            "nomResponsable" => $this->nomResponsable,
            "motCle" => $this->motCle
        ];
    }

    /**
     * Get the value of nomResponsable
     */
    public function getNomResponsable()
    {
        return $this->nomResponsable;
    }

    /**
     * Set the value of nomResponsable
     */
    public function setNomResponsable($nomResponsable): self
    {
        $this->nomResponsable = $nomResponsable;
        return $this;
    }

    /**
     * Get the value of processusLie
     */
    public function getProcessusLie()
    {
        return $this->processusLie;
    }

    /**
     * Set the value of processusLie
     */
    public function setProcessusLie($processusLie): self
    {
        $this->processusLie = $processusLie;
        return $this;
    }

    /**
     * Get the value of perimetre
     */
    public function getPerimetre()
    {
        return $this->perimetre;
    }

    /**
     * Set the value of perimetre
     */
    public function setPerimetre($perimetre): self
    {
        $this->perimetre = $perimetre;
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
     */
    public function setTypeDocument($typeDocument): self
    {
        $this->typeDocument = $typeDocument;
        return $this;
    }

    /**
     * Get the value of nomDocument
     */
    public function getNomDocument()
    {
        return $this->nomDocument;
    }

    /**
     * Set the value of nomDocument
     */
    public function setNomDocument($nomDocument): self
    {
        $this->nomDocument = $nomDocument;
        return $this;
    }

    /**
     * Get the value of motCle
     */
    public function getMotCle()
    {
        return $this->motCle;
    }

    /**
     * Set the value of motCle
     *
     * @return  self
     */
    public function setMotCle($motCle)
    {
        $this->motCle = $motCle;

        return $this;
    }

    /**
     * Get the value of dateDocumentDebut
     */
    public function getDateDocumentDebut()
    {
        return $this->dateDocumentDebut;
    }

    /**
     * Set the value of dateDocumentDebut
     */
    public function setDateDocumentDebut($dateDocumentDebut): self
    {
        $this->dateDocumentDebut = $dateDocumentDebut;
        return $this;
    }

    /**
     * Get the value of dateDocumentFin
     */
    public function getDateDocumentFin()
    {
        return $this->dateDocumentFin;
    }

    /**
     * Set the value of dateDocumentFin
     */
    public function setDateDocumentFin($dateDocumentFin): self
    {
        $this->dateDocumentFin = $dateDocumentFin;
        return $this;
    }
}
