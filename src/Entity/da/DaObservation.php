<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\da\DaObservationRepository;

/**
 * @ORM\Entity(repositoryClass=DaObservationRepository::class)
 * @ORM\Table(name="da_observation")
 * @ORM\HasLifecycleCallbacks
 */
class DaObservation
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_da")
     */
    private string $numDa;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur")
     */
    private ?string $utilisateur = '';

    /**
     * @ORM\Column(type="string", name="observation")
     *
     * @var string|NULL
     */
    private ?string $observation = '';

    /**
     * @ORM\Column(type="json", name="file_names")
     */
    private $fileNames = [];

    private bool $statutChange = false;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
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
     * Get the value of observation
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get the value of statutChange
     */
    public function getStatutChange()
    {
        return $this->statutChange;
    }

    /**
     * Set the value of statutChange
     *
     * @return  self
     */
    public function setStatutChange($statutChange)
    {
        $this->statutChange = $statutChange;

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
     */
    public function setFileNames($fileNames): self
    {
        $this->fileNames = $fileNames;

        return $this;
    }
}
