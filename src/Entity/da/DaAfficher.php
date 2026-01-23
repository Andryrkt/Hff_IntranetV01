<?php

namespace App\Entity\da;

use App\Entity\dit\DemandeIntervention;
use App\Entity\Traits\DateTrait;
use App\Repository\da\DaAfficherRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DaAfficherRepository::class)
 * @ORM\Table(name="da_afficher")
 * @ORM\HasLifecycleCallbacks
 */
class DaAfficher
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_appro_mere")
     */
    private string $numeroDemandeApproMere;

    /**
     * @ORM\Column(type="string", length=12, name="numero_demande_appro")
     */
    private string $numeroDemandeAppro;

    /**
     * @ORM\Column(type="integer", name="da_type_id")
     */
    private ?int $daTypeId = 0;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit")
     */
    private ?string $numeroDemandeDit = null;

    /**
     * @ORM\Column(type="string", length=11, name="numero_or")
     */
    private ?string $numeroOr = null;

    /**
     * @ORM\Column(type="string", length=11, name="numero_cde")
     */
    private ?string $numeroCde = '';

    /**
     * @ORM\Column(type="string", length=50, name="statut_dal")
     */
    private string $statutDal = '';

    /**
     * @ORM\Column(type="string", length=50, name="statut_or")
     */
    private ?string $statutOr = null;

    /**
     * @ORM\Column(type="string", length=50, name="statut_cde")
     */
    private ?string $statutCde = null;

    /**
     * @ORM\Column(type="string", length=100, name="objet_dal")
     */
    private string $objetDal = '';

    /**
     * @ORM\Column(type="string", length=1000, name="detail_dal", nullable=true)
     */
    private ?string $detailDal = null;

    /**
     * @ORM\Column(type="string", length=11, name="num_ligne")
     */
    private ?string $numeroLigne = '0';

    /**
     * @ORM\Column(type="integer", name="num_ligne_tableau", nullable=true)
     */
    private $numLigneTableau;

    /**
     * @ORM\Column(type="integer", name="qte_dem")
     */
    private int $qteDem = 0;

    /**
     * @ORM\Column(type="integer", name="qte_dispo")
     */
    private int $qteDispo = 0;

    /**
     * @ORM\Column(type="integer", name="qte_livrer")
     */
    private int $qteLivrer = 0;

    /**
     * @ORM\Column(type="string", length=3, name="art_constp")
     */
    private ?string $artConstp = '';

    /**
     * @ORM\Column(type="string", length=50, name="art_refp")
     */
    private ?string $artRefp = '';

    /**
     * @ORM\Column(type="string", length=100, name="art_desi")
     */
    private ?string $artDesi = '';

    /**
     * @ORM\Column(type="string", length=50, name="art_fams1")
     */
    private ?string $artFams1;

    /**
     * @ORM\Column(type="string", length=50, name="art_fams2")
     */
    private ?string $artFams2;

    /**
     * @ORM\Column(type="string", length=10, name="code_fams1")
     */
    private ?string $codeFams1;

    /**
     * @ORM\Column(type="string", length=10, name="code_fams2")
     */
    private ?string $codeFams2;

    /**
     * @ORM\Column(type="string", length=7, name="numero_fournisseur")
     */
    private ?string $numeroFournisseur = null;

    /**
     * @ORM\Column(type="string", length=50, name="nom_fournisseur")
     */
    private ?string $nomFournisseur = null;

    /**
     * @ORM\Column(type="datetime", name="date_fin_souhaitee_l", nullable=true)
     */
    private $dateFinSouhaite;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private string $commentaire = '';

    /**
     * @ORM\Column(type="string", length=100, name="prix_unitaire")
     */
    private ?string $prixUnitaire = '0';

    /**
     * @ORM\Column(type="string", length=100, name="total")
     */
    private ?string $total = '0';

    /**
     * @ORM\Column(type="boolean", name="est_fiche_technique")
     */
    private $estFicheTechnique = false;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fiche_technique")
     */
    private $nomFicheTechnique;

    /**
     * @ORM\Column(type="text", name="pj_new_ate", nullable=true)
     */
    private ?string $pjNewAte = null;

    /**
     * @ORM\Column(type="text", name="pj_proposition_appro", nullable=true)
     */
    private ?string $pjPropositionAppro = null; //plus fiche technique

    /**
     * @ORM\Column(type="text", name="pj_bc", nullable=true)
     */
    private ?string $pjBc = null;

    /**
     * @ORM\Column(type="boolean", name="catalogue")
     */
    private $catalogue = false;

    /**
     * @ORM\Column(type="datetime", name="date_livraison_prevue", nullable=true)
     */
    private $dateLivraisonPrevue;

    /**
     * @ORM\Column(type="string", length=50, name="valide_par")
     */
    private ?string $validePar = null;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     *
     * @var integer | null
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="integer", name="numero_version_or_maj_statut")
     *
     * @var integer | null
     */
    private ?int $numeroVersionOrMajStatut = 0;

    /**
     * @ORM\Column(type="string", length=50, name="niveau_urgence")
     */
    private ?string $niveauUrgence = null;

    /**
     * @ORM\Column(type="string", length=50, name="code_centrale")
     */
    private ?string $codeCentrale = null;

    /**
     * @ORM\Column(type="string", length=50, name="designation_central")
     */
    private ?string $desiCentrale = null;

    /**
     * @ORM\Column(type="integer", name="jours_dispo")
     *
     * @var integer | null
     */
    private ?int $joursDispo = null;

    /**
     * @ORM\Column(type="integer", name="qte_en_attent")
     */
    private int $qteEnAttent = 0;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $demandeur = '';

    /**
     * @ORM\Column(type="boolean", name="bc_envoyer_fournisseur")
     */
    private $bcEnvoyerFournisseur = false;

    /**
     * @ORM\Column(type="boolean", name="achat_direct")
     */
    private bool $achatDirect = false;

    /**
     * @ORM\Column(type="boolean", name="or_a_resoumettre")
     */
    private bool $orResoumettre = false;

    /**
     * @ORM\Column(type="string", length=100, name="position_bc")
     */
    private ?string $positionBc = null;

    /**
     * @ORM\Column(type="datetime", name="date_planning_or", nullable=true)
     */
    private $datePlannigOr = null;

    /**
     * @ORM\Column(type="integer", name="numero_ligne_ips")
     */
    private ?int $numeroLigneIps = null;

    /**
     * @ORM\Column(type="datetime", name="date_demande", nullable=true)
     */
    private $dateDemande;

    /**
     * @ORM\Column(type="datetime", name="date_derniere_bav", nullable=true)
     */
    private $dateValidation;

    /**
     * @ORM\Column(type="datetime", name="date_maj_statut_or", nullable=true)
     */
    private $dateMajStatutOr;

    /**
     * @ORM\Column(type="boolean", name="est_dalr")
     */
    private bool $estDalr = false;

    /**
     * @ORM\Column(type="integer", name="agence_emmetteur_id")
     */
    private  $agenceEmetteur;

    /**
     * @ORM\Column(type="integer", name="service_emmetteur_id")
     */
    private  $serviceEmetteur;

    /**
     * @ORM\Column(type="integer", name="agence_debiteur_id")
     */
    private  $agenceDebiteur;

    /**
     * @ORM\Column(type="integer", name="service_debiteur_id")
     */
    private  $serviceDebiteur;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeAppro::class)
     * @ORM\JoinColumn(name="demande_appro_id", referencedColumnName="id", nullable=false)
     */
    private ?DemandeAppro $demandeAppro = null;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeApproParent::class)
     * @ORM\JoinColumn(name="demande_appro_parent_id", referencedColumnName="id", nullable=false)
     */
    private ?DemandeApproParent $demandeApproParent = null;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeIntervention::class)
     * @ORM\JoinColumn(name="dit_id", referencedColumnName="id", nullable=true)
     */
    private ?DemandeIntervention $dit = null;

    private $verouille = false;
    private bool $demandeDeverouillage = false;

    /**
     * @ORM\Column(type="boolean", name="deleted")
     */
    private $deleted = false;

    /**
     * @ORM\Column(type="string", name="deleted_by", nullable=true)
     */
    private ?string $deletedBy = null;

    /**
     * @ORM\Column(type="boolean", name="est_facture_bl_soumis")
     */
    private $estFactureBlSoumis = false;

    /**
     * @ORM\Column(type="boolean", name="non_dispo")
     */
    private $nonDispo = false;

    /**
     * @ORM\Column(type="integer", name="qte_dem_ips")
     */
    private int $qteDemIps = 0;

    /**
     * @ORM\Column(type="integer", name="numero_intervention_ips")
     */
    private ?int $numeroInterventionIps = 0;

    /**
     * @ORM\Column(type="boolean", name="est_bl_reappro_soumis")
     */
    private $estBlReapproSoumis = false;

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
     * Get the value of numeroDemandeApproMere
     */
    public function getNumeroDemandeApproMere(): string
    {
        return $this->numeroDemandeApproMere;
    }

    /**
     * Set the value of numeroDemandeApproMere
     */
    public function setNumeroDemandeApproMere(string $numeroDemandeApproMere): self
    {
        $this->numeroDemandeApproMere = $numeroDemandeApproMere;

        return $this;
    }

    /**
     * Get the value of numeroDemandeAppro
     */
    public function getNumeroDemandeAppro()
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     *
     * @return  self
     */
    public function setNumeroDemandeAppro($numeroDemandeAppro)
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;

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
     * Get the value of numeroCde
     */
    public function getNumeroCde()
    {
        return $this->numeroCde;
    }

    /**
     * Set the value of numeroCde
     *
     * @return  self
     */
    public function setNumeroCde($numeroCde)
    {
        $this->numeroCde = $numeroCde;

        return $this;
    }

    /**
     * Get the value of objetDal
     */
    public function getObjetDal()
    {
        return $this->objetDal;
    }

    /**
     * Set the value of objetDal
     *
     * @return  self
     */
    public function setObjetDal($objetDal)
    {
        $this->objetDal = $objetDal;

        return $this;
    }

    /**
     * Get the value of detailDal
     */
    public function getDetailDal(): ?string
    {
        return $this->detailDal;
    }

    /**
     * Set the value of detailDal
     *
     * @return  self
     */
    public function setDetailDal(?string $detailDal): self
    {
        $this->detailDal = $detailDal;

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
     * Get the value of qteDem
     */
    public function getQteDem()
    {
        return $this->qteDem;
    }

    /**
     * Set the value of qteDem
     *
     * @return  self
     */
    public function setQteDem($qteDem)
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
     *
     * @return  self
     */
    public function setQteDispo($qteDispo)
    {
        $this->qteDispo = $qteDispo;

        return $this;
    }

    /**
     * Get the value of qteLivrer
     */
    public function getQteLivrer()
    {
        return $this->qteLivrer;
    }

    /**
     * Set the value of qteLivrer
     *
     * @return  self
     */
    public function setQteLivrer($qteLivrer)
    {
        $this->qteLivrer = $qteLivrer;

        return $this;
    }

    /**
     * Get the value of artConstp
     */
    public function getArtConstp()
    {
        return $this->artConstp;
    }

    /**
     * Set the value of artConstp
     *
     * @return  self
     */
    public function setArtConstp($artConstp)
    {
        $this->artConstp = $artConstp;

        return $this;
    }

    /**
     * Get the value of artRefp
     */
    public function getArtRefp()
    {
        return $this->artRefp;
    }

    /**
     * Set the value of artRefp
     *
     * @return  self
     */
    public function setArtRefp($artRefp)
    {
        $this->artRefp = $artRefp;

        return $this;
    }

    /**
     * Get the value of artDesi
     */
    public function getArtDesi()
    {
        return $this->artDesi;
    }

    /**
     * Set the value of artDesi
     *
     * @return  self
     */
    public function setArtDesi($artDesi)
    {
        $this->artDesi = $artDesi;

        return $this;
    }

    /**
     * Get the value of artFams1
     */
    public function getArtFams1()
    {
        return $this->artFams1;
    }

    /**
     * Set the value of artFams1
     *
     * @return  self
     */
    public function setArtFams1($artFams1)
    {
        $this->artFams1 = $artFams1;

        return $this;
    }

    /**
     * Get the value of artFams2
     */
    public function getArtFams2()
    {
        return $this->artFams2;
    }

    /**
     * Set the value of artFams2
     *
     * @return  self
     */
    public function setArtFams2($artFams2)
    {
        $this->artFams2 = $artFams2;

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
     * Get the value of numeroFournisseur
     */
    public function getNumeroFournisseur()
    {
        return $this->numeroFournisseur;
    }

    /**
     * Set the value of numeroFournisseur
     *
     * @return  self
     */
    public function setNumeroFournisseur($numeroFournisseur)
    {
        $this->numeroFournisseur = $numeroFournisseur;

        return $this;
    }

    /**
     * Get the value of nomFournisseur
     */
    public function getNomFournisseur()
    {
        return $this->nomFournisseur;
    }

    /**
     * Set the value of nomFournisseur
     *
     * @return  self
     */
    public function setNomFournisseur($nomFournisseur)
    {
        $this->nomFournisseur = $nomFournisseur;

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
     *
     * @return  self
     */
    public function setDateFinSouhaite($dateFinSouhaite)
    {
        $this->dateFinSouhaite = $dateFinSouhaite;

        return $this;
    }

    /**
     * Get the value of commentaire
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set the value of commentaire
     *
     * @return  self
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

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

    // Setter
    public function setPjNewAte(array $files): self
    {
        $this->pjNewAte = json_encode($files);
        return $this;
    }

    // Getter
    public function getPjNewAte(): array
    {
        return $this->pjNewAte ? json_decode($this->pjNewAte, true) : [];
    }

    /**
     * Get the value of pjPropositionAppro
     */
    public function getPjPropositionAppro()
    {
        return $this->pjPropositionAppro ? json_decode($this->pjPropositionAppro, true) : [];
    }

    /**
     * Set the value of pjPropositionAppro
     *
     * @return  self
     */
    public function setPjPropositionAppro($pjPropositionAppro)
    {
        $this->pjPropositionAppro = json_encode($pjPropositionAppro);

        return $this;
    }

    /**
     * Get the value of pjBc
     */
    public function getPjBc()
    {
        return $this->pjBc ? json_decode($this->pjBc, true) : [];
    }

    /**
     * Set the value of pjBc
     *
     * @return  self
     */
    public function setPjBc($pjBc)
    {
        $this->pjBc = json_encode($pjBc);

        return $this;
    }

    /**
     * Get the value of catalogue
     */
    public function getCatalogue()
    {
        return $this->catalogue;
    }

    /**
     * Set the value of catalogue
     *
     * @return  self
     */
    public function setCatalogue($catalogue)
    {
        $this->catalogue = $catalogue;

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
     * Get | null
     *
     * @return  integer
     */
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set | null
     *
     * @param  integer  $numeroVersion  | null
     *
     * @return  self
     */
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

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
     * Get the value of statutCde
     */
    public function getStatutCde()
    {
        return $this->statutCde;
    }

    /**
     * Set the value of statutCde
     *
     * @return  self
     */
    public function setStatutCde($statutCde)
    {
        $this->statutCde = $statutCde;

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
     *
     * @return  self
     */
    public function setNomFicheTechnique($nomFicheTechnique)
    {
        $this->nomFicheTechnique = $nomFicheTechnique;

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
     * Get the value of $joursDispo
     */
    public function getJoursDispo()
    {
        return $this->joursDispo;
    }

    /**
     * Set the value of $joursDispo
     */
    public function setJoursDispo($joursDispo)
    {
        $this->joursDispo = $joursDispo;

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
     */
    public function setStatutOr($statutOr): self
    {
        $this->statutOr = $statutOr;

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

    public function getConstructeurRefDesi(): ?string
    {
        if (!empty($this->artConstp) && !empty($this->artRefp) && !empty($this->artDesi)) {
            $designation = mb_convert_encoding($this->artDesi, 'ISO-8859-1', 'UTF-8');
            $designation = str_replace(["'", '^'], ["''", ''], $designation);

            $ref = str_replace(' ', '', $this->artRefp);

            return $this->artConstp . '_' . $ref . '_' . $designation;
        }


        return null;
    }

    public function getReferenceCataloguee(): ?string
    {
        if (!empty($this->artRefp)) {

            $ref = str_replace(' ', '', $this->artRefp);

            return $ref;
        }


        return null;
    }

    /**
     * Get the value of bcEnvoyerFournisseur
     */
    public function getBcEnvoyerFournisseur()
    {
        return $this->bcEnvoyerFournisseur;
    }

    /**
     * Set the value of bcEnvoyerFournisseur
     */
    public function setBcEnvoyerFournisseur($bcEnvoyerFournisseur): self
    {
        $this->bcEnvoyerFournisseur = $bcEnvoyerFournisseur;

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
     * Get the value of positionBc
     */
    public function getPositionBc()
    {
        return $this->positionBc;
    }

    /**
     * Set the value of positionBc
     *
     * @return  self
     */
    public function setPositionBc($positionBc)
    {
        $this->positionBc = $positionBc;

        return $this;
    }

    /**
     * Get the value of datePlannigOr
     */
    public function getDatePlannigOr()
    {
        return $this->datePlannigOr;
    }

    /**
     * Set the value of datePlannigOr
     *
     * @return  self
     */
    public function setDatePlannigOr($datePlannigOr)
    {
        $this->datePlannigOr = $datePlannigOr;

        return $this;
    }

    /**
     * Get the value of orResoumettre
     */
    public function getOrResoumettre()
    {
        return $this->orResoumettre;
    }

    /**
     * Set the value of orResoumettre
     *
     * @return  self
     */
    public function setOrResoumettre($orResoumettre)
    {
        $this->orResoumettre = $orResoumettre;

        return $this;
    }

    /**
     * Get the value of numeroLigneIps
     */
    public function getNumeroLigneIps()
    {
        return $this->numeroLigneIps;
    }

    /**
     * Set the value of numeroLigneIps
     *
     * @return  self
     */
    public function setNumeroLigneIps($numeroLigneIps)
    {
        $this->numeroLigneIps = $numeroLigneIps;

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
     * Get the value of estDalr
     */
    public function getEstDalr()
    {
        return $this->estDalr;
    }

    /**
     * Set the value of estDalr
     *
     * @return  self
     */
    public function setEstDalr($estDalr)
    {
        $this->estDalr = $estDalr;

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
     * Get the value of demandeAppro
     */
    public function getDemandeAppro()
    {
        return $this->demandeAppro;
    }

    /**
     * Set the value of demandeAppro
     *
     * @return  self
     */
    public function setDemandeAppro($demandeAppro)
    {
        $this->demandeAppro = $demandeAppro;

        return $this;
    }

    /**
     * Get the value of demandeApproParent
     */
    public function getDemandeApproParent(): ?DemandeApproParent
    {
        return $this->demandeApproParent;
    }

    /**
     * Set the value of demandeApproParent
     */
    public function setDemandeApproParent(?DemandeApproParent $demandeApproParent): self
    {
        $this->demandeApproParent = $demandeApproParent;

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
     * Get the value of deletedBy
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set the value of deletedBy
     *
     * @return  self
     */
    public function setDeletedBy($deletedBy)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Get the value of dateValidation
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Set the value of dateValidation
     *
     * @return  self
     */
    public function setDateValidation($dateValidation)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get the value of estFactureBlSoumis
     */
    public function getEstFactureBlSoumis()
    {
        return $this->estFactureBlSoumis;
    }

    /**
     * Set the value of estFactureBlSoumis
     */
    public function setEstFactureBlSoumis($estFactureBlSoumis): self
    {
        $this->estFactureBlSoumis = $estFactureBlSoumis;

        return $this;
    }

    /**
     * Get the value of dateMajStatutOr
     */
    public function getDateMajStatutOr()
    {
        return $this->dateMajStatutOr;
    }

    /**
     * Set the value of dateMajStatutOr
     *
     * @return  self
     */
    public function setDateMajStatutOr($dateMajStatutOr)
    {
        $this->dateMajStatutOr = $dateMajStatutOr;

        return $this;
    }

    /**
     * Get | null
     *
     * @return  integer
     */
    public function getNumeroVersionOrMajStatut()
    {
        return $this->numeroVersionOrMajStatut;
    }

    /**
     * Set | null
     *
     * @param  integer  $numeroVersionOrMajStatut  | null
     *
     * @return  self
     */
    public function setNumeroVersionOrMajStatut($numeroVersionOrMajStatut)
    {
        $this->numeroVersionOrMajStatut = $numeroVersionOrMajStatut;

        return $this;
    }

    /**
     * Get the value of qteDemIps
     */
    public function getQteDemIps(): int
    {
        return $this->qteDemIps;
    }

    /**
     * Set the value of qteDemIps
     */
    public function setQteDemIps(int $qteDemIps): self
    {
        $this->qteDemIps = $qteDemIps;

        return $this;
    }

    /**
     * Get the value of numeroInterventionIps
     */
    public function getNumeroInterventionIps()
    {
        return $this->numeroInterventionIps;
    }

    /**
     * Set the value of numeroInterventionIps
     *
     * @return  self
     */
    public function setNumeroInterventionIps($numeroInterventionIps)
    {
        $this->numeroInterventionIps = $numeroInterventionIps;

        return $this;
    }

    /**
     * Get the value of nonDispo
     */
    public function getNonDispo()
    {
        return $this->nonDispo;
    }

    /**
     * Set the value of nonDispo
     *
     * @return  self
     */
    public function setNonDispo($nonDispo)
    {
        $this->nonDispo = $nonDispo;

        return $this;
    }

    /**
     * Get the value of daTypeId
     */
    public function getDaTypeId()
    {
        return $this->daTypeId;
    }

    /**
     * Set the value of daTypeId
     *
     * @return  self
     */
    public function setDaTypeId($daTypeId)
    {
        $this->daTypeId = $daTypeId;

        return $this;
    }

    /**
     * Get the value of codeCentrale
     */
    public function getCodeCentrale()
    {
        return $this->codeCentrale;
    }

    /**
     * Set the value of codeCentrale
     *
     * @return  self
     */
    public function setCodeCentrale($codeCentrale)
    {
        $this->codeCentrale = $codeCentrale;

        return $this;
    }

    public function getEstBlReapproSoumis()
    {
        return $this->estBlReapproSoumis;
    }

    public function setEstBlReapproSoumis($estBlReapproSoumis)
    {
        $this->estBlReapproSoumis = $estBlReapproSoumis;

        return $this;
    }

    /**
     * Get the value of desiCentrale
     */
    public function getDesiCentrale()
    {
        return $this->desiCentrale;
    }

    /**
     * Set the value of desiCentrale
     *
     * @return  self
     */
    public function setDesiCentrale($desiCentrale)
    {
        $this->desiCentrale = $desiCentrale;

        return $this;
    }

    /**
     * Copie les propriétés pertinentes d'un ancien DaAfficher vers l'objet courant.
     *
     * Cela permet de "mettre à jour" l'objet courant avec les valeurs de référence
     * de l'ancien DaAfficher, par exemple lors d'une rectification ou d'une version précédente.
     *
     * @param DaAfficher $oldDaAfficher L'objet source dont les propriétés doivent être copiées.
     * @return void
     */
    public function copyFromOld(DaAfficher $oldDaAfficher): void
    {
        $this
            ->setNumeroOr($oldDaAfficher->getNumeroOr())
            ->setStatutOr($oldDaAfficher->getStatutOr())
            ->setDatePlannigOr($oldDaAfficher->getDatePlannigOr())
            ->setNumeroVersionOrMajStatut($oldDaAfficher->getNumeroVersionOrMajStatut())
            ->setDateValidation($oldDaAfficher->getDateValidation())
            ->setDateMajStatutOr($oldDaAfficher->getDateMajStatutOr())
            ->setNumeroCde($oldDaAfficher->getNumeroCde())
            ->setPositionBc($oldDaAfficher->getPositionBc())
            ->setStatutCde($oldDaAfficher->getStatutCde())
            ->setDit($oldDaAfficher->getDit())
            ->setEstFactureBlSoumis($oldDaAfficher->getEstFactureBlSoumis())
            ->setBcEnvoyerFournisseur($oldDaAfficher->getBcEnvoyerFournisseur())
            ->setDateLivraisonPrevue($oldDaAfficher->getDateLivraisonPrevue())
        ;
    }

    public function enregistrerDaParent(DemandeApproParent $demandeApproParent)
    {
        $this
            ->setDemandeApproParent($demandeApproParent)
            ->setNumeroDemandeApproMere($demandeApproParent->getNumeroDemandeAppro())
            ->setNumeroDemandeAppro($demandeApproParent->getNumeroDemandeAppro())
            ->setStatutDal($demandeApproParent->getStatutDal())
            ->setObjetDal($demandeApproParent->getObjetDal())
            ->setDetailDal($demandeApproParent->getDetailDal())
            ->setDemandeur($demandeApproParent->getDemandeur())
            ->setCodeCentrale($demandeApproParent->getCodeCentrale())
            ->setDesiCentrale($demandeApproParent->getDesiCentrale())
            ->setDaTypeId(DemandeAppro::TYPE_DA_PARENT)
            ->setDateDemande($demandeApproParent->getDateCreation())
            ->setNiveauUrgence($demandeApproParent->getNiveauUrgence())
            ->setAgenceEmetteur($demandeApproParent->getAgenceEmetteur()->getId())
            ->setServiceEmetteur($demandeApproParent->getServiceEmetteur()->getId())
            ->setAgenceDebiteur($demandeApproParent->getAgenceDebiteur()->getId())
            ->setServiceDebiteur($demandeApproParent->getServiceDebiteur()->getId())
        ;
    }

    public function enregistrerDaParentLine(DemandeApproParentLine $demandeApproParentLine)
    {
        $this
            ->setQteDem($demandeApproParentLine->getQteDem())
            ->setNumeroLigne($demandeApproParentLine->getNumeroLigne())
            ->setArtConstp($demandeApproParentLine->getArtConstp())
            ->setArtRefp($demandeApproParentLine->getArtRefp())
            ->setArtDesi($demandeApproParentLine->getArtDesi())
            ->setArtFams1($demandeApproParentLine->getArtFams1())
            ->setArtFams2($demandeApproParentLine->getArtFams2())
            ->setCodeFams1($demandeApproParentLine->getCodeFams1())
            ->setCodeFams2($demandeApproParentLine->getCodeFams2())
            ->setNumeroFournisseur($demandeApproParentLine->getNumeroFournisseur())
            ->setNomFournisseur($demandeApproParentLine->getNomFournisseur())
            ->setDateFinSouhaite($demandeApproParentLine->getDateFinSouhaite())
            ->setCommentaire($demandeApproParentLine->getCommentaire())
            ->setPrixUnitaire($demandeApproParentLine->getPrixUnitaire())
            ->setTotal($demandeApproParentLine->getPrixUnitaire() * $demandeApproParentLine->getQteDem())
            ->setEstFicheTechnique($demandeApproParentLine->getEstFicheTechnique())
            ->setPjNewAte($demandeApproParentLine->getFileNames())
            ->setNomFicheTechnique($demandeApproParentLine->getNomFicheTechnique())
            ->setJoursDispo($demandeApproParentLine->getJoursDispo())
        ;
    }

    public function enregistrerDa(DemandeAppro $da)
    {
        $this
            ->setDemandeAppro($da)
            ->setNumeroDemandeAppro($da->getNumeroDemandeAppro())
            ->setNumeroDemandeDit($da->getNumeroDemandeDit())
            ->setStatutDal($da->getStatutDal())
            ->setObjetDal($da->getObjetDal())
            ->setDetailDal($da->getDetailDal())
            ->setDemandeur($da->getDemandeur())
            ->setAchatDirect($da->getAchatDirect())
            ->setCodeCentrale($da->getCodeCentrale())
            ->setDesiCentrale($da->getDesiCentrale())
            ->setDaTypeId($da->getDaTypeId())
            ->setDateDemande($da->getDateCreation())
            ->setNiveauUrgence($da->getNiveauUrgence())
            ->setAgenceEmetteur($da->getAgenceEmetteur()->getId())
            ->setServiceEmetteur($da->getServiceEmetteur()->getId())
            ->setAgenceDebiteur($da->getAgenceDebiteur()->getId())
            ->setServiceDebiteur($da->getServiceDebiteur()->getId())
        ;
    }

    public function enregistrerDal(DemandeApproL $dal)
    {
        $this
            ->setQteDem($dal->getQteDem())
            ->setNumeroLigne($dal->getNumeroLigne())
            ->setArtConstp($dal->getArtConstp())
            ->setArtRefp($dal->getArtRefp())
            ->setArtDesi($dal->getArtDesi())
            ->setArtFams1($dal->getArtFams1())
            ->setArtFams2($dal->getArtFams2())
            ->setCodeFams1($dal->getCodeFams1())
            ->setCodeFams2($dal->getCodeFams2())
            ->setNumeroFournisseur($dal->getNumeroFournisseur())
            ->setNomFournisseur($dal->getNomFournisseur())
            ->setDateFinSouhaite($dal->getDateFinSouhaite())
            ->setCommentaire($dal->getCommentaire())
            ->setPrixUnitaire($dal->getPrixUnitaire())
            ->setTotal($dal->getPrixUnitaire() * $dal->getQteDem())
            ->setEstFicheTechnique($dal->getEstFicheTechnique())
            ->setPjNewAte($dal->getFileNames())
            ->setNomFicheTechnique($dal->getNomFicheTechnique())
            ->setValidePar($dal->getValidePar())
            ->setJoursDispo($dal->getJoursDispo())
        ;
    }

    public function enregistrerDalr(DemandeApproLR $dalr)
    {
        $this
            ->setQteDem($dalr->getQteDem())
            ->setNumeroLigne($dalr->getNumeroLigne())
            ->setNumLigneTableau($dalr->getNumLigneTableau())
            ->setArtConstp($dalr->getArtConstp())
            ->setArtRefp($dalr->getArtRefp())
            ->setArtDesi($dalr->getArtDesi())
            ->setArtFams1($dalr->getArtFams1())
            ->setArtFams2($dalr->getArtFams2())
            ->setCodeFams1($dalr->getCodeFams1())
            ->setCodeFams2($dalr->getCodeFams2())
            ->setQteDispo($dalr->getQteDispo() === '-' || !$dalr->getQteDispo() ? 0 : $dalr->getQteDispo())
            ->setNumeroFournisseur($dalr->getNumeroFournisseur())
            ->setNomFournisseur($dalr->getNomFournisseur())
            ->setDateFinSouhaite($dalr->getDateFinSouhaite())
            ->setCommentaire($dalr->getMotif())
            ->setPrixUnitaire($dalr->getPrixUnitaire())
            ->setTotal($dalr->getTotal())
            ->setEstFicheTechnique($dalr->getEstFicheTechnique())
            ->setNomFicheTechnique($dalr->getNomFicheTechnique())
            ->setPjPropositionAppro($dalr->getFileNames())
            ->setValidePar($dalr->getValidePar())
            ->setJoursDispo($dalr->getDemandeApproL()->getJoursDispo())
            ->setEstDalr(true)
        ;
    }

    public function toObject(array $data)
    {
        $this
            ->setNumeroDemandeAppro($data['numeroDemandeAppro'] ?? null)
            ->setNumeroDemandeDit($data['numeroDemandeDit'] ?? null)
            ->setNumeroOr($data['numeroOr'] ?? null)
            ->setNumeroCde($data['numeroCde'] ?? "")
            ->setStatutDal($data['statutDal'] ?? "")
            ->setStatutCde($data['statutCde'] ?? null)
            ->setStatutOr($data['statutOr'] ?? null)
            ->setObjetDal($data['objetDal'] ?? null)
            ->setDetailDal($data['detailDal'] ?? null)
            ->setNumeroLigne($data['numeroLigne'] ?? null)
            ->setQteDem($data['qteDem'] ?? 0)
            ->setQteDispo($data['qteDispo'] ?? 0)
            ->setQteLivrer($data['qteLivrer'] ?? 0)
            ->setArtConstp($data['artConstp'] ?? null)
            ->setArtRefp($data['artRefp'] ?? null)
            ->setArtDesi($data['artDesi'] ?? null)
            ->setArtFams1($data['artFams1'] ?? null)
            ->setArtFams2($data['artFams2'] ?? null)
            ->setCodeFams1($data['codeFams1'] ?? null)
            ->setCodeFams2($data['codeFams2'] ?? null)
            ->setNumeroFournisseur($data['numeroFournisseur'] ?? null)
            ->setNomFournisseur($data['nomFournisseur'] ?? null)
            ->setDateFinSouhaite($data['dateFinSouhaite'] ?? null)
            ->setCommentaire($data['commentaire'] ?? null)
            ->setPrixUnitaire($data['prixUnitaire'] ?? 0)
            ->setTotal($data['total'] ?? 0)
            ->setEstFicheTechnique($data['estFicheTechnique'] ?? false)
            ->setNomFicheTechnique($data['nomFicheTechnique'] ?? null)
            ->setPjNewAte($data['pjNewAte'] ? json_decode($data['pjNewAte']) : [])
            ->setPjPropositionAppro($data['pjPropositionAppro'] ?? [])
            ->setPjBc($data['pjBc'] ?? [])
            ->setCatalogue($data['catalogue'] ?? false)
            ->setDateLivraisonPrevue($data['dateLivraisonPrevue'] ?? null)
            ->setValidePar($data['validePar'] ?? null)
            ->setNumeroVersion($data['numeroVersion'] ?? null)
            ->setNiveauUrgence($data['niveauUrgence'] ?? null)
            ->setJoursDispo($data['joursDispo'] ?? null)
            ->setQteEnAttent($data['qteEnAttent'] ?? 0)
            ->setDemandeur($data['demandeur'] ?? null)
            ->setBcEnvoyerFournisseur($data['bcEnvoyerFournisseur'] ?? false)
            ->setDaTypeId($data['daTypeId'] ?? 0)
            ->setPositionBc($data['positionBc'] ?? null)
            ->setOrResoumettre($data['orResoumettre'] ?? false)
            ->setdatePlannigOr($data['datePlannigOr'] ?? null)
            ->setNumeroLigneIps($data['numeroLigneIps'] ?? null)
            ->setDateDemande($data['dateDemande'] ?? null)
            ->setEstDalr($data['estDalr'] ?? false)
            ->setAgenceEmetteur($data['agenceEmetteur'] ?? null)
            ->setServiceEmetteur($data['serviceEmetteur'] ?? null)
            ->setAgenceDebiteur($data['agenceDebiteur'] ?? null)
            ->setServiceDebiteur($data['serviceDebiteur'] ?? null)
            ->setDeleted($data['deleted'] ?? false)
            ->setDeletedBy($data['deletedBy'] ?? null)
            ->setDateCreation($data['dateCreation'] ?? null)
            ->setDateModification($data['dateModification'] ?? null)
        ;
    }
}
