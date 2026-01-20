<?php

namespace App\Entity\da;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\DateTrait;
use App\Repository\da\DemandeApproParentLineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=DemandeApproParentLineRepository::class)
 * @ORM\Table(name="Demande_Appro_P_L")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeApproParentLine
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
     * @ORM\Column(type="string", length=11, name="num_ligne")
     */
    private string $numeroLigne;

    /**
     * @ORM\Column(type="integer", name="qte_dem")
     */
    private $qteDem;

    /**
     * @ORM\Column(type="string", length=3, name="art_constp")
     */
    private ?string $artConstp = '';

    /**
     * @ORM\Column(type="string", length=50, name="art_refp")
     */
    private string $artRefp = '';

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
     * @ORM\Column(type="boolean", name="art_stocke")
     */
    private $articleStocke = false;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeApproParent::class, inversedBy="demandeApproParentLines")
     * @ORM\JoinColumn(name="demande_appro_id", referencedColumnName="id", nullable=false)
     */
    private ?DemandeApproParent $demandeApproParent = null;

    /**
     * @ORM\Column(type="boolean", name="est_fiche_technique")
     */
    private $estFicheTechnique = false;

    /**
     * @ORM\Column(type="string", length=100, name="prix_unitaire")
     */
    private ?string $prixUnitaire = '0';

    /**
     * @ORM\Column(type="string", length=255, name="nom_fiche_technique")
     */
    private $nomFicheTechnique;

    /**
     * @ORM\Column(type="json", name="file_names")
     */
    private $fileNames = [];

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $demandeur = '';

    /**
     * @ORM\Column(type="integer", name="jours_dispo")
     */
    private ?int $joursDispo;

    private bool $deleted = false;

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
     */
    public function getNumeroDemandeAppro(): string
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     */
    public function setNumeroDemandeAppro(string $numeroDemandeAppro): self
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;

        return $this;
    }

    /**
     * Get the value of numeroLigne
     */
    public function getNumeroLigne(): string
    {
        return $this->numeroLigne;
    }

    /**
     * Set the value of numeroLigne
     */
    public function setNumeroLigne(string $numeroLigne): self
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
     */
    public function setQteDem($qteDem): self
    {
        $this->qteDem = $qteDem;

        return $this;
    }

    /**
     * Get the value of artConstp
     */
    public function getArtConstp(): ?string
    {
        return $this->artConstp;
    }

    /**
     * Set the value of artConstp
     */
    public function setArtConstp(?string $artConstp): self
    {
        $this->artConstp = $artConstp;

        return $this;
    }

    /**
     * Get the value of artRefp
     */
    public function getArtRefp(): string
    {
        return $this->artRefp;
    }

    /**
     * Set the value of artRefp
     */
    public function setArtRefp(string $artRefp): self
    {
        $this->artRefp = $artRefp;

        return $this;
    }

    /**
     * Get the value of artDesi
     */
    public function getArtDesi(): string
    {
        return $this->artDesi;
    }

    /**
     * Set the value of artDesi
     */
    public function setArtDesi(string $artDesi): self
    {
        $this->artDesi = $artDesi;

        return $this;
    }

    /**
     * Get the value of artFams1
     */
    public function getArtFams1(): ?string
    {
        return $this->artFams1;
    }

    /**
     * Set the value of artFams1
     */
    public function setArtFams1(?string $artFams1): self
    {
        $this->artFams1 = $artFams1;

        return $this;
    }

    /**
     * Get the value of artFams2
     */
    public function getArtFams2(): ?string
    {
        return $this->artFams2;
    }

    /**
     * Set the value of artFams2
     */
    public function setArtFams2(?string $artFams2): self
    {
        $this->artFams2 = $artFams2;

        return $this;
    }

    /**
     * Get the value of codeFams1
     */
    public function getCodeFams1(): ?string
    {
        return $this->codeFams1;
    }

    /**
     * Set the value of codeFams1
     */
    public function setCodeFams1(?string $codeFams1): self
    {
        $this->codeFams1 = $codeFams1;

        return $this;
    }

    /**
     * Get the value of codeFams2
     */
    public function getCodeFams2(): ?string
    {
        return $this->codeFams2;
    }

    /**
     * Set the value of codeFams2
     */
    public function setCodeFams2(?string $codeFams2): self
    {
        $this->codeFams2 = $codeFams2;

        return $this;
    }

    /**
     * Get the value of numeroFournisseur
     */
    public function getNumeroFournisseur(): ?string
    {
        return $this->numeroFournisseur;
    }

    /**
     * Set the value of numeroFournisseur
     */
    public function setNumeroFournisseur(?string $numeroFournisseur): self
    {
        $this->numeroFournisseur = $numeroFournisseur;

        return $this;
    }

    /**
     * Get the value of nomFournisseur
     */
    public function getNomFournisseur(): ?string
    {
        return $this->nomFournisseur;
    }

    /**
     * Set the value of nomFournisseur
     */
    public function setNomFournisseur(?string $nomFournisseur): self
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
     */
    public function setDateFinSouhaite($dateFinSouhaite): self
    {
        $this->dateFinSouhaite = $dateFinSouhaite;

        return $this;
    }

    /**
     * Get the value of commentaire
     */
    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    /**
     * Set the value of commentaire
     */
    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get the value of statutDal
     */
    public function getStatutDal(): string
    {
        return $this->statutDal;
    }

    /**
     * Set the value of statutDal
     */
    public function setStatutDal(string $statutDal): self
    {
        $this->statutDal = $statutDal;

        return $this;
    }

    /**
     * Get the value of articleStocke
     */
    public function getArticleStocke()
    {
        return $this->articleStocke;
    }

    /**
     * Set the value of articleStocke
     */
    public function setArticleStocke($articleStocke): self
    {
        $this->articleStocke = $articleStocke;

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
     * Get the value of estFicheTechnique
     */
    public function getEstFicheTechnique()
    {
        return $this->estFicheTechnique;
    }

    /**
     * Set the value of estFicheTechnique
     */
    public function setEstFicheTechnique($estFicheTechnique): self
    {
        $this->estFicheTechnique = $estFicheTechnique;

        return $this;
    }

    /**
     * Get the value of prixUnitaire
     */
    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    /**
     * Set the value of prixUnitaire
     */
    public function setPrixUnitaire(?string $prixUnitaire): self
    {
        $this->prixUnitaire = $prixUnitaire;

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
     * Get the value of fileNames
     */
    public function getFileNames()
    {
        return $this->fileNames;
    }

    /**
     * Set the value of fileNames
     */
    public function setFileNames($fileNames): self
    {
        $this->fileNames = $fileNames;

        return $this;
    }

    /**
     * Get the value of demandeur
     */
    public function getDemandeur(): ?string
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     */
    public function setDemandeur(?string $demandeur): self
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    /**
     * Get the value of deleted
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Set the value of deleted
     */
    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get the value of joursDispo
     */
    public function getJoursDispo(): ?int
    {
        return $this->joursDispo;
    }

    /**
     * Set the value of joursDispo
     */
    public function setJoursDispo(?int $joursDispo): self
    {
        $this->joursDispo = $joursDispo;

        return $this;
    }
}
