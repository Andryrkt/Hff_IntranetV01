<?php

namespace App\Entity\ddp;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ddp\DemandePaiementLigneRepository;

/**
 * @ORM\Entity(repositoryClass=DemandePaiementLigneRepository::class)
 * @ORM\Table(name="demande_paiement_ligne")
 * @ORM\HasLifecycleCallbacks
 */
class DemandePaiementLigne
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_paiement")
     *
     * @var string|null
     */
    private ?string $numeroDdp;

    /**
     * @ORM\Column(type="integer", name="numero_ligne")
     */
    private int $numeroLigne = 0;

    /**
     * @ORM\Column(type="string", length=5, name="numero_commande")
     */
    private ?string $numeroCommande;

    /**
     * @ORM\Column(type="string", length=5, name="numero_facture")
     */
    private ?string $numeroFacture;

    /**
     * @ORM\Column(type="float", scale="2", name="montant_facture")
     */
    private float $montantFacture = 0.00;

    /**
     * @ORM\Column(type="integer", name="numeroVersion")
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="float", scale="2", name="ratio_montant_payer")
     */
    private $ratioMontantPayer;


    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDdp
     *
     * @return  string|null
     */
    public function getNumeroDdp()
    {
        return $this->numeroDdp;
    }

    /**
     * Set the value of numeroDdp
     *
     * @param  string|null  $numeroDdp
     *
     * @return  self
     */
    public function setNumeroDdp($numeroDdp)
    {
        $this->numeroDdp = $numeroDdp;

        return $this;
    }

    /**
     * Get the value of numeroLigne
     */
    public function getNumeroLigne()
    {
        return $this->numeroLigne;
    }

    /**
     * Set the value of numeroLigne
     *
     * @return  self
     */
    public function setNumeroLigne($numeroLigne)
    {
        $this->numeroLigne = $numeroLigne;

        return $this;
    }

    /**
     * Get the value of numeroCommande
     */
    public function getNumeroCommande()
    {
        return $this->numeroCommande;
    }

    /**
     * Set the value of numeroCommande
     *
     * @return  self
     */
    public function setNumeroCommande($numeroCommande)
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    /**
     * Get the value of numeroFacture
     */
    public function getNumeroFacture()
    {
        return $this->numeroFacture;
    }

    /**
     * Set the value of numeroFacture
     *
     * @return  self
     */
    public function setNumeroFacture($numeroFacture)
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    /**
     * Get the value of montantFacture
     */
    public function getMontantFacture()
    {
        return $this->montantFacture;
    }

    /**
     * Set the value of montantFacture
     *
     * @return  self
     */
    public function setMontantFacture($montantFacture)
    {
        $this->montantFacture = $montantFacture;

        return $this;
    }



    /**
     * Get the value of numeroVersion
     */
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     *
     * @return  self
     */
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }

    /**
     * Get the value of ratioMontantPayer
     */
    public function getRatioMontantPayer()
    {
        return $this->ratioMontantPayer;
    }

    /**
     * Set the value of ratioMontantPayer
     */
    public function setRatioMontantPayer($ratioMontantPayer): self
    {
        $this->ratioMontantPayer = $ratioMontantPayer;

        return $this;
    }
}
