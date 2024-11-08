<?php

namespace App\Entity\admin\tik;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiStatutRepository::class)
 * @ORM\Table(name="TKI_Statut_Ticket_Informatique")
 * @ORM\HasLifecycleCallbacks
 */
class TkiStatutTicketInformatique
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_TKI_Statut")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Ticket", nullable=false)
     */
    private $numeroTicket;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Statut", nullable=false)
     */
    private $codeStatut;

    /**
     * @ORM\Column(type="datetime", name="Date_Statut")
     */
    private $dateStatut;

    
    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }
    
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
     * Get the value of codeStatut
     */ 
    public function getCodeStatut()
    {
        return $this->codeStatut;
    }

    /**
     * Set the value of codeStatut
     *
     * @return  self
     */ 
    public function setCodeStatut($codeStatut)
    {
        $this->codeStatut = $codeStatut;

        return $this;
    }

    /**
     * Get the value of dateStatut
     */ 
    public function getDateStatut()
    {
        return $this->dateStatut;
    }

    /**
     * Set the value of dateStatut
     *
     * @return  self
     */ 
    public function setDateStatut($dateStatut)
    {
        $this->dateStatut = $dateStatut;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->dateStatut = new \DateTime('now');
    }
}