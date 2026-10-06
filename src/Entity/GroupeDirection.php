<?php

namespace App\Entity;

use App\Repository\GroupeDirectionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GroupeDirectionRepository::class)
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
     * @ORM\Column(type="string", length=100, name="nom_prenoms")
     */
    private $nomPrenoms;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur_creation")
     */
    private $utilisateurCreation;

    /**
     * @ORM\Column(type="date", nullable=true, name="date_creation")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="integer", name="actif")
     */
    private $actif = 0;

    public function isActif(): bool
    {
        return $this->actif === 1;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif ? 1 : 0;

        return $this;
    }

    /**
     * @ORM\Column(type="date", nullable=true, name="date_activation")
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
