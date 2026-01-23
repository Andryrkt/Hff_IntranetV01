<?php

namespace App\Entity\da;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\DateTrait;
use App\Repository\da\DemandeApproLRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=DemandeApproLRepository::class)
 * @ORM\Table(name="Demande_Appro_L")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeApproL
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
     * @ORM\Column(type="string", length=11, name="num_ligne")
     */
    private string $numeroLigne;

    /**
     * @ORM\Column(type="integer", name="qte_dem")
     */
    private $qteDem;

    /**
     * @ORM\Column(type="integer", name="qte_valide_appro")
     */
    private $qteValAppro; // seulement pour les DA réappro

    /**
     * @ORM\Column(type="integer", name="qte_dispo")
     */
    private $qteDispo;

    /**
     * @ORM\Column(type="string", length=3, name="art_constp")
     */
    private ?string $artConstp = '';

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
     * @ORM\Column(type="string", length=10, name="code_fams1")
     */
    private ?string $codeFams1 = null;

    /**
     * @ORM\Column(type="string", length=10, name="code_fams2")
     */
    private ?string $codeFams2 = null;

    /**
     * @ORM\Column(type="string", length=7, name="numero_fournisseur")
     */
    private ?string $numeroFournisseur = null;

    /**
     * @ORM\Column(type="string", length=50, name="nom_fournisseur")
     */
    private ?string $nomFournisseur = "-";

    /**
     * @ORM\Column(type="datetime", name="date_fin_souhaitee_l", nullable=true)
     */
    private $dateFinSouhaite;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private string $commentaire;

    /**
     * @ORM\Column(type="string", length=50, name="statut_dal")
     */
    private string $statutDal;

    /**
     * @ORM\Column(type="boolean", name="catalogue")
     */
    private $catalogue = false;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeAppro::class, inversedBy="DAL")
     * @ORM\JoinColumn(name="demande_appro_id", referencedColumnName="id", nullable=false)
     */
    private ?DemandeAppro $demandeAppro = null;

    /**
     * @ORM\OneToMany(targetEntity=DemandeApproLR::class, mappedBy="demandeApproL")
     */
    private Collection $demandeApproLR;

    /**
     * @ORM\Column(type="boolean", name="est_validee")
     */
    private $estValidee = false;

    /**
     * @ORM\Column(type="boolean", name="est_fiche_technique")
     */
    private $estFicheTechnique = false;

    /**
     * @ORM\Column(type="boolean", name="est_modifier")
     */
    private $estModifier = false;

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
     * @ORM\Column(type="integer", name="edit")
     *
     * @var integer | null
     */
    private ?int $edit = 0;

    /**
     * @ORM\Column(type="string", length=100, name="prix_unitaire")
     */
    private ?string $prixUnitaire = '0';

    /**
     * @ORM\Column(type="string", length=50, name="numero_dit")
     */
    private ?string $numeroDit = '0';

    /**
     * @ORM\Column(type="boolean", name="deleted")
     */
    private $deleted = false;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fiche_technique")
     */
    private $nomFicheTechnique;

    /**
     * @ORM\Column(type="integer", name="jours_dispo")
     *
     * @var integer | null
     */
    private ?int $joursDispo;

    /**
     * @ORM\Column(type="json", name="file_names")
     */
    private $fileNames = [];

    /**
     * @ORM\Column(type="datetime", name="date_livraison_prevue", nullable=true)
     */
    private $dateLivraisonPrevue;

    private $statutBc;

    private $qteLivee = 0;
    private $qteALivrer = 0;
    private $qteEnAttent = 0;

    private $datePlanningOR;

    private $verouille = false;

    /**==============================================================================
     * GETTERS & SETTERS
     *===============================================================================*/

    public function __construct()
    {
        $this->demandeApproLR = new ArrayCollection();
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
     * Get the value of numeroLigne
     *
     * @return string
     */
    public function getNumeroLigne(): string
    {
        return $this->numeroLigne;
    }

    /**
     * Set the value of numeroLigne
     *
     * @param string $numeroLigne
     *
     * @return self
     */
    public function setNumeroLigne(string $numeroLigne): self
    {
        $this->numeroLigne = $numeroLigne;
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
     * Get the value of demandeAppro
     */
    public function getDemandeAppro(): ?DemandeAppro
    {
        return $this->demandeAppro;
    }

    /**
     * Set the value of demandeAppro
     *
     * @return  self
     */
    public function setDemandeAppro(?DemandeAppro $demandeAppro)
    {
        $this->demandeAppro = $demandeAppro;

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
     * Get the value of demandeApproLR
     */
    public function getDemandeApproLR()
    {
        return $this->demandeApproLR;
    }

    public function addDemandeApproLR(DemandeApproLR $demandeApproLR): self
    {
        if (!$this->demandeApproLR->contains($demandeApproLR)) {
            $this->demandeApproLR[] = $demandeApproLR;
            $demandeApproLR->setDemandeApproL($this);
        }

        return $this;
    }

    public function removeDemandeApproLR(DemandeApproLR $demandeApproLR): self
    {
        if ($this->demandeApproLR->contains($demandeApproLR)) {
            $this->demandeApproLR->removeElement($demandeApproLR);
            if ($demandeApproLR->getDemandeApproL() === $this) {
                $demandeApproLR->setDemandeApproL(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of demandeApproLR
     *
     * @return  self
     */
    public function setDemandeApproLR($demandeApproLR)
    {
        $this->demandeApproLR = $demandeApproLR;

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
     */
    public function setCodeFams2($codeFams2)
    {
        $this->codeFams2 = $codeFams2;
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
     * Get the value of estModifier
     */
    public function getEstModifier()
    {
        return $this->estModifier;
    }

    /**
     * Set the value of estModifier
     *
     * @return  self
     */
    public function setEstModifier($estModifier)
    {
        $this->estModifier = $estModifier;

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
     * Get the value of numeroVersion
     *
     * @return  integer
     */
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     *
     * @param  ?integer  $numeroVersion
     *
     * @return  self
     */
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }

    public function getEdit()
    {
        return $this->edit;
    }

    public function setEdit($edit)
    {
        $this->edit = $edit;

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
     * Get the value of numeroDit
     */
    public function getNumeroDit()
    {
        return $this->numeroDit;
    }

    /**
     * Set the value of numeroDit
     *
     * @return  self
     */
    public function setNumeroDit($numeroDit)
    {
        $this->numeroDit = $numeroDit;

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
     * Get the value of joursDispo
     */
    public function getJoursDispo()
    {
        return $this->joursDispo;
    }

    /**
     * Set the value of joursDispo
     *
     * @return  self
     */
    public function setJoursDispo($joursDispo)
    {
        $this->joursDispo = $joursDispo;

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
     * Get the value of qteValAppro
     */
    public function getQteValAppro()
    {
        return $this->qteValAppro;
    }

    /**
     * Set the value of qteValAppro
     *
     * @return  self
     */
    public function setQteValAppro($qteValAppro)
    {
        $this->qteValAppro = $qteValAppro;

        return $this;
    }

    /** 
     * ===================
     * Méthodes pour Twig
     * ===================
     */
    public function getMontantTwig(): float
    {
        return $this->getPrixUnitaire() * $this->getQteDem();
    }

    public function getMontantFormatted(): string
    {
        $montant = $this->getMontantTwig();
        return $montant == 0 ? '-' : number_format($montant, 2, ',', '.');
    }

    public function getPUFormatted(): string
    {
        $montant = $this->getPrixUnitaire();
        return $montant == 0 ? '-' : number_format($montant, 2, ',', '.');
    }

    public function duplicateDaParentLine(DemandeApproParentLine $daParentLine): self
    {
        $this
            ->setQteDem($daParentLine->getQteDem())
            ->setArtConstp($daParentLine->getArtConstp())
            ->setArtRefp($daParentLine->getArtRefp())
            ->setArtDesi($daParentLine->getArtDesi())
            ->setNumeroFournisseur($daParentLine->getNumeroFournisseur() ?? "-")
            ->setNomFournisseur($daParentLine->getNomFournisseur() ?? "-")
            ->setDateFinSouhaite($daParentLine->getDateFinSouhaite())
            ->setCommentaire($daParentLine->getCommentaire() ?? "-")
            ->setPrixUnitaire($daParentLine->getPrixUnitaire())
            ->setStatutDal($daParentLine->getStatutDal())
            ->setCatalogue($daParentLine->getArticleStocke())
            ->setEstFicheTechnique($daParentLine->getEstFicheTechnique())
            ->setNomFicheTechnique($daParentLine->getNomFicheTechnique())
            ->setJoursDispo($daParentLine->getJoursDispo())
            ->setFileNames($daParentLine->getFileNames())
        ;

        return $this;
    }
}
