<?php

namespace App\Entity\ddp;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\Traits\AgenceServiceTrait;
use App\Repository\ddp\DemandePaiementRepository;

/**
 * @ORM\Entity(repositoryClass=DemandePaiementRepository::class)
 * @ORM\Table(name="demande_paiement")
 * @ORM\HasLifecycleCallbacks
 */
class DemandePaiement
{
    use DateTrait;
    use AgenceServiceTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_paiement")
     *
     * @var string|null
     */
    private ?string $numeroDdp;


    /**
     * @ORM\ManyToOne(targetEntity=TypeDemande::class, inversedBy="demandePaiement")
     * @ORM\JoinColumn(name="type_demande_id", referencedColumnName="id")
     */
    private $typeDemandeId;

    /**
     * @ORM\Column(type="string", length=7, name="numero_fournisseur")
     *
     * @var string|null
     */
    private ?string $numeroFournisseur;

    /**
     * @ORM\Column(type="string", length=50, name="rib_fournisseur")
     *
     * @var string|null
     */
    private ?string $ribFournisseur;

    /**
     * @ORM\Column(type="string", length=50, name="beneficiaire")
     *
     * @var string|null
     */
    private ?string $beneficiaire;

    /**
     * @ORM\Column(type="string", length=255, name="motif")
     *
     * @var string|null
     */
    private ?string $motif = '';

    /**
     * @ORM\Column(type="string", length=2, name="agence_a_debiter")
     *
     * @var string|null
     */
    private ?string $agenceDebiter;

    /**
     * @ORM\Column(type="string", length=3, name="service_a_debiter")
     *
     * @var string|null
     */
    private ?string $serviceDebiter;

    /**
     * @ORM\Column(type="string", length=50, name="statut")
     *
     * @var string|null
     */
    private ?string $statut;

    /**
     * @ORM\Column(type="string", length=100, name="adresse_mail_demandeur")
     *
     * @var string|null
     */
    private ?string $adresseMailDemandeur;

    /**
     * @ORM\Column(type="string", length=100, name="demandeur")
     *
     * @var string|null
     */
    private ?string $demandeur;

    /**
     * @ORM\Column(type="string", length=50, name="mode_paiement")
     *
     * @var string|null
     */
    private ?string $modePaiement;

    /**
     * @ORM\Column(type="float", scale="2", name="montant_a_payer")
     */
    private ?float $montantAPayers = 0.00;


    /**
     * @ORM\Column(type="string", length=50, name="contact")
     *
     * @var [type]
     */
    private $contact;


    /**
     * @ORM\Column(type="json", name="numero_commande")
     */
    private $numeroCommande = [];

    /**
     * @ORM\Column(type="json", name="numero_facture")
     */
    private $numeroFacture = [];

    /**
     * @ORM\Column(type="string", length=5, name="devise")
     *
     * @var [type]
     */
    private $devise;

    /**
     * @ORM\Column(type="string", length=100, name="statut_dossier_regul")
     *
     * @var string|null
     */
    private ?string $statutDossierRegul;

    /**
     * @ORM\Column(type="integer", name="numeroVersion")
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="boolean", name="est_autre_doc")
     */
    private $estAutreDoc = false;

    /**
     * @ORM\Column(type="string", length=100, name="nom_autre_doc")
     */
    private ?string $nomAutreDoc;

    /**
     * @ORM\Column(type="boolean", name="est_cde_client_externe_doc")
     */
    private $estCdeClientExterneDoc = false;

    /**
     * @ORM\Column(type="json", name="nom_cde_client_externe_doc")
     */
    private $nomCdeClientExterneDoc = [];

    /**
     * @ORM\Column(type="json", name="numero_dossier_douane")
     */
    private $numeroDossierDouane = [];

    /**
     * @ORM\Column(type="boolean", name="appro")
     */
    private $appro = false;

    /**
     * @ORM\Column(type="integer", name="type_da")
     */
    private $typeDa;

    private string $montantAPayer = '0';

    private $pieceJoint01;

    private $pieceJoint02;

    private array $pieceJoint03 = [];

    private $pieceJoint04;

    private $commandeFichier;

    private $factureFournisseurFichier;

    private $titreDeTransportFichier;

    private $lesFichiers;

