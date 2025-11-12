<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\da\DaSoumissionBcRepository;

/**
 * @ORM\Entity(repositoryClass=DaSoumissionBcRepository::class)
 * @ORM\Table(name="da_soumission_bc")
 * @ORM\HasLifecycleCallbacks
 */
class DaSoumissionBc
{
    use DateTrait;

    public const STATUT_A_GENERER                = 'A générer';
    public const STATUT_A_EDITER                 = 'A éditer';
    public const STATUT_A_SOUMETTRE_A_VALIDATION = 'A soumettre à validation';
    public const STATUT_A_ENVOYER_AU_FOURNISSEUR = 'A envoyer au fournisseur';
    public const STATUT_SOUMISSION               = 'Soumis à validation';
    public const STATUT_A_VALIDER_DA             = 'A valider DA';
    public const STATUT_VALIDE                   = 'Validé';
    public const STATUT_CLOTURE                  = 'Clôturé';
    public const STATUT_REFUSE                   = 'Refusé';
    public const STATUT_BC_ENVOYE_AU_FOURNISSEUR = 'BC envoyé au fournisseur';
    public const STATUT_PAS_DANS_OR              = 'PAS DANS OR';
    public const STATUT_NON_DISPO                = 'Non Dispo Fournisseur';

    // statut pour Da Reappro
    public const STATUT_CESSION_A_GENERER = 'Cession à générer';
    public const STATUT_EN_COURS_DE_PREPARATION = 'En cours de préparation';


    // statut pour Da Reappro , Da Direct, Da Via OR
    public const STATUT_TOUS_LIVRES              = 'Tous livrés';
    public const STATUT_PARTIELLEMENT_LIVRE      = 'Partiellement livré';
    public const STATUT_PARTIELLEMENT_DISPO      = 'Partiellement dispo';
    public const STATUT_COMPLET_NON_LIVRE        = 'Complet non livré';

    public const POSITION_TERMINER = 'TE';
    public const POSITION_ENCOUR   = 'EC';
    public const POSITION_EDITER   = 'ED';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_appro")
     */
    private ?string $numeroDemandeAppro;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit")
     */
    private ?string $numeroDemandeDit;

    /**
     * @ORM\Column(type="string", length=11, name="numero_or")
     */
    private ?string $numeroOR;

    /**
     * @ORM\Column(type="string", length=11, name="numero_cde")
     */
    private ?string $numeroCde;

    /**
     * @ORM\Column(type="string", length=100, name="statut")
     */
    private ?string $statut;

    /**
     * @ORM\Column(type="string", length=255, name="piece_joint1")
     */
    private $pieceJoint1;

    private $pieceJoint2;

    /**
     * @ORM\Column(type="string", length=255, name="utilisateur")
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private $numeroVersion;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="montant_bc", nullable=true)
     *
     * @var float|null
     */
    private ?float $montantBc;

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
     * Get the value of numeroOR
     */
    public function getNumeroOR()
    {
        return $this->numeroOR;
    }

    /**
     * Set the value of numeroOR
     *
     * @return  self
     */
    public function setNumeroOR($numeroOR)
    {
        $this->numeroOR = $numeroOR;

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
     * Get the value of statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of nomFicheBc
     */
    public function getPieceJoint1()
    {
        return $this->pieceJoint1;
    }

    /**
     * Set the value of nomFicheBc
     *
     * @return  self
     */
    public function setPieceJoint1($pieceJoint1)
    {
        $this->pieceJoint1 = $pieceJoint1;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    public function setNumeroVersion($numeroVersion): self
    {
        $this->numeroVersion = $numeroVersion;

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
     *
     * @return  self
     */
    public function setPieceJoint2($pieceJoint2)
    {
        $this->pieceJoint2 = $pieceJoint2;

        return $this;
    }

    /**
     * Get the value of montantBc
     *
     * @return  float|null
     */
    public function getMontantBc()
    {
        return $this->montantBc;
    }

    /**
     * Set the value of montantBc
     *
     * @param  float|null  $montantBc
     *
     * @return  self
     */
    public function setMontantBc($montantBc)
    {
        $this->montantBc = $montantBc;

        return $this;
    }
}
