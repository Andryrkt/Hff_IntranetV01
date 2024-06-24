<?php

namespace App\Entity;

use App\Traits\AgenceServiceTrait;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class Dom
{
    use AgenceServiceTrait;

     /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_demande_ordre_mission")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_ordre_mission")
     */
    private string $numeroOrdreMission;


     /**
     * @ORM\Column(type="date")
     */
    private $dateDemande;

    /**
     * @ORM\Column(type="string", length=10, name="type_document")
     */
    private string $typeDocument;

    /**
     * @ORM\ManyToOne(targetEntity="sousTypeDocument", inversedBy="dom")
     * @ORM\JoinColumn(name="sous_Type_Document", referencedColumnName="id")
     */
    private string $sousTypeDocument;//relation avec la table sousTypeDocument

    /**
     * @ORM\Column(type="string", length=50, name="autre_type_document",nullable=true)
     */
    private ?string $autreTypeDocument;

    /**
     * @ORM\Column(type="string", length=50, name="matricule",nullable=true)
     */
    private ?string $matricule;

    /**
     * @ORM\Column(type="string", length=100, name="nom_session_utilisateur")
     */
    private string $nomSessionUtilisateur;

    /**
     * @ORM\Column(type="string", length=6, name="code_Agence_Service_Debiteur", nullable=true)
     */
    private ?string $codeAgenceServiceDebiteur;

    /**
     * @ORM\Column(type="date_debut")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="string", length=5, name="heure_debut")
     */
    private string $heureDebut;

    /**
     * @ORM\Column(type="date_fin")
     */
    private  $dateFin;

    /**
     * @ORM\Column(type="string", length=5, name="heure_fin")
     */
    private string $heureFin;

    /**
     * @ORM\Column(type="integer", name="nombre_Jour", nullable=true)
     */
    private ?int $nombreJour;

    /**
     * @ORM\Column(type="string", length=100, name="motif_Deplacement")
     */
    private string $motifDeplacement;

    /**
     * @ORM\Column(type="string", length=100, name="client")
     */
    private string $client;

    /**
     * @ORM\Column(type="string", length=50, name="numero_Or",nullable=true)
     */
    private ?string $numeroOr;

    /**
     * @ORM\Column(type="string", length=100, name="lieu_Intervention")
     */
    private string $lieuIntervention;

    /**
     * @ORM\Column(type="string", length=3, name="vehicule_Societe")
     */
    private string $vehiculeSociete;

    /**
     * @ORM\ManyToOne(targetEntity="idemnity", inversedBy="dom")
     * @ORM\JoinColumn(name="indemnite_Forfaitaire", referencedColumnName="id")
     */
    private ?string $indemniteForfaitaire;//relation avec la table idemnity

    /**
     * @ORM\Column(type="string", length=50, name="total_Indemnite_Forfaitaire",nullable=true)
     */
    private ?string $totalIndemniteForfaitaire;
    
    /**
     * @ORM\Column(type="string", length=50, name="motif_AutresDepense1",nullable=true)
     */
    private ?string $motifAutresDepense1;

   /**
     * @ORM\Column(type="string", length=50, name="autres_Depense1",nullable=true)
     */
    private ?string $autresDepense1;

   /**
     * @ORM\Column(type="string", length=50, name="motif_Autres_Depense2",nullable=true)
     */
    private ?string $motifAutresDepense2;

   /**
     * @ORM\Column(type="string", length=50, name="autres_Depense2",nullable=true)
     */
    private ?string $autresDepense2;

   /**
     * @ORM\Column(type="string", length=50, name="motif_Autres_Depense3",nullable=true)
     */
    private ?string $motifAutresDepense3;

   /**
     * @ORM\Column(type="string", length=50, name="autres_Depense3",nullable=true)
     */
    private ?string $autresDepense3;

   /**
     * @ORM\Column(type="string", length=50, name="total_Autres_Depenses",nullable=true)
     */
    private ?string $totalAutresDepenses;

   /**
     * @ORM\Column(type="string", length=50, name="total_General_Payer",nullable=true)
     */
    private ?string $totalGeneralPayer;

   /**
     * @ORM\Column(type="string", length=50, name="mode_Payement",nullable=true)
     */
    private ?string $modePayement;

   /**
     * @ORM\Column(type="string", length=50, name="piece_Jointe1",nullable=true)
     */
    private ?string $pieceJointe1;

   /**
     * @ORM\Column(type="string", length=50, name="piece_Jointe2",nullable=true)
     */
    private ?string $pieceJointe2;

   /**
     * @ORM\Column(type="string", length=50, name="piece_Jointe3",nullable=true)
     */
    private ?string $pieceJointe3;

   /**
     * @ORM\Column(type="string", length=50, name="utilisateur_Creation")
     */
    private string $utilisateurCreation;

   /**
     * @ORM\Column(type="string", length=50, name="utilisateur_Modification",nullable=true)
     */
    private ?string $utilisateurModification;

   /**
     * @ORM\Column(type="string",  name="date_Modif",nullable=true)
     */
    private  ?string $dateModif;

  /**
     * @ORM\Column(type="string", length=3, name="code_Statut",nullable=true)
     */
    private ?string $codeStatut;

 /**
     * @ORM\Column(type="string", length=10, name="numero_Tel",nullable=true)
     */
    private ?string $numeroTel;

