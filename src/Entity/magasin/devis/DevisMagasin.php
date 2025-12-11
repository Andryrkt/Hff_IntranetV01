<?php

namespace App\Entity\magasin\devis;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\magasin\devis\DevisMagasinRepository;

/**
 * @ORM\Entity(repositoryClass=DevisMagasinRepository::class)
 * @ORM\Table(name="devis_soumis_a_validation_neg")
 * @ORM\HasLifecycleCallbacks
 */
class DevisMagasin
{
    use DateTrait;

    public const STATUT_PRIX_A_CONFIRMER = 'Prix à confirmer';
    public const STATUT_PRIX_VALIDER_TANA = 'Prix validé - devis à envoyer au client';
    public const STATUT_PRIX_VALIDER_AGENCE = 'Prix validé - devis à soumettre';
    public const STATUT_PRIX_MODIFIER_TANA = 'Prix modifié - devis à envoyer au client';
    public const STATUT_PRIX_MODIFIER_AGENCE = 'Prix modifié - devis à soumettre';
    public const STATUT_DEMANDE_REFUSE_PAR_PM = 'Demande refusée par le PM';
    public const STATUT_A_VALIDER_CHEF_AGENCE = 'A valider chef d’agence';
    public const STATUT_VALIDE_AGENCE = 'Validé - à envoyer au client';
    public const STATUT_ENVOYER_CLIENT = 'Envoyé au client';
    public const STATUT_CLOTURER_A_MODIFIER = 'Cloturé - A modifier';
    public const STATUT_A_TRAITER = 'A traiter';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8, name="numero_devis", nullable=false)
     *
     * @var string
     */
    private string $numeroDevis;

    /**
     * @ORM\Column(type="integer", name="numero_version", nullable=false)
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="string", length=100, name="statut_dw", nullable=true)
     *
     * @var string|null
     */
    private ?string $statutDw = '';

    /**
     * @ORM\Column(type="integer", name="nombre_lignes", nullable=false)
     *
     * @var integer
     */
    private int $nombreLignes = 0;

    /**
     * @ORM\Column(type="float", name="montant_devis", nullable=false)
     *
     * @var float
     */
    private float $montantDevis = 0.00;

    /**
     * @ORM\Column(type="string", length=3, name="devise", nullable=false)
     *
     * @var string
     */
    private string $devise = '';

    /**
     * @ORM\Column(type="string", length=2, name="type_soumission", nullable=false)
     *
     * @var string
     */
    private string $typeSoumission = '';

    /**
     * @ORM\Column(type="datetime", name="date_maj_statut", nullable=true)
     *
     * @var [type]
     */
    private $dateMajStatut;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur", nullable=false)
     *
     * @var string
     */
    private string $utilisateur = '';

    /**
     * @ORM\Column(type="boolean", name="cat", options={"default": false}, nullable=false)
     */
    private bool $cat = false;

    /**
     * @ORM\Column(type="boolean", name="non_cat", options={"default": false}, nullable=false)
     */
    private bool $nonCat = false;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fichier", nullable=true)
     *
     * @var string
     */
    private string $nomFichier = '';

    /**
     * @ORM\Column(type="datetime", name="date_envoye_devis_client", nullable=true)
     *
     * @var [type]
     */
    private $dateEnvoiDevisAuClient = null;

    /**
     * @ORM\Column(type="integer", name="somme_numero_lignes", nullable=false)
     *
     * @var integer
     */
    private int $sommeNumeroLignes;

    /**
     * @ORM\Column(type="datetime", name="date_pointage", nullable=true)
     *
     * @var [type]
     */
    private $datePointage = null;


    /**
     * @ORM\Column(type="text", name="tache_validateur", nullable=true)
     *
     * @var string|null
     */
    private ?string $tacheValidateur = null;

    /**
     * @ORM\Column(type="boolean", name="est_validation_pm")
     */
    private $estValidationPm = false;

    /**
     * @ORM\Column(type="string", length=100, name="statut_bc", nullable=true)
     *
     * @var string|null
     */
    private ?string $statutBc = '';

    /**
     * @ORM\Column(type="string", length=100, name="relance", nullable=true)
     *
     * @var string|null
     */
    private ?string $relance = '';

    /**
     * @ORM\Column(type="datetime", name="date_bc", nullable=true)
     */
    private $dateBc = null;

    /**
     * @ORM\Column(type="string", length=5000, name="observation", nullable=true)
     *
     * @var string|null
     */
    private ?string $observation = null;


    private $pieceJoint01;

    private $pieceJoint2;

    public  $constructeur;

    /**
     * @ORM\Column(type="string", length=255, name="piece_joint_excel", nullable=true)
     *
     * @var string|null
     */
    private ?string $pieceJointExcel = null;

    /** =========================================
     * GETTERS & SETTERS
     *============================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDevis
     *
     * @return  string
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     *
     * @param  string  $numeroDevis
     *
     * @return  self
     */
    public function setNumeroDevis(string $numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;

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
     * Get the value of statutDw
     *
     * @return  string|null
     */
    public function getStatutDw()
    {
        return $this->statutDw;
    }

    /**
     * Set the value of statutDw
     *
     * @param  string|null  $statutDw
     *
     * @return  self
     */
    public function setStatutDw($statutDw)
    {
        $this->statutDw = $statutDw;

        return $this;
    }

    /**
     * Get the value of nombreLignes
     *
     * @return  integer
     */
    public function getNombreLignes()
    {
        return $this->nombreLignes;
    }

    /**
     * Set the value of nombreLignes
     *
     * @param  integer  $nombreLignes
     *
     * @return  self
     */
    public function setNombreLignes($nombreLignes)
    {
        $this->nombreLignes = $nombreLignes;

        return $this;
    }

    /**
     * Get the value of montantDevis
     *
     * @return  float
     */
    public function getMontantDevis()
    {
        return $this->montantDevis;
    }

    /**
     * Set the value of montantDevis
     *
     * @param  float  $montantDevis
     *
     * @return  self
     */
    public function setMontantDevis(float $montantDevis)
    {
        $this->montantDevis = $montantDevis;

        return $this;
    }

    /**
     * Get the value of devise
     *
     * @return  string
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Set the value of devise
     *
     * @param  string  $devise
     *
     * @return  self
     */
    public function setDevise(string $devise)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * Get the value of typeSoumission
     *
     * @return  string
     */
    public function getTypeSoumission()
    {
        return $this->typeSoumission;
    }

    /**
     * Set the value of typeSoumission
     *
     * @param  string  $typeSoumission
     *
     * @return  self
     */
    public function setTypeSoumission(string $typeSoumission)
    {
        $this->typeSoumission = $typeSoumission;

        return $this;
    }

    /**
     * Get the value of dateMajStatut
     */
    public function getDateMajStatut()
    {
        return $this->dateMajStatut;
    }

    /**
     * Set the value of dateMajStatut
     *
     * @param  $dateMajStatut
     *
     * @return  self
     */
    public function setDateMajStatut($dateMajStatut)
    {
        $this->dateMajStatut = $dateMajStatut;

        return $this;
    }

    /**
     * Get the value of utilisateur
     *
     * @return  string
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @param  string  $utilisateur
     *
     * @return  self
     */
    public function setUtilisateur(string $utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of cat
     */
    public function getCat()
    {
        return $this->cat;
    }

    /**
     * Set the value of cat
     *
     * @return  self
     */
    public function setCat($cat)
    {
        $this->cat = $cat;

        return $this;
    }

    /**
     * Get the value of nonCat
     */
    public function getNonCat()
    {
        return $this->nonCat;
    }

    /**
     * Set the value of nonCat
     *
     * @return  self
     */
    public function setNonCat($nonCat)
    {
        $this->nonCat = $nonCat;

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
     * Get the value of dateEnvoiDevisAuClient
     */
    public function getDateEnvoiDevisAuClient()
    {
        return $this->dateEnvoiDevisAuClient;
    }

    /**
     * Set the value of dateEnvoiDevisAuClient
     *
     * @return  self
     */
    public function setDateEnvoiDevisAuClient($dateEnvoiDevisAuClient)
    {
        $this->dateEnvoiDevisAuClient = $dateEnvoiDevisAuClient;

        return $this;
    }



    /**
     * Get the value of sommeNumeroLignes
     *
     * @return  integer
     */
    public function getSommeNumeroLignes()
    {
        return $this->sommeNumeroLignes;
    }

    /**
     * Set the value of sommeNumeroLignes
     *
     * @param  integer  $sommeNumeroLignes
     *
     * @return  self
     */
    public function setSommeNumeroLignes($sommeNumeroLignes)
    {
        $this->sommeNumeroLignes = $sommeNumeroLignes;

        return $this;
    }

    /**
     * Get the value of dateEnvoiDevisAuClient
     */
    public function getDatePointage()
    {
        return $this->datePointage;
    }

    /**
     * Set the value of dateEnvoiDevisAuClient
     *
     * @return  self
     */
    public function setDatePointage($datePointage)
    {
        $this->datePointage = $datePointage;

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
     * Get the value of pieceJoint2
     */
    public function getPieceJoint2()
    {
        return $this->pieceJoint2;
    }

    /**
     * Set the value of pieceJoint2
     */
    public function setPieceJoint2($pieceJoint2): self
    {
        $this->pieceJoint2 = $pieceJoint2;

        return $this;
    }

    /**
     * Get the value of tacheValidateur
     */
    public function getTacheValidateur(): array
    {
        return $this->tacheValidateur ? json_decode($this->tacheValidateur, true) : [];
    }

    /**
     * Set the value of tacheValidateur
     *
     * @return  self
     */
    public function setTacheValidateur(array $tacheValidateur): self
    {
        $this->tacheValidateur = json_encode($tacheValidateur);

        return $this;
    }

    /**
     * Get the value of statutBc
     *
     * @return  string|null
     */
    public function getStatutBc()
    {
        return $this->statutBc;
    }

    /**
     * Set the value of statutBc
     *
     * @param  string|null  $statutBc
     *
     * @return  self
     */
    public function setStatutBc($statutBc)
    {
        $this->statutBc = $statutBc;

        return $this;
    }

    /**
     * Get the value of relance
     *
     * @return  string|null
     */
    public function getRelance()
    {
        return $this->relance;
    }

    /**
     * Set the value of relance
     *
     * @param  string|null  $relance
     *
     * @return  self
     */
    public function setRelance($relance)
    {
        $this->relance = $relance;

        return $this;
    }

    /**
     * Get the value of estValidationPm
     */
    public function getEstValidationPm()
    {
        return $this->estValidationPm;
    }

    /**
     * Set the value of estValidationPm
     */
    public function setEstValidationPm($estValidationPm): self
    {
        $this->estValidationPm = $estValidationPm;

        return $this;
    }

    /**
     * Get the value of dateBc
     */
    public function getDateBc()
    {
        return $this->dateBc;
    }

    /**
     * Set the value of dateBc
     */
    public function setDateBc($dateBc): self
    {
        $this->dateBc = $dateBc;

        return $this;
    }

    /**
     * Get the value of observation
     */
    public function getObservation(): ?string
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     */
    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get the value of pieceJointExcel
     */
    public function getPieceJointExcel(): ?string
    {
        return $this->pieceJointExcel;
    }

    /**
     * Set the value of pieceJointExcel
     */
    public function setPieceJointExcel($pieceJointExcel): self
    {
        $this->pieceJointExcel = $pieceJointExcel;

        return $this;
    }
}
