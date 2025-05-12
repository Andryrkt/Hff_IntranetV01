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
     * @ORM\Column(type="string", length=200, name="libelle_fournisseur")
     */
    private string $libelleFournisseur = '';

    /**
     * @ORM\Column(type="integer", name="numeroVersion")
     */
    private int $numVersion = 0;
    
    /**
     * @ORM\Column(type="date", name="date_commande")
     */
    private $dateCommande;

    /**
     * @ORM\Column(type="float", scale="2", name="montant_commande")
     */
    private ?float $montantCommande = 0.00;

    /**
     * @ORM\Column(type="string", length=3, name="devise_commande")
     */
    private string $deviseCommande = '';

    /**
     * @ORM\Column(type="datetime", name="date_heure_soumission")
     */
    private  $dateHeureSoumission;

    /**
     * @ORM\Column(type="string", length=50, name="statut")
     */
    private string $statut = '';

    private $pieceJoint01;

    /**
     * @ORM\Column(type="boolean", name="est_facture")
     */
    private $estFacture = false;
    

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
     * Get the value of libelleFournisseur
     */ 
    public function getLibelleFournisseur()
    {
        return $this->libelleFournisseur;
    }

    /**
     * Set the value of libelleFournisseur
     *
     * @return  self
     */ 
    public function setLibelleFournisseur($libelleFournisseur)
    {
        $this->libelleFournisseur = $libelleFournisseur;

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
     * Get the value of dateCommande
     */ 
    public function getDateCommande()
    {
        return $this->dateCommande;
    }

    /**
     * Set the value of dateCommande
     *
     * @return  self
     */ 
    public function setDateCommande($dateCommande)
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    /**
     * Get the value of montantCommande
     */ 
    public function getMontantCommande()
    {
        return $this->montantCommande;
    }

    /**
     * Set the value of montantCommande
     *
     * @return  self
     */ 
    public function setMontantCommande($montantCommande)
    {
        $this->montantCommande = $montantCommande;

        return $this;
    }

    /**
     * Get the value of deviseCommande
     */ 
    public function getDeviseCommande()
    {
        return $this->deviseCommande;
    }

    /**
     * Set the value of deviseCommande
     *
     * @return  self
     */ 
    public function setDeviseCommande($deviseCommande)
    {
        $this->deviseCommande = $deviseCommande;

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
     * Get the value of estFacture
     */ 
    public function getEstFacture()
    {
        return $this->estFacture;
    }

    /**
     * Set the value of estFacture
     *
     * @return  self
     */ 
    public function setEstFacture($estFacture)
    {
        $this->estFacture = $estFacture;

        return $this;
    }
}