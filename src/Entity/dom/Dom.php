<?php

namespace App\Entity\dom;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\dom\Rmq;
use App\Entity\admin\StatutDemande;
use App\Repository\dom\DomRepository;
use App\Entity\Traits\AgenceServiceTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\Traits\AgenceServiceEmetteurTrait;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=DomRepository::class)
 * @ORM\Table(name="Demande_ordre_mission")
 * @ORM\HasLifecycleCallbacks
 */
class Dom
{
    use AgenceServiceEmetteurTrait;
    use AgenceServiceTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Ordre_Mission")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Ordre_Mission")
     */
    private string $numeroOrdreMission;


    /**
     * @ORM\Column(type="datetime", name="Date_Demande")
     */
    private  $dateDemande;

    /**
     * @ORM\Column(type="string", length=10, name="Type_Document")
     */
    private string $typeDocument;

    /**
     * @ORM\ManyToOne(targetEntity=SousTypeDocument::class, inversedBy="dom")
     * @ORM\JoinColumn(name="Sous_Type_Document", referencedColumnName="ID_Sous_Type_Document")
     */
    private ?SousTypeDocument $sousTypeDocument; //relation avec la table sousTypeDocument


    /**
     * @ORM\Column(type="string", length=50, name="Autre_Type_Document",nullable=true)
     */
    private ?string $autreTypeDocument = null;

    /**
     * @ORM\Column(type="string", length=50, name="Matricule",nullable=true)
     */
    private ?string $matricule = null;

    /**
     * @ORM\Column(type="string", length=100, name="Nom_Session_Utilisateur")
     */
    private string $nomSessionUtilisateur;

    /**
     * @ORM\Column(type="string", length=6, name="Code_AgenceService_Debiteur", nullable=true)
     */
    private ?string $codeAgenceServiceDebiteur;

    /**
     * @ORM\Column(type="datetime", name="Date_Debut")
     */
    private  $dateDebut;

    /**
     * @ORM\Column(type="string", length=5, name="Heure_Debut")
     */
    private  $heureDebut;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin")
     */
    private  $dateFin;

    /**
     * @ORM\Column(type="string", length=5, name="Heure_Fin")
     */
    private  $heureFin;

    /**
     * @ORM\Column(type="integer", name="Nombre_Jour", nullable=true)
     */
    private ?int $nombreJour = null;

    /**
     * @ORM\Column(type="string", length=100, name="Motif_Deplacement")
     */
    private string $motifDeplacement;

    /**
     * @ORM\Column(type="string", length=100, name="Client")
     */
    private string $client = '';

    /**
     * @ORM\Column(type="string", length=50, name="Numero_OR",nullable=true)
     */
    private ?string $numeroOr = null;

    /**
     * @ORM\Column(type="string", length=100, name="Lieu_Intervention")
     */
    private string $lieuIntervention;

    /**
     * @ORM\Column(type="string", length=3, name="Vehicule_Societe")
     */
    private string $vehiculeSociete;

    /**
     * @ORM\Column(type="string",name= "Indemnite_Forfaitaire", nullable=true)
     */
    private ?string $indemniteForfaitaire = null; //relation avec la table idemnity

    /**
     * @ORM\Column(type="string", length=50, name="Total_Indemnite_Forfaitaire",nullable=true)
     */
    private ?string $totalIndemniteForfaitaire = null;

    /**
     * @ORM\Column(type="string", length=50, name="Motif_Autres_depense_1",nullable=true)
     */
    private $motifAutresDepense1 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Autres_depense_1",nullable=true)
     */
    private  $autresDepense1 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Motif_Autres_depense_2",nullable=true)
     */
    private ?string $motifAutresDepense2 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Autres_depense_2",nullable=true)
     */
    private  $autresDepense2 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Motif_Autres_depense_3",nullable=true)
     */
    private ?string $motifAutresDepense3 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Autres_depense_3",nullable=true)
     */
    private  $autresDepense3 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Total_Autres_Depenses",nullable=true)
     */
    private ?string $totalAutresDepenses = null;

    /**
     * @ORM\Column(type="string", length=50, name="Total_General_Payer",nullable=true)
     */
    private ?string $totalGeneralPayer = null;

    /**
     * @ORM\Column(type="string", length=50, name="Mode_Paiement",nullable=true)
     */
    private ?string $modePayement = null;

    /**
     * @ORM\Column(type="string", length=50, name="Piece_Jointe_1",nullable=true)
     */
    private ?string $pieceJoint01 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Piece_Jointe_2",nullable=true)
     */
    private ?string $pieceJoint02 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Piece_Jointe_3",nullable=true)
     */
    private ?string $pieceJoint3 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Utilisateur_Creation")
     */
    private string $utilisateurCreation;

    /**
     * @ORM\Column(type="string", length=50, name="Utilisateur_Modification",nullable=true)
     */
    private ?string $utilisateurModification = null;

    /**
     * @ORM\Column(type="string",  name="Date_Modif",nullable=true)
     */
    private  ?string $dateModif = null;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Statut",nullable=true)
     */
    private ?string $codeStatut = null;

    /**
     * @ORM\Column(type="string", length=10, name="Numero_Tel",nullable=true)
     */
    private ?string $numeroTel = null;

    /**
     * @ORM\Column(type="string", length=100, name="Nom",nullable=true)
     */
    private ?string $nom = null;


    /**
     * @ORM\Column(type="string", length=100, name="Prenom",nullable=true)
     */
    private ?string $prenom = null;


    /**
     * @ORM\Column(type="string", length=3, name="Devis",nullable=true)
     */
    private ?string $devis = null;

    /**
     * @ORM\Column(type="string", length=50, name="LibelleCodeAgence_Service",nullable=true)
     */
    private ?string $libelleCodeAgenceService = null;


    /**
     * @ORM\Column(type="string", length=50, name="Fiche",nullable=true)
     */
    private ?string $fiche = null;

    /**
     * @ORM\Column(type="string", length=50, name="NumVehicule",nullable=true)
     */
    private ?string $numVehicule = null;


    /**
     * @ORM\Column(type="string", length=50, name="Doit_indemnite",nullable=true)
     */
    private ?string $droitIndemnite = null;

    /**
     * @ORM\Column(type="string", length=50, name="Categorie",nullable=true)
     */
    private  $categorie = null;

    /**
     * @ORM\Column(type="string", length=50, name="Site",nullable=true)
     */
    private  $site = null;


    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="idemnity_depl")
     */
    private $idemnityDepl;

    /**
     * @ORM\Column(type="string",  name="Date_CPT",nullable=true)
     */
    private ?string $dateCpt = null;

    /**
     * @ORM\Column(type="string",  name="Date_PAY",nullable=true)
     */
    private ?string $datePay = null;

    /**
     * @ORM\Column(type="string",  name="Date_ANN",nullable=true)
     */
    private ?string $dateAnn = null;

    /**
     * @ORM\Column(type="string", length=50, name="Emetteur",nullable=true)
     */
    private ?string $emetteur = null;

    /**
     * @ORM\Column(type="string", length=50, name="Debiteur",nullable=true)
     */
    private ?string $debiteur = null;


    /**
     * @ORM\ManyToOne(targetEntity=StatutDemande::class, inversedBy="doms")
     * @ORM\JoinColumn(name="id_statut_demande", referencedColumnName="ID_Statut_Demande")
     */
    private $idStatutDemande = null;

    /**
     * @ORM\Column(type="datetime",  name="Date_heure_modif_statut",nullable=true)
     */
    private ?datetime $dateHeureModifStatut = null;


    private $cin = null;

    private string $salarier;

    private Indemnite $indemnite;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="domAgenceEmetteur")
     * @ORM\JoinColumn(name="agence_emetteur_id", referencedColumnName="id")
     */
    private  $agenceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="domServiceEmetteur")
     * @ORM\JoinColumn(name="service_emetteur_id", referencedColumnName="id")
     */
    private  $serviceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="domAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     */
    private  $agenceDebiteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="domServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     */
    private  $serviceDebiteurId;


    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="domSite")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private  $siteId;

    /**
     * @ORM\ManyToOne(targetEntity=Catg::class, inversedBy="domCatg")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private  $categoryId;


    private $codeAgenceAutoriser;

    private $codeServiceAutoriser;

    private $rmq;

    //======================================================================================================================================================
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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



    public function getSousTypeDocument()
    {
        return $this->sousTypeDocument;
    }

    public function setSousTypeDocument($sousTypeDocument): self
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


    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): self
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



    public function getHeureDebut()
    {
        return $this->heureDebut;
    }

    public function setHeureDebut($heureDebut): self
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



    public function getHeureFin()
    {
        return $this->heureFin;
    }

    public function setHeureFin($heureFin): self
    {
        $this->heureFin = $heureFin;

        return $this;
    }


    public function getNombreJour()
    {
        return $this->nombreJour;
    }

    public function setNombreJour(?string $nombreJour): self
    {
        $this->nombreJour = $nombreJour;

        return $this;
    }




    public function getMotifDeplacement()
    {
        return $this->motifDeplacement;
    }

    public function setMotifDeplacement($motifDeplacement): self
    {
        $this->motifDeplacement = $motifDeplacement;

        return $this;
    }


    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        $this->client = $client;

        return $this;
    }


    public function getNumeroOr()
    {
        return $this->numeroOr;
    }

    public function setNumeroOr($numeroOr): self
    {
        $this->numeroOr = $numeroOr;

        return $this;
    }


    public function getLieuIntervention()
    {
        return $this->lieuIntervention;
    }

    public function setLieuIntervention($lieuIntervention): self
    {
        $this->lieuIntervention = $lieuIntervention;

        return $this;
    }


    public function getVehiculeSociete()
    {
        return $this->vehiculeSociete;
    }

    public function setVehiculeSociete($vehiculeSociete): self
    {
        $this->vehiculeSociete = $vehiculeSociete;

        return $this;
    }


    public function getIndemniteForfaitaire()
    {
        return $this->indemniteForfaitaire;
    }

    public function setIndemniteForfaitaire($indemniteForfaitaire): self
    {
        $this->indemniteForfaitaire = $indemniteForfaitaire;

        return $this;
    }


    public function getTotalIndemniteForfaitaire()
    {
        return $this->totalIndemniteForfaitaire;
    }

    public function setTotalIndemniteForfaitaire($totalIndemniteForfaitaire): self
    {
        $this->totalIndemniteForfaitaire = $totalIndemniteForfaitaire;

        return $this;
    }


    public function getMotifAutresDepense1()
    {
        return $this->motifAutresDepense1;
    }

    public function setMotifAutresDepense1($motifAutresDepense1): self
    {
        $this->motifAutresDepense1 = $motifAutresDepense1;

        return $this;
    }

    public function getAutresDepense1()
    {
        return $this->autresDepense1;
    }

    public function setAutresDepense1($autresDepense1): self
    {
        $this->autresDepense1 = $autresDepense1;

        return $this;
    }



    public function getMotifAutresDepense2()
    {
        return $this->motifAutresDepense2;
    }

    public function setMotifAutresDepense2($motifAutresDepense2): self
    {
        $this->motifAutresDepense2 = $motifAutresDepense2;

        return $this;
    }



    public function getAutresDepense2()
    {
        return $this->autresDepense2;
    }

    public function setAutresDepense2($autresDepense2): self
    {
        $this->autresDepense2 = $autresDepense2;

        return $this;
    }




    public function getMotifAutresDepense3()
    {
        return $this->motifAutresDepense3;
    }

    public function setMotifAutresDepense3($motifAutresDepense3): self
    {
        $this->motifAutresDepense3 = $motifAutresDepense3;

        return $this;
    }

    public function getAutresDepense3()
    {
        return $this->autresDepense3;
    }

    public function setAutresDepense3($autresDepense3): self
    {
        $this->autresDepense3 = $autresDepense3;

        return $this;
    }


    public function getTotalAutresDepenses()
    {
        return $this->totalAutresDepenses;
    }

    public function setTotalAutresDepenses($totalAutresDepenses): self
    {
        $this->totalAutresDepenses = $totalAutresDepenses;

        return $this;
    }



    public function getTotalGeneralPayer()
    {
        return $this->totalGeneralPayer;
    }

    public function setTotalGeneralPayer($totalGeneralPayer): self
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


    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    public function setPieceJoint01($pieceJointe1): self
    {
        $this->pieceJoint01 = $pieceJointe1;

        return $this;
    }



    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    public function setPieceJoint02($pieceJointe2): self
    {
        $this->pieceJoint02 = $pieceJointe2;

        return $this;
    }


    public function getPieceJoint3()
    {
        return $this->pieceJoint3;
    }

    public function setPieceJoint3($pieceJointe3): self
    {
        $this->pieceJoint3 = $pieceJointe3;

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


    public function getNumeroTel()
    {
        return $this->numeroTel;
    }

    public function setNumeroTel($numeroTel): self
    {
        $this->numeroTel = $numeroTel;

        return $this;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }


    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
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


    public function getFiche()
    {
        return $this->fiche;
    }

    public function setFiche($fiche): self
    {
        $this->fiche = $fiche;

        return $this;
    }


    public function getNumVehicule()
    {
        return $this->numVehicule;
    }

    public function setNumVehicule($numVehicule): self
    {
        $this->numVehicule = $numVehicule;

        return $this;
    }



    public function getDroitIndemnite()
    {
        return $this->droitIndemnite;
    }

    public function setDroitIndemnite($droitIndemnite): self
    {
        $this->droitIndemnite = $droitIndemnite;

        return $this;
    }


    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }


    public function getSite()
    {
        return $this->site;
    }

    public function setSite($site): self
    {
        $this->site = $site;

        return $this;
    }


    public function getIdemnityDepl()
    {
        return $this->idemnityDepl;
    }

    public function setIdemnityDepl($idemnityDepl): self
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
        $this->emetteur = $emetteur;

        return $this;
    }


    public function getDebiteur(): string
    {
        return $this->debiteur;
    }

    public function setDebiteur(string $debiteur): self
    {
        $this->debiteur = $debiteur;

        return $this;
    }


    public function getIdStatutDemande()
    {
        return $this->idStatutDemande;
    }

    public function setIdStatutDemande($idStatutDemande): self
    {
        $this->idStatutDemande = $idStatutDemande;

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


    public function getCin()
    {
        return $this->cin;
    }

    public function setCin($cin): self
    {
        $this->cin = $cin;
        return $this;
    }


    public function getSalarier(): string
    {
        return $this->salarier;
    }

    public function setSalarier(string $salarier): self
    {
        $this->salarier = $salarier;
        return $this;
    }


    public function getIndemnite()
    {
        return $this->indemnite;
    }


    public function setIndemnite($indemnite): self
    {
        $this->indemnite = $indemnite;

        return $this;
    }

    public function getAgenceEmetteurId()
    {
        return $this->agenceEmetteurId;
    }


    public function setAgenceEmetteurId($agenceEmetteurId): self
    {
        $this->agenceEmetteurId = $agenceEmetteurId;

        return $this;
    }


    public function getServiceEmetteurId()
    {
        return $this->serviceEmetteurId;
    }


    public function setServiceEmetteurId($serviceEmetteurId): self
    {
        $this->serviceEmetteurId = $serviceEmetteurId;

        return $this;
    }


    public function getAgenceDebiteurId()
    {
        return $this->agenceDebiteurId;
    }


    public function setAgenceDebiteurId($agenceDebiteurId): self
    {
        $this->agenceDebiteurId = $agenceDebiteurId;

        return $this;
    }


    public function getServiceDebiteurId()
    {
        return $this->serviceDebiteurId;
    }


    public function setServiceDebiteurId($serviceDebiteurId): self
    {
        $this->serviceDebiteurId = $serviceDebiteurId;

        return $this;
    }

    /**
     * Get the value of siteId
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * Set the value of siteId
     *
     * @return  self
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * Get the value of categoryId
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set the value of categoryId
     *
     * @return  self
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }


    public function getCodeAgenceAutoriser()
    {
        return $this->codeAgenceAutoriser;
    }

    public function setCodeAgenceAutoriser($codeAgenceAutoriser): self
    {
        $this->codeAgenceAutoriser = $codeAgenceAutoriser;
        return $this;
    }

    public function getCodeSreviceAutoriser()
    {
        return $this->codeServiceAutoriser;
    }

    public function setCodeServiceAutoriser($codeServiceAutoriser): self
    {
        $this->codeServiceAutoriser = $codeServiceAutoriser;
        return $this;
    }

    public function getRmq()
    {
        return $this->rmq;
    }

    public function setRmq($rmq): self
    {
        $this->rmq = $rmq;
        return $this;
    }

    public function toArray(): array
    {
        return [

            'sousTypeDocument' => $this->sousTypeDocument,
            'salarier' => $this->salarier,
            'categorie' => $this->categorie,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'cin' => $this->cin
        ];
    }
}
