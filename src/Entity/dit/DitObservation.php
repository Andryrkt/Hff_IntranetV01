<?php

namespace App\Entity\dit;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\dit\DitObservationRepository;

/**
 * @ORM\Entity(repositoryClass=DitObservationRepository::class)
 * @ORM\Table(name="dit_observation")
 * @ORM\HasLifecycleCallbacks
 */
class DitObservation
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=12, name="numero_dit")
     */
    private string $numDit;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur")
     */
    private ?string $utilisateur = '';

    /**
     * @ORM\Column(type="string", name="observation")
     *
     * @var string|NULL
     */
    private ?string $observation = '';

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
     * Get the value of numDit
     */
    public function getNumDit(): string
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     *
     * @return  self
     */
    public function setNumDit(string $numDit)
    {
        $this->numDit = $numDit;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur(): ?string
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur(string $utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of observation
     */
    public function getObservation(): ?string
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     */
    public function setObservation(?string $observation)
    {
        $this->observation = $observation;

        return $this;
    }
}