/**
     * @ORM\Column(type="string", length=100, name="nom",nullable=true)
     */
    private ?string $nom;


/**
     * @ORM\Column(type="string", length=100, name="prenom",nullable=true)
     */
    private ?string $prenom;


/**
     * @ORM\Column(type="string", length=3, name="devis",nullable=true)
     */
    private ?string $devis;

/**
     * @ORM\Column(type="string", length=50, name="libelle_Code_Agence_Service",nullable=true)
     */
    private ?string $libelleCodeAgenceService;


/**
     * @ORM\Column(type="string", length=50, name="fiche",nullable=true)
     */
    private ?string $fiche;

/**
     * @ORM\Column(type="string", length=50, name="numero_Vehicule",nullable=true)
     */
    private ?string $numVehicule;


/**
     * @ORM\Column(type="string", length=50, name="droit_Indemnite",nullable=true)
     */
    private ?string $droitIndemnite;

/**
     * @ORM\Column(type="string", length=50, name="categorie",nullable=true)
     */
    private ?string $categorie;

/**
     * @ORM\Column(type="string", length=50, name="site",nullable=true)
     */
    private ?string $site;

    
/**
     * @ORM\Column(type="string", length=50, name="idemnit_yDepl",nullable=true)
     */
    private ?string $idemnityDepl;

/**
     * @ORM\Column(type="string",  name="date_Cpt",nullable=true)
     */
    private ?string $dateCpt;

/**
     * @ORM\Column(type="string",  name="date_Pay",nullable=true)
     */
    private ?string $datePay;

/**
     * @ORM\Column(type="string",  name="date_Ann",nullable=true)
     */
    private ?string $dateAnn;

/**
     * @ORM\Column(type="string", length=50, name="emetteur",nullable=true)
     */
    private ?string $emetteur;

/**
     * @ORM\Column(type="string", length=50, name="debiteur",nullable=true)
     */
    private ?string $debiteur;


