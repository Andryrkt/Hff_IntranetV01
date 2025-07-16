<?php

namespace App\Entity\dom;

use App\Entity\Traits\DateTrait;
use App\Repository\da\DomHistoriqueComplementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DomHistoriqueComplementRepository::class)
 * @ORM\Table(name="historique_dom_complement")
 * @ORM\HasLifecycleCallbacks
 */
class DomHistoriqueComplement
{
    use DateTrait;

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
     * @ORM\Column(type="string", length=100, name="demandeur")
     */
    private string $demandeur;

    /**
     * @ORM\OneToOne(targetEntity=Dom::class)
     * @ORM\JoinColumn(name="id_dom", referencedColumnName="id")
     */
    private $dom;

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
     * Get the value of dom
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * Set the value of dom
     *
     * @return  self
     */
    public function setDom($dom)
    {
        $this->dom = $dom;

        return $this;
    }
}
