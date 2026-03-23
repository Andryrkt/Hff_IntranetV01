<?php

namespace App\Entity\admin;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\Profil;
use Doctrine\Common\Collections\Collection;
use App\Repository\admin\SocietteRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=SocietteRepository::class)
 * @ORM\Table(name="societe")
 * @ORM\HasLifecycleCallbacks
 */
class Societte
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=3, name="code_societe")
     */
    private $codeSociete;

    /**
     * @ORM\OneToMany(targetEntity=Profil::class, mappedBy="societe", cascade={"persist"})
     */
    private Collection $profils;

    public function __construct()
    {
        $this->profils = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }



    public function getNom()
    {
        return $this->nom;
    }


    public function setNom($nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodeSociete()
    {
        return $this->codeSociete;
    }


    public function setCodeSociete($codeSociete): self
    {
        $this->codeSociete = $codeSociete;

        return $this;
    }

    public function getProfils(): Collection
    {
        return $this->profils;
    }

    public function addProfil(Profil $profil): self
    {
        if (!$this->profils->contains($profil)) {
            $this->profils[] = $profil;
            $profil->setSociete($this);
        }

        return $this;
    }

    public function removeProfil(Profil $profil): self
    {
        if ($this->profils->contains($profil)) {
            $this->profils->removeElement($profil);
            if ($profil->getSociete() === $this) {
                $profil->setSociete(null);
            }
        }

        return $this;
    }
}
