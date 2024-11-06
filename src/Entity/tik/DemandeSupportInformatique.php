<?php
namespace App\Entity\tik;

use App\Entity\admin\tik\TkiCategorie;
use App\Entity\Traits\AgenceServiceTrait;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\Traits\AgenceServiceEmetteurTrait;
use Symfony\Component\Validator\Constraints\DateTime;
use App\Repository\tik\DemandeSupportInformatiqueRepository;

/**
 * @ORM\Entity(repositoryClass=DemandeSupportInformatiqueRepository::class)
 * @ORM\Table(name="Demande_Support_Informatique")Date_Fin
 */
class DemandeSupportInformatique
{
    use AgenceServiceEmetteurTrait;
    use AgenceServiceTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Support_Informatique")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime", name="Date_Creation")
     */
    private $dateCreation;

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
    private string $codeSociete;

    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(nullable=false, name="ID_Categorie", referencedColumnName="id")
     */
    private ?TkiCategorie $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=TkiSousCategorie::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(nullable=false, name="ID_Sous_Categorie", referencedColumnName="id")
     */
    private ?TkiSousCategorie $sousCategorie;

   /**
     * @ORM\ManyToOne(targetEntity=TkiAutresCategorie::class, inversedBy="supportInfo")
     * @ORM\JoinColumn(nullable=false, name="ID_Autres_Categorie", referencedColumnName="id")
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
     * @ORM\Column(type="string", length=100, name="Mail_Intervenant")
     */
    private string $nomIntervenant;

    /**
     * @ORM\Column(type="string", length=100, name="Nom_Intervenant")
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
    private ?string $pieceJointe1 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe2")
     */
    private ?string $pieceJointe2 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe3")
     */
    private ?string $pieceJointe3 = null;

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
     * @ORM\JoinColumn(nullable=false, name="id", referencedColumnName="id")
     */
    private int $niveauUrgence;

    /**
     * @ORM\Column(type="string", length=50, name="Parc_Informatique")
     */
    private string $parcInformatique;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Souhaitee")
     */
    private $dateFinSouhaitee;

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

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function setDateCreation($dateCreation): self
    {
        $this->dateCreation = $dateCreation;
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
    public function getPieceJointe1()
    {
        return $this->pieceJointe1;
    }

    /**
     * Set the value of pieceJointe1
     *
     * @return  self
     */ 
    public function setPieceJointe1($pieceJointe1)
    {
        $this->pieceJointe1 = $pieceJointe1;

        return $this;
    }

    /**
     * Get the value of pieceJointe2
     */ 
    public function getPieceJointe2()
    {
        return $this->pieceJointe2;
    }

    /**
     * Set the value of pieceJointe2
     *
     * @return  self
     */ 
    public function setPieceJointe2($pieceJointe2)
    {
        $this->pieceJointe2 = $pieceJointe2;

        return $this;
    }

    /**
     * Get the value of pieceJointe3
     */ 
    public function getPieceJointe3()
    {
        return $this->pieceJointe3;
    }

    /**
     * Set the value of pieceJointe3
     *
     * @return  self
     */ 
    public function setPieceJointe3($pieceJointe3)
    {
        $this->pieceJointe3 = $pieceJointe3;

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

}