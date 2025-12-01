<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dw\DwDaDirectRepository;

/**
 * @ORM\Entity(repositoryClass=DwDaDirectRepository::class)
 * @ORM\Table(name="DW_DA_Direct")
 */
class DwDaDirect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_da_dir", nullable=true)
     */
    private $idDaDirect;

    /**
     * @ORM\Column(type="string", length=50, name="numero_da_dir", nullable=true)
     */
    private $numeroDaDirect;

    /**
     * @ORM\Column(type="string", length=50, name="statut_da_dir", nullable=true)
     */
    private $statutDaDirect;

    /**
     * @ORM\Column(type="string", name="id_tiroir", length=255, nullable=true)
     */
    private $idTiroir;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private $numeroVersion;

    /**
     * @ORM\Column(type="date", name="date_creation", nullable=true)
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="time", name="heure_creation", nullable=true)
     */
    private $heureCreation;

    /**
     * @ORM\Column(type="date", name="date_derniere_modification", nullable=true)
     */
    private $dateDerniereModification;


    /**
     * @ORM\Column(type="time", name="heure_derniere_modification", nullable=true)
     */
    private $heureDerniereModification;

    /**
     * @ORM\Column(type="string", name="extension_fichier", nullable=true)
     */
    private $extensionFichier;

    /**
     * @ORM\Column(type="integer", name="total_page", nullable=true)
     */
    private $totalPage;

    /**
     * @ORM\Column(type="integer", name="taille_fichier", nullable=true)
     */
    private $tailleFichier;

    /**
     * @ORM\Column(type="string", name="path", length=255, nullable=true)
     */
    private $path;

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
     * Get the value of idDaDirect
     */
    public function getIdDaDirect()
    {
        return $this->idDaDirect;
    }

    /**
     * Set the value of idDaDirect
     *
     * @return  self
     */
    public function setIdDaDirect($idDaDirect)
    {
        $this->idDaDirect = $idDaDirect;

        return $this;
    }

    /**
     * Get the value of numeroDaDirect
     */
    public function getNumeroDaDirect()
    {
        return $this->numeroDaDirect;
    }

    /**
     * Set the value of numeroDaDirect
     *
     * @return  self
     */
    public function setNumeroDaDirect($numeroDaDirect)
    {
        $this->numeroDaDirect = $numeroDaDirect;

        return $this;
    }

    /**
     * Get the value of statutDaDirect
     */
    public function getStatutDaDirect()
    {
        return $this->statutDaDirect;
    }

    /**
     * Set the value of statutDaDirect
     *
     * @return  self
     */
    public function setStatutDaDirect($statutDaDirect)
    {
        $this->statutDaDirect = $statutDaDirect;

        return $this;
    }

    /**
     * Get the value of idTiroir
     */
    public function getIdTiroir()
    {
        return $this->idTiroir;
    }

    /**
     * Set the value of idTiroir
     *
     * @return  self
     */
    public function setIdTiroir($idTiroir)
    {
        $this->idTiroir = $idTiroir;

        return $this;
    }

    /**
     * Get the value of dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set the value of dateCreation
     *
     * @return  self
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get the value of heureCreation
     */
    public function getHeureCreation()
    {
        return $this->heureCreation;
    }

    /**
     * Set the value of heureCreation
     *
     * @return  self
     */
    public function setHeureCreation($heureCreation)
    {
        $this->heureCreation = $heureCreation;

        return $this;
    }

    /**
     * Get the value of dateDerniereModification
     */
    public function getDateDerniereModification()
    {
        return $this->dateDerniereModification;
    }

    /**
     * Set the value of dateDerniereModification
     *
     * @return  self
     */
    public function setDateDerniereModification($dateDerniereModification)
    {
        $this->dateDerniereModification = $dateDerniereModification;

        return $this;
    }

    /**
     * Get the value of heureDerniereModification
     */
    public function getHeureDerniereModification()
    {
        return $this->heureDerniereModification;
    }

    /**
     * Set the value of heureDerniereModification
     *
     * @return  self
     */
    public function setHeureDerniereModification($heureDerniereModification)
    {
        $this->heureDerniereModification = $heureDerniereModification;

        return $this;
    }

    /**
     * Get the value of extensionFichier
     */
    public function getExtensionFichier()
    {
        return $this->extensionFichier;
    }

    /**
     * Set the value of extensionFichier
     *
     * @return  self
     */
    public function setExtensionFichier($extensionFichier)
    {
        $this->extensionFichier = $extensionFichier;

        return $this;
    }

    /**
     * Get the value of totalPage
     */
    public function getTotalPage()
    {
        return $this->totalPage;
    }

    /**
     * Set the value of totalPage
     *
     * @return  self
     */
    public function setTotalPage($totalPage)
    {
        $this->totalPage = $totalPage;

        return $this;
    }

    /**
     * Get the value of tailleFichier
     */
    public function getTailleFichier()
    {
        return $this->tailleFichier;
    }

    /**
     * Set the value of tailleFichier
     *
     * @return  self
     */
    public function setTailleFichier($tailleFichier)
    {
        $this->tailleFichier = $tailleFichier;

        return $this;
    }

    /**
     * Get the value of path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of numeroVersion
     */ 
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     *
     * @return  self
     */ 
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }
}
