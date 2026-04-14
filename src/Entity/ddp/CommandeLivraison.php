<?php

namespace App\Entity\ddp;

use App\Entity\ddp\CommandeLivraisonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandeLivraisonRepository::class)
 * @ORM\Table(name="commande_livraison")
 * @ORM\HasLifecycleCallbacks
 */
class CommandeLivraison
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=50, name="numero_commande", nullable=true)
     *
     * @var string|null
     */
    private ?string $numeroCommande = null;

    /**
     * @ORM\Column(type="string", length=50, name="numero_livraison", nullable=true)
     *
     * @var string|null
     */
    private ?string $numeroLivraison = null;

    /**
     * @ORM\Column(type="string", length=50, name="numero_facture", nullable=true)
     *
     * @var string|null
     */
    private ?string $numero_facture = null;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId(int $id): self
    {
        $this->id = $id;

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
     * Get the value of numeroLivraison
     */
    public function getNumeroLivraison(): ?string
    {
        return $this->numeroLivraison;
    }

    /**
     * Set the value of numeroLivraison
     */
    public function setNumeroLivraison(?string $numeroLivraison): self
    {
        $this->numeroLivraison = $numeroLivraison;

        return $this;
    }

    /**
     * Get the value of numero_facture
     */
    public function getNumeroFacture(): ?string
    {
        return $this->numero_facture;
    }

    /**
     * Set the value of numero_facture
     */
    public function setNumeroFacture(?string $numero_facture): self
    {
        $this->numero_facture = $numero_facture;

        return $this;
    }
}
