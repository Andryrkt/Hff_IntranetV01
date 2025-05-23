<?php

namespace App\Entity\dw;

use App\Entity\dw\DwCommande;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\dw\DwOrdreDeReparation;
use App\Entity\dw\DwDemandeIntervention;
use App\Entity\dw\DwRapportIntervention;
use App\Repository\dw\DwProcessusProcedureRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=DwProcessusProcedureRepository::class)
 * @ORM\Table(name="DW_Processus_procedure")
 * @ORM\HasLifecycleCallbacks
 */
class DwProcessusProcedure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="id_document")
     */
    private $idDocument;

    /**
     * @ORM\Column(type="string", length=100, name="nom_document")
     */
    private $nomDocument;

    /**
     * @ORM\Column(type="string", length=50, name="processus_lie")
     */
    private $processusLie ;

    /**
     * @ORM\Column(type="string", length=50, name="type_document")
     */
    private $typeDocument;
    
    /**
     * @ORM\Column(type="datetime", name="date_document")
     */
    private $dateDocument;
    
    /**
     * @ORM\Column(type="datetime", name="date_de_prochaine_revue")
     */
    private $dateProchainRevue;
    
    /**
     * @ORM\Column(type="string", length=50, name="nom_du_responsable")
     */
    private $nomResponsable ;
    
    /**
     * @ORM\Column(type="string", length=50, name="email_responsable_processus")
     */
    private $emailResponsable ;
    
    /**
     * @ORM\Column(type="datetime", name="date_derniere_modification")
     */
    private $dateModification;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private $numeroVersion;

    /**
     * @ORM\Column(type="string", length=3, name="code_service")
     */
    private $codeService ;

    /**
     * @ORM\Column(type="string", length=3, name="code_agence")
     */
    private $codeAgence ;

    /**
     * @ORM\Column(type="string", length=50, name="statut")
     */
    private $statut ;

    /**
     * @ORM\Column(type="string", length=50, name="perimetre")
     */
    private $perimetre ;

    /**
     * @ORM\Column(type="string", length=1000, name="mot_cle")
     */
    private $motCle ;

    /**
     * @ORM\Column(type="integer", name="numero_version_2")
     */
    private $numeroVersion2;

    /**
     * @ORM\Column(type="string", length=100, name="path")
     */
    private $path ;

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
     * Get the value of idDocument
     */ 
    public function getIdDocument()
    {
        return $this->idDocument;
    }

    /**
     * Set the value of idDocument
     *
     * @return  self
     */ 
    public function setIdDocument($idDocument)
    {
        $this->idDocument = $idDocument;

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
     * Get the value of processusLie
     */ 
    public function getProcessusLie()
    {
        return $this->processusLie;
    }

    /**
     * Set the value of processusLie
     *
     * @return  self
     */ 
    public function setProcessusLie($processusLie)
    {
        $this->processusLie = $processusLie;

        return $this;
    }

    /**
     * Get the value of typeDocument
     */ 
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * Set the value of typeDocument
     *
     * @return  self
     */ 
    public function setTypeDocument($typeDocument)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get the value of dateDocument
     */ 
    public function getDateDocument()
    {
        return $this->dateDocument;
    }

    /**
     * Set the value of dateDocument
     *
     * @return  self
     */ 
    public function setDateDocument($dateDocument)
    {
        $this->dateDocument = $dateDocument;

        return $this;
    }

    /**
     * Get the value of dateProchainRevue
     */ 
    public function getDateProchainRevue()
    {
        return $this->dateProchainRevue;
    }

    /**
     * Set the value of dateProchainRevue
     *
     * @return  self
     */ 
    public function setDateProchainRevue($dateProchainRevue)
    {
        $this->dateProchainRevue = $dateProchainRevue;

        return $this;
    }

    /**
     * Get the value of nomResponsable
     */ 
    public function getNomResponsable()
    {
        return $this->nomResponsable;
    }

    /**
     * Set the value of nomResponsable
     *
     * @return  self
     */ 
    public function setNomResponsable($nomResponsable)
    {
        $this->nomResponsable = $nomResponsable;

        return $this;
    }

    /**
     * Get the value of emailResponsable
     */ 
    public function getEmailResponsable()
    {
        return $this->emailResponsable;
    }

    /**
     * Set the value of emailResponsable
     *
     * @return  self
     */ 
    public function setEmailResponsable($emailResponsable)
    {
        $this->emailResponsable = $emailResponsable;

        return $this;
    }

    /**
     * Get the value of dateModification
     */ 
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * Set the value of dateModification
     *
     * @return  self
     */ 
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

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
     * Get the value of codeService
     */ 
    public function getCodeService()
    {
        return $this->codeService;
    }

    /**
     * Set the value of codeService
     *
     * @return  self
     */ 
    public function setCodeService($codeService)
    {
        $this->codeService = $codeService;

        return $this;
    }

    /**
     * Get the value of codeAgence
     */ 
    public function getCodeAgence()
    {
        return $this->codeAgence;
    }

    /**
     * Set the value of codeAgence
     *
     * @return  self
     */ 
    public function setCodeAgence($codeAgence)
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    /**
     * Get the value of statut
     */ 
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */ 
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of perimetre
     */ 
    public function getPerimetre()
    {
        return $this->perimetre;
    }

    /**
     * Set the value of perimetre
     *
     * @return  self
     */ 
    public function setPerimetre($perimetre)
    {
        $this->perimetre = $perimetre;

        return $this;
    }

    /**
     * Get the value of motCle
     */ 
    public function getMotCle()
    {
        return $this->motCle;
    }

    /**
     * Set the value of motCle
     *
     * @return  self
     */ 
    public function setMotCle($motCle)
    {
        $this->motCle = $motCle;

        return $this;
    }

    /**
     * Get the value of numeroVersion2
     */ 
    public function getNumeroVersion2()
    {
        return $this->numeroVersion2;
    }

    /**
     * Set the value of numeroVersion2
     *
     * @return  self
     */ 
    public function setNumeroVersion2($numeroVersion2)
    {
        $this->numeroVersion2 = $numeroVersion2;

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