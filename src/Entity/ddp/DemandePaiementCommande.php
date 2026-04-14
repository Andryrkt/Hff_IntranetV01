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
}
