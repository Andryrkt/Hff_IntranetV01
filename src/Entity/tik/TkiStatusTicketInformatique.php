<?php

namespace App\Entity;

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
}