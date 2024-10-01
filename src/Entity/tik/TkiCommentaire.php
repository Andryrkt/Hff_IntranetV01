<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiCommentairesRepository::class)
 */
class TkiCommentaires
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
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $nomUtilisateur;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $commentaires;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $piecesJointes1;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $piecesJointes2;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $piecesJointes3;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCommentaire;

    // ... (getters et setters)
}