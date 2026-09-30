<?php

namespace App\Entity\cde;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\cde\CdefnrSoumisAValidationRepository;

/**
 * @ORM\Entity(repositoryClass=CdefnrSoumisAValidationRepository::class)
 * @ORM\Table(name="cdefnr_soumis_a_validation")
 */
class CdefnrSoumisAValidation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8, name="numero_commande_fournisseur")
     */
    private string $numCdeFournisseur = '';

    /**
     * @ORM\Column(type="string", length=8, name="code_fournisseur")
     */
    private string $codeFournisseur = '';

    /**
     * @ORM\Column(type="string", length=200, name="libelle_fournisseur")
     */
    private string $libelleFournisseur = '';

    /**
     * @ORM\Column(type="integer", name="numeroVersion")
     */
    private int $numVersion = 0;

    /**
     * @ORM\Column(type="datetime", name="date_heure_soumission")
     */
    private $dateHeureSoumission;

    /**
     * @ORM\Column(type="string", length=50, name="statut")
     */
    private string $statut = '';

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
     * Get the value of numCdeFournisseur
     */
    public function getNumCdeFournisseur(): string
    {
        return $this->numCdeFournisseur;
    }

    /**
     * Set the value of numCdeFournisseur
     */
    public function setNumCdeFournisseur(string $numCdeFournisseur): self
    {
        $this->numCdeFournisseur = $numCdeFournisseur;

        return $this;
    }

    /**
     * Get the value of codeFournisseur
     */
    public function getCodeFournisseur(): string
    {
        return $this->codeFournisseur;
    }

    /**
     * Set the value of codeFournisseur
     */
    public function setCodeFournisseur(string $codeFournisseur): self
    {
        $this->codeFournisseur = $codeFournisseur;

        return $this;
    }

    /**
     * Get the value of libelleFournisseur
     */
    public function getLibelleFournisseur(): string
    {
        return $this->libelleFournisseur;
    }

    /**
     * Set the value of libelleFournisseur
     */
    public function setLibelleFournisseur(string $libelleFournisseur): self
    {
        $this->libelleFournisseur = $libelleFournisseur;

        return $this;
    }

    /**
     * Get the value of numVersion
     */
    public function getNumVersion(): int
    {
        return $this->numVersion;
    }

    /**
     * Set the value of numVersion
     */
    public function setNumVersion(int $numVersion): self
    {
        $this->numVersion = $numVersion;

        return $this;
    }

    /**
     * Get the value of dateHeureSoumission
     */
    public function getDateHeureSoumission()
    {
        return $this->dateHeureSoumission;
    }

    /**
     * Set the value of dateHeureSoumission
     */
    public function setDateHeureSoumission($dateHeureSoumission): self
    {
        $this->dateHeureSoumission = $dateHeureSoumission;

        return $this;
    }

    /**
     * Get the value of statut
     */
    public function getStatut(): string
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     */
    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}
