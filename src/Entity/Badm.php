<?php

namespace App\Entity;

use DateTime;
use App\Entity\CasierValider;
use Doctrine\ORM\Mapping as ORM;

use App\Traits\AgenceServiceTrait;
use App\Traits\AgenceServiceEmetteurTrait;


/**
 * @ORM\Entity(repositoryClass="App\Repository\BadmRepository")
 * @ORM\Table(name="Demande_Mouvement_Materiel")
 * @ORM\HasLifecycleCallbacks
 */
class Badm
{
    use AgenceServiceEmetteurTrait;
    use AgenceServiceTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Mouvement_Materiel")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Demande_BADM")
     *
     * @var string
     */
    private string $numBadm;

    /**
     * @ORM\Column(type="integer", name="ID_Materiel")
     *
     * @var integer
     */
    private int $idMateriel;

    /**
     * @ORM\Column(type="string", length=50, name="Nom_Session_Utilisateur")
     *
     * @var string
     */
    private string $nomUtilisateur;

    /**
     * @ORM\Column(type="datetime", name="Date_Demande")
     *
     * @var datetime
     */
    private DateTime $dateDemande;

    /**
     * @ORM\Column(type="string", length=5 ,name="Heure_Demande")
     *
     * @var string
     */
    private string $heureDemande;

    /**
     * @ORM\Column(type="string", length=5, name="Agence_Service_Emetteur")
     *
     * @var string
     */
    private string $agenceServiceEmetteur;

    /**
     * @ORM\Column(type="string", length=20, name="Casier_Emetteur", nullable=true)
     *
     * @var ?string
     */
    private ?string $casierEmetteur = null; 

    /**
     * @ORM\Column(type="string", length=5, name="Agence_Service_Destinataire")
     *
     * @var string
     */
    private string $agenceServiceDestinataire;

     /**
     * @ORM\ManyToOne(targetEntity=CasierValider::class, inversedBy="Badms")
     * @ORM\JoinColumn(name="Casier_Destinataire", referencedColumnName="id")
     */
    private  ?CasierValider $casierDestinataire;

    /**
     * @ORM\Column(type="string", length=100, name="Motif_Arret_Materiel", nullable=true)
     *
     * @var ?string
     */
    private ?string $motifMateriel = null;

    /**
     * @ORM\Column(type="string", length=10, name="Etat_Achat")
     *
     * @var string
     */
    private ?string $etatAchat = null;

    /**
     * @ORM\Column(type="datetime", name="Date_Mise_Location")
     *
     * 
     */
    private  $dateMiseLocation = null;

    /**
     * @ORM\Column(type="float", scale="2", name="Cout_Acquisition")
     *
     * @var float
     */
    private ?float $coutAcquisition;

    /**
     * @ORM\Column(type="float", scale="2", name="Amortissement")
     *
     * @var float
     */
    private ?float $amortissement;

    /**
     * @ORM\Column(type="float", scale="2", name="Valeur_Net_Comptable")
     *
     * @var float
     */
    private ?float $valeurNetComptable;

    /**
     * @ORM\Column(type="string", length=50, name="Nom_Client", nullable=true)
     *
     * @var ?string
     */
    private ?string $nomClient= null;

    /**
     * @ORM\Column(type="string", length=20, name="Modalite_Paiement", nullable=true)
     *
     * @var ?string
     */
    private ?string $modalitePaiement=null;

    /**
     * @ORM\Column(type="float", scale="2", name="Prix_Vente_HT")
     *
     * @var float
     */
    private ?float $prixVenteHt = null;

    /**
     * @ORM\Column(type="string", length=100, name="Motif_Mise_Rebut", nullable=true)
     *
     * @var ?string
     */
    private ?string $motifMiseRebut = null;

    /**
     * @ORM\Column(type="integer", name="Heure_machine")
     *
     * @var int
     */
    private ?int $heureMachine;

    /**
     * @ORM\Column(type="integer", name="KM_machine")
     *
     * @var int
     */
    private ?int $kmMachine;

    /**
     * @ORM\Column(type="string", length=15 ,name="Num_Parc", nullable=true)
     *
     * @var string
     */
    private ?string $numParc = null;

    /**
     * @ORM\Column(type="string", length=50, name="Nom_Image", nullable=true)
     *
     * @var ?string
     */
    private ?string $nomImage = null;

    /**
     * @ORM\Column(type="string", length=50, name="Nom_Fichier", nullable=true)
     *
     * @var ?string
     */
    private ?string $nomFichier = null;

    /**
     * @ORM\ManyToOne(targetEntity="TypeMouvement", inversedBy="Badm")
     * @ORM\JoinColumn(name="Code_Mouvement", referencedColumnName="ID_Type_Mouvement")
     *
     * @var [type]
     */
    private $typeMouvement;

    /**
     * @ORM\ManyToOne(targetEntity="StatutDemande", inversedBy="Badm")
     * @ORM\JoinColumn(name="ID_Statut_Demande", referencedColumnName="ID_Statut_Demande")
     */
    private $statutDemande;

    private $numSerie;

    private $constructeur = "";

    private $designation = "";
 
    private $modele = "";

    private $groupe;

    private $anneeDuModele;

    private $affectation;

    private $dateAchat;

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numBadm
     *
     * @return  string
     */ 
    public function getNumBadm()
    {
        return $this->numBadm;
    }

    /**
     * Set the value of numBadm
     *
     * @param  string  $numBadm
     *
     * @return  self
     */ 
    public function setNumBadm(string $numBadm)
    {
        $this->numBadm = $numBadm;

        return $this;
    }

    /**
     * Get the value of idMateriel
     *
     * @return  integer
     */ 
    public function getIdMateriel()
    {
        return $this->idMateriel;
    }

    /**
     * Set the value of idMateriel
     *
     * @param  integer  $idMateriel
     *
     * @return  self
     */ 
    public function setIdMateriel($idMateriel)
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    /**
     * Get the value of nom_utilisateur
     *
     * @return  string
     */ 
    public function getNomUtilisateur()
    {
        return $this->nomUtilisateur;
    }

    /**
     * Set the value of nom_utilisateur
     *
     * @param  string  $nom_utilisateur
     *
     * @return  self
     */ 
    public function setNomUtilisateur(string $nom_utilisateur)
    {
        $this->nomUtilisateur = $nom_utilisateur;

        return $this;
    }

    
    public function getHeureDemande(): string
    {
        return $this->heureDemande;
    }

   
    public function setHeureDemande(string $heureDemande): self
    {
        $this->heureDemande = $heureDemande;

        return $this;
    }

    /**
     * Get the value of agenceServiceEmetteur
     *
     * @return  string
     */ 
    public function getAgenceServiceEmetteur()
    {
        return $this->agenceServiceEmetteur;
    }

    /**
     * Set the value of agenceServiceEmetteur
     *
     * @param  string  $agenceServiceEmetteur
     *
     * @return  self
     */ 
    public function setAgenceServiceEmetteur(string $agenceServiceEmetteur)
    {
        $this->agenceServiceEmetteur = $agenceServiceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceServiceDestinataire
     *
     * @return  string
     */ 
    public function getAgenceServiceDestinataire()
    {
        return $this->agenceServiceDestinataire;
    }

    /**
     * Set the value of agenceServiceDestinataire
     *
     * @param  string  $agenceServiceDestinataire
     *
     * @return  self
     */ 
    public function setAgenceServiceDestinataire(string $agenceServiceDestinataire)
    {
        $this->agenceServiceDestinataire = $agenceServiceDestinataire;

        return $this;
    }

   
    public function getCasierDestinataire()
    {
        return $this->casierDestinataire;
    }

   
    public function setCasierDestinataire( $casierDestinataire): self
    {
        $this->casierDestinataire = $casierDestinataire;

        return $this;
    }

    
    public function getMotifMateriel()
    {
        return $this->motifMateriel;
    }

  
    public function setMotifMateriel(?string $motifMateriel): self
    {
        $this->motifMateriel = $motifMateriel;

        return $this;
    }

   
    public function getEtatAchat()
    {
        return $this->etatAchat;
    }

   
    public function setEtatAchat(?string $etatAchat): self
    {
        $this->etatAchat = $etatAchat;

        return $this;
    }

  
    public function getDateMiseLocation()
    {
        return $this->dateMiseLocation;
    }

   
    public function setDateMiseLocation( $dateMiseLocation): self
    {
        $this->dateMiseLocation = $dateMiseLocation;

        return $this;
    }

  
    public function getCoutAcquisition()
    {
        return $this->coutAcquisition;
    }

    
    public function setCoutAcquisition(?float $coutAcquisition): self
    {
        $this->coutAcquisition = $coutAcquisition;

        return $this;
    }

  
    public function getAmortissement()
    {
        return $this->amortissement;
    }

    
    public function setAmortissement(?float $amortissement): self
    {
        $this->amortissement = $amortissement;

        return $this;
    }

    
    public function getValeurNetComptable()
    {
        return $this->valeurNetComptable;
    }

 
    public function setValeurNetComptable(?float $valeurNetComptable): self
    {
        $this->valeurNetComptable = $valeurNetComptable;

        return $this;
    }

    
    public function getNomClient()
    {
        return $this->nomClient;
    }

    
    public function setNomClient(?string $nomClient): self
    {
        $this->nomClient = $nomClient;

        return $this;
    }

   
    public function getModalitePaiement()
    {
        return $this->modalitePaiement;
    }

   
    public function setModalitePaiement(?string $modalitePaiement)
    {
        $this->modalitePaiement = $modalitePaiement;

        return $this;
    }

  
    public function getPrixVenteHt()
    {
        return $this->prixVenteHt;
    }

    
    public function setPrixVenteHt( $prixVenteHt): self
    {
        $this->prixVenteHt = $prixVenteHt;

        return $this;
    }

    
    public function getMotifMiseRebut()
    {
        return $this->motifMiseRebut;
    }

    public function setMotifMiseRebut(?string $motifMiseRebut): self
    {
        $this->motifMiseRebut = $motifMiseRebut;

        return $this;
    }

   

   
    public function getHeureMachine()
    {
        return $this->heureMachine;
    }

   
    public function setHeureMachine($heureMachine): self
    {
        $this->heureMachine = $heureMachine;

        return $this;
    }

    
    public function getKmMachine()
    {
        return $this->kmMachine;
    }

  
    public function setKmMachine($kmMachine): self
    {
        $this->kmMachine = $kmMachine;

        return $this;
    }

    
    public function getNumParc()
    {
        return $this->numParc;
    }

    public function setNumParc($numParc): self
    {
        $this->numParc = $numParc;

        return $this;
    }

    /**
     * Get the value of nomImage
     *
     * @return  string
     */ 
    public function getNomImage()
    {
        return $this->nomImage;
    }

    /**
     * Set the value of nomImage
     *
     * @param  string  $nomImage
     *
     * @return  self
     */ 
    public function setNomImage(string $nomImage)
    {
        $this->nomImage = $nomImage;

        return $this;
    }

    /**
     * Get the value of nomFichier
     *
     * @return  string
     */ 
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    /**
     * Set the value of nomFichier
     *
     * @param  string  $nomFichier
     *
     * @return  self
     */ 
    public function setNomFichier(string $nomFichier)
    {
        $this->nomFichier = $nomFichier;

        return $this;
    }

    /**
     * Get the value of typeMouvement
     *
     * @return  [type]
     */ 
    public function getTypeMouvement()
    {
        return $this->typeMouvement;
    }

    /**
     * Set the value of typeMouvement
     *
     * @param  [type]  $typeMouvement
     *
     * @return  self
     */ 
    public function setTypeMouvement( $typeMouvement)
    {
        $this->typeMouvement = $typeMouvement;

        return $this;
    }

    
    public function getStatutDemande()
    {
        return $this->statutDemande;
    }

   
    public function setStatutDemande( $statutDemande): self
    {
        $this->statutDemande = $statutDemande;

        return $this;
    }

 
    public function getDateDemande()
    {
        return $this->dateDemande;
    }

    /**
     * Set the value of dateDemande
     *
     * @param  Datetime  $dateDemande
     *
     * @return  self
     */ 
    public function setDateDemande(DateTime $dateDemande)
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

  
    public function getCasierEmetteur()
    {
        return $this->casierEmetteur;
    }

   
    public function setCasierEmetteur(?string $casierEmetteur): self
    {
        $this->casierEmetteur = $casierEmetteur;

        return $this;
    }

    public function getNumSerie()
    {
        return $this->numSerie;
    }

   
    public function setNumSerie($numSerie): self
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    /**
     * Get the value of groupe
     */ 
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set the value of groupe
     *
     * @return  self
     */ 
    public function setGroupe($groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    

    /**
     * Get the value of anneeDuModele
     */ 
    public function getAnneeDuModele()
    {
        return $this->anneeDuModele;
    }

    /**
     * Set the value of anneeDuModele
     *
     * @return  self
     */ 
    public function setAnneeDuModele($anneeDuModele)
    {
        $this->anneeDuModele = $anneeDuModele;

        return $this;
    }

    /**
     * Get the value of affectation
     */ 
    public function getAffectation()
    {
        return $this->affectation;
    }

    
    public function setAffectation($affectation): self
    {
        $this->affectation = $affectation;

        return $this;
    }

  
    public function getDateAchat()
    {
        return $this->dateAchat;
    }

   
    public function setDateAchat($dateAchat): self
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function getConstructeur()
    {
        return $this->constructeur;
    }

   
    public function setConstructeur($constructeur): self
    {
        $this->constructeur = $constructeur;

        return $this;
    }

    public function getDesignation()
    {
        return $this->designation;
    }

   
    public function setDesignation($designation): self
    {
        $this->designation = $designation;

        return $this;
    }


    public function getModele()
    {
        return $this->modele;
    }

   
    public function setModele($modele): self
    {
        $this->modele = $modele;

        return $this;
    }
}
