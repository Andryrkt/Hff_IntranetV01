<?php

namespace App\Entity;


use DateTime;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\BadmRepository")
 * @ORM\Table(name="Demande_Mouvement_Materiel")
 * @ORM\HasLifecycleCallbacks
 */
class Badm
{
    //use DateTrait;

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
    private ?string $casierEmetteur="";

    /**
     * @ORM\Column(type="string", length=5, name="Agence_Service_Destinataire")
     *
     * @var string
     */
    private string $agenceServiceDestinataire;

    /**
     * @ORM\Column(type="string", length=20, name="Casier_Destinataire", nullable=true)
     *
     * @var ?string
     */
    private ?string $casierDestinataire="";

    /**
     * @ORM\Column(type="string", length=100, name="Motif_Arret_Materiel", nullable=true)
     *
     * @var ?string
     */
    private ?string $motifMateriel="";

    /**
     * @ORM\Column(type="string", length=10, name="Etat_Achat")
     *
     * @var string
     */
    private string $etatAchat;

    /**
     * @ORM\Column(type="datetime", name="Date_Mise_Location")
     *
     * @var DateTime
     */
    private DateTime $dateMiseLocation;

    /**
     * @ORM\Column(type="float", scale="2", name="Cout_Acquisition")
     *
     * @var float
     */
    private float $coutAcquisition;

    /**
     * @ORM\Column(type="float", scale="2", name="Amortissement")
     *
     * @var float
     */
    private float $amortissement;

    /**
     * @ORM\Column(type="float", scale="2", name="Valeur_Net_Comptable")
     *
     * @var float
     */
    private float $valeurNetComptable;

    /**
     * @ORM\Column(type="string", length=50, name="Nom_Client", nullable=true)
     *
     * @var ?string
     */
    private ?string $nomClient="";

    /**
     * @ORM\Column(type="string", length=20, name="Modalite_Paiement", nullable=true)
     *
     * @var ?string
     */
    private ?string $modalitePaiement="";

    /**
     * @ORM\Column(type="float", scale="2", name="Prix_Vente_HT")
     *
     * @var float
     */
    private float $prixVenteHt;

    /**
     * @ORM\Column(type="string", length=100, name="Motif_Mise_Rebut", nullable=true)
     *
     * @var ?string
     */
    private ?string $motifMiseRebut ="";

    /**
     * @ORM\Column(type="integer", name="Heure_machine")
     *
     * @var integer
     */
    private int $heureMachine;

    /**
     * @ORM\Column(type="integer", name="KM_machine")
     *
     * @var integer
     */
    private int $kmMachine;

    /**
     * @ORM\Column(type="string", length=15 ,name="Num_Parc")
     *
     * @var string
     */
    private string $numParc;

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
     *
     * @var [type]
     */
    private $statutDemande;

    /**
     * Get the value of id
     */ 
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

    /**
     * Get the value of casierDestinataire
     *
     * @return  string
     */ 
    public function getCasierDestinataire()
    {
        return $this->casierDestinataire;
    }

    /**
     * Set the value of casierDestinataire
     *
     * @param  string  $casierDestinataire
     *
     * @return  self
     */ 
    public function setCasierDestinataire(string $casierDestinataire)
    {
        $this->casierDestinataire = $casierDestinataire;

        return $this;
    }

    /**
     * Get the value of motifMateriel
     *
     * @return  string
     */ 
    public function getMotifMateriel()
    {
        return $this->motifMateriel;
    }

    /**
     * Set the value of motifMateriel
     *
     * @param  string  $motifMateriel
     *
     * @return  self
     */ 
    public function setMotifMateriel(string $motifMateriel)
    {
        $this->motifMateriel = $motifMateriel;

        return $this;
    }

    /**
     * Get the value of etatAchat
     *
     * @return  string
     */ 
    public function getEtatAchat()
    {
        return $this->etatAchat;
    }

    /**
     * Set the value of etatAchat
     *
     * @param  string  $etatAchat
     *
     * @return  self
     */ 
    public function setEtatAchat(string $etatAchat)
    {
        $this->etatAchat = $etatAchat;

        return $this;
    }

    /**
     * Get the value of dateMiseLocation
     *
     * @return  DateTime
     */ 
    public function getDateMiseLocation()
    {
        return $this->dateMiseLocation;
    }

   
    public function setDateMiseLocation(DateTime $dateMiseLocation): self
    {
        $this->dateMiseLocation = $dateMiseLocation;

        return $this;
    }

  
    public function getCoutAcquisition()
    {
        return $this->coutAcquisition;
    }

    
    public function setCoutAcquisition(float $coutAcquisition): self
    {
        $this->coutAcquisition = $coutAcquisition;

        return $this;
    }

  
    public function getAmortissement()
    {
        return $this->amortissement;
    }

    
    public function setAmortissement(float $amortissement): self
    {
        $this->amortissement = $amortissement;

        return $this;
    }

    
    public function getValeurNetComptable()
    {
        return $this->valeurNetComptable;
    }

 
    public function setValeurNetComptable(float $valeurNetComptable): self
    {
        $this->valeurNetComptable = $valeurNetComptable;

        return $this;
    }

    /**
     * Get the value of nomClient
     *
     * @return  string
     */ 
    public function getNomClient()
    {
        return $this->nomClient;
    }

    /**
     * Set the value of nomClient
     *
     * @param  string  $nomClient
     *
     * @return  self
     */ 
    public function setNomClient(string $nomClient)
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    /**
     * Get the value of modalitePaiement
     *
     * @return  string
     */ 
    public function getModalitePaiement()
    {
        return $this->modalitePaiement;
    }

    /**
     * Set the value of modalitePaiement
     *
     * @param  string  $modalitePaiement
     *
     * @return  self
     */ 
    public function setModalitePaiement(string $modalitePaiement)
    {
        $this->modalitePaiement = $modalitePaiement;

        return $this;
    }

  
    public function getPrixVenteHt()
    {
        return $this->prixVenteHt;
    }

    
    public function setPrixVenteHt(float $prixVenteHt): self
    {
        $this->prixVenteHt = $prixVenteHt;

        return $this;
    }

    /**
     * Get the value of motifMiseRebut
     *
     * @return  string
     */ 
    public function getMotifMiseRebut()
    {
        return $this->motifMiseRebut;
    }

    /**
     * Set the value of motifMiseRebut
     *
     * @param  string  $motifMiseRebut
     *
     * @return  self
     */ 
    public function setMotifMiseRebut(string $motifMiseRebut)
    {
        $this->motifMiseRebut = $motifMiseRebut;

        return $this;
    }

   

    /**
     * Get the value of heureMachine
     *
     * @return  integer
     */ 
    public function getHeureMachine()
    {
        return $this->heureMachine;
    }

    /**
     * Set the value of heureMachine
     *
     * @param  integer  $heureMachine
     *
     * @return  self
     */ 
    public function setHeureMachine($heureMachine)
    {
        $this->heureMachine = $heureMachine;

        return $this;
    }

    /**
     * Get the value of kmMachine
     *
     * @return  integer
     */ 
    public function getKmMachine()
    {
        return $this->kmMachine;
    }

    /**
     * Set the value of kmMachine
     *
     * @param  integer  $kmMachine
     *
     * @return  self
     */ 
    public function setKmMachine($kmMachine)
    {
        $this->kmMachine = $kmMachine;

        return $this;
    }

    /**
     * Get the value of numParc
     *
     * @return  string
     */ 
    public function getNumParc()
    {
        return $this->numParc;
    }

    /**
     * Set the value of numParc
     *
     * @param  string  $numParc
     *
     * @return  self
     */ 
    public function setNumParc(string $numParc)
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

    /**
     * Get the value of statutDemande
     *
     * @return  [type]
     */ 
    public function getStatutDemande()
    {
        return $this->statutDemande;
    }

    /**
     * Set the value of statutDemande
     *
     * @param  [type]  $statutDemande
     *
     * @return  self
     */ 
    public function setStatutDemande( $statutDemande)
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

    /**
     * Get the value of casierEmetteur
     *
     * @return  string
     */ 
    public function getCasierEmetteur()
    {
        return $this->casierEmetteur;
    }

    /**
     * Set the value of casierEmetteur
     *
     * @param  string  $casierEmetteur
     *
     * @return  self
     */ 
    public function setCasierEmetteur(string $casierEmetteur)
    {
        $this->casierEmetteur = $casierEmetteur;

        return $this;
    }
}