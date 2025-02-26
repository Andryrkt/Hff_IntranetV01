<?php

namespace App\Entity\mutation;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\dom\SousTypeDocument;
use App\Repository\mutation\MutationRepository;

/**
 * @ORM\Entity(repositoryClass=MutationRepository::class)
 * @ORM\Table(name="Demande_de_mutation")
 * @ORM\HasLifecycleCallbacks
 */
class Mutation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Mutation")
     */
    private string $numeroMutation = '';

    /**
     * @ORM\Column(type="datetime", name="Date_Demande")
     */
    private $dateDemande;

    /**
     * @ORM\Column(type="string", length=100, name="Nom",nullable=true)
     */
    private ?string $nom = null;

    /**
     * @ORM\Column(type="string", length=100, name="Prenom",nullable=true)
     */
    private ?string $prenom = null;

    /**
     * @ORM\Column(type="string", length=50, name="Matricule",nullable=true)
     */
    private ?string $matricule = null;

    /**
     * @ORM\ManyToOne(targetEntity=Catg::class, inversedBy="mutCatg")
     * @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=10, name="Type_Document")
     */
    private string $typeDocument;

    /**
     * @ORM\ManyToOne(targetEntity=SousTypeDocument::class, inversedBy="mutation")
     * @ORM\JoinColumn(name="sous_type_document_id", referencedColumnName="ID_Sous_Type_Document")
     */
    private ?SousTypeDocument $sousTypeDocument; //relation avec la table sousTypeDocument

    /**
     * @ORM\Column(type="string", length=6, name="Code_Agence_Service_Debiteur", nullable=true)
     */
    private ?string $codeAgenceServiceDebiteur;

    /**
     * @ORM\Column(type="string", length=50, name="LibelleCodeAgence_Service",nullable=true)
     */
    private ?string $libelleCodeAgenceService = null;

    /**
     * @ORM\Column(type="datetime", name="Date_Debut")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin")
     */
    private $dateFin;

    /**
     * @ORM\Column(type="string", length=100, name="Lieu_Mutation")
     */
    private string $lieuMutation;

    /**
     * @ORM\Column(type="string", length=100, name="Motif_Mutation")
     */
    private string $motifMutation;

    /**
     * @ORM\Column(type="string", length=100, name="Client")
     */
    private ?string $client = null;

    /**
     * @ORM\Column(type="integer", name="Nombre_Jour_Avance", nullable=true)
     */
    private ?int $nombreJourAvance = null;

    /**
     * @ORM\Column(type="string",name= "Indemnite_Forfaitaire", nullable=true)
     */
    private ?string $indemniteForfaitaire = null; // en relation avec la table idemnity

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
    private $autresDepense1 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Motif_Autres_depense_2",nullable=true)
     */
    private ?string $motifAutresDepense2 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Autres_depense_2",nullable=true)
     */
    private $autresDepense2 = null;

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
    private ?string $modePaiement = null;

    /**
     * @ORM\Column(type="string", length=50, name="Piece_Jointe_1",nullable=true)
     */
    private ?string $pieceJoint01 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Piece_Jointe_2",nullable=true)
     */
    private ?string $pieceJoint02 = null;

    /**
     * @ORM\Column(type="string", length=50, name="Utilisateur_Creation")
     */
    private string $utilisateurCreation;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Statut",nullable=true)
     */
    private ?string $codeStatut = null;

    /**
     * @ORM\Column(type="string", length=10, name="Numero_Tel",nullable=true)
     */
    private ?string $numeroTel = null;

    /**
     * @ORM\Column(type="string", length=3, name="Devis",nullable=true)
     */
    private ?string $devis = null;

    /**
     * @ORM\ManyToOne(targetEntity=StatutDemande::class, inversedBy="mutation")
     * @ORM\JoinColumn(name="statut_demande_id", referencedColumnName="ID_Statut_Demande")
     */
    private $statutDemande = null;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="mutationAgenceEmetteur")
     * @ORM\JoinColumn(name="agence_emetteur_id", referencedColumnName="id")
     */
    private $agenceEmetteur;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="mutationServiceEmetteur")
     * @ORM\JoinColumn(name="service_emetteur_id", referencedColumnName="id")
     */
    private $serviceEmetteur;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="mutationAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     */
    private $agenceDebiteur;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="mutationServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     */
    private $serviceDebiteur;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="mutation")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private $site;

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
     * Get the value of numeroMutation
     */
    public function getNumeroMutation()
    {
        return $this->numeroMutation;
    }

    /**
     * Set the value of numeroMutation
     *
     * @return  self
     */
    public function setNumeroMutation($numeroMutation)
    {
        $this->numeroMutation = $numeroMutation;

        return $this;
    }

    /**
     * Get the value of dateDemande
     */
    public function getDateDemande()
    {
        return $this->dateDemande;
    }

    /**
     * Set the value of dateDemande
     *
     * @return  self
     */
    public function setDateDemande($dateDemande)
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    /**
     * Get the value of nom
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     *
     * @return  self
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get the value of prenom
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set the value of prenom
     *
     * @return  self
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get the value of matricule
     */
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set the value of matricule
     *
     * @return  self
     */
    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * Get the value of categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set the value of categorie
     *
     * @return  self
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

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
     * Get the value of sousTypeDocument
     */
    public function getSousTypeDocument()
    {
        return $this->sousTypeDocument;
    }

    /**
     * Set the value of sousTypeDocument
     *
     * @return  self
     */
    public function setSousTypeDocument($sousTypeDocument)
    {
        $this->sousTypeDocument = $sousTypeDocument;

        return $this;
    }

    /**
     * Get the value of codeAgenceServiceDebiteur
     */
    public function getCodeAgenceServiceDebiteur()
    {
        return $this->codeAgenceServiceDebiteur;
    }

    /**
     * Set the value of codeAgenceServiceDebiteur
     *
     * @return  self
     */
    public function setCodeAgenceServiceDebiteur($codeAgenceServiceDebiteur)
    {
        $this->codeAgenceServiceDebiteur = $codeAgenceServiceDebiteur;

        return $this;
    }

    /**
     * Get the value of libelleCodeAgenceService
     */
    public function getLibelleCodeAgenceService()
    {
        return $this->libelleCodeAgenceService;
    }

    /**
     * Set the value of libelleCodeAgenceService
     *
     * @return  self
     */
    public function setLibelleCodeAgenceService($libelleCodeAgenceService)
    {
        $this->libelleCodeAgenceService = $libelleCodeAgenceService;

        return $this;
    }

    /**
     * Get the value of site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set the value of site
     *
     * @return  self
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get the value of lieuMutation
     */
    public function getLieuMutation()
    {
        return $this->lieuMutation;
    }

    /**
     * Set the value of lieuMutation
     *
     * @return  self
     */
    public function setLieuMutation($lieuMutation)
    {
        $this->lieuMutation = $lieuMutation;

        return $this;
    }

    /**
     * Get the value of motifMutation
     */
    public function getMotifMutation()
    {
        return $this->motifMutation;
    }

    /**
     * Set the value of motifMutation
     *
     * @return  self
     */
    public function setMotifMutation($motifMutation)
    {
        $this->motifMutation = $motifMutation;

        return $this;
    }

    /**
     * Get the value of client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return  self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of nombreJourAvance
     */
    public function getNombreJourAvance()
    {
        return $this->nombreJourAvance;
    }

    /**
     * Set the value of nombreJourAvance
     *
     * @return  self
     */
    public function setNombreJourAvance($nombreJourAvance)
    {
        $this->nombreJourAvance = $nombreJourAvance;

        return $this;
    }

    /**
     * Get the value of indemniteForfaitaire
     */
    public function getIndemniteForfaitaire()
    {
        return $this->indemniteForfaitaire;
    }

    /**
     * Set the value of indemniteForfaitaire
     *
     * @return  self
     */
    public function setIndemniteForfaitaire($indemniteForfaitaire)
    {
        $this->indemniteForfaitaire = $indemniteForfaitaire;

        return $this;
    }

    /**
     * Get the value of totalIndemniteForfaitaire
     */
    public function getTotalIndemniteForfaitaire()
    {
        return $this->totalIndemniteForfaitaire;
    }

    /**
     * Set the value of totalIndemniteForfaitaire
     *
     * @return  self
     */
    public function setTotalIndemniteForfaitaire($totalIndemniteForfaitaire)
    {
        $this->totalIndemniteForfaitaire = $totalIndemniteForfaitaire;

        return $this;
    }

    /**
     * Get the value of motifAutresDepense1
     */
    public function getMotifAutresDepense1()
    {
        return $this->motifAutresDepense1;
    }

    /**
     * Set the value of motifAutresDepense1
     *
     * @return  self
     */
    public function setMotifAutresDepense1($motifAutresDepense1)
    {
        $this->motifAutresDepense1 = $motifAutresDepense1;

        return $this;
    }

    /**
     * Get the value of autresDepense1
     */
    public function getAutresDepense1()
    {
        return $this->autresDepense1;
    }

    /**
     * Set the value of autresDepense1
     *
     * @return  self
     */
    public function setAutresDepense1($autresDepense1)
    {
        $this->autresDepense1 = $autresDepense1;

        return $this;
    }

    /**
     * Get the value of motifAutresDepense2
     */
    public function getMotifAutresDepense2()
    {
        return $this->motifAutresDepense2;
    }

    /**
     * Set the value of motifAutresDepense2
     *
     * @return  self
     */
    public function setMotifAutresDepense2($motifAutresDepense2)
    {
        $this->motifAutresDepense2 = $motifAutresDepense2;

        return $this;
    }

    /**
     * Get the value of autresDepense2
     */
    public function getAutresDepense2()
    {
        return $this->autresDepense2;
    }

    /**
     * Set the value of autresDepense2
     *
     * @return  self
     */
    public function setAutresDepense2($autresDepense2)
    {
        $this->autresDepense2 = $autresDepense2;

        return $this;
    }

    /**
     * Get the value of totalAutresDepenses
     */
    public function getTotalAutresDepenses()
    {
        return $this->totalAutresDepenses;
    }

    /**
     * Set the value of totalAutresDepenses
     *
     * @return  self
     */
    public function setTotalAutresDepenses($totalAutresDepenses)
    {
        $this->totalAutresDepenses = $totalAutresDepenses;

        return $this;
    }

    /**
     * Get the value of totalGeneralPayer
     */
    public function getTotalGeneralPayer()
    {
        return $this->totalGeneralPayer;
    }

    /**
     * Set the value of totalGeneralPayer
     *
     * @return  self
     */
    public function setTotalGeneralPayer($totalGeneralPayer)
    {
        $this->totalGeneralPayer = $totalGeneralPayer;

        return $this;
    }

    /**
     * Get the value of modePaiement
     */
    public function getModePaiement()
    {
        return $this->modePaiement;
    }

    /**
     * Set the value of modePaiement
     *
     * @return  self
     */
    public function setModePaiement($modePaiement)
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    /**
     * Get the value of pieceJoint01
     */
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of pieceJoint01
     *
     * @return  self
     */
    public function setPieceJoint01($pieceJoint01)
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }

    /**
     * Get the value of pieceJoint02
     */
    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    /**
     * Set the value of pieceJoint02
     *
     * @return  self
     */
    public function setPieceJoint02($pieceJoint02)
    {
        $this->pieceJoint02 = $pieceJoint02;

        return $this;
    }

    /**
     * Get the value of utilisateurCreation
     */
    public function getUtilisateurCreation()
    {
        return $this->utilisateurCreation;
    }

    /**
     * Set the value of utilisateurCreation
     *
     * @return  self
     */
    public function setUtilisateurCreation($utilisateurCreation)
    {
        $this->utilisateurCreation = $utilisateurCreation;

        return $this;
    }

    /**
     * Get the value of codeStatut
     */
    public function getCodeStatut()
    {
        return $this->codeStatut;
    }

    /**
     * Set the value of codeStatut
     *
     * @return  self
     */
    public function setCodeStatut($codeStatut)
    {
        $this->codeStatut = $codeStatut;

        return $this;
    }

    /**
     * Get the value of numeroTel
     */
    public function getNumeroTel()
    {
        return $this->numeroTel;
    }

    /**
     * Set the value of numeroTel
     *
     * @return  self
     */
    public function setNumeroTel($numeroTel)
    {
        $this->numeroTel = $numeroTel;

        return $this;
    }

    /**
     * Get the value of devis
     */
    public function getDevis()
    {
        return $this->devis;
    }

    /**
     * Set the value of devis
     *
     * @return  self
     */
    public function setDevis($devis)
    {
        $this->devis = $devis;

        return $this;
    }

    /**
     * Get the value of statutDemande
     */
    public function getStatutDemande()
    {
        return $this->statutDemande;
    }

    /**
     * Set the value of statutDemande
     *
     * @return  self
     */
    public function setStatutDemande($statutDemande)
    {
        $this->statutDemande = $statutDemande;

        return $this;
    }

    /**
     * Get the value of agenceEmetteur
     */
    public function getAgenceEmetteur()
    {
        return $this->agenceEmetteur;
    }

    /**
     * Set the value of agenceEmetteur
     *
     * @return  self
     */
    public function setAgenceEmetteur($agenceEmetteur)
    {
        $this->agenceEmetteur = $agenceEmetteur;

        return $this;
    }

    /**
     * Get the value of serviceEmetteur
     */
    public function getServiceEmetteur()
    {
        return $this->serviceEmetteur;
    }

    /**
     * Set the value of serviceEmetteur
     *
     * @return  self
     */
    public function setServiceEmetteur($serviceEmetteur)
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceDebiteur
     */
    public function getAgenceDebiteur()
    {
        return $this->agenceDebiteur;
    }

    /**
     * Set the value of agenceDebiteur
     *
     * @return  self
     */
    public function setAgenceDebiteur($agenceDebiteur)
    {
        $this->agenceDebiteur = $agenceDebiteur;

        return $this;
    }

    /**
     * Get the value of serviceDebiteur
     */
    public function getServiceDebiteur()
    {
        return $this->serviceDebiteur;
    }

    /**
     * Set the value of serviceDebiteur
     *
     * @return  self
     */
    public function setServiceDebiteur($serviceDebiteur)
    {
        $this->serviceDebiteur = $serviceDebiteur;

        return $this;
    }

    /**
     * Get the value of dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set the value of dateFin
     *
     * @return  self
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get the value of dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set the value of dateDebut
     *
     * @return  self
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }
}
