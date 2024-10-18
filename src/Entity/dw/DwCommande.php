<?php

namespace App\Entity\dw;

use App\Entity\dw\DwTiroir;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\dw\DwOrdreDeReparation;
use App\Repository\dw\DwCommandeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass=DwCommandeRepository::class)
 * @ORM\Table(name="DW_Commande")
 * @ORM\HasLifecycleCallbacks
 */
class DwCommande
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_cde")
     */
    private $idCde;

    /**
     * @ORM\Column(type="string", length=8, name="id_tiroir")
     */
    private $numeroCde;


    /**
     * @ORM\Column(type="string", length=8, name="numero_or")
     */
    private $numeroOR;

    /**
     * @ORM\Column(type="date", name="date_creation")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="time", name="heure_creation")
     */
    private $heureCreation;

    /**
     * @ORM\Column(type="date", name="date_derniere_modification")
     */
    private $dateDerniereModification;

    /**
     * @ORM\Column(type="time", name="heure_derniere_modification")
     */
    private $heureDerniereModification;

    /**
     * @ORM\Column(type="integer", name="total_page")
     */
    private $totalPage;

    /**
     * @ORM\Column(type="string", length=50, name="extension_fichier")
     */
    private $extensionFichier;

    /**
     * @ORM\Column(type="integer", name="taille_fichier")
     */
    private $tailleFichier;

    /**
     * @ORM\Column(type="string", length=255, name="path")
     */
    private $path;

    /**
 * @ORM\ManyToOne(targetEntity=DwTiroir::class, inversedBy="commande")
 * @ORM\JoinColumn(name="id_tiroir", referencedColumnName="id_tiroir", nullable=true)
 */
private $tiroir;

    /**
     * @ORM\ManyToMany(targetEntity=DwOrdreDeReparation::class, inversedBy="commandes")
     * @ORM\JoinTable(name="dw_commande_ordre_reparation")
     */
    private $ordresDeReparation;

    /** ===========================================================================
 * getteur and setteur
 *
 * ================================================================================
 */

    public function __construct()
    {
        $this->ordresDeReparation = new ArrayCollection();
    }


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get the value of idCde
     */ 
    public function getIdCde()
    {
        return $this->idCde;
    }

    /**
     * Set the value of idCde
     *
     * @return  self
     */ 
    public function setIdCde($idCde)
    {
        $this->idCde = $idCde;

        return $this;
    }
    
    /**
     * Get the value of numeroCde
     */ 
    public function getNumeroCde()
    {
        return $this->numeroCde;
    }

    /**
     * Set the value of numeroCde
     *
     * @return  self
     */ 
    public function setNumeroCde($numeroCde)
    {
        $this->numeroCde = $numeroCde;

        return $this;
    }


    /**
     * Get the value of numeroOR
     */ 
    public function getNumeroOR()
    {
        return $this->numeroOR;
    }

    /**
     * Set the value of numeroOR
     *
     * @return  self
     */ 
    public function setNumeroOR($numeroOR)
    {
        $this->numeroOR = $numeroOR;

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

    public function getTiroir(): ?DwTiroir
    {
        return $this->tiroir;
    }

    public function setTiroir(?DwTiroir $tiroir): self
    {
        $this->tiroir = $tiroir;

        return $this;
    }

    /**
     * @return Collection|DwOrdreDeReparation[]
     */
    public function getOrdresDeReparation(): Collection
    {
        return $this->ordresDeReparation;
    }

    public function addOrdreDeReparation(DwOrdreDeReparation $ordreDeReparation): self
    {
        if (!$this->ordresDeReparation->contains($ordreDeReparation)) {
            $this->ordresDeReparation[] = $ordreDeReparation;
        }

        return $this;
    }

    public function removeOrdreDeReparation(DwOrdreDeReparation $ordreDeReparation): self
    {
        $this->ordresDeReparation->removeElement($ordreDeReparation);

        return $this;
    }

    
}