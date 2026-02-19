<?php

namespace App\Entity\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\tik\TkiPlanning;
use DateTime as GlobalDateTime;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\utilisateur\User;
use App\Entity\Traits\AgenceServiceTrait;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\Traits\AgenceServiceEmetteurTrait;
use App\Repository\tik\DemandeSupportInformatiqueRepository;

/**
 * @ORM\Entity(repositoryClass=DemandeSupportInformatiqueRepository::class)
 * @ORM\Table(name="Demande_Support_Informatique")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeSupportInformatique
{
    use AgenceServiceEmetteurTrait;
    use AgenceServiceTrait;
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Support_Informatique")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=11, name="Numero_Ticket")
     */
    private string $numeroTicket;

    /**
     * @ORM\Column(type="string", length=5, name="heure_creation")
     */
    private $heureCreation;

    /**
     * @ORM\Column(type="string", length=50, name="Utilisateur_Demandeur")
     */
    private string $utilisateurDemandeur;

    /**
     * @ORM\Column(type="string", length=50, name="Mail_Demandeur")
     */
    private string $mailDemandeur;

    /**
     * @ORM\Column(type="string", length=1000, name="Mail_En_Copie")
     */
    private string $mailEnCopie;

    /**
     * @ORM\Column(type="string", length=2, name="Code_Societe")
     */
    private ?string $codeSociete;

    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(nullable=false, name="ID_TKI_Categorie", referencedColumnName="id")
     */
    private ?TkiCategorie $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=TkiSousCategorie::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(nullable=false, name="ID_TKL_Sous_Categorie", referencedColumnName="id")
     */
    private ?TkiSousCategorie $sousCategorie;

    /**
     * @ORM\ManyToOne(targetEntity=TkiAutresCategorie::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(nullable=false, name="ID_TKL_Autres_Categorie", referencedColumnName="id")
     */
    private ?TkiAutresCategorie $autresCategorie = null;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Emetteur")
     */
    private string $agenceServiceEmetteur;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Debiteur")
     */
    private string $agenceServiceDebiteur;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true, name="ID_Intervenant", referencedColumnName="id")
     */
    private ?User $intervenant;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true, name="ID_Validateur", referencedColumnName="id")
     */
    private ?User $validateur;

    /**
     * @ORM\Column(type="string", length=100, name="Nom_Intervenant")
     */
    private ?string $nomIntervenant = null;

    /**
     * @ORM\Column(type="string", length=100, name="Mail_Intervenant")
     */
    private ?string $mailIntervenant = null;

    /**
     * @ORM\Column(type="string", length=100, name="Objet_Demande")
     */
    private string $objetDemande;

    /**
     * @ORM\Column(type="string", length=5000, name="Detail_Demande")
     */
    private string $detailDemande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe1")
     */
    private ?string $pieceJoint01 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe2")
     */
    private ?string $pieceJoint02 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe3")
     */
    private ?string $pieceJoint03 = null;

    /**
     * @ORM\Column(type="datetime", name="Date_Deb_Planning")
     */
    private $dateDebutPlanning;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Planning")
     */
    private $dateFinPlanning;

    /**
     * @ORM\ManyToOne(targetEntity=WorNiveauUrgence::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(name="ID_Niveau_Urgence", referencedColumnName="id")
     */
    private ?WorNiveauUrgence $niveauUrgence;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, name="Parc_Informatique")
     */
    private ?string $parcInformatique = null;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Souhaitee")
     */
    private $dateFinSouhaitee;

    /**
     * @ORM\Column(type="json", name="file_names")
     */
    private $fileNames = [];

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="ditAgenceEmetteur")
     * @ORM\JoinColumn(name="agence_emetteur_id", referencedColumnName="id")
     *
     */
    private  $agenceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="ditServiceEmetteur")
     * @ORM\JoinColumn(name="service_emetteur_id", referencedColumnName="id")
     * 
     */
    private  $serviceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="ditAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     * 
     */
    private  $agenceDebiteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="ditServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     * 
     */
    private  $serviceDebiteurId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * 
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity=StatutDemande::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(name="id_statut_demande", referencedColumnName="ID_Statut_Demande")
     */
    private $idStatutDemande = null;

    /**
     * @ORM\OneToMany(targetEntity=TkiCommentaires::class, mappedBy="demandeSupportInformatique")
     */
    private $commentaires;

    /**
     * @ORM\OneToOne(targetEntity=TkiPlanning::class, mappedBy="demandeSupportInfo", cascade={"persist", "remove"})
     */
    private $planning;

    /**
     * @ORM\OneToOne(targetEntity=TkiReplannification::class, mappedBy="demandeSupportInfo", cascade={"persist", "remove"})
     */
    private $replanification;

    /**
     * @ORM\Column(type="string", length=2, nullable=true, name="part_day_planning")
     */
    private ?string $partOfDay;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroTicket
     */
    public function getNumeroTicket()
    {
        return $this->numeroTicket;
    }

    /**
     * Set the value of numeroTicket
     *
     * @return  self
     */
    public function setNumeroTicket($numeroTicket)
    {
        $this->numeroTicket = $numeroTicket;

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
     * Get the value of utilisateurDemandeur
     */
    public function getUtilisateurDemandeur()
    {
        return $this->utilisateurDemandeur;
    }

    /**
     * Set the value of utilisateurDemandeur
     *
     * @return  self
     */
    public function setUtilisateurDemandeur($utilisateurDemandeur)
    {
        $this->utilisateurDemandeur = $utilisateurDemandeur;

        return $this;
    }

    /**
     * Get the value of mailDemandeur
     */
    public function getMailDemandeur()
    {
        return $this->mailDemandeur;
    }

    /**
     * Set the value of mailDemandeur
     *
     * @return  self
     */
    public function setMailDemandeur($mailDemandeur)
    {
        $this->mailDemandeur = $mailDemandeur;

        return $this;
    }

    /**
     * Get the value of mailEnCopie
     */
    public function getMailEnCopie()
    {
        return $this->mailEnCopie;
    }

    /**
     * Set the value of mailEnCopie
     *
     * @return  self
     */
    public function setMailEnCopie($mailEnCopie)
    {
        $this->mailEnCopie = $mailEnCopie;

        return $this;
    }

    /**
     * Get the value of codeSociete
     */
    public function getCodeSociete()
    {
        return $this->codeSociete;
    }

    /**
     * Set the value of codeSociete
     *
     * @return  self
     */
    public function setCodeSociete($codeSociete)
    {
        $this->codeSociete = $codeSociete;

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
     * Get the value of sousCategorie
     */
    public function getSousCategorie()
    {
        return $this->sousCategorie;
    }

    /**
     * Set the value of sousCategorie
     *
     * @return  self
     */
    public function setSousCategorie($sousCategorie)
    {
        $this->sousCategorie = $sousCategorie;

        return $this;
    }

    /**
     * Get the value of autresCategorie
     */
    public function getAutresCategorie()
    {
        return $this->autresCategorie;
    }

    /**
     * Set the value of autresCategorie
     *
     * @return  self
     */
    public function setAutresCategorie($autresCategorie)
    {
        $this->autresCategorie = $autresCategorie;

        return $this;
    }

    /**
     * Get the value of agenceServiceEmetteur
     */
    public function getAgenceServiceEmetteur()
    {
        return $this->agenceServiceEmetteur;
    }

    /**
     * Set the value of agenceServiceEmetteur
     *
     * @return  self
     */
    public function setAgenceServiceEmetteur($agenceServiceEmetteur)
    {
        $this->agenceServiceEmetteur = $agenceServiceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceServiceDebiteur
     */
    public function getAgenceServiceDebiteur()
    {
        return $this->agenceServiceDebiteur;
    }

    /**
     * Set the value of agenceServiceDebiteur
     *
     * @return  self
     */
    public function setAgenceServiceDebiteur($agenceServiceDebiteur)
    {
        $this->agenceServiceDebiteur = $agenceServiceDebiteur;

        return $this;
    }

    /**
     * Get the value of nomIntervenant
     */
    public function getNomIntervenant()
    {
        return $this->nomIntervenant;
    }

    /**
     * Set the value of nomIntervenant
     *
     * @return  self
     */
    public function setNomIntervenant($nomIntervenant)
    {
        $this->nomIntervenant = $nomIntervenant;

        return $this;
    }

    /**
     * Get the value of mailIntervenant
     */
    public function getMailIntervenant()
    {
        return $this->mailIntervenant;
    }

    /**
     * Set the value of mailIntervenant
     *
     * @return  self
     */
    public function setMailIntervenant($mailIntervenant)
    {
        $this->mailIntervenant = $mailIntervenant;

        return $this;
    }

    /**
     * Get the value of objetDemande
     */
    public function getObjetDemande()
    {
        return $this->objetDemande;
    }

    /**
     * Set the value of objetDemande
     *
     * @return  self
     */
    public function setObjetDemande($objetDemande)
    {
        $this->objetDemande = $objetDemande;

        return $this;
    }



    /**
     * Get the value of detailDemande
     */
    public function getDetailDemande()
    {
        return $this->detailDemande;
    }

    /**
     * Set the value of detailDemande
     *
     * @return  self
     */
    public function setDetailDemande($detailDemande)
    {
        $this->detailDemande = $detailDemande;

        return $this;
    }

    /**
     * Get the value of pieceJointe1
     */
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of pieceJointe1
     *
     * @return  self
     */
    public function setPieceJoint01($pieceJointe1)
    {
        $this->pieceJoint01 = $pieceJointe1;

        return $this;
    }

    /**
     * Get the value of pieceJointe2
     */
    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    /**
     * Set the value of pieceJointe2
     *
     * @return  self
     */
    public function setPieceJoint02($pieceJointe2)
    {
        $this->pieceJoint02 = $pieceJointe2;

        return $this;
    }

    /**
     * Get the value of pieceJointe3
     */
    public function getPieceJoint03()
    {
        return $this->pieceJoint03;
    }

    /**
     * Set the value of pieceJointe3
     *
     * @return  self
     */
    public function setPieceJoint03($pieceJointe3)
    {
        $this->pieceJoint03 = $pieceJointe3;

        return $this;
    }

    /**
     * Get the value of dateDebutPlanning
     */
    public function getDateDebutPlanning()
    {
        return $this->dateDebutPlanning;
    }

    /**
     * Set the value of dateDebutPlanning
     *
     * @return  self
     */
    public function setDateDebutPlanning($dateDebutPlanning)
    {
        $this->dateDebutPlanning = $dateDebutPlanning;

        return $this;
    }

    /**
     * Get the value of dateFinPlanning
     */
    public function getDateFinPlanning()
    {
        return $this->dateFinPlanning;
    }

    /**
     * Set the value of dateFinPlanning
     *
     * @return  self
     */
    public function setDateFinPlanning($dateFinPlanning)
    {
        $this->dateFinPlanning = $dateFinPlanning;

        return $this;
    }

    /**
     * Get the value of niveauUrgence
     */
    public function getNiveauUrgence()
    {
        return $this->niveauUrgence;
    }

    /**
     * Set the value of niveauUrgence
     *
     * @return  self
     */
    public function setNiveauUrgence($niveauUrgence)
    {
        $this->niveauUrgence = $niveauUrgence;

        return $this;
    }

    /**
     * Get the value of parcInformatique
     */
    public function getParcInformatique()
    {
        return $this->parcInformatique;
    }

    /**
     * Set the value of parcInformatique
     *
     * @return  self
     */
    public function setParcInformatique($parcInformatique)
    {
        $this->parcInformatique = $parcInformatique;

        return $this;
    }

    /**
     * Get the value of dateFinSouhaitee
     */
    public function getDateFinSouhaitee()
    {
        return $this->dateFinSouhaitee;
    }

    /**
     * Set the value of dateFinSouhaitee
     *
     * @return  self
     */
    public function setDateFinSouhaitee($dateFinSouhaitee)
    {
        $this->dateFinSouhaitee = $dateFinSouhaitee;

        return $this;
    }

    /**
     * Set the automatic value of dateFinSouhaitee
     *
     * @return  self
     */
    public function setDateFinSouhaiteeAutomatique()
    {
        $date = new GlobalDateTime();

        // Compteur pour les jours ouvrables ajoutés
        $joursOuvrablesAjoutes = 0;

        // Ajouter des jours jusqu'à obtenir 2 jours ouvrables
        while ($joursOuvrablesAjoutes < 2) {
            // Ajouter un jour
            $date->modify('+1 day');

            // Vérifier si le jour actuel est un jour ouvrable (ni samedi ni dimanche)
            if ($date->format('N') < 6) { // 'N' donne 1 (lundi) à 7 (dimanche)
                $joursOuvrablesAjoutes++;
            }
        }

        $this->setDateFinSouhaitee($date);

        return $this;
    }

    /**
     * Get the value of fileNames
     */
    public function getFileNames()
    {
        return $this->fileNames;
    }

    /**
     * Set the value of fileNames
     *
     * @return  self
     */
    public function setFileNames($fileNames)
    {
        $this->fileNames = $fileNames;

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
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of idStatutDemande
     */
    public function getIdStatutDemande()
    {
        return $this->idStatutDemande;
    }

    /**
     * Set the value of idStatutDemande
     *
     * @return  self
     */
    public function setIdStatutDemande($idStatutDemande)
    {
        $this->idStatutDemande = $idStatutDemande;

        return $this;
    }

    /**
     * Get the value of intervenant
     */
    public function getIntervenant()
    {
        return $this->intervenant;
    }

    /**
     * Set the value of intervenant
     *
     * @return  self
     */
    public function setIntervenant($intervenant)
    {
        $this->intervenant = $intervenant;

        return $this;
    }

    public function getPlanning(): ?TkiPlanning
    {
        return $this->planning;
    }

    public function setPlanning(?TkiPlanning $planning): self
    {
        // set the owning side of the relation if necessary
        if ($planning && $planning->getDemandeSupportInfo() !== $this) {
            $planning->setDemandeSupportInfo($this);
        }

        $this->planning = $planning;

        return $this;
    }

    /**
     * Get the value of validateur
     */
    public function getValidateur()
    {
        return $this->validateur;
    }

    /**
     * Set the value of validateur
     *
     * @return  self
     */
    public function setValidateur($validateur)
    {
        $this->validateur = $validateur;

        return $this;
    }

    /**
     * Get the value of commentaires
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * Set the value of commentaires
     *
     * @return  self
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get the value of partOfDay
     */
    public function getPartOfDay()
    {
        return $this->partOfDay;
    }

    /**
     * Set the value of partOfDay
     *
     * @return  self
     */
    public function setPartOfDay($partOfDay)
    {
        $this->partOfDay = $partOfDay;

        return $this;
    }

    /**
     * Get the value of replanification
     */
    public function getReplanification()
    {
        return $this->replanification;
    }

    /**
     * Set the value of replanification
     *
     * @return  self
     */
    public function setReplanification($replanification)
    {
        $this->replanification = $replanification;

        return $this;
    }
}
