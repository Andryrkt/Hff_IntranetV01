<?php

namespace App\Entity\admin\tik;

use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\Traits\DateTrait;
use App\Repository\admin\tik\TkiCommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiCommentaireRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class TkiCommentaires
{
    use DateTrait;

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
     * @ORM\ManyToOne(targetEntity=DemandeSupportInformatique::class, inversedBy="commentaires")
     * @ORM\JoinColumn(name="id_demande_support", referencedColumnName="ID_Demande_Support_Informatique")
     */
    private $demandeSupportInformatique;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="commentaires")
     * @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="json", name="fichiers_detail")
     */
    private $fileNames = [];

    public function __construct($numeroTicket = '', $nomUtilisateur = '')
    {
        $this->numeroTicket = $numeroTicket;
        $this->nomUtilisateur = $nomUtilisateur;
    }

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
     * Get the value of nomUtilisateur
     */
    public function getNomUtilisateur()
    {
        return $this->nomUtilisateur;
    }

    /**
     * Set the value of nomUtilisateur
     *
     * @return  self
     */
    public function setNomUtilisateur($nomUtilisateur)
    {
        $this->nomUtilisateur = $nomUtilisateur;

        return $this;
    }

    /**
     * Get the value of commentaires
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * Set the value of commentaires
     *
     * @return  self
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get the value of piecesJointes1
     */
    public function getPiecesJointes1()
    {
        return $this->piecesJointes1;
    }

    /**
     * Set the value of piecesJointes1
     *
     * @return  self
     */
    public function setPiecesJointes1($piecesJointes1)
    {
        $this->piecesJointes1 = $piecesJointes1;

        return $this;
    }

    /**
     * Get the value of piecesJointes2
     */
    public function getPiecesJointes2()
    {
        return $this->piecesJointes2;
    }

    /**
     * Set the value of piecesJointes2
     *
     * @return  self
     */
    public function setPiecesJointes2($piecesJointes2)
    {
        $this->piecesJointes2 = $piecesJointes2;

        return $this;
    }

    /**
     * Get the value of piecesJointes3
     */
    public function getPiecesJointes3()
    {
        return $this->piecesJointes3;
    }

    /**
     * Set the value of piecesJointes3
     *
     * @return  self
     */
    public function setPiecesJointes3($piecesJointes3)
    {
        $this->piecesJointes3 = $piecesJointes3;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of demandeSupportInformatique
     */
    public function getDemandeSupportInformatique()
    {
        return $this->demandeSupportInformatique;
    }

    /**
     * Set the value of demandeSupportInformatique
     *
     * @return  self
     */
    public function setDemandeSupportInformatique($demandeSupportInformatique)
    {
        $this->demandeSupportInformatique = $demandeSupportInformatique;

        return $this;
    }

    /**
     * Get the value of fileNames
     */
    public function getFileNames()
    {
        return $this->fileNames;
    }

    /**
     * Set the value of fileNames
     *
     * @return  self
     */
    public function setFileNames($fileNames)
    {
        $this->fileNames = $fileNames;

        return $this;
    }
}
