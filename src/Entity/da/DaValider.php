<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\da\DaValiderRepository;

/**
 * @ORM\Entity(repositoryClass=DaValiderRepository::class)
 * @ORM\Table(name="Da_valider")
 * @ORM\HasLifecycleCallbacks
 */
class DaValider
{
    use DateTrait;
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

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit")
     */
    private string $numeroDemandeDit;

    /**
     * @ORM\Column(type="string", length=11, name="numero_or")
     */
    private ?string $numeroOr;

    /**
     * @ORM\Column(type="string", length=11, name="numero_cde")
     */
    private ?string $numeroCde = '';

    /**
     * @ORM\Column(type="string", length=50, name="statut_dal")
     */
    private string $statutDal;

    /**
     * @ORM\Column(type="string", length=50, name="statut_or")
     */
    private ?string $statutOr;

    /**
     * @ORM\Column(type="string", length=50, name="statut_cde")
     */
    private ?string $statutCde;

    /**
     * @ORM\Column(type="string", length=100, name="objet_dal")
     */
    private string $objetDal;

    /**
     * @ORM\Column(type="string", length=1000, name="detail_dal", nullable=true)
     */
    private string $detailDal;

    /**
     * @ORM\Column(type="string", length=11, name="num_ligne")
     */
    private string $numeroLigne;

    /**
     * @ORM\Column(type="integer", name="qte_dem")
     */
    private int $qteDem;

    /**
     * @ORM\Column(type="integer", name="qte_dispo")
     */
    private int $qteDispo;
    /**
     * @ORM\Column(type="integer", name="qte_a_livrer")
     */
    private ?int $qteALivrer = 0;
    /**
     * @ORM\Column(type="integer", name="qte_livrer")
     */
    private ?int $qteLivrer = 0;

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
    private ?string $nomFournisseur;

    /**
     * @ORM\Column(type="datetime", name="date_fin_souhaitee_l", nullable=true)
     */
    private $dateFinSouhaite;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private string $commentaire;

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
     * @ORM\Column(type="json", name="pj_new_ate")
     */
    private $pjNewAte = [];

    /**
     * @ORM\Column(type="json", name="pj_proposition_appro")
     */
    private $pjPropositionAppro = []; //plus fiche technique

    /**
     * @ORM\Column(type="json", name="pj_bc")
     */
    private $pjBc = [];

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
    private string $validePar;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     *
     * @var integer | null
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="string", length=50, name="niveau_urgence")
     */
    private string $niveauUrgence;

    /**
     * @ORM\Column(type="integer", name="jours_dispo")
     *
     * @var integer | null
     */
    private ?int $joursDispo;

    /**
     * @ORM\Column(type="integer", name="qte_en_attent")
     */
    private ?int $qteEnAttent = 0;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $demandeur = '';

    /**
     * @ORM\Column(type="boolean", name="bc_envoyer_fournisseur")
     */
    private $bcEnvoyerFournisseur = false;

    /**
     * @ORM\Column(type="integer", name="id_da")
     */
    private int $idDa;

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
    private ?string $positionBc;

    /**
     * @ORM\Column(type="datetime", name="date_planning_or", nullable=true)
     */
    private $datePlannigOr;

    /**
     * @ORM\Column(type="integer", name="numero_ligne_ips")
     */
    private ?int $numeroLigneIps;

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
    public function getDetailDal()
    {
        return $this->detailDal;
    }

    /**
     * Set the value of detailDal
     *
     * @return  self
     */
    public function setDetailDal($detailDal)
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

    /**
     * Get the value of pjNewAte
     */
    public function getPjNewAte()
    {
        return $this->pjNewAte;
    }

    /**
     * Set the value of pjNewAte
     *
     * @return  self
     */
    public function setPjNewAte($pjNewAte)
    {
        $this->pjNewAte = $pjNewAte;

        return $this;
    }

    /**
     * Get the value of pjPropositionAppro
     */
    public function getPjPropositionAppro()
    {
        return $this->pjPropositionAppro;
    }

    /**
     * Set the value of pjPropositionAppro
     *
     * @return  self
     */
    public function setPjPropositionAppro($pjPropositionAppro)
    {
        $this->pjPropositionAppro = $pjPropositionAppro;

        return $this;
    }

    /**
     * Get the value of pjBc
     */
    public function getPjBc()
    {
        return $this->pjBc;
    }

    /**
     * Set the value of pjBc
     *
     * @return  self
     */
    public function setPjBc($pjBc)
    {
        $this->pjBc = $pjBc;

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
     * Get the value of statutOr
     */
    public function getStatutOr(): string
    {
        return $this->statutOr;
    }

    /**
     * Set the value of statutOr
     */
    public function setStatutOr(?string $statutOr): self
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
     * Get the value of idDa
     */
    public function getIdDa()
    {
        return $this->idDa;
    }

    /**
     * Set the value of idDa
     *
     * @return  self
     */
    public function setIdDa($idDa)
    {
        $this->idDa = $idDa;

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

    public function duplicateDa(DemandeAppro $da)
    {
        $this
            ->setNumeroDemandeAppro($da->getNumeroDemandeAppro())
            ->setNumeroDemandeDit($da->getNumeroDemandeDit())
            ->setNiveauUrgence($da->getNiveauUrgence())
            ->setStatutDal($da->getStatutDal())
            ->setObjetDal($da->getObjetDal())
            ->setDetailDal($da->getDetailDal())
            ->setDemandeur($da->getDemandeur())
            ->setIdDa($da->getId())
            ->setAchatDirect($da->getAchatDirect())
        ;
    }

    public function duplicateDal(DemandeApproL $dal)
    {
        $this
            ->setQteDem($dal->getQteDem())
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

    public function duplicateDalr(DemandeApproLR $dalr)
    {
        $this
            ->setQteDem($dalr->getQteDem())
            ->setArtConstp($dalr->getArtConstp())
            ->setArtRefp($dalr->getArtRefp())
            ->setArtDesi($dalr->getArtDesi())
            ->setArtFams1($dalr->getArtFams1())
            ->setArtFams2($dalr->getArtFams2())
            ->setCodeFams1($dalr->getCodeFams1())
            ->setCodeFams2($dalr->getCodeFams2())
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
        ;
    }
}
