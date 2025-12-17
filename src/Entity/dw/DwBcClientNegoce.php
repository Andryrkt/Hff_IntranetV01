<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dw\DwBcClientNegoceRepository;

/**
 * @ORM\Entity(repositoryClass=DwBcClientNegoceRepository::class)
 * @ORM\Table(name="DW_BC_Client_Negoce")
 */
class DwBcClientNegoce
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_bc")
     */
    private $idBc;

    /**
     * @ORM\Column(type="string", length=8, name="numero_bc")
     */
    private $numeroBc;


    /**
     * @ORM\Column(type="string", name="nom_document", length=255, nullable=true)
     */
    private $nomDocument;


    /**
     * @ORM\Column(type="string", name="id_tiroir", length=255, nullable=true)
     */
    private $idTiroir;


    /**
     * @ORM\Column(type="string", name="numero_dit", length=11, nullable=true)
     */
    private $numeroDit;


    /**
     * @ORM\Column(type="string", name="numero_devis", length=11, nullable=true)
     */
    private $numeroDevis;

    /**
     * @ORM\Column(type="integer", name="numero_version", nullable=true)
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
     * @ORM\Column(type="string", name="statut_bc", nullable=true)
     */
    private $statutBc;


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


    // Getters and setters for each property...

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of idBc
     */
    public function getIdBc()
    {
        return $this->idBc;
    }

    /**
     * Set the value of idBc
     *
     * @return  self
     */
    public function setIdBc($idBc)
    {
        $this->idBc = $idBc;

        return $this;
    }

    /**
     * Get the value of numeroBc
     */
    public function getNumeroBc()
    {
        return $this->numeroBc;
    }

    /**
     * Set the value of numeroBc
     *
     * @return  self
     */
    public function setNumeroBc($numeroBc)
    {
        $this->numeroBc = $numeroBc;

        return $this;
    }

    /**
     * Get the value of nomDocument
     */
    public function getNomDocument()
    {
        return $this->nomDocument;
    }

    /**
     * Set the value of nomDocument
     *
     * @return  self
     */
    public function setNomDocument($nomDocument)
    {
        $this->nomDocument = $nomDocument;

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
     * Get the value of numeroDit
     */
    public function getNumeroDit()
    {
        return $this->numeroDit;
    }

    /**
     * Set the value of numeroDit
     *
     * @return  self
     */
    public function setNumeroDit($numeroDit)
    {
        $this->numeroDit = $numeroDit;

        return $this;
    }

    /**
     * Get the value of numeroDevis
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     *
     * @return  self
     */
    public function setNumeroDevis($numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;

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
     * Get the value of statutBc
     */
    public function getStatutBc()
    {
        return $this->statutBc;
    }

    /**
     * Set the value of statutBc
     *
     * @return  self
     */
    public function setStatutBc($statutBc)
    {
        $this->statutBc = $statutBc;

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
}
