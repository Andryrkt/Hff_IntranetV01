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
use App\Repository\da\DemandeApproLRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DemandeApproLRepository::class)
 * @ORM\Table(name="Demande_Appro_L")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeApproL
{
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
     * @ORM\Column(type="boolean", name="art_rempl")
     */
    private $artRempl = false;

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
    private string $artRefp;

    /**
     * @ORM\Column(type="string", length=100, name="art_desi")
     */
    private string $artDesi;

    /**
     * @ORM\Column(type="string", length=50, name="art_fams1")
     */
    private string $artFams1;

    /**
     * @ORM\Column(type="string", length=50, name="art_fams2")
     */
    private string $artFams2;

    /**
     * @ORM\Column(type="string", length=7, name="numero_fournisseur")
     */
    private string $numeroFournisseur;

    /**
     * @ORM\Column(type="string", length=50, name="nom_fournisseur")
     */
    private string $nomFournisseur;

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
     */
    private ?DemandeAppro $demandeAppro = null;

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
     * Get the value of artRempl
     */
    public function getArtRempl()
    {
        return $this->artRempl;
    }

    /**
     * Set the value of artRempl
     *
     * @return  self
     */
    public function setArtRempl($artRempl)
    {
        $this->artRempl = $artRempl;

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
}
