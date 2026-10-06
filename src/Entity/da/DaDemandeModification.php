<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\da\DaDemandeModificationRepository;

/**
 * @ORM\Entity(repositoryClass=DaDemandeModificationRepository::class)
 * @ORM\Table(name="da_demande_modification")
 * @ORM\HasLifecycleCallbacks
 */
class DaDemandeModification
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_da")
     */
    private string $numDa;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur")
     */
    private ?string $utilisateur = '';

    /**
     * @ORM\Column(type="boolean", name="est_deverouille")
     */
    private bool $estDeverouille = false;

    /**
     * @ORM\Column(type="string", name="motif")
     */
    private ?string $motif = null;


    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Get the value of numDa
     */
    public function getNumDa()
    {
        return $this->numDa;
    }

    /**
     * Set the value of numDa
     *
     * @return  self
     */
    public function setNumDa($numDa)
    {
        $this->numDa = $numDa;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of estDeverouille
     */
    public function getEstDeverouille()
    {
        return $this->estDeverouille;
    }

    /**
     * Set the value of estDeverouille
     *
     * @return  self
     */
    public function setEstDeverouille($estDeverouille)
    {
        $this->estDeverouille = $estDeverouille;

        return $this;
    }

    /**
     * Get the value of motif
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set the value of motif
     *
     * @return  self
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;

        return $this;
    }
}
