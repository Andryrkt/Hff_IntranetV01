<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dw\DwContratRepository;

/**
 * @ORM\Entity(repositoryClass=DwContratRepository::class)
 * @ORM\Table(name="DW_Contrat")
 */
class DwContrat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_contrat", nullable=true)
     */
    private $idContrat;

    /**
     * @ORM\Column(type="string", length=255, name="ref_contrat", nullable=true)
     */
    private $reference;

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
     * Get the value of idContrat
     */
    public function getIdContrat()
    {
        return $this->idContrat;
    }

    /**
     * Set the value of idContrat
     */
    public function setIdContrat($idContrat): self
    {
        $this->idContrat = $idContrat;

        return $this;
    }

    /**
     * Get the value of reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     */
    public function setReference($reference): self
    {
        $this->reference = $reference;

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
