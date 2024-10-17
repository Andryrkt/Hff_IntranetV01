<?php

namespace App\Entity\dw;

use App\Entity\dw\DwCommande;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\dw\DwDemandeIntervention;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\dw\DwOrdreDeReparationRepository;


/**
 * @ORM\Entity(repositoryClass=DwOrdreDeReparationRepository::class)
 * @ORM\Table(name="DW_Ordre_De_Reparation")
 * @ORM\HasLifecycleCallbacks
 */
class DwOrdreDeReparation
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_or")
     */
    private $idOr;

    /**
     * @ORM\Column(type="string", length=8, name="numero_or")
     */
    private $numeroOR;

    /**
     * @ORM\Column(type="string", length=100, name="id_tiroir")
     */
    private $idTiroir;


    /**
      * @ORM\Column(type="string", length=11, name="numero_dit", unique=true)
      *
      * @var string
      */
    private string $numeroDit;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private $numeroVersion;


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
     * @ORM\Column(type="string", length=50, name="statut_or")
     */
    private $statutOr;

    /**
     * @ORM\Column(type="string", length=50, name="extension_fichier")
     */
    private $extensionFichier;

    /**
     * @ORM\Column(type="integer", name="total_page")
     */
    private $totalPage;


    /**
     * @ORM\Column(type="integer", name="taille_fichier")
     */
    private $tailleFichier;

    /**
     * @ORM\Column(type="string", length=255, name="path")
     */
    private $path;

    /**
     * @ORM\OneToOne(targetEntity=DwDemandeIntervention::class, inversedBy="ordreDeReparation")
     * @ORM\JoinColumn(name="numero_dit", referencedColumnName="numero_dit", unique=true)
     */
    private $demandeIntervention;

    /**
     * @ORM\ManyToMany(targetEntity=DwCommande::class, mappedBy="ordresDeReparation")
     */
    private $commandes;

      /**
     * @ORM\OneToMany(targetEntity=DwFacture::class, mappedBy="ordreDeReparation")
     */
    private $factures;

    /**
     * @ORM\ManyToOne(targetEntity=DwTiroir::class, inversedBy="ordresDeReparation")
     * @ORM\JoinColumn(name="id_tiroir", referencedColumnName="id_tiroir", nullable=false)
     */
    private $tiroir;

    /**
     * @ORM\OneToMany(targetEntity=DwRapportIntervention::class, mappedBy="ordreDeReparation")
     */
    private $rapportsIntervention;

    /** ===========================================================================
     * getteur and setteur
     *
     * ================================================================================
     */

     public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->rapportsIntervention = new ArrayCollection();
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of idOr
     */ 
    public function getIdOr()
    {
        return $this->idOr;
    }

    /**
     * Set the value of idOr
     *
     * @return  self
     */ 
    public function setIdOr($idOr)
    {
        $this->idOr = $idOr;

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
     * Get the value of statutOr
     */ 
    public function getStatutOr()
    {
        return $this->statutOr;
    }

    /**
     * Set the value of statutOr
     *
     * @return  self
     */ 
    public function setStatutOr($statutOr)
    {
        $this->statutOr = $statutOr;

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

     

    // Getter et setter pour demandeIntervention
    public function getDemandeIntervention(): ?DwDemandeIntervention
    {
        return $this->demandeIntervention;
    }

    public function setDemandeIntervention(?DwDemandeIntervention $demandeIntervention): self
    {
        $this->demandeIntervention = $demandeIntervention;
        return $this;
    }

     /**
     * @return Collection|DwCommande[]
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(DwCommande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->addOrdreDeReparation($this);
        }

        return $this;
    }

    public function removeCommande(DwCommande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            $commande->removeOrdreDeReparation($this);
        }

        return $this;
    }

    /**
     * @return Collection|DwFacture[]
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(DwFacture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures[] = $facture;
            $facture->setOrdreDeReparation($this);
        }

        return $this;
    }

    public function removeFacture(DwFacture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getOrdreDeReparation() === $this) {
                $facture->setOrdreDeReparation(null);
            }
        }

        return $this;
    }

    // Getter et setter pour tiroir
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
     * @return Collection|DwRapportIntervention[]
     */
    public function getRapportsIntervention(): Collection
    {
        return $this->rapportsIntervention;
    }

    public function addRapportIntervention(DwRapportIntervention $rapportIntervention): self
    {
        if (!$this->rapportsIntervention->contains($rapportIntervention)) {
            $this->rapportsIntervention[] = $rapportIntervention;
            $rapportIntervention->setOrdreDeReparation($this);
        }

        return $this;
    }

    public function removeRapportIntervention(DwRapportIntervention $rapportIntervention): self
    {
        if ($this->rapportsIntervention->removeElement($rapportIntervention)) {
            // set the owning side to null (unless already changed)
            if ($rapportIntervention->getOrdreDeReparation() === $this) {
                $rapportIntervention->setOrdreDeReparation(null);
            }
        }

        return $this;
    }

}