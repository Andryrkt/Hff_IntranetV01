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
     * @ORM\Column(type="integer", name="id_bcc_neg")
     */
    private $idBccNeg;

    /**
     * @ORM\Column(type="string", length=100, name="numero_bcc_neg", nullable=true)
     */
    private $numeroBccNeg;

    /**
     * @ORM\Column(type="string", name="numero_devis", length=50, nullable=true)
     */
    private $numeroDevis;
    
    /**
     * @ORM\Column(type="string", name="statut_bcc_neg", nullable=true)
     */
    private $statutBccNeg;

    /**
     * @ORM\Column(type="integer", name="numero_version", nullable=true)
     */
    private $numeroVersion;

    /**
     * @ORM\Column(type="string", name="id_tiroir", length=255, nullable=true)
     */
    private $idTiroir;

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

    // Getters and setters for each property...



    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of idBccNeg
     */
    public function getIdBccNeg()
    {
        return $this->idBccNeg;
    }

    /**
     * Set the value of idBccNeg
     */
    public function setIdBccNeg($idBccNeg): self
    {
        $this->idBccNeg = $idBccNeg;

        return $this;
    }

    /**
     * Get the value of numeroBccNeg
     */
    public function getNumeroBccNeg()
    {
        return $this->numeroBccNeg;
    }

    /**
     * Set the value of numeroBccNeg
     */
    public function setNumeroBccNeg($numeroBccNeg): self
    {
        $this->numeroBccNeg = $numeroBccNeg;

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
     */
    public function setNumeroDevis($numeroDevis): self
    {
        $this->numeroDevis = $numeroDevis;

        return $this;
    }

    /**
     * Get the value of statutBccNeg
     */
    public function getStatutBccNeg()
    {
        return $this->statutBccNeg;
    }

    /**
     * Set the value of statutBccNeg
     */
    public function setStatutBccNeg($statutBccNeg): self
    {
        $this->statutBccNeg = $statutBccNeg;

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
     */
    public function setNumeroVersion($numeroVersion): self
    {
        $this->numeroVersion = $numeroVersion;

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
     */
    public function setIdTiroir($idTiroir): self
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
     */
    public function setDateCreation($dateCreation): self
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
     */
    public function setHeureCreation($heureCreation): self
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
     */
    public function setDateDerniereModification($dateDerniereModification): self
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
     */
    public function setHeureDerniereModification($heureDerniereModification): self
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
     */
    public function setExtensionFichier($extensionFichier): self
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
     */
    public function setTotalPage($totalPage): self
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
     */
    public function setTailleFichier($tailleFichier): self
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
     */
    public function setPath($path): self
    {
        $this->path = $path;

        return $this;
    }
}
