<?php

namespace App\Entity\ddc;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="groupe_direction")
 */
class GroupeDirection
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $matricule;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nomPrenoms;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $utilisateurCreation;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="integer")
     */
    private $actif;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateActivation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getNomPrenoms(): ?string
    {
        return $this->nomPrenoms;
    }

    public function setNomPrenoms(string $nomPrenoms): self
    {
        $this->nomPrenoms = $nomPrenoms;

        return $this;
    }

    public function getUtilisateurCreation(): ?string
    {
        return $this->utilisateurCreation;
    }

    public function setUtilisateurCreation(string $utilisateurCreation): self
    {
        $this->utilisateurCreation = $utilisateurCreation;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getActif(): ?int
    {
        return $this->actif;
    }

    public function setActif(int $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getDateActivation(): ?\DateTimeInterface
    {
        return $this->dateActivation;
    }

    public function setDateActivation(?\DateTimeInterface $dateActivation): self
    {
        $this->dateActivation = $dateActivation;

        return $this;
    }
}
