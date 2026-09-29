<?php

namespace App\Entity\cde;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\cde\CdefnrSoumisAValidationLigneRepository;

/**
 * @ORM\Entity(repositoryClass=CdefnrSoumisAValidationLigneRepository::class)
 * @ORM\Table(name="cdefnr_soumis_a_validation_ligne")
 */
class CdefnrSoumisAValidationLigne
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8, name="numero_cde")
     */
    private string $numCde = '';

    /**
     * @ORM\Column(type="string", length=3, name="type_document")
     */
    private string $typeDocument = '';

    /**
     * @ORM\Column(type="string", length=100, name="numero_document")
     */
    private string $numeroDocument = '';

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private int $numeroVersion = 0;

    /**==============================================================================
     * GETTERS & SETTERS
     *===============================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numCde
     */
    public function getNumCde(): string
    {
        return $this->numCde;
    }

    /**
     * Set the value of numCde
     */
    public function setNumCde(string $numCde): self
    {
        $this->numCde = $numCde;

        return $this;
    }

    /**
     * Get the value of typeDocument
     */
    public function getTypeDocument(): string
    {
        return $this->typeDocument;
    }

    /**
     * Set the value of typeDocument
     */
    public function setTypeDocument(string $typeDocument): self
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get the value of numeroDocument
     */
    public function getNumeroDocument(): string
    {
        return $this->numeroDocument;
    }

    /**
     * Set the value of numeroDocument
     */
    public function setNumeroDocument(string $numeroDocument): self
    {
        $this->numeroDocument = $numeroDocument;

        return $this;
    }

    /**
     * Get the value of numeroVersion
     */
    public function getNumeroVersion(): int
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     */
    public function setNumeroVersion(int $numeroVersion): self
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }
}
