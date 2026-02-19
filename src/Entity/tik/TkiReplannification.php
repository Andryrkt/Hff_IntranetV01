<?php

namespace App\Entity\tik;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Repository\tik\TkiReplannificationRepository;
use App\Entity\tik\DemandeSupportInformatique;

/**
 * @ORM\Entity(repositoryClass=TkiReplannificationRepository::class)
 * @ORM\Table(name="TKI_Replannification")
 * @ORM\HasLifecycleCallbacks
 */
class TkiReplannification
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
     * @ORM\Column(type="datetime", name="old_date_heure_debut_planning")
     */
    private $oldDateDebutPlanning;

    /**
     * @ORM\Column(type="datetime", name="old_date_heure_fin_planning")
     */
    private $oldDateFinPlanning;

    /**
     * @ORM\Column(type="datetime", name="new_date_heure_debut_planning")
     */
    private $newDateDebutPlanning;

    /**
     * @ORM\Column(type="datetime", name="new_date_heure_fin_planning")
     */
    private $newDateFinPlanning;

    /**
     * @ORM\OneToOne(targetEntity=DemandeSupportInformatique::class, inversedBy="replanification", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="demande_id", referencedColumnName="ID_Demande_Support_Informatique", nullable=true)
     */
    private $demandeSupportInfo;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=TkiPlanning::class, inversedBy="replanificationPlanning")
     * @ORM\JoinColumn(name="planning_id", referencedColumnName="id")
     */
    private $planning;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

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
     * Get the value of oldDateDebutPlanning
     */
    public function getOldDateDebutPlanning()
    {
        return $this->oldDateDebutPlanning;
    }

    /**
     * Set the value of oldDateDebutPlanning
     *
     * @return  self
     */
    public function setOldDateDebutPlanning($oldDateDebutPlanning)
    {
        $this->oldDateDebutPlanning = $oldDateDebutPlanning;

        return $this;
    }

    /**
     * Get the value of oldDateFinPlanning
     */
    public function getOldDateFinPlanning()
    {
        return $this->oldDateFinPlanning;
    }

    /**
     * Set the value of oldDateFinPlanning
     *
     * @return  self
     */
    public function setOldDateFinPlanning($oldDateFinPlanning)
    {
        $this->oldDateFinPlanning = $oldDateFinPlanning;

        return $this;
    }

    /**
     * Get the value of newDateDebutPlanning
     */
    public function getNewDateDebutPlanning()
    {
        return $this->newDateDebutPlanning;
    }

    /**
     * Set the value of newDateDebutPlanning
     *
     * @return  self
     */
    public function setNewDateDebutPlanning($newDateDebutPlanning)
    {
        $this->newDateDebutPlanning = $newDateDebutPlanning;

        return $this;
    }

    /**
     * Get the value of newDateFinPlanning
     */
    public function getNewDateFinPlanning()
    {
        return $this->newDateFinPlanning;
    }

    /**
     * Set the value of newDateFinPlanning
     *
     * @return  self
     */
    public function setNewDateFinPlanning($newDateFinPlanning)
    {
        $this->newDateFinPlanning = $newDateFinPlanning;

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
     * Get the value of planning
     */
    public function getPlanning()
    {
        return $this->planning;
    }

    /**
     * Set the value of planning
     *
     * @return  self
     */
    public function setPlanning($planning)
    {
        $this->planning = $planning;

        return $this;
    }
}
