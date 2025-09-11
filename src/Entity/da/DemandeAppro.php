<?php

namespace App\Entity\da;

use App\Controller\Traits\da\DaTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\Traits\DateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Repository\da\DemandeApproRepository;

/**
 * @ORM\Entity(repositoryClass=DemandeApproRepository::class)
 * @ORM\Table(name="Demande_Appro")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeAppro
{
    use DateTrait;
    use DaTrait;

    public const ID_APPRO                   = 16;
    public const ID_ATELIER                 = 3;
    public const MAIL_APPRO                 = 'hoby.ralahy@hff.mg';        // TODO: à changer en 'appro@hff.mg' si c'est en PROD
    public const STATUT_A_VALIDE_DW         = 'Soumis à validation';       /*_________ DA direct ________*/ // ! non cliquable par quiconque
    public const STATUT_VALIDE              = 'Bon d’achats validé';       /*__ DA direct et DA via OR __*/ // cliquable par Admin et Appro
    public const STATUT_TERMINER            = 'TERMINER';                  /*__ DA direct et DA via OR __*/ // ! non cliquable par quiconque
    public const STATUT_EN_COURS_CREATION   = 'En cours de création';      /*_________ DA via OR ________*/ // cliquable par Admin et Atelier
    public const STATUT_DW_A_MODIFIER       = 'DA à modifier';             /*_________ DA direct ________*/ // cliquable par Admin , service emetteur + chef de service
    public const STATUT_SOUMIS_APPRO        = 'Demande d’achats';          /*__ DA direct et DA via OR __*/ // cliquable par Admin et Appro
    public const STATUT_AUTORISER_MODIF_ATE = 'Création demande initiale'; /*_________ DA via OR ________*/ // cliquable par Admin et Atelier
    public const STATUT_SOUMIS_ATE          = 'Proposition achats';        /*__ DA direct et DA via OR __*/ // cliquable par Admin et (Atelier ou service emetteur) et Appro

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_appro")
     */
    private string $numeroDemandeAppro;

    private string $numeroOr;
    private string $statutOr;

    /**
     * @ORM\Column(type="boolean", name="achat_direct")
     */
    private $achatDirect = false;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit")
     */
    private string $numeroDemandeDit = '';

    /**
     * @ORM\Column(type="string", length=100, name="objet_dal")
     */
    private string $objetDal;

    /**
     * @ORM\Column(type="string", length=1000, name="detail_dal", nullable=true)
     */
    private string $detailDal;

    /**
     * @ORM\Column(type="string", length=6, name="agence_service_emmeteur")
     */
    private string $agenceServiceEmetteur;

    /**
     * @ORM\Column(type="string", length=6, name="agence_service_debiteur")
     */
    private string $agenceServiceDebiteur;

    /**
     * @ORM\Column(type="datetime", name="date_heure_fin_souhaitee", nullable=true)
     */
    private $dateFinSouhaite;

    /**
     * @ORM\Column(type="string", length=100, name="statut_dal", nullable=true)
     */
    private string $statutDal;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="daAgenceEmetteur")
     * @ORM\JoinColumn(name="agence_emmetteur_id", referencedColumnName="id")
     */
    private  $agenceEmetteur;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="daServiceEmetteur")
     * @ORM\JoinColumn(name="service_emmetteur_id", referencedColumnName="id")
     */
    private  $serviceEmetteur;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="daAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     */
    private  $agenceDebiteur;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="daServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     */
    private  $serviceDebiteur;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $demandeur = '';

    /**
     * @ORM\Column(type="integer", name="id_materiel", nullable=true)
     */
    private ?int $idMateriel = 0;

    /**
     * @ORM\OneToMany(targetEntity=DemandeApproL::class, mappedBy="demandeAppro")
     */
    private Collection $DAL;
    /**
     * @ORM\Column(type="string", length=100, name="statut_email")
     */
    private ?string $statutEmail = '';

    /**
     * @ORM\Column(type="boolean", name="est_validee")
     */
    private $estValidee = false;

    /**
     * @ORM\Column(type="string", length=50, name="valide_par")
     */
    private string $validePar;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fichier_bav")
     */
    private ?string $nomFichierBav = null;

    /**
     * @ORM\OneToMany(targetEntity=DaHistoriqueDemandeModifDA::class, mappedBy="demandeAppro")
     */
    private $historiqueDemandeModifDA;

    private ?DemandeIntervention $dit = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="demandeApproUser")
     * @ORM\JoinColumn(nullable=true, name="user_id", referencedColumnName="id")
     */
    private ?User $user;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="demandeApproValidateur")
     * @ORM\JoinColumn(nullable=true, name="validateur_id", referencedColumnName="id")
     */
    private ?User $validateur;

    private $observation;

    private $numDossierDouane;

    private bool $demandeDeverouillage = false;

    private array $daValiderOuProposer = [];

    /**
     * @ORM\Column(type="string", length=50, name="niveau_urgence")
     */
    private string $niveauUrgence = '';

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    public function __construct()
    {
        $this->DAL = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of numeroDemandeAppro
     *
     * @return string
     */
    public function getNumeroDemandeAppro(): string
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     *
     * @param string $numeroDemandeAppro
     *
     * @return self
     */
    public function setNumeroDemandeAppro(string $numeroDemandeAppro): self
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;
        return $this;
    }

    /**
     * Get the value of achatDirect
     */
    public function getAchatDirect()
    {
        return $this->achatDirect;
    }

    /**
     * Set the value of achatDirect
     *
     * @return  self
     */
    public function setAchatDirect($achatDirect)
    {
        $this->achatDirect = $achatDirect;

        return $this;
    }

    /**
     * Get the value of numeroDemandeDit
     *
     * @return string
     */
    public function getNumeroDemandeDit(): string
    {
        return $this->numeroDemandeDit;
    }

    /**
     * Set the value of numeroDemandeDit
     *
     * @param string $numeroDemandeDit
     *
     * @return self
     */
    public function setNumeroDemandeDit(string $numeroDemandeDit): self
    {
        $this->numeroDemandeDit = $numeroDemandeDit;
        return $this;
    }

    /**
     * Get the value of objetDal
     *
     * @return string
     */
    public function getObjetDal(): string
    {
        return $this->objetDal;
    }

    /**
     * Set the value of objetDal
     *
     * @param string $objetDal
     *
     * @return self
     */
    public function setObjetDal(string $objetDal): self
    {
        $this->objetDal = $objetDal;
        return $this;
    }

    /**
     * Get the value of detailDal
     *
     * @return string
     */
    public function getDetailDal(): string
    {
        return $this->detailDal;
    }

    /**
     * Set the value of detailDal
     *
     * @param string $detailDal
     *
     * @return self
     */
    public function setDetailDal(string $detailDal): self
    {
        $this->detailDal = $detailDal;
        return $this;
    }

    /**
     * Get the value of agenceServiceEmetteur
     *
     * @return string
     */
    public function getAgenceServiceEmetteur(): string
    {
        return $this->agenceServiceEmetteur;
    }

    /**
     * Set the value of agenceServiceEmetteur
     *
     * @param string $agenceServiceEmetteur
     *
     * @return self
     */
    public function setAgenceServiceEmetteur(string $agenceServiceEmetteur): self
    {
        $this->agenceServiceEmetteur = $agenceServiceEmetteur;
        return $this;
    }

    /**
     * Get the value of agenceServiceDebiteur
     *
     * @return string
     */
    public function getAgenceServiceDebiteur(): string
    {
        return $this->agenceServiceDebiteur;
    }

    /**
     * Set the value of agenceServiceDebiteur
     *
     * @param string $agenceServiceDebiteur
     *
     * @return self
     */
    public function setAgenceServiceDebiteur(string $agenceServiceDebiteur): self
    {
        $this->agenceServiceDebiteur = $agenceServiceDebiteur;
        return $this;
    }

    /**
     * Get the value of dateFinSouhaite
     */
    public function getDateFinSouhaite()
    {
        return $this->dateFinSouhaite;
    }

    /**
     * Set the value of dateFinSouhaite
     */
    public function setDateFinSouhaite($dateFinSouhaite): self
    {
        $this->dateFinSouhaite = $dateFinSouhaite;
        return $this;
    }

    /**
     * Définit la date de fin souhaitée automatiquement à 3 jours ouvrables à partir d'aujourd'hui.
     *
     * @return  self
     */
    public function setDateFinSouhaiteAutomatique()
    {
        $date = $this->ajouterJoursOuvrables(3);

        $this->setDateFinSouhaite($date);

        return $this;
    }

    /**
     * Get the value of statutDal
     *
     * @return string
     */
    public function getStatutDal(): string
    {
        return $this->statutDal;
    }

    /**
     * Set the value of statutDal
     *
     * @param string $statutDal
     *
     * @return self
     */
    public function setStatutDal(string $statutDal): self
    {
        $this->statutDal = $statutDal;
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
     */
    public function setAgenceEmetteur($agenceEmetteur): self
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
     */
    public function setServiceEmetteur($serviceEmetteur): self
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
     */
    public function setAgenceDebiteur($agenceDebiteur): self
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
     */
    public function setServiceDebiteur($serviceDebiteur): self
    {
        $this->serviceDebiteur = $serviceDebiteur;
        return $this;
    }

    /**
     * Get the value of dit
     */
    public function getDit()
    {
        return $this->dit;
    }

    /**
     * Set the value of dit
     *
     * @return  self
     */
    public function setDit($dit)
    {
        $this->dit = $dit;

        return $this;
    }

    /**
     * Get the value of DAL
     */
    public function getDAL(): Collection
    {
        return $this->DAL;
    }

    public function addDAL(DemandeApproL $DAL): void
    {
        if (!$this->DAL->contains($DAL)) {
            $this->DAL[] = $DAL;
            $DAL->setDemandeAppro($this);
        }
    }

    public function removeDAL(DemandeApproL $DAL): void
    {
        if ($this->DAL->removeElement($DAL)) {
            if ($DAL->getDemandeAppro() === $this) {
                $DAL->setDemandeAppro(null);
            }
        }
    }

    public function setDAL(Collection $collection): self
    {
        $this->DAL = $collection;
        return $this;
    }
    /**
     * Get the value of demandeur
     */
    public function getDemandeur()
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     *
     * @return  self
     */
    public function setDemandeur($demandeur)
    {
        $this->demandeur = $demandeur;

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
     * Get the value of observation
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     *
     * @return  self
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get the value of statutEmail
     */
    public function getStatutEmail()
    {
        return $this->statutEmail;
    }

    /**
     * Set the value of statutEmail
     *
     * @return  self
     */
    public function setStatutEmail($statutEmail)
    {
        $this->statutEmail = $statutEmail;

        return $this;
    }

    /**
     * Get the value of estValidee
     */
    public function getEstValidee()
    {
        return $this->estValidee;
    }

    /**
     * Set the value of estValidee
     *
     * @return  self
     */
    public function setEstValidee($estValidee)
    {
        $this->estValidee = $estValidee;

        return $this;
    }

    /**
     * Get the value of validePar
     */
    public function getValidePar()
    {
        return $this->validePar;
    }

    /**
     * Set the value of validePar
     *
     * @return  self
     */
    public function setValidePar($validePar)
    {
        $this->validePar = $validePar;

        return $this;
    }

    /**
     * Get the value of numDossierDouane
     */
    public function getNumDossierDouane()
    {
        return $this->numDossierDouane;
    }

    /**
     * Set the value of numDossierDouane
     *
     * @return  self
     */
    public function setNumDossierDouane($numDossierDouane)
    {
        $this->numDossierDouane = $numDossierDouane;

        return $this;
    }

    /**
     * Get the value of historiqueDemandeModifDA
     */
    public function getHistoriqueDemandeModifDA()
    {
        return $this->historiqueDemandeModifDA;
    }

    /**
     * Set the value of historiqueDemandeModifDA
     *
     * @return  self
     */
    public function setHistoriqueDemandeModifDA($historiqueDemandeModifDA)
    {
        $this->historiqueDemandeModifDA = $historiqueDemandeModifDA;

        return $this;
    }

    /**
     * Get the value of demandeDeverouillage
     */
    public function getDemandeDeverouillage()
    {
        return $this->demandeDeverouillage;
    }

    /**
     * Set the value of demandeDeverouillage
     *
     * @return  self
     */
    public function setDemandeDeverouillage($demandeDeverouillage)
    {
        $this->demandeDeverouillage = $demandeDeverouillage;

        return $this;
    }

    /**
     * Get the value of daValiderOuProposer
     */
    public function getDaValiderOuProposer()
    {
        return $this->daValiderOuProposer;
    }

    /**
     * Set the value of daValiderOuProposer
     *
     * @return  self
     */
    public function setDaValiderOuProposer($daValiderOuProposer)
    {
        $this->daValiderOuProposer = $daValiderOuProposer;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

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
     * Get the value of numeroOr
     */
    public function getNumeroOr()
    {
        return $this->numeroOr;
    }

    /**
     * Set the value of numeroOr
     *
     * @return  self
     */
    public function setNumeroOr($numeroOr)
    {
        $this->numeroOr = $numeroOr;

        return $this;
    }

    /**
     * Get the value of statutOr
     */
    public function getStatutOr()
    {
        return $this->statutOr;
    }

    /**
     * Set the value of statutOr
     *
     * @return  self
     */
    public function setStatutOr($statutOr)
    {
        $this->statutOr = $statutOr;

        return $this;
    }

    /**
     * Get the value of nomFichierBav
     */
    public function getNomFichierBav()
    {
        return $this->nomFichierBav;
    }

    /**
     * Set the value of nomFichierBav
     *
     * @return  self
     */
    public function setNomFichierBav($nomFichierBav)
    {
        $this->nomFichierBav = $nomFichierBav;

        return $this;
    }
}
