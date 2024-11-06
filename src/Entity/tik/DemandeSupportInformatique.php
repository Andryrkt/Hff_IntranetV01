<?php
namespace App\Entity\tik;

use App\Entity\Traits\AgenceServiceTrait;
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
     * @ORM\Column(type="integer", name="ID_TKI_Categorie")
     * TODO : RELATION A FAITE
     */
    private int $categorie;

    /**
     * @ORM\Column(type="integer", name="ID_TKI_Sous_Categorie")
     * TODO : RELATION A FAITE
     */
    private int $sousCategorie;

    /**
     * @ORM\Column(type="integer", nullable=true, name="ID_TKI_Autres_Categorie")
     * TODO : RELATION A FAITE
     */
    private ?int $autresCategorie = null;

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
     * @ORM\Column(type="integer", name="ID_Niveau_Urgence")
     * TODO : RELATIION A FAIRE
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
     * @Groups("intervention")
     */
    private  $agenceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="ditServiceEmetteur")
     * @ORM\JoinColumn(name="service_emetteur_id", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $serviceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="ditAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $agenceDebiteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="ditServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     * @Groups("intervention")
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