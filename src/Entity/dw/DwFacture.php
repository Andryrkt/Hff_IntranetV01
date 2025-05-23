<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dw\DwFactureRepository;


/**
 * @ORM\Entity(repositoryClass=DwFactureRepository::class)
 * @ORM\Table(name="DW_Facture")
 * @ORM\HasLifecycleCallbacks
 */
class DwFacture
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_fac")
     */
    private $idFac;

    /**
     * @ORM\Column(type="string", length=8, name="numero_fac")
     */
    private $numeroFac;

    /**
     * @ORM\Column(type="string", length=100, name="id_tiroir")
     */
    private $idTiroir;

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
     * @ORM\ManyToOne(targetEntity=DwOrdreDeReparation::class, inversedBy="factures")
     * @ORM\JoinColumn(name="numero_or", referencedColumnName="numero_or", nullable=true)
     */
    private $ordreDeReparation;

    /** ===========================================================================
 * getteur and setteur
 *
 * ================================================================================
 */

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of idFac
     */ 
    public function getIdFac()
    {
        return $this->idFac;
    }

    /**
     * Set the value of idFac
     *
     * @return  self
     */ 
    public function setIdFac($idFac)
    {
        $this->idFac = $idFac;

        return $this;
    }
    
    /**
     * Get the value of numeroFac
     *
     * @return  [type]
     */ 
    public function getNumeroFac()
    {
        return $this->numeroFac;
    }

    /**
     * Set the value of numeroFac
     *
     * @param  [type]  $numeroFac
     *
     * @return  self
     */ 
    public function setNumeroFac($numeroFac)
    {
        $this->numeroFac = $numeroFac;

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

    // Getter et setter pour ordreDeReparation
    public function getOrdreDeReparation(): ?DwOrdreDeReparation
    {
        return $this->ordreDeReparation;
    }

    public function setOrdreDeReparation(?DwOrdreDeReparation $ordreDeReparation): self
    {
        $this->ordreDeReparation = $ordreDeReparation;

        return $this;
    }

}