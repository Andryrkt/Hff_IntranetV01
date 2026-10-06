<?php

namespace App\Entity\tik;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Repository\tik\TkiPlanningRepository;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\tik\TkiReplannification;

/**
 * @ORM\Entity(repositoryClass=TkiPlanningRepository::class)
 * @ORM\Table(name="TKI_Planning")
 * @ORM\HasLifecycleCallbacks
 */
class TkiPlanning
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_ticket")
     */
    private $numeroTicket;

    /**
     * @ORM\Column(type="string", length=100, name="Objet_Demande")
     */
    private string $objetDemande;

    /**
     * @ORM\Column(type="string", length=5000, name="Detail_Demande")
     */
    private string $detailDemande;


    /**
     * @ORM\Column(type="datetime", name="date_heure_debut_planning")
     */
    private $dateDebutPlanning;

    /**
     * @ORM\Column(type="datetime", name="date_heure_fin_planning")
     */
    private $dateFinPlanning;

    /**
     * @ORM\OneToOne(targetEntity=DemandeSupportInformatique::class, inversedBy="planning", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="demande_id", referencedColumnName="ID_Demande_Support_Informatique", nullable=true)
     */
    private $demandeSupportInfo;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * 
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=TkiReplannification::class, mappedBy="planning")
     */
    private $replanificationPlanning;

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
     * Get the value of objetDemande
     */
    public function getObjetDemande()
    {
        return $this->objetDemande;
    }

    /**
     * Set the value of objetDemande
     *
     * @return  self
     */
    public function setObjetDemande($objetDemande)
    {
        $this->objetDemande = $objetDemande;

        return $this;
    }

    /**
     * Get the value of detailDemande
     */
    public function getDetailDemande()
    {
        return $this->detailDemande;
    }

    /**
     * Set the value of detailDemande
     *
     * @return  self
     */
    public function setDetailDemande($detailDemande)
    {
        $this->detailDemande = $detailDemande;

        return $this;
    }

    /**
     * Get the value of dateDebutPlanning
     */
    public function getDateDebutPlanning()
    {
        return $this->dateDebutPlanning;
    }

    /**
     * Set the value of dateDebutPlanning
     *
     * @return  self
     */
    public function setDateDebutPlanning($dateDebutPlanning)
    {
        $this->dateDebutPlanning = $dateDebutPlanning;

        return $this;
    }

    /**
     * Get the value of dateFinPlanning
     */
    public function getDateFinPlanning()
    {
        return $this->dateFinPlanning;
    }

    /**
     * Set the value of dateFinPlanning
     *
     * @return  self
     */
    public function setDateFinPlanning($dateFinPlanning)
    {
        $this->dateFinPlanning = $dateFinPlanning;

        return $this;
    }

    /**
     * Get the value of demandeSupportInfo
     */
    public function getDemandeSupportInfo()
    {
        return $this->demandeSupportInfo;
    }

    /**
     * Set the value of demandeSupportInfo
     *
     * @return  self
     */
    public function setDemandeSupportInfo($demandeSupportInfo)
    {
        $this->demandeSupportInfo = $demandeSupportInfo;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of replanificationPlanning
     */
    public function getReplanificationPlanning()
    {
        return $this->replanificationPlanning;
    }

    /**
     * Set the value of replanificationPlanning
     *
     * @return  self
     */
    public function setReplanificationPlanning($replanificationPlanning)
    {
        $this->replanificationPlanning = $replanificationPlanning;

        return $this;
    }
}