    // Pour le DA =====================================
    private $montantTotalCde;
    private $montantDejaPaye;
    private $montantRestantApayer;
    private $poucentageAvance;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numero
     *
     * @return  string|null
     */
    public function getNumeroDdp()
    {
        return $this->numeroDdp;
    }

    /**
     * Set the value of numero
     *
     * @param  string|null  $numero
     *
     * @return  self
     */
    public function setNumeroDdp($numeroDdp)
    {
        $this->numeroDdp = $numeroDdp;

        return $this;
    }

    /**
     * Get the value of typeDemandeId
     */
    public function getTypeDemandeId()
    {
        return $this->typeDemandeId;
    }

    /**
     * Set the value of typeDemandeId
     *
     * @return  self
     */
    public function setTypeDemandeId($typeDemandeId)
    {
        $this->typeDemandeId = $typeDemandeId;

        return $this;
    }

    /**
     * Get the value of numeroFournisseur
     *
     * @return  string|null
     */
    public function getNumeroFournisseur()
    {
        return $this->numeroFournisseur;
    }

    /**
     * Set the value of numeroFournisseur
     *
     * @param  string|null  $numeroFournisseur
     *
     * @return  self
     */
    public function setNumeroFournisseur($numeroFournisseur)
    {
        $this->numeroFournisseur = $numeroFournisseur;

        return $this;
    }

    /**
     * Get the value of ribFournisseur
     *
     * @return  string|null
     */
    public function getRibFournisseur()
    {
        return $this->ribFournisseur;
    }

    /**
     * Set the value of ribFournisseur
     *
     * @param  string|null  $ribFournisseur
     *
     * @return  self
     */
    public function setRibFournisseur($ribFournisseur)
    {
        $this->ribFournisseur = $ribFournisseur;

        return $this;
    }

    /**
     * Get the value of beneficiaire
     *
     * @return  string|null
     */
    public function getBeneficiaire()
    {
        return $this->beneficiaire;
    }

    /**
     * Set the value of beneficiaire
     *
     * @param  string|null  $beneficiaire
     *
     * @return  self
     */
    public function setBeneficiaire($beneficiaire)
    {
        $this->beneficiaire = $beneficiaire;

        return $this;
    }

    /**
     * Get the value of motif
     *
     * @return  string|null
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set the value of motif
     *
     * @param  string|null  $motif
     *
     * @return  self
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;

        return $this;
    }

    /**
     * Get the value of agenceDebiter
     *
     * @return  string|null
     */
    public function getAgenceDebiter()
    {
        return $this->agenceDebiter;
    }

    /**
     * Set the value of agenceDebiter
     *
     * @param  string|null  $agenceDebiter
     *
     * @return  self
     */
    public function setAgenceDebiter($agenceDebiter)
    {
        $this->agenceDebiter = $agenceDebiter;

        return $this;
    }

    /**
     * Get the value of serviceDebiter
     *
     * @return  string|null
     */
    public function getServiceDebiter()
    {
        return $this->serviceDebiter;
    }

    /**
     * Set the value of serviceDebiter
     *
     * @param  string|null  $serviceDebiter
     *
     * @return  self
     */
    public function setServiceDebiter($serviceDebiter)
    {
        $this->serviceDebiter = $serviceDebiter;

        return $this;
    }

    /**
     * Get the value of statut
     *
     * @return  string|null
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @param  string|null  $statut
     *
     * @return  self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of adresseMailDemandeur
     *
     * @return  string|null
     */
    public function getAdresseMailDemandeur()
    {
        return $this->adresseMailDemandeur;
    }

    /**
     * Set the value of adresseMailDemandeur
     *
     * @param  string|null  $adresseMailDemandeur
     *
     * @return  self
     */
    public function setAdresseMailDemandeur($adresseMailDemandeur)
    {
        $this->adresseMailDemandeur = $adresseMailDemandeur;

        return $this;
    }

    /**
     * Get the value of demandeur
     *
     * @return  string|null
     */
    public function getDemandeur()
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     *
     * @param  string|null  $demandeur
     *
     * @return  self
     */
    public function setDemandeur($demandeur)
    {
        $this->demandeur = $demandeur;

        return $this;
    }



    /**
     * Get the value of numeroCommande
     */
    public function getNumeroCommande()
    {
        return $this->numeroCommande;
    }

    /**
     * Set the value of numeroCommande
     *
     * @return  self
     */
    public function setNumeroCommande($numeroCommande)
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    /**
     * Get the value of numeroFacture
     */
    public function getNumeroFacture()
    {
        return $this->numeroFacture;
    }

    /**
     * Set the value of numeroFacture
     *
     * @return  self
     */
    public function setNumeroFacture($numeroFacture)
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    /**
     * Get the value of contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set the value of contact
     *
     * @return  self
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get the value of devise
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Set the value of devise
     *
     * @return  self
     */
    public function setDevise($devise)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * Get the value of montantAPayer
     */
    public function getMontantAPayer()
    {
        return $this->montantAPayer;
    }

    /**
     * Set the value of montantAPayer
     *
     * @return  self
     */
    public function setMontantAPayer($montantAPayer)
    {
        $this->montantAPayer = $montantAPayer;

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
     * Get the value of commandeFichier
     */
    public function getCommandeFichier()
    {
        return $this->commandeFichier;
    }

    /**
     * Set the value of commandeFichier
     *
     * @return  self
     */
    public function setCommandeFichier($commandeFichier)
    {
        $this->commandeFichier = $commandeFichier;

        return $this;
    }

    /**
     * Get the value of factureFournisseurFichier
     */
    public function getFactureFournisseurFichier()
    {
        return $this->factureFournisseurFichier;
    }

    /**
     * Set the value of factureFournisseurFichier
     *
     * @return  self
     */
    public function setFactureFournisseurFichier($factureFournisseurFichier)
    {
        $this->factureFournisseurFichier = $factureFournisseurFichier;

        return $this;
    }


    /**
     * Get the value of titreDeTransportFichier
     */
    public function getTitreDeTransportFichier()
    {
        return $this->titreDeTransportFichier;
    }

    /**
     * Set the value of titreDeTransportFichier
     *
     * @return  self
     */
    public function setTitreDeTransportFichier($titreDeTransportFichier)
    {
        $this->titreDeTransportFichier = $titreDeTransportFichier;

        return $this;
    }

    /**
     * Get the value of modePaiement
     */
    public function getModePaiement()
    {
        return $this->modePaiement;
    }

    /**
     * Set the value of modePaiement
     *
     * @return  self
     */
    public function setModePaiement($modePaiement)
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }


    /**
     * Get the value of montantAPayers
     */
    public function getMontantAPayers()
    {
        return $this->montantAPayers;
    }

    /**
     * Set the value of montantAPayers
     *
     * @return  self
     */
    public function setMontantAPayers($montantAPayers)
    {
        $this->montantAPayers = $montantAPayers;

        return $this;
    }

    /**
     * Get the value of lesFichiers
     */
    public function getLesFichiers()
    {
        return $this->lesFichiers;
    }

    /**
     * Set the value of lesFichiers
     *
     * @return  self
     */
    public function setLesFichiers($lesFichiers)
    {
        $this->lesFichiers = $lesFichiers;

        return $this;
    }

    /**
     * Get the value of pieceJoint02
     */
    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    /**
     * Set the value of pieceJoint02
     *
     * @return  self
     */
    public function setPieceJoint02($pieceJoint02)
    {
        $this->pieceJoint02 = $pieceJoint02;

        return $this;
    }

    /**
     * Get the value of pieceJoint03
     */
    public function getPieceJoint03()
    {
        return $this->pieceJoint03;
    }

    /**
     * Set the value of pieceJoint03
     *
     * @return  self
     */
    public function setPieceJoint03($pieceJoint03)
    {
        $this->pieceJoint03 = $pieceJoint03;

        return $this;
    }

    /**
     * Get the value of pieceJoint04
     */
    public function getPieceJoint04()
    {
        return $this->pieceJoint04;
    }

    /**
     * Set the value of pieceJoint04
     *
     * @return  self
     */
    public function setPieceJoint04($pieceJoint04)
    {
        $this->pieceJoint04 = $pieceJoint04;

        return $this;
    }

    /**
     * Get the value of statutDossierRegul
     *
     * @return  string|null
     */
    public function getStatutDossierRegul()
    {
        return $this->statutDossierRegul;
    }

    /**
     * Set the value of statutDossierRegul
     *
     * @param  string|null  $statutDossierRegul
     *
     * @return  self
     */
    public function setStatutDossierRegul($statutDossierRegul)
    {
        $this->statutDossierRegul = $statutDossierRegul;

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
     * Get the value of estAutreDoc
     */
    public function getEstAutreDoc()
    {
        return $this->estAutreDoc;
    }

    /**
     * Set the value of estAutreDoc
     *
     * @return  self
     */
    public function setEstAutreDoc($estAutreDoc)
    {
        $this->estAutreDoc = $estAutreDoc;

        return $this;
    }

    /**
     * Get the value of nomAutreDoc
     */
    public function getNomAutreDoc()
    {
        return $this->nomAutreDoc;
    }

    /**
     * Set the value of nomAutreDoc
     *
     * @return  self
     */
    public function setNomAutreDoc($nomAutreDoc)
    {
        $this->nomAutreDoc = $nomAutreDoc;

        return $this;
    }

    /**
     * Get the value of estCdeClientExterneDoc
     */
    public function getEstCdeClientExterneDoc()
    {
        return $this->estCdeClientExterneDoc;
    }

    /**
     * Set the value of estCdeClientExterneDoc
     *
     * @return  self
     */
    public function setEstCdeClientExterneDoc($estCdeClientExterneDoc)
    {
        $this->estCdeClientExterneDoc = $estCdeClientExterneDoc;

        return $this;
    }

    /**
     * Get the value of nomCdeClientExterneDoc
     */
    public function getNomCdeClientExterneDoc()
    {
        return $this->nomCdeClientExterneDoc;
    }

    /**
     * Set the value of nomCdeClientExterneDoc
     *
     * @return  self
     */
    public function setNomCdeClientExterneDoc($nomCdeClientExterneDoc)
    {
        $this->nomCdeClientExterneDoc = $nomCdeClientExterneDoc;

        return $this;
    }

    /**
     * Get the value of numeroDossierDouane
     */
    public function getNumeroDossierDouane()
    {
        return $this->numeroDossierDouane;
    }

    /**
     * Set the value of numeroDossierDouane
     *
     * @return  self
     */
    public function setNumeroDossierDouane($numeroDossierDouane)
    {
        $this->numeroDossierDouane = $numeroDossierDouane;

        return $this;
    }

    /**
     * Get the value of appro
     */
    public function getAppro()
    {
        return $this->appro;
    }

    /**
     * Set the value of appro
     */
    public function setAppro($appro): self
    {
        $this->appro = $appro;

        return $this;
    }

    /**
     * Get the value of montantTotalCde
     */
    public function getMontantTotalCde()
    {
        return $this->montantTotalCde;
    }

    /**
     * Set the value of montantTotalCde
     */
    public function setMontantTotalCde($montantTotalCde): self
    {
        $this->montantTotalCde = $montantTotalCde;

        return $this;
    }

    /**
     * Get the value of typeDa
     */
    public function getTypeDa()
    {
        return $this->typeDa;
    }

    /**
     * Set the value of typeDa
     */
    public function setTypeDa($typeDa): self
    {
        $this->typeDa = $typeDa;

        return $this;
    }

    public function dupliquer(): self
    {
        $nouvelle = new self();
        $nouvelle->numeroDdp = $this->numeroDdp;
        $nouvelle->numeroFournisseur = $this->numeroFournisseur;
        $nouvelle->ribFournisseur = $this->ribFournisseur;
        $nouvelle->motif = $this->motif;
        $nouvelle->agenceDebiter = $this->agenceDebiter;
        $nouvelle->serviceDebiter = $this->serviceDebiter;
        $nouvelle->adresseMailDemandeur = $this->adresseMailDemandeur;
        $nouvelle->demandeur = $this->demandeur;
        $nouvelle->montantAPayers = $this->montantAPayers;
        $nouvelle->montantAPayer = $this->montantAPayer;
        $nouvelle->contact = $this->contact;
        $nouvelle->numeroCommande = $this->numeroCommande;
        $nouvelle->devise = $this->devise;
        $nouvelle->numeroFacture = $this->numeroFacture;
        $nouvelle->pieceJoint01 = $this->pieceJoint01;
        $nouvelle->pieceJoint02 = $this->pieceJoint02;
        $nouvelle->pieceJoint03 = $this->pieceJoint03;
        $nouvelle->pieceJoint04 = $this->pieceJoint04;
        $nouvelle->beneficiaire = $this->beneficiaire;
        $nouvelle->modePaiement = $this->modePaiement;
        $nouvelle->typeDemandeId  = $this->typeDemandeId;
        return $nouvelle;
    }
}
