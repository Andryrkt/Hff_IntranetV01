<?php

namespace App\Entity\bl;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\bl\BLSoumissionRepository;

/**
 * @ORM\Entity(repositoryClass=BLSoumissionRepository::class)
 * @ORM\Table(name="bl_soumission")
 * @ORM\HasLifecycleCallbacks
 */
class BLSoumission
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Mouvement_Materiel")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, name="agence_user")
     */
    private $agenceUser;

    /**
     * @ORM\Column(type="string", length=100, name="service_user")
     */
    private $serviceUser;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur")
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="string", length=255, name="path_fichier_soumis")
     */
    private $pathFichierSoumis;



    // Getters and Setters for each property
    public function getId()
    {
        return $this->id;
    }

    public function getAgenceUser()
    {
        return $this->agenceUser;
    }

    public function setAgenceUser($agenceUser)
    {
        $this->agenceUser = $agenceUser;
        return $this;
    }

    public function getServiceUser()
    {
        return $this->serviceUser;
    }

    public function setServiceUser($serviceUser)
    {
        $this->serviceUser = $serviceUser;
        return $this;
    }

    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getPathFichierSoumis()
    {
        return $this->pathFichierSoumis;
    }

    public function setPathFichierSoumis($pathFichierSoumis)
    {
        $this->pathFichierSoumis = $pathFichierSoumis;
        return $this;
    }
}