/**
     * @ORM\Column(type="integer", length=50, name="id_Statut_Demande",nullable=true)
     */
    private ?int $idStatutDemande;

 /**
     * @ORM\Column(type="datetime",  name="date_Heure_Modif_Statut",nullable=true)
     */
    private ?datetime $dateHeureModifStatut;



    public function getId()
    {
        return $this->id;
    }


    public function getNumeroOrdreMission(): string
    {
        return $this->numeroOrdreMission;
    }

    public function setNumeroOrdreMission(string $numeroOrdreMission): self
    {
        $this->numeroOrdreMission = $numeroOrdreMission;

        return $this;
    }


    public function getDateDemande()
    {
        return $this->dateDemande;
    }

    
    public function setDateDemande($dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }


    
    public function getTypeDocument(): string
    {
        return $this->typeDocument;
    }

    public function setTypeDocument(string $typeDocument): self
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }


    
    public function getSousTypeDocument(): string
    {
        return $this->sousTypeDocument;
    }

    public function setSousTypeDocument(string $sousTypeDocument): self
    {
        $this->sousTypeDocument = $sousTypeDocument;

        return $this;
    }


     
    public function getAutreTypeDocument(): string
    {
        return $this->autreTypeDocument;
    }

    public function setAutreTypeDocument(string $autreTypeDocument): self
    {
        $this->autreTypeDocument = $autreTypeDocument;

        return $this;
    }


    public function getMatricule(): string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }


    
    public function getNomSessionUtilisateur(): string
    {
        return $this->nomSessionUtilisateur;
    }

    public function setNomSessionUtilisateur(string $nomSessionUtilisateur): self
    {
        $this->nomSessionUtilisateur = $nomSessionUtilisateur;

        return $this;
    }


    public function getCodeAgenceServiceDebiteur(): string
    {
        return $this->codeAgenceServiceDebiteur;
    }

    public function setCodeAgenceServiceDebiteur(string $codeAgenceServiceDebiteur): self
    {
        $this->codeAgenceServiceDebiteur = $codeAgenceServiceDebiteur;

        return $this;
    }


    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    
    public function setDateDebut($dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }


    
    public function getHeureDebut(): string
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(string $heureDebut): self
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }


    
    public function getDateFin()
    {
        return $this->dateFin;
    }

    
    public function setDateFin($dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }


    
    public function getHeureFin(): string
    {
        return $this->heureFin;
    }

    public function setHeureFin(string $heureFin): self
    {
        $this->heureFin = $heureFin;

        return $this;
    }


    public function getNombreJour()
    {
        return $this->nombreJour;
    }

    public function setNombreJour(string $nombreJour): self
    {
        $this->nombreJour = $nombreJour;

        return $this;
    }




    public function getMotifDeplacement(): string
    {
        return $this->motifDeplacement;
    }

    public function setMotifDeplacement(string $motifDeplacement): self
    {
        $this->motifDeplacement = $motifDeplacement;

        return $this;
    }


    public function getClient(): string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;

        return $this;
    }


    public function getNumeroOr(): string
    {
        return $this->numeroOr;
    }

    public function setNumeroOr(string $numeroOr): self
    {
        $this->numeroOr = $numeroOr;

        return $this;
    }


    public function getLieuIntervention(): string
    {
        return $this->lieuIntervention;
    }

    public function setLieuIntervention(string $lieuIntervention): self
    {
        $this->lieuIntervention = $lieuIntervention;

        return $this;
    }


    public function getVehiculeSociete(): string
    {
        return $this->vehiculeSociete;
    }

    public function setVehiculeSociete(string $vehiculeSociete): self
    {
        $this->vehiculeSociete = $vehiculeSociete;

        return $this;
    }
    

    public function getIndemniteForfaitaire(): string
    {
        return $this->indemniteForfaitaire;
    }

    public function setIndemniteForfaitaire(string $indemniteForfaitaire): self
    {
        $this->indemniteForfaitaire = $indemniteForfaitaire;

        return $this;
    }


    public function getTotalIndemniteForfaitaire(): string
    {
        return $this->totalIndemniteForfaitaire;
    }

    public function setTotalIndemniteForfaitaire(string $totalIndemniteForfaitaire): self
    {
        $this->totalIndemniteForfaitaire = $totalIndemniteForfaitaire;

        return $this;
    }


    public function gettMotifAutreDepense1(): string
    {
        return $this->motifAutresDepense1;
    }

    public function setMotifAutresDepense1(string $motifAutresDepense1): self
    {
        $this->motifAutresDepense1 = $motifAutresDepense1;

        return $this;
    }

    public function gettAutreDepense1(): string
    {
        return $this->autresDepense1;
    }

    public function setAutresDepense1(string $autresDepense1): self
    {
        $this->autresDepense1 = $autresDepense1;

        return $this;
    }


    
    public function gettMotifAutreDepense2(): string
    {
        return $this->motifAutresDepense2;
    }

    public function setMotifAutresDepense2(string $motifAutresDepense2): self
    {
        $this->motifAutresDepense2 = $motifAutresDepense2;

        return $this;
    }



    public function gettAutreDepense2(): string
    {
        return $this->autresDepense2;
    }

    public function setAutresDepense2(string $autresDepense2): self
    {
        $this->autresDepense2 = $autresDepense2;

        return $this;
    }


    
    
    public function gettMotifAutreDepense3(): string
    {
        return $this->motifAutresDepense3;
    }

    public function setMotifAutresDepense3(string $motifAutresDepense3): self
    {
        $this->motifAutresDepense3 = $motifAutresDepense3;

        return $this;
    }

    public function getAutreDepense3(): string
    {
        return $this->autresDepense3;
    }

    public function setAutreDepense3(string $autresDepense3): self
    {
        $this->autresDepense3 = $autresDepense3;

        return $this;
    }


    public function gettTotalAutresDepenses(): string
    {
        return $this->totalAutresDepenses;
    }

    public function setTotalAutresDepenses(string $totalAutresDepenses): self
    {
        $this->totalAutresDepenses = $totalAutresDepenses;

        return $this;
    }



    public function getTotalGeneralPayer(): string
    {
        return $this->totalGeneralPayer;
    }

    public function setTotalGeneralPayer(string $totalGeneralPayer): self
    {
        $this->totalGeneralPayer = $totalGeneralPayer;

        return $this;
    }


    public function getModePayement(): string
    {
        return $this->modePayement;
    }

    public function setModePayement(string $modePayement): self
    {
        $this->modePayement = $modePayement;

        return $this;
    }


    public function getPieceJointe1(): string
    {
        return $this->pieceJointe1;
    }

    public function setPieceJointe1(string $pieceJointe1): self
    {
        $this->pieceJointe1 = $pieceJointe1;

        return $this;
    }


    
    public function getPieceJointe2(): string
    {
        return $this->pieceJointe2;
    }

    public function setPieceJointe2(string $pieceJointe2): self
    {
        $this->pieceJointe2 = $pieceJointe2;

        return $this;
    }


    public function getPieceJointe3(): string
    {
        return $this->pieceJointe3;
    }

    public function setPieceJointe3(string $pieceJointe3): self
    {
        $this->pieceJointe3 = $pieceJointe3;

        return $this;
    }


    public function getUtilisateurCreation(): string
    {
        return $this->utilisateurCreation;
    }

    public function setUtilisateurCreation(string $utilisateurCreation): self
    {
        $this->utilisateurCreation = $utilisateurCreation;

        return $this;
    }


    public function getUtilisateurModification(): string
    {
        return $this->utilisateurModification;
    }

    public function setUtilisateurModification(string $utilisateurModification): self
    {
        $this->utilisateurModification = $utilisateurModification;

        return $this;
    }


    public function getDateModif(): string
    {
        return $this->dateModif;
    }

    public function setDateModif(string $dateModif): self
    {
        $this->dateModif = $dateModif;

        return $this;
    }


    
    public function getCodeStatut(): string
    {
        return $this->codeStatut;
    }

    public function setCodeStatut(string $codeStatut): self
    {
        $this->codeStatut = $codeStatut;

        return $this;
    }


    public function getNumeroTel(): string
    {
        return $this->numeroTel;
    }

    public function setNumeroTel(string $numeroTel): self
    {
        $this->numeroTel = $numeroTel;

        return $this;
    }


    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }


    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }


    public function getDevis(): string
    {
        return $this->devis;
    }

    public function setDevis(string $devis): self
    {
        $this->devis = $devis;

        return $this;
    }


    public function getLibelleCodeAgenceService(): string
    {
        return $this->libelleCodeAgenceService;
    }

    public function setLibelleCodeAgenceService(string $libelleCodeAgenceService): self
    {
        $this->libelleCodeAgenceService = $libelleCodeAgenceService;

        return $this;
    }


    public function getFiche(): string
    {
        return $this->fiche;
    }

    public function setFiche(string $fiche): self
    {
        $this->fiche = $fiche;

        return $this;
    }


    public function getNumVehicule(): string
    {
        return $this->numVehicule;
    }

    public function setNumVehicule(string $numVehicule): self
    {
        $this->numVehicule = $numVehicule;

        return $this;
    }


    
    public function getDroitIndemnite(): string
    {
        return $this->droitIndemnite;
    }

    public function setDroitIndemnite(string $droitIndemnite): self
    {
        $this->droitIndemnite = $droitIndemnite;

        return $this;
    }


    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }


    public function getSite(): string
    {
        return $this->site;
    }

    public function setSite(string $site): self
    {
        $this->site = $site;

        return $this;
    }


    public function geIdemnityDepl(): string
    {
        return $this->idemnityDepl;
    }

    public function setIdemnityDepl(string $idemnityDepl): self
    {
        $this->idemnityDepl = $idemnityDepl;

        return $this;
    }



    public function getDateCpt(): string
    {
        return $this->dateCpt;
    }

    public function setDateCpt(string $dateCpt): self
    {
        $this->dateCpt = $dateCpt;

        return $this;
    }


    public function getDatePay(): string
    {
        return $this->datePay;
    }

    public function setDatePay(string $datePay): self
    {
        $this->datePay = $datePay;

        return $this;
    }


    public function getDateAnn(): string
    {
        return $this->dateAnn;
    }

    public function setDateAnn(string $dateAnn): self
    {
        $this->dateAnn = $dateAnn;

        return $this;
    }


    public function getEmetteur(): string
    {
        return $this->emetteur;
    }

    public function setEmetteur(string $emetteur): self
    { 
        $this->emetteur= $emetteur;

        return $this;
    }


    public function getDebiteur(): string
    {
        return $this->debiteur;
    }

    public function setDebiteur(string $debiteur): self
    { 
        $this->debiteur= $debiteur;

        return $this;
    }


    public function getIdStatutDemande(): string
    {
        return $this->idStatutDemande;
    }

    public function setIdStatutDemande(string $idStatutDemande): self
    { 
        $this->idStatutDemande= $idStatutDemande;

        return $this;
    }


    public function getDateHeureModifStatut()
    {
        return $this->dateHeureModifStatut;
    }

    
    public function setDateHeureModifStatut($dateHeureModifStatut): self
    {
        $this->dateHeureModifStatut = $dateHeureModifStatut;

        return $this;
    }
















}