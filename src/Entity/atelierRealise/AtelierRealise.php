<?php

namespace App\Entity\atelierRealise;

use App\Repository\atelierRealise\AtelierRealiseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AtelierRealiseRepository::class)
 * @ORM\Table(name="agence_atelier_realise")
 */
class AtelierRealise
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Mouvement_Materiel")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, name="code_agence")
     */
    private $codeAgence;

    /**
     * @ORM\Column(type="string", length=100, name="code_atelier")
     */
    private $codeAtelier;

    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Get the value of codeAgence
     */
    public function getCodeAgence()
    {
        return $this->codeAgence;
    }

    /**
     * Set the value of codeAgence
     *
     * @return  self
     */
    public function setCodeAgence($codeAgence)
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    /**
     * Get the value of codeAtelier
     */
    public function getCodeAtelier()
    {
        return $this->codeAtelier;
    }

    /**
     * Set the value of codeAtelier
     *
     * @return  self
     */
    public function setCodeAtelier($codeAtelier)
    {
        $this->codeAtelier = $codeAtelier;

        return $this;
    }
}
