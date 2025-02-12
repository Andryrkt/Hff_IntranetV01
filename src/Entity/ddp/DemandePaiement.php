<?php

namespace App\Entity\ddp;

use App\Entity\Traits\DateTrait;

/**
 * @ORM\Entity(repositoryClass=DemandePaiementRepository::class)
 * @ORM\Table(name="demande_paiement")
 * @ORM\HasLifecycleCallbacks
 */
class DemandePaiement
{
    use DateTrait;

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
    private ?string $numero;


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
    private ?string $motif;

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

    private $numeroCommande;

    private $numeroFacture;

    private $contact;

    private $devise;

    private $montantAPayer;

    private $pieceJoint01; // proforma facture fournisseur

    private $commandeFichier;

    private $factureFournisseurFichier;

    private $controleLivraisonFichier;

    private $titreDeTransportFichier;

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
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set the value of numero
     *
     * @param  string|null  $numero
     *
     * @return  self
     */ 
    public function setNumero($numero)
    {
        $this->numero = $numero;

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
     * Get the value of controleLivraisonFichier
     */ 
    public function getControleLivraisonFichier()
    {
        return $this->controleLivraisonFichier;
    }

    /**
     * Set the value of controleLivraisonFichier
     *
     * @return  self
     */ 
    public function setControleLivraisonFichier($controleLivraisonFichier)
    {
        $this->controleLivraisonFichier = $controleLivraisonFichier;

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
}