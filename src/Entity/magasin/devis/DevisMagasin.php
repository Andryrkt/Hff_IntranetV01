<?php

namespace App\Entity\magasin\devis;

use App\Repository\magasin\devis\DevisMagasinRepository;

/**
 * @ORM\Entity(repositoryClass=DevisMagasinRepository::class)
 * @ORM\Table(name="devis_magasin") // TODO: table à crée
 */
class DevisMagasin
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    private $pieceJoint01;

    /** =========================================
     * GETTERS & SETTERS
     *============================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of pieceJoint01
     */
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of pieceJoint01
     *
     * @return  self
     */
    public function setPieceJoint01($pieceJoint01)
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }
}
