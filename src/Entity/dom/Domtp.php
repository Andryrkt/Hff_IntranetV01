<?php

namespace App\Entity\dom;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Demande_ordre_mission_tp")
 * @ORM\HasLifecycleCallbacks
 */
class Domtp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Ordre_Mission")
     */
    private string $numeroOrdreMission;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Ordre_Mission_Tp")
     */
    private string $numeroOrdreMissionTp;

    /**
     * @ORM\Column(type="integer", name="Nombre_Jour_Tp")
     */
    private string $nombreJourTp;

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
     * Get the value of numeroOrdreMission
     */
    public function getNumeroOrdreMission()
    {
        return $this->numeroOrdreMission;
    }

    /**
     * Set the value of numeroOrdreMission
     *
     * @return  self
     */
    public function setNumeroOrdreMission($numeroOrdreMission)
    {
        $this->numeroOrdreMission = $numeroOrdreMission;

        return $this;
    }

    /**
     * Get the value of numeroOrdreMissionTp
     */
    public function getNumeroOrdreMissionTp()
    {
        return $this->numeroOrdreMissionTp;
    }

    /**
     * Set the value of numeroOrdreMissionTp
     *
     * @return  self
     */
    public function setNumeroOrdreMissionTp($numeroOrdreMissionTp)
    {
        $this->numeroOrdreMissionTp = $numeroOrdreMissionTp;

        return $this;
    }

    /**
     * Get the value of nombreJourTp
     */
    public function getNombreJourTp()
    {
        return $this->nombreJourTp;
    }

    /**
     * Set the value of nombreJourTp
     *
     * @return  self
     */
    public function setNombreJourTp($nombreJourTp)
    {
        $this->nombreJourTp = $nombreJourTp;

        return $this;
    }
}
