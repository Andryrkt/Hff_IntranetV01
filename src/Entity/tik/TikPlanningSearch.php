<?php

namespace App\Entity\tik;

use App\Entity\admin\utilisateur\User;

class TikPlanningSearch
{
    private ?string $demandeur = '';

    private ?User $nomIntervenant = null;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    /**
     * Get the value of demandeur
     */
    public function getDemandeur()
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     *
     * @return  self
     */
    public function setDemandeur($demandeur)
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    /**
     * Get the value of nomIntervenant
     */
    public function getNomIntervenant()
    {
        return $this->nomIntervenant;
    }

    /**
     * Set the value of nomIntervenant
     *
     * @return  self
     */
    public function setNomIntervenant($nomIntervenant)
    {
        $this->nomIntervenant = $nomIntervenant;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'demandeur'       => $this->demandeur,
            'nomIntervenant'  => $this->nomIntervenant === null ? null : $this->nomIntervenant->getId(),
        ];
    }
}
