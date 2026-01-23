<?php

namespace App\Entity\da;

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
use App\Entity\Traits\DateTrait;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=DemandeApproLRRepository::class)
 * @ORM\Table(name="Demande_Appro_L_R")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeApproLR
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12, name="numero_demande_appro")
     */
    private string $numeroDemandeAppro;

    /**
     * @ORM\Column(type="string", length=11, name="num_ligne_dem")
     */
    private string $numeroLigne;

    /**
     * @ORM\Column(type="integer", name="qte_dem")
     */
    private $qteDem;

    /**
     * @ORM\Column(type="integer", name="qte_dispo")
     */
    private $qteDispo;

    /**
     * @ORM\Column(type="string", length=3, name="art_constp")
     */
    private string $artConstp;

    /**
     * @ORM\Column(type="string", length=50, name="art_refp")
     */
    private string $artRefp = 'ST';

    /**
     * @ORM\Column(type="string", length=100, name="art_desi")
     */
    private string $artDesi;

    /**
     * @ORM\Column(type="string", length=50, name="art_fams1")
     */
    private ?string $artFams1 = '-';

    /**
     * @ORM\Column(type="string", length=50, name="art_fams2")
     */
    private ?string $artFams2 = '-';

    /**
     * @ORM\Column(type="string", length=7, name="numero_fournisseur")
     */
    private string $numeroFournisseur;

    /**
     * @ORM\Column(type="string", length=50, name="nom_fournisseur")
     */
    private string $nomFournisseur;

    /**
     * @ORM\Column(type="string", length=100, name="PU")
     */
    private string $prixUnitaire;

    /**
     * @ORM\Column(type="string", length=100, name="total")
     */
    private string $total;

    /**
     * @ORM\Column(type="string", length=10, name="conditionnement")
     */
    private string $conditionnement;

    /**
     * @ORM\Column(type="string", length=1000, name="motif")
     */
    private string $motif;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeApproL::class, inversedBy="demandeApproLR")
     * @ORM\JoinColumn(name="demande_appro_l_id", referencedColumnName="id")
     */
    private ?DemandeApproL $demandeApproL = null;

    /**
     * @ORM\Column(type="boolean", name="est_validee")
     */
    private $estValidee = false;

    /**
     * @ORM\Column(type="integer", name="num_ligne_tableau")
     */
    private $numLigneTableau = 0;

    /**
     * @ORM\Column(type="boolean", name="choix")
     */
    private $choix = false;

    /**
     * @ORM\Column(type="string", length=10, name="code_fams1")
     */
    private ?string $codeFams1 = null;

    /**
     * @ORM\Column(type="string", length=10, name="code_fams2")
     */
    private ?string $codeFams2 = null;

    /**
     * @ORM\Column(type="string", length=50, name="valide_par")
     */
    private ?string $validePar = null;

    /**
     * @ORM\Column(type="boolean", name="deleted")
     */
    private $deleted = false;

    /**
     * @ORM\Column(type="boolean", name="est_fiche_technique")
     */
    private $estFicheTechnique = false;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fiche_technique")
     */
    private $nomFicheTechnique;

    /**
     * @ORM\Column(type="datetime", name="date_fin_souhaitee_l", nullable=true)
     */
    private $dateFinSouhaite;

    /**
     * @ORM\Column(type="json", name="file_names")
     */
    private $fileNames = [];

    private $qteLivee;
    private $qteALivrer;
    private $qteEnAttent = 0;
    private $statutBc;

    private $datePlanningOR;

    /**
     * @ORM\Column(type="string", length=50, name="statut_dal")
     */
    private ?string $statutDal;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit")
     */
    private ?string $numeroDemandeDit;

    /**
     * @ORM\Column(type="integer", name="jours_dispo")
     *
     * @var integer | null
     */
    private ?int $joursDispo;

    private $verouille = false;

    /**
     * @ORM\Column(type="datetime", name="date_livraison_prevue", nullable=true)
     */
    private $dateLivraisonPrevue;


    /**==============================================================================
     * GETTERS & SETTERS
     *===============================================================================*/

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
     * Get the value of qteDem
     */
    public function getQteDem()
    {
        return $this->qteDem;
    }

    /**
     * Set the value of qteDem
     */
    public function setQteDem($qteDem): self
    {
        $this->qteDem = $qteDem;
        return $this;
    }

    /**
     * Get the value of qteDispo
     */
    public function getQteDispo()
    {
        return $this->qteDispo;
    }

    /**
     * Set the value of qteDispo
     */
    public function setQteDispo($qteDispo): self
    {
        $this->qteDispo = $qteDispo;
        return $this;
    }

    /**
     * Get the value of artConstp
     *
     * @return string
     */
    public function getArtConstp(): string
    {
        return $this->artConstp;
    }

    /**
     * Set the value of artConstp
     *
     * @param string $artConstp
     *
     * @return self
     */
    public function setArtConstp(string $artConstp): self
    {
        $this->artConstp = $artConstp;
        return $this;
    }

    /**
     * Get the value of artRefp
     *
     * @return string
     */
    public function getArtRefp(): string
    {
        return $this->artRefp;
    }

    /**
     * Set the value of artRefp
     *
     * @param string $artRefp
     *
     * @return self
     */
    public function setArtRefp(string $artRefp): self
    {
        $this->artRefp = $artRefp;
        return $this;
    }

    /**
     * Get the value of artDesi
     *
     * @return string
     */
    public function getArtDesi(): string
    {
        return $this->artDesi;
    }

    /**
     * Set the value of artDesi
     *
     * @param string $artDesi
     *
     * @return self
     */
    public function setArtDesi(string $artDesi): self
    {
        $this->artDesi = $artDesi;
        return $this;
    }

    /**
     * Get the value of artFams1
     *
     * @return string
     */
    public function getArtFams1(): ?string
    {
        return $this->artFams1;
    }

    /**
     * Set the value of artFams1
     *
     * @param ?string $artFams1
     *
     * @return self
     */
    public function setArtFams1(?string $artFams1): self
    {
        $this->artFams1 = $artFams1;
        return $this;
    }

    /**
     * Get the value of artFams2
     *
     * @return string
     */
    public function getArtFams2(): ?string
    {
        return $this->artFams2;
    }

    /**
     * Set the value of artFams2
     *
     * @param string $artFams2
     *
     * @return self
     */
    public function setArtFams2(?string $artFams2): self
    {
        $this->artFams2 = $artFams2;
        return $this;
    }

    /**
     * Get the value of numeroFournisseur
     *
     * @return string
     */
    public function getNumeroFournisseur(): string
    {
        return $this->numeroFournisseur;
    }

    /**
     * Set the value of numeroFournisseur
     *
     * @param string $numeroFournisseur
     *
     * @return self
     */
    public function setNumeroFournisseur(string $numeroFournisseur): self
    {
        $this->numeroFournisseur = $numeroFournisseur;
        return $this;
    }

    /**
     * Get the value of nomFournisseur
     *
     * @return string
     */
    public function getNomFournisseur(): string
    {
        return $this->nomFournisseur;
    }

    /**
     * Set the value of nomFournisseur
     *
     * @param string $nomFournisseur
     *
     * @return self
     */
    public function setNomFournisseur(string $nomFournisseur): self
    {
        $this->nomFournisseur = $nomFournisseur;
        return $this;
    }

    /**
     * Get the value of demandeApproL
     */
    public function getDemandeApproL()
    {
        return $this->demandeApproL;
    }

    /**
     * Set the value of demandeApproL
     *
     * @return  self
     */
    public function setDemandeApproL($demandeApproL)
    {
        $this->demandeApproL = $demandeApproL;

        return $this;
    }

    /**
     * Get the value of prixUnitaire
     */
    public function getPrixUnitaire()
    {
        return $this->prixUnitaire;
    }

    /**
     * Set the value of prixUnitaire
     *
     * @return  self
     */
    public function setPrixUnitaire($prixUnitaire)
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    /**
     * Get the value of total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set the value of total
     *
     * @return  self
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get the value of conditionnement
     */
    public function getConditionnement()
    {
        return $this->conditionnement;
    }

    /**
     * Set the value of conditionnement
     *
     * @return  self
     */
    public function setConditionnement($conditionnement)
    {
        $this->conditionnement = $conditionnement;

        return $this;
    }

    /**
     * Get the value of motif
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set the value of motif
     *
     * @return  self
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;

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
     * Get the value of numLigneTableau
     */
    public function getNumLigneTableau()
    {
        return $this->numLigneTableau;
    }

    /**
     * Set the value of numLigneTableau
     *
     * @return  self
     */
    public function setNumLigneTableau($numLigneTableau)
    {
        $this->numLigneTableau = $numLigneTableau;

        return $this;
    }

    /**
     * Get the value of choix
     */
    public function getChoix()
    {
        return $this->choix;
    }

    /**
     * Set the value of choix
     *
     * @return  self
     */
    public function setChoix($choix)
    {
        $this->choix = $choix;

        return $this;
    }

    /**
     * Get the value of codeFams1
     */
    public function getCodeFams1()
    {
        return $this->codeFams1;
    }

    /**
     * Set the value of codeFams1
     *
     * @return  self
     */
    public function setCodeFams1($codeFams1)
    {
        $this->codeFams1 = $codeFams1;

        return $this;
    }

    /**
     * Get the value of codeFams2
     */
    public function getCodeFams2()
    {
        return $this->codeFams2;
    }

    /**
     * Set the value of codeFams2
     *
     * @return  self
     */
    public function setCodeFams2($codeFams2)
    {
        $this->codeFams2 = $codeFams2;

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
     * Get the value of deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set the value of deleted
     *
     * @return  self
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get the value of estFicheTechnique
     */
    public function getEstFicheTechnique()
    {
        return $this->estFicheTechnique;
    }

    /**
     * Set the value of estFicheTechnique
     *
     * @return  self
     */
    public function setEstFicheTechnique($estFicheTechnique)
    {
        $this->estFicheTechnique = $estFicheTechnique;

        return $this;
    }

    /**
     * Get the value of nomFicheTechnique
     */
    public function getNomFicheTechnique()
    {
        return $this->nomFicheTechnique;
    }

    /**
     * Set the value of nomFicheTechnique
     */
    public function setNomFicheTechnique($nomFicheTechnique): self
    {
        $this->nomFicheTechnique = $nomFicheTechnique;
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

    /**
     * Get the value of qteLivee
     */
    public function getQteLivee()
    {
        return $this->qteLivee;
    }

    /**
     * Set the value of qteLivee
     *
     * @return  self
     */
    public function setQteLivee($qteLivee)
    {
        $this->qteLivee = $qteLivee;

        return $this;
    }

    /**
     * Get the value of qteALivrer
     */
    public function getQteALivrer()
    {
        return $this->qteALivrer;
    }

    /**
     * Set the value of qteALivrer
     *
     * @return  self
     */
    public function setQteALivrer($qteALivrer)
    {
        $this->qteALivrer = $qteALivrer;

        return $this;
    }

    /**
     * Get the value of statutBc
     */
    public function getStatutBc()
    {
        return $this->statutBc;
    }

    /**
     * Set the value of statutBc
     *
     * @return  self
     */
    public function setStatutBc($statutBc)
    {
        $this->statutBc = $statutBc;

        return $this;
    }

    /**
     * Get the value of statutDal
     */
    public function getStatutDal()
    {
        return $this->statutDal;
    }

    /**
     * Set the value of statutDal
     *
     * @return  self
     */
    public function setStatutDal($statutDal)
    {
        $this->statutDal = $statutDal;

        return $this;
    }

    /**
     * Get the value of numeroDemandeDit
     */
    public function getNumeroDemandeDit()
    {
        return $this->numeroDemandeDit;
    }

    /**
     * Set the value of numeroDemandeDit
     *
     * @return  self
     */
    public function setNumeroDemandeDit($numeroDemandeDit)
    {
        $this->numeroDemandeDit = $numeroDemandeDit;

        return $this;
    }

    /**
     * Get | null
     *
     * @return  integer
     */
    public function getJoursDispo()
    {
        return $this->joursDispo;
    }

    /**
     * Set | null
     *
     * @param  integer  $joursDispo  | null
     *
     * @return  self
     */
    public function setJoursDispo($joursDispo)
    {
        $this->joursDispo = $joursDispo;

        return $this;
    }

    /**
     * Get the value of qteEnAttent
     */
    public function getQteEnAttent()
    {
        return $this->qteEnAttent;
    }

    /**
     * Set the value of qteEnAttent
     *
     * @return  self
     */
    public function setQteEnAttent($qteEnAttent)
    {
        $this->qteEnAttent = $qteEnAttent;

        return $this;
    }

    /**
     * Get the value of verouille
     */
    public function getVerouille()
    {
        return $this->verouille;
    }

    /**
     * Set the value of verouille
     *
     * @return  self
     */
    public function setVerouille($verouille)
    {
        $this->verouille = $verouille;

        return $this;
    }

    /**
     * Get the value of datePlanningOR
     */
    public function getDatePlanningOR()
    {
        return $this->datePlanningOR;
    }

    /**
     * Set the value of datePlanningOR
     *
     * @return  self
     */
    public function setDatePlanningOR($datePlanningOR)
    {
        $this->datePlanningOR = $datePlanningOR;

        return $this;
    }

    /**
     * Get the value of numeroLigne
     */
    public function getNumeroLigne()
    {
        return $this->numeroLigne;
    }

    /**
     * Set the value of numeroLigne
     *
     * @return  self
     */
    public function setNumeroLigne($numeroLigne)
    {
        $this->numeroLigne = $numeroLigne;

        return $this;
    }

    /**
     * Get the value of dateLivraisonPrevue
     */
    public function getDateLivraisonPrevue()
    {
        return $this->dateLivraisonPrevue;
    }

    /**
     * Set the value of dateLivraisonPrevue
     *
     * @return  self
     */
    public function setDateLivraisonPrevue($dateLivraisonPrevue)
    {
        $this->dateLivraisonPrevue = $dateLivraisonPrevue;

        return $this;
    }
}
