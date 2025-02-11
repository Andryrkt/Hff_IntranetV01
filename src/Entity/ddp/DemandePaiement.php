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

    
    private ?int $modePaiementId;

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
     * Get the value of modePaiementId
     */ 
    public function getModePaiementId()
    {
        return $this->modePaiementId;
    }

    /**
     * Set the value of modePaiementId
     *
     * @return  self
     */ 
    public function setModePaiementId($modePaiementId)
    {
        $this->modePaiementId = $modePaiementId;

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
}