<?php

namespace App\Entity\tik;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiStatutRepository::class)
 */
class TkiStatutTicketInformatique
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, nullable=false)
     */
    private $numeroTicket;

    /**
     * @ORM\Column(type="string", length=3, nullable=false)
     */
    private $codeStatut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateStatut;

    // ... (getters et setters)

    /**
     * Get the value of numeroTicket
     */ 
    public function getNumeroTicket()
    {
        return $this->numeroTicket;
    }

    /**
     * Set the value of numeroTicket
     *
     * @return  self
     */ 
    public function setNumeroTicket($numeroTicket)
    {
        $this->numeroTicket = $numeroTicket;

        return $this;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of codeStatut
     */ 
    public function getCodeStatut()
    {
        return $this->codeStatut;
    }
}