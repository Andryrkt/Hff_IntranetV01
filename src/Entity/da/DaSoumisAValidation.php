<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use App\Repository\da\DaSoumisAValidationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DaSoumisAValidationRepository::class)
 * @ORM\Table(name="da_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DaSoumisAValidation
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12, name="numero_demande_appro")
     */
    private string $numeroDemandeAppro;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     *
     * @var integer | null
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="string", length=100, name="statut")
     */
    private string $statut;

    /**
     * @ORM\Column(type="datetime", name="date_soumission")
     */
    private $dateSoumission;

    /**
     * @ORM\Column(type="datetime", name="date_validation", nullable=true)
     */
    private $dateValidation;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $utilisateur = '';

    public function __construct()
    {
        $this->dateSoumission = new \DateTime();
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
     * Get the value of numeroDemandeAppro
     */
    public function getNumeroDemandeAppro()
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     *
     * @return  self
     */
    public function setNumeroDemandeAppro($numeroDemandeAppro)
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;

        return $this;
    }

    /**
     * Get | null
     *
     * @return  integer
     */
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set | null
     *
     * @param  integer  $numeroVersion  | null
     *
     * @return  self
     */
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }

    /**
     * Get the value of statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of dateSoumission
     */
    public function getDateSoumission()
    {
        return $this->dateSoumission;
    }

    /**
     * Set the value of dateSoumission
     *
     * @return  self
     */
    public function setDateSoumission($dateSoumission)
    {
        $this->dateSoumission = $dateSoumission;

        return $this;
    }

    /**
     * Get the value of dateValidation
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Set the value of dateValidation
     *
     * @return  self
     */
    public function setDateValidation($dateValidation)
    {
        $this->dateValidation = $dateValidation;

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
}
