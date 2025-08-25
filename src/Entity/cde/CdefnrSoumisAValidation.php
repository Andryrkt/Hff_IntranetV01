<?php

namespace App\Entity\cde;

use App\Repository\cde\CdefnrSoumisAValidationRepository;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="integer", name="numeroVersion")
     */
    private int $numVersion = 0;

    /**
     * @ORM\Column(type="datetime", name="date_heure_soumission")
     */
    private  $dateHeureSoumission;

    /**
     * @ORM\Column(type="string", length=50, name="statut")
     */
    private string $statut = '';

    /**
     * @ORM\Column(type="string", length=255, name="nom_fichier")
     */
    private string $nomFichier;

    /**
     * @ORM\Column(type="string", length=11, name="numero_da")
     */
    private string $numeroDa;

    private $pieceJoint01;

    private array $pieceJoint02 = [];



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
    public function getNumCdeFournisseur()
    {
        return $this->numCdeFournisseur;
    }

    /**
     * Set the value of numCdeFournisseur
     *
     * @return  self
     */
    public function setNumCdeFournisseur($numCdeFournisseur)
    {
        $this->numCdeFournisseur = $numCdeFournisseur;

        return $this;
    }

    /**
     * Get the value of codeFournisseur
     */
    public function getCodeFournisseur()
    {
        return $this->codeFournisseur;
    }

    /**
     * Set the value of codeFournisseur
     *
     * @return  self
     */
    public function setCodeFournisseur($codeFournisseur)
    {
        $this->codeFournisseur = $codeFournisseur;

        return $this;
    }

    /**
     * Get the value of numVersion
     */
    public function getNumVersion()
    {
        return $this->numVersion;
    }

    /**
     * Set the value of numVersion
     *
     * @return  self
     */
    public function setNumVersion($numVersion)
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
     *
     * @return  self
     */
    public function setDateHeureSoumission($dateHeureSoumission)
    {
        $this->dateHeureSoumission = $dateHeureSoumission;

        return $this;
    }

    /**
     * Get the value of statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of nomFichier
     */
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    /**
     * Set the value of nomFichier
     *
     * @return  self
     */
    public function setNomFichier($nomFichier)
    {
        $this->nomFichier = $nomFichier;

        return $this;
    }

    /**
     * Get the value of numeroDa
     */
    public function getNumeroDa()
    {
        return $this->numeroDa;
    }

    /**
     * Set the value of numeroDa
     *
     * @return  self
     */
    public function setNumeroDa($numeroDa)
    {
        $this->numeroDa = $numeroDa;

        return $this;
    }

    /**
     * Get the value of pieceJoint01
     */
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of pieceJoint01
     *
     * @return  self
     */
    public function setPieceJoint01($pieceJoint01)
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }

    /**
     * Get the value of pieceJoint02
     */
    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    /**
     * Set the value of pieceJoint02
     *
     * @return  self
     */
    public function setPieceJoint02($pieceJoint02)
    {
        $this->pieceJoint02 = $pieceJoint02;

        return $this;
    }
}
