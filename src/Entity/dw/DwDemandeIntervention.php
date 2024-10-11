<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dw\DwDemandeInterventionRepository;


/**
 * @ORM\Entity(repositoryClass=DwDemandeInterventionRepository::class)
 * @ORM\Table(name="DW_Demande_Intervention")
 * @ORM\HasLifecycleCallbacks
 */
class DwDemandeIntervention
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_dit")
     */
    private int $id;


    /**
     * @ORM\Column(type="string", length=11, name='numero_dit')
     */
    private $numeroDit;

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
     * @ORM\Column(type="string", length=50, name="extension_fichier")
     */
    private $extensionFichier;

    /**
     * @ORM\Column(type="string", length=100, name="type_reparation")
     */
    private $typeReparation;

    /**
     * @ORM\Column(type="string", length=11, name="id_materiel")
     */
    private $idMateriel;

    /**
     * @ORM\Column(type="string", length=50, name="numero_parc")
     */
    private $numeroParc;
    
    /**
     * @ORM\Column(type="string", length=100, name="numero_serie")
     */
    private $numeroSerie;

    /**
     * @ORM\Column(type="string", length=255, name="designation_materiel")
     */
    private $designationMateriel;

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
     * Get the value of typeReparation
     */ 
    public function getTypeReparation()
    {
        return $this->typeReparation;
    }

    /**
     * Set the value of typeReparation
     *
     * @return  self
     */ 
    public function setTypeReparation($typeReparation)
    {
        $this->typeReparation = $typeReparation;

        return $this;
    }

    /**
     * Get the value of idMateriel
     */ 
    public function getIdMateriel()
    {
        return $this->idMateriel;
    }

    /**
     * Set the value of idMateriel
     *
     * @return  self
     */ 
    public function setIdMateriel($idMateriel)
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    /**
     * Get the value of numeroParc
     */ 
    public function getNumeroParc()
    {
        return $this->numeroParc;
    }

    /**
     * Set the value of numeroParc
     *
     * @return  self
     */ 
    public function setNumeroParc($numeroParc)
    {
        $this->numeroParc = $numeroParc;

        return $this;
    }

    /**
     * Get the value of numeroSerie
     */ 
    public function getNumeroSerie()
    {
        return $this->numeroSerie;
    }

    /**
     * Set the value of numeroSerie
     *
     * @return  self
     */ 
    public function setNumeroSerie($numeroSerie)
    {
        $this->numeroSerie = $numeroSerie;

        return $this;
    }

    /**
     * Get the value of designationMateriel
     */ 
    public function getDesignationMateriel()
    {
        return $this->designationMateriel;
    }

    /**
     * Set the value of designationMateriel
     *
     * @return  self
     */ 
    public function setDesignationMateriel($designationMateriel)
    {
        $this->designationMateriel = $designationMateriel;

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