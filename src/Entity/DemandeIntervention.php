<?php

namespace App\Entity;

use App\Entity\Role;
use App\Traits\DateTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="demande_intervention")
 * @ORM\HasLifecycleCallbacks
 */

class DemandeIntervention
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_demande_interention_atelier")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit",nullable=true)
     */
    private ?string $numeroDemandeIntervention;
    /**
     * @ORM\Column(type="string", length=3, name="type_document",nullable=true)
     */
    private ?string $typeDocument;
    /**
     * @ORM\Column(type="string", length=2, name="code_societe",nullable=true)
     */
    private ?string $codeSociete;
    /**
     * @ORM\Column(type="string", length=30, name="type_reparation",nullable=true)
     */
    private ?string $typeReparation;
    /**
     * @ORM\Column(type="string", length=30, name="reparation_realise",nullable=true)
     */
    private ?string $reparationRealise;
    /**
     * @ORM\Column(type="string", length=30, name="categorie_demande",nullable=true)
     */
    private ?string $categorieDemande;
    /**
     * @ORM\Column(type="string", length=140, name="internet_externe",nullable=true)
     */
    private ?string $internetExterne;
    /**
     * @ORM\Column(type="string", length=5, name="agence_service_debiteur",nullable=true)
     */
    private ?string $agenceServiceDebiteur;
    /**
     * @ORM\Column(type="string", length=5, name="agence_service_emmetteur",nullable=true)
     */
    private ?string $agenceServiceEmetteur;
    /**
     * @ORM\Column(type="string", length=100, name="nom_client",nullable=true)
     */
    private ?string $nomClient;
    /**
     * @ORM\Column(type="string", length=10, name="numero_telephone",nullable=true)
     */
    private ?string $numeroTel;
    /**
     * @ORM\Column(type="datetime",  name="date_or",nullable=true)
     */
    private ?DateTime $dateOr;
    /**
     * @ORM\Column(type="string", length=5, name="heure_or",nullable=true)
     */
    private ?string $heureOR;
    /**
     * @ORM\Column(type="datetime",  name="date_prevue_travaux",nullable=true)
     */
    private ?DateTime $datePrevueTravaux;
    /**
     * @ORM\Column(type="string", length=3, name="demande_devis",nullable=true)
     */
    private ?string $demande_Devis;

    private  $idNiveauUrgence;
    /**
     * @ORM\Column(type="string", length=3, name="avis_recouvrement",nullable=true)
     */
    private ?string $avisRecouvrement;
    /**
     * @ORM\Column(type="string", length=3, name="client_sous_contrat",nullable=true)
     */
    private ?string $clientSousContrat;
    /**
     * @ORM\Column(type="string", length=100, name="objet_demande",nullable=true)
     */
    private ?string $objetDemande;
    /**
     * @ORM\Column(type="string", length=5000, name="detail_demande",nullable=true)
     */
    private ?string $detailDemande;
    /**
     * @ORM\Column(type="string", length=3, name="livraison_partiel",nullable=true)
     */
    private ?string $livraisonPartiel;

    private $idMateriel;
    /**
     * @ORM\Column(type="string", length=100, name="mail_demandeur",nullable=true)
     */
    private ?string $mailDemandeur;
    /**
     * @ORM\Column(type="datetimes",  name="date_demande",nullable=true)
     */
    private datetime $dateDemande;
    /**
     * @ORM\Column(type="string", length=5, name="heure_cloture",nullable=true)
     */
    private ?string $heureCloture;
    /**
     * @ORM\Column(type="string", length=200, name="piece_joint",nullable=true)
     */
    private ?string $pieceJoint;
    /**
     * @ORM\Column(type="string", length=200, name="piece_joint1",nullable=true)
     */
    private ?string $pieceJoint01;
    /**
     * @ORM\Column(type="string", length=200, name="piece_joint2,nullable=true)
     */
    private ?string $pieceJoint02;
    /**
     * @ORM\Column(type="string", length=50, name="utilisateur_demandeur,nullable=true)
     */
    private ?string $utilisateurDemandeur;
    /**
     * @ORM\Column(type="string", length=3000, name="observations,nullable=true)
     */
    private ?string $observations;

    private $idStatutDemande;
    /**
     * @ORM\Column(type="datetime",  name="date_validation",nullable=true)
     */
    private ?datetime $dateValidation;
    /**
     * @ORM\Column(type="string", length=5, name="heure_validation",nullable=true)
     */
    private ?string $heureValidation;
    /**
     * @ORM\Column(type="string", length=15, name="numero_client",nullable=true)
     */
    private ?string $numeroClient;
    /**
     * @ORM\Column(type="string", length=50, name="libelle_client",nullable=true)
     */
    private ?string $libelleClient;
    /**
     * @ORM\Column(type="datetime",  name="date_fin_souhaite",nullable=true)
     */
    private ?datetime $dateFinSouhaite;
    /**
     * @ORM\Column(type="string", length=15, name="numero_or",nullable=true)
     */
    private ?string $numeroOR;
    /**
     * @ORM\Column(type="string", length=3000, name="observation_direction_technique",nullable=true)
     */
    private ?string $observationDirectionTechnique;
    /**
     * @ORM\Column(type="string", length=3000, name="observation_devis",nullable=true)
     */
    private ?string $observationDevis;
    /**
     * @ORM\Column(type="string", length=200, name="numero_devis_rattache",nullable=true)
     */
    private ?string $numeroDevisRattache;
/**
     * @ORM\Column(type="datetime",  name="date_soumission_devis",nullable=true)
     */
    private ?datetime $dateSoumissionDevis;
    /**
     * @ORM\Column(type="string", length=3, name="devis_valide",nullable=true)
     */
    private ?string $devisValide;

    private $îdServiceIntervenant;
/**
     * @ORM\Column(type="datetime",  name="date_devis_fin_probable",nullable=true)
     */
    private ?DateTime $dateDevisFinProbable;
/**
     * @ORM\Column(type="datetime", name="date_fin_estimation_travaux",nullable=true)
     */
    private ?datetime $dateFinEstimationTravaux;
/**
     * @ORM\Column(type="string", length=3, name="code_section",nullable=true)
     */
    private ?string $codeSection;
    /**
     * @ORM\Column(type="string", length=3, name="mase_ate",nullable=true)
     */
    private ?string $masAte;
    /**
     * @ORM\Column(type="string", length=6, name="code_ate",nullable=true)
     */
    private ?string $codeAte;
    /**
     * @ORM\Column(type="string", length=50, name="secteur",nullable=true)
     */
    private ?string $secteur;
     /**
     * @ORM\Column(type="string", length=3, name="utilisateur_intervenant",nullable=true)
     */
    private ?string $utilisateurIntervenant;
}
