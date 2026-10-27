<?php

namespace App\Entity\ddp;


use App\Repository\ddp\DemandePaiementCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandePaiementCommandeRepository::class)
 * @ORM\Table(name="demande_paiement_commande")
 * @ORM\HasLifecycleCallbacks
 */
class DemandePaiementCommande
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=50, name="numero_ddp")
     *
     * @var string|null
     */
    private ?string $numeroDdp;

    /**
     * @ORM\Column(type="string", length=50, name="numero_commande", nullable=true)
     *
     * @var string|null
     */
    private ?string $numeroCommande;

    /**
     * @ORM\Column(type="string", length=50, name="numero_demande_appro", nullable=true)
     *
     * @var string|null
     */
    private ?string $numeroDemandeAppro = null;

    /**
     * @ORM\Column(type="string", length=50, name="client", nullable=true)
     *
     * @var string|null
     */
    private ?string $client = null;

    /**
     * @ORM\Column(type="float", scale="2", name="montant_commande_a_payer", nullable=true)
     */
    private ?float $montantCommandeAPayer = null;

    /**
     * @ORM\Column(type="float", scale="2", name="solde_avance", nullable=true)
     */
    private ?float $soldeAvance = null;

    /**
     * @ORM\ManyToOne(targetEntity=DemandePaiement::class, inversedBy="demandePaiementCommandes")
     * @ORM\JoinColumn(name="demandePaiementId", referencedColumnName="id")
     */
    private $demandePaiement;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get the value of numeroDdp
     */
    public function getNumeroDdp(): ?string
    {
        return $this->numeroDdp;
    }

    /**
     * Set the value of numeroDdp
     */
    public function setNumeroDdp(?string $numeroDdp): self
    {
        $this->numeroDdp = $numeroDdp;

        return $this;
    }

    /**
     * Get the value of numeroCommande
     */
    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    /**
     * Set the value of numeroCommande
     */
    public function setNumeroCommande(?string $numeroCommande): self
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    /**
     * Get the value of numeroDemandeAppro
     */
    public function getNumeroDemandeAppro(): ?string
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     */
    public function setNumeroDemandeAppro(?string $numeroDemandeAppro): self
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;

        return $this;
    }

    /**
     * Get the value of client
     */
    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * Set the value of client
     */
    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getDemandePaiement(): ?DemandePaiement
    {
        return $this->demandePaiement;
    }

    public function setDemandePaiement(?DemandePaiement $demandePaiement): self
    {
        $this->demandePaiement = $demandePaiement;

        return $this;
    }

    /**
     * Get the value of montantCommandeAPayer
     */
    public function getMontantCommandeAPayer(): ?float
    {
        return $this->montantCommandeAPayer;
    }

    /**
     * Set the value of montantCommandeAPayer
     */
    public function setMontantCommandeAPayer(?float $montantCommandeAPayer): self
    {
        $this->montantCommandeAPayer = $montantCommandeAPayer;

        return $this;
    }

    /**
     * Get the value of soldeAvance
     */
    public function getSoldeAvance(): ?float
    {
        return $this->soldeAvance;
    }

    /**
     * Set the value of soldeAvance
     */
    public function setSoldeAvance(?float $soldeAvance): self
    {
        $this->soldeAvance = $soldeAvance;

        return $this;
    }
}
