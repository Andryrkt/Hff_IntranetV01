<?php

namespace App\Entity\ddp;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ddp\HistoriqueStatutDdpRepository;

/**
 * @ORM\Entity(repositoryClass=HistoriqueStatutDdpRepository::class)
 * @ORM\Table(name="historique_statut_ddp")
 * @ORM\HasLifecycleCallbacks
 */
class HistoriqueStatutDdp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, name="numero_ddp")
     */
    private $numeroDdp;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $statut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;



    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDdp
     */ 
    public function getNumeroDdp()
    {
        return $this->numeroDdp;
    }

    /**
     * Set the value of numeroDdp
     *
     * @return  self
     */ 
    public function setNumeroDdp($numeroDdp)
    {
        $this->numeroDdp = $numeroDdp;

        return $this;
    }

    /**
     * Get the value of statut
     */ 
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */ 
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of date
     */ 
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of date
     *
     * @return  self
     */ 
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }
}