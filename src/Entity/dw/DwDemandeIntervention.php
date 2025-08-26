<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\dw\DwOrdreDeReparation;
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
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_dit")
     */
    private $idDit;

    /**
     * @ORM\Column(type="string", length=11, name="numero_dit")
     */
    private string $numeroDit;


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

    /**
     * @ORM\OneToOne(targetEntity=DwOrdreDeReparation::class, mappedBy="demandeIntervention")
     */
    private $ordreDeReparation;

    /**
     * @ORM\ManyToOne(targetEntity=DwTiroir::class, inversedBy="demandesIntervention")
     * @ORM\JoinColumn(name="id_tiroir", referencedColumnName="id_tiroir", nullable=true)
     */
    private $tiroir;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit_avoir")
     */
    private $numeroDemandeDitAvoit;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit_refacturation")
     */
    private $numeroDemandeDitRefacturation;

    /**
     * @ORM\Column(type="boolean", name="dit_avoir")
     */
    private bool $estDitAvoir = false;

    /**
     * @ORM\Column(type="boolean", name="dit_refacturation")
     */
    private bool $estDitRefacturation = false;

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
     * Get the value of idDit
     */
    public function getIdDit()
    {
        return $this->idDit;
    }

    /**
     * Set the value of idDit
     *
     * @return  self
     */
    public function setIdDit($idDit)
    {
        $this->idDit = $idDit;

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

    // Getter et setter pour ordreDeReparation
    public function getOrdreDeReparation(): ?DwOrdreDeReparation
    {
        return $this->ordreDeReparation;
    }

    public function setOrdreDeReparation(?DwOrdreDeReparation $ordreDeReparation): self
    {
        // Assurez-vous de maintenir la cohérence bidirectionnelle
        if ($ordreDeReparation === null && $this->ordreDeReparation !== null) {
            $this->ordreDeReparation->setDemandeIntervention(null);
        }

        if ($ordreDeReparation !== null && $ordreDeReparation->getDemandeIntervention() !== $this) {
            $ordreDeReparation->setDemandeIntervention($this);
        }

        $this->ordreDeReparation = $ordreDeReparation;
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
     * Get the value of numeroDemandeDitAvoit
     */
    public function getNumeroDemandeDitAvoit()
    {
        return $this->numeroDemandeDitAvoit;
    }

    /**
     * Set the value of numeroDemandeDitAvoit
     *
     * @return  self
     */
    public function setNumeroDemandeDitAvoit($numeroDemandeDitAvoit)
    {
        $this->numeroDemandeDitAvoit = $numeroDemandeDitAvoit;

        return $this;
    }

    /**
     * Get the value of numeroDemandeDitRefacturation
     */
    public function getNumeroDemandeDitRefacturation()
    {
        return $this->numeroDemandeDitRefacturation;
    }

    /**
     * Set the value of numeroDemandeDitRefacturation
     *
     * @return  self
     */
    public function setNumeroDemandeDitRefacturation($numeroDemandeDitRefacturation)
    {
        $this->numeroDemandeDitRefacturation = $numeroDemandeDitRefacturation;

        return $this;
    }

    /**
     * Get the value of estDitAvoir
     */
    public function getEstDitAvoir()
    {
        return $this->estDitAvoir;
    }

    /**
     * Set the value of estDitAvoir
     *
     * @return  self
     */
    public function setEstDitAvoir($estDitAvoir)
    {
        $this->estDitAvoir = $estDitAvoir;

        return $this;
    }

    /**
     * Get the value of estDitRefacturation
     */
    public function getEstDitRefacturation()
    {
        return $this->estDitRefacturation;
    }

    /**
     * Set the value of estDitRefacturation
     *
     * @return  self
     */
    public function setEstDitRefacturation($estDitRefacturation)
    {
        $this->estDitRefacturation = $estDitRefacturation;

        return $this;
    }
}
