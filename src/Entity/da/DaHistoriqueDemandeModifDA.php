<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="historique_demande_modif_DA")
 * @ORM\HasLifecycleCallbacks
 */
class DaHistoriqueDemandeModifDA
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_appro")
     */
    private string $numDa;

    /**
     * @ORM\Column(type="string", length=100, name="demandeur")
     */
    private ?string $demandeur = '';

    /**
     * @ORM\Column(type="string", name="motif")
     */
    private ?string $motif = '';

    /**
     * @ORM\ManyToOne(targetEntity=DemandeAppro::class, inversedBy="historiqueDemandeModifDA")
     * @ORM\JoinColumn(name="demande_appro_id", referencedColumnName="id", nullable=false)
     */
    private ?DemandeAppro $demandeAppro;

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
     * Get the value of numDa
     */
    public function getNumDa()
    {
        return $this->numDa;
    }

    /**
     * Set the value of numDa
     *
     * @return  self
     */
    public function setNumDa($numDa)
    {
        $this->numDa = $numDa;

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
     * Get the value of motif
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set the value of motif
     *
     * @return  self
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;

        return $this;
    }

    /**
     * Get the value of demandeAppro
     */
    public function getDemandeAppro()
    {
        return $this->demandeAppro;
    }

    /**
     * Set the value of demandeAppro
     *
     * @return  self
     */
    public function setDemandeAppro($demandeAppro)
    {
        $this->demandeAppro = $demandeAppro;

        return $this;
    }
}
