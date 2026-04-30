<?php

namespace App\Entity\dit;

use DateTime;
use App\Entity\admin\utilisateur\User;
use App\Repository\dit\CommentaireDitOrRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentaireDitOrRepository::class)
 * @ORM\Table(name="commentaire_dit_or")
 * @ORM\HasLifecycleCallbacks
 */
class CommentaireDitOr
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id")
     */
    private $utilisateurId;

    /**
     * @ORM\Column(type="string", length=11, name="num_dit",nullable=true)
     */
    private ?string $numDit;

    /**
     * @ORM\Column(type="string", length=50, name="num_or",nullable=true)
     */
    private ?string $numOr;

    /**
     * @ORM\Column(type="string", length=3, name="type_commentaire")
     */
    private ?string $typeCommentaire;

    /**
     * @ORM\Column(type="text", name="commentaire")
     */
    private $commentaire;

    /**
     * @ORM\Column(type="datetime", length=11, name="numero_demande_dit")
     */
    private $dateCreation;

    //==============================================================================================================================
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of utilisateurId
     */
    public function getUtilisateurId()
    {
        return $this->utilisateurId;
    }

    /**
     * Set the value of utilisateurId
     *
     * @return  self
     */
    public function setUtilisateurId($utilisateurId)
    {
        $this->utilisateurId = $utilisateurId;

        return $this;
    }

    /**
     * Get the value of numDit
     */
    public function getNumDit()
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     *
     * @return  self
     */
    public function setNumDit($numDit)
    {
        $this->numDit = $numDit;

        return $this;
    }

    /**
     * Get the value of numOr
     */
    public function getNumOr()
    {
        return $this->numOr;
    }

    /**
     * Set the value of numOr
     *
     * @return  self
     */
    public function setNumOr($numOr)
    {
        $this->numOr = $numOr;

        return $this;
    }

    /**
     * Get the value of typeCommentaire
     */
    public function getTypeCommentaire()
    {
        return $this->typeCommentaire;
    }

    /**
     * Set the value of typeCommentaire
     *
     * @return  self
     */
    public function setTypeCommentaire($typeCommentaire)
    {
        $this->typeCommentaire = $typeCommentaire;

        return $this;
    }

    /**
     * Get the value of commentaire
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set the value of commentaire
     *
     * @return  self
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get the value of dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDateCreation()
    {
        $this->dateCreation = new DateTime();
    }
}
