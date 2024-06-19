<?php

namespace App\Entity;

use App\Traits\AgenceServiceEmetteurTrait;
use App\Traits\AgenceServiceTrait;
use App\Traits\CaracteristiqueMaterielTrait;
use App\Traits\numParcNumSerieTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DemandeInterventionRepository")
 * @ORM\Table(name="demande_intervention")
 * @ORM\HasLifecycleCallbacks
 */

class DemandeIntervention
{
   use AgenceServiceEmetteurTrait;
   use AgenceServiceTrait;
   use CaracteristiqueMaterielTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_demande_intervention_atelier")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit",nullable=true)
     */
    private ?string $numeroDemandeIntervention = null;

    /**
     * @ORM\ManyToOne(targetEntity="WorTypeDocument", inversedBy="demandeInterventions")
     * @ORM\JoinColumn(name="type_document", referencedColumnName="id_type_document")
     */
    private  $typeDocument = null;//relation avec la table wor_type_document

    /**
     * @ORM\ManyToOne(targetEntity="Societte", inversedBy="demandeInterventions")
     * @ORM\JoinColumn(name="code_societe", referencedColumnName="id_societe")
     */
    private  $codeSociete = null;// relation avec la table societe

    /**
     * @ORM\Column(type="string", length=30, name="type_reparation",nullable=true)
     */
    private ?string $typeReparation = null;

    /**
     * @ORM\Column(type="string", length=30, name="reparation_realise",nullable=true)
     */
    private ?string $reparationRealise = null;

   /**
     * @ORM\ManyToOne(targetEntity="CategorieATEAPP", inversedBy="DemandeIntervention")
     * @ORM\JoinColumn(name="categorie_demande", referencedColumnName="id_categorie_ate_app")
     */
    private ?string $categorieDemande = null;//relation avec la table categorie_ate_app

    /**
     * @ORM\Column(type="string", length=140, name="internet_externe",nullable=true)
     */
    private ?string $internetExterne = null;

    /**
     * @ORM\Column(type="string", length=5, name="agence_service_debiteur",nullable=true)
     */
    private ?string $agenceServiceDebiteur = null;

    /**
     * @ORM\Column(type="string", length=5, name="agence_service_emmeteur",nullable=true)
     */
    private ?string $agenceServiceEmetteur = null;

    /**
     * @ORM\Column(type="string", length=100, name="nom_client",nullable=true)
     */
    private ?string $nomClient = null;

    /**
     * @ORM\Column(type="string", length=10, name="numero_telephone",nullable=true)
     */
    private ?string $numeroTel= null;

    /**
     * @ORM\Column(type="datetime",  name="date_or",nullable=true)
     */
    private ?DateTime $dateOr = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_or",nullable=true)
     */
    private ?string $heureOR = null;

    /**
     * @ORM\Column(type="datetime",  name="date_prevue_travaux",nullable=true)
     */
    private ?DateTime $datePrevueTravaux = null;

    /**
     * @ORM\Column(type="string", length=3, name="demande_devis",nullable=true)
     */
    private ?string $demandeDevis = null;

    /**
     * @ORM\ManyToOne(targetEntity="WorNiveauUrgence", inversedBy="DemandeInterventions")
     * @ORM\JoinColumn(name="id_niveau_urgence", referencedColumnName="id_niveau_urgence")
     */
    private  $idNiveauUrgence = null;

    /**
     * @ORM\Column(type="string", length=3, name="avis_recouvrement",nullable=true)
     */
    private ?string $avisRecouvrement = null;

    /**
     * @ORM\Column(type="string", length=3, name="client_sous_contrat",nullable=true)
     */
    private ?string $clientSousContrat = null;

    /**
     * @ORM\Column(type="string", length=100, name="objet_demande",nullable=true)
     */
    private ?string $objetDemande = null;

    /**
     * @ORM\Column(type="string", length=5000, name="detail_demande",nullable=true)
     */
    private ?string $detailDemande = null;

    /**
     * @ORM\Column(type="string", length=3, name="livraison_partiel",nullable=true)
     */
    private ?string $livraisonPartiel = null;

    /**
     * @ORM\Column(type="integer", name="id_materiel", nullable=true)
     */
    private ?int $idMateriel = null;

    /**
     * @ORM\Column(type="string", length=100, name="mail_demandeur",nullable=true)
     */
    private ?string $mailDemandeur = null;

    /**
     * @ORM\Column(type="datetime",  name="date_demande", nullable=true)
     */
    private ?datetime $dateDemande = null;

/**
 * @ORM\Column(type="string", length=5, name="heure_demande", nullable=true)
 *
 * @var string|null
 */
    private ?string $heureDemande = null;

    /**
     * @ORM\Column(type="datetime", name="date_cloture")
     *
     * @var DateTime|null
     */
    private ?DateTime $dateCloture = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_cloture",nullable=true)
     */
    private ?string $heureCloture = null;

    /**
     * @ORM\Column(type="string", length=200, name="piece_joint",nullable=true)
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "image/jpeg", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
     *     mimeTypesMessage="Please upload a valid PDF, JPEG, XLSX, or DOCX file."
     * )
     */
    private ?string $pieceJoint03 = null;

    /**
     * @ORM\Column(type="string", length=200, name="piece_joint1",nullable=true)
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "image/jpeg", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
     *     mimeTypesMessage="Please upload a valid PDF, JPEG, XLSX, or DOCX file."
     * )
     */
    private ?string $pieceJoint01 =null;

    /**
 * @ORM\Column(type="string", length=200, name="piece_joint2", nullable=true)
 * @Assert\File(
 *     maxSize="5M",
 *     mimeTypes={"application/pdf", "image/jpeg", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
 *     mimeTypesMessage="Please upload a valid PDF, JPEG, XLSX, or DOCX file."
 * )
 */
    private ?string $pieceJoint02=null;

    /**
     * @ORM\Column(type="string", length=50, name="utilisateur_demandeur", nullable=true)
     */
    private ?string $utilisateurDemandeur = null;

    /**
     * @ORM\Column(type="string", length=3000, name="observations", nullable=true)
     */
    private ?string $observations = null;

    /**
     * @ORM\ManyToOne(targetEntity="StatutDemande", inversedBy="DemandeIntervention")
     * @ORM\JoinColumn(name="id_statut_demande", referencedColumnName="ID_Statut_Demande")
     */
    private $idStatutDemande = null;

    /**
     * @ORM\Column(type="datetime",  name="date_validation",nullable=true)
     */
    private ?datetime $dateValidation = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_validation",nullable=true)
     */
    private ?string $heureValidation = null;

    /**
     * @ORM\Column(type="string", length=15, name="numero_client",nullable=true)
     */
    private ?string $numeroClient = null;

    /**
     * @ORM\Column(type="string", length=50, name="libelle_client",nullable=true)
     */
    private ?string $libelleClient = null;

    /**
     * @ORM\Column(type="datetime",  name="date_fin_souhaite",nullable=true)
     */
    private ?datetime $dateFinSouhaite = null;

    /**
     * @ORM\Column(type="string", length=15, name="numero_or",nullable=true)
     */
    private ?string $numeroOR = null;

    /**
     * @ORM\Column(type="string", length=3000, name="observation_direction_technique",nullable=true)
     */
    private ?string $observationDirectionTechnique = null;

    /**
     * @ORM\Column(type="string", length=3000, name="observation_devis",nullable=true)
     */
    private ?string $observationDevis = null;

    /**
     * @ORM\Column(type="string", length=200, name="numero_devis_rattache",nullable=true)
     */
    private ?string $numeroDevisRattache = null;

    /**
     * @ORM\Column(type="datetime",  name="date_soumission_devis",nullable=true)
     */
    private ?datetime $dateSoumissionDevis = null;

    /**
     * @ORM\Column(type="string", length=3, name="devis_valide",nullable=true)
     */
    private ?string $devisValide = null;

    /**
     * @ORM\Column(type="datetime", name="date_validation_devis", nullable=true)
     *
     * @var datetime|null
     */
    private ?datetime $dateValidationDevis = null;

    /**
     * @ORM\Column(type="string", length=3, name="id_service_intervenant", nullable=true)
     *
     * @var string|null
     */
    private ?string $idServiceIntervenant = null;

    /**
     * @ORM\Column(type="datetime",  name="date_devis_fin_probable",nullable=true)
     */
    private ?DateTime $dateDevisFinProbable = null;

    /**
     * @ORM\Column(type="datetime", name="date_fin_estimation_travaux",nullable=true)
     */
    private ?datetime $dateFinEstimationTravaux = null;

    /**
     * @ORM\Column(type="string", length=3, name="code_section",nullable=true)
     */
    private ?string $codeSection = null;

    /**
     * @ORM\Column(type="string", length=3, name="mas_ate",nullable=true)
     */
    private ?string $masAte = null;

    /**
     * @ORM\Column(type="string", length=6, name="code_ate",nullable=true)
     */
    private ?string $codeAte = null;

    /**
     * @ORM\ManyToOne(targetEntity="Secteur", inversedBy="demandeInterventions")
     * @ORM\JoinColumn(name="secteur", referencedColumnName="id_secteur")
     */
    private $secteur = null;

    /**
     * @ORM\Column(type="string", length=50, name="utilisateur_intervenant",nullable=true)
     */
    private ?string $utilisateurIntervenant = null;



    public function getId()
    {
        return $this->id;
    }

    
    public function getNumeroDemandeIntervention(): string
    {
        return $this->numeroDemandeIntervention;
    }

   
    public function setNumeroDemandeIntervention(string $numeroDemandeIntervention): self
    {
        $this->numeroDemandeIntervention = $numeroDemandeIntervention;

        return $this;
    }


    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    
    public function setTypeDocument($typeDocument): self
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }
    
     
    public function getCodeSociete()
    {
        return $this->codeSociete;
    }

    
    public function setCodeSociete($codeSociete): self
    {
        $this->codeSociete = $codeSociete;

        return $this;
    }

    public function getTypeReparation(): string
    {
        return $this->typeReparation;
    }

    public function setTypeReparation( string $typeReparation): self
    {
        $this->typeReparation = $typeReparation;

        return $this;
    }

    
    public function getReparationRealise()
    {
        return $this->reparationRealise;
    }

    
    public function setReparationRealise($reparationRealise): self
    {
        $this->reparationRealise = $reparationRealise;

        return $this;
    }

    
    public function getCategorieDemande()
    {
        return $this->categorieDemande;
    }

    
    public function setCategorieDemande($categorieDemande): self
    {
        $this->categorieDemande = $categorieDemande;

        return $this;
    }

   
    public function getInternetExterne()
    {
        return $this->internetExterne;
    }

   
    public function setInternetExterne($internetExterne): self
    {
        $this->internetExterne = $internetExterne;

        return $this;
    }

    
    public function getAgenceServiceDebiteur()
    {
        return $this->agenceServiceDebiteur;
    }

    
    public function setAgenceServiceDebiteur($agenceServiceDebiteur): self
    {
        $this->agenceServiceDebiteur = $agenceServiceDebiteur;

        return $this;
    }

    
    public function getAgenceServiceEmetteur()
    {
        return $this->agenceServiceEmetteur;
    }

    
    public function setAgenceServiceEmetteur($agenceServiceEmetteur): self
    {
        $this->agenceServiceEmetteur = $agenceServiceEmetteur;

        return $this;
    }

   
    public function getNomClient()
    {
        return $this->nomClient;
    }

    
    public function setNomClient($nomClient): self
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    public function getNumeroTel()
    {
        return $this->numeroTel;
    }

    
    public function setNumeroTel($numeroTel): self
    {
        $this->numeroTel = $numeroTel;

        return $this;
    }

   
    public function getDateOr()
    {
        return $this->dateOr;
    }

    
    public function setDateOr($dateOr): self
    {
        $this->dateOr = $dateOr;

        return $this;
    }

    
    public function getHeureOR()
    {
        return $this->heureOR;
    }

    
    public function setHeureOR($heureOR): self
    {
        $this->heureOR = $heureOR;

        return $this;
    }

    
    public function getDatePrevueTravaux()
    {
        return $this->datePrevueTravaux;
    }

    
    public function setDatePrevueTravaux($datePrevueTravaux): self
    {
        $this->datePrevueTravaux = $datePrevueTravaux;

        return $this;
    }

    
    public function getDemandeDevis()
    {
        return $this->demandeDevis;
    }

    
    public function setDemandeDevis($demandeDevis): self
    {
        $this->demandeDevis = $demandeDevis;

        return $this;
    }

    
    public function getIdNiveauUrgence()
    {
        return $this->idNiveauUrgence;
    }

    
    public function setIdNiveauUrgence($idNiveauUrgence): self
    {
        $this->idNiveauUrgence = $idNiveauUrgence;

        return $this;
    }

  
    public function getAvisRecouvrement()
    {
        return $this->avisRecouvrement;
    }

    
    public function setAvisRecouvrement($avisRecouvrement): self
    {
        $this->avisRecouvrement = $avisRecouvrement;

        return $this;
    }

   
    public function getClientSousContrat()
    {
        return $this->clientSousContrat;
    }

   
    public function setClientSousContrat($clientSousContrat): self
    {
        $this->clientSousContrat = $clientSousContrat;

        return $this;
    }

    
    public function getObjetDemande()
    {
        return $this->objetDemande;
    }

    public function setObjetDemande($objetDemande): self
    {
        $this->objetDemande = $objetDemande;

        return $this;
    }

   
    public function getDetailDemande()
    {
        return $this->detailDemande;
    }

   
    public function setDetailDemande($detailDemande): self
    {
        $this->detailDemande = $detailDemande;

        return $this;
    }

    
    public function getLivraisonPartiel()
    {
        return $this->livraisonPartiel;
    }

    
    public function setLivraisonPartiel($livraisonPartiel): self
    {
        $this->livraisonPartiel = $livraisonPartiel;

        return $this;
    }

    
    public function getIdMateriel()
    {
        return $this->idMateriel;
    }

    
    public function setIdMateriel($idMateriel): self
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    
    public function getMailDemandeur()
    {
        return $this->mailDemandeur;
    }

    
    public function setMailDemandeur($mailDemandeur): self
    {
        $this->mailDemandeur = $mailDemandeur;

        return $this;
    }


    public function getDateDemande()
    {
        return $this->dateDemande;
    }


    public function setDateDemande($dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    
    public function getHeureDemande()
    {
        return $this->heureDemande;
    }

   
    public function setHeureDemande($heureDemande): self
    {
        $this->heureDemande = $heureDemande;

        return $this;
    }

    
    public function getDateCloture()
    {
        return $this->dateCloture;
    }

   
    public function setDateCloture($dateCloture): self
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

   
    public function getHeureCloture()
    {
        return $this->heureCloture;
    }

   
    public function setHeureCloture($heureCloture): self
    {
        $this->heureCloture = $heureCloture;

        return $this;
    }

    
    public function getPieceJoint03()
    {
        return $this->pieceJoint03;
    }

    
    public function setPieceJoint03($pieceJoint03): self
    {
        $this->pieceJoint03 = $pieceJoint03;

        return $this;
    }

    
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

   
    public function setPieceJoint01($pieceJoint01): self
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }

   
    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    
    public function setPieceJoint02($pieceJoint02): self
    {
        $this->pieceJoint02 = $pieceJoint02;

        return $this;
    }

    
    public function getUtilisateurDemandeur()
    {
        return $this->utilisateurDemandeur;
    }

   
    public function setUtilisateurDemandeur($utilisateurDemandeur): self
    {
        $this->utilisateurDemandeur = $utilisateurDemandeur;

        return $this;
    }


    public function getObservations()
    {
        return $this->observations;
    }

  
    public function setObservations($observations): self
    {
        $this->observations = $observations;

        return $this;
    }


    public function getIdStatutDemande()
    {
        return $this->idStatutDemande;
    }

   
    public function setIdStatutDemande($idStatutDemande): self
    {
        $this->idStatutDemande = $idStatutDemande;

        return $this;
    }


    public function getDateValidation()
    {
        return $this->dateValidation;
    }


    public function setDateValidation($dateValidation): self
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }


    public function getHeureValidation()
    {
        return $this->heureValidation;
    }


    public function setHeureValidation($heureValidation): self
    {
        $this->heureValidation = $heureValidation;

        return $this;
    }


    public function getNumeroClient()
    {
        return $this->numeroClient;
    }

   
    public function setNumeroClient($numeroClient): self
    {
        $this->numeroClient = $numeroClient;

        return $this;
    }


    public function getLibelleClient()
    {
        return $this->libelleClient;
    }

    public function setLibelleClient($libelleClient): self
    {
        $this->libelleClient = $libelleClient;

        return $this;
    }


    public function getDateFinSouhaite()
    {
        return $this->dateFinSouhaite;
    }


    public function setDateFinSouhaite($dateFinSouhaite): self
    {
        $this->dateFinSouhaite = $dateFinSouhaite;

        return $this;
    }


    public function getNumeroOR()
    {
        return $this->numeroOR;
    }


    public function setNumeroOR($numeroOR): self
    {
        $this->numeroOR = $numeroOR;

        return $this;
    }


    public function getObservationDirectionTechnique()
    {
        return $this->observationDirectionTechnique;
    }

    
    public function setObservationDirectionTechnique($observationDirectionTechnique): self
    {
        $this->observationDirectionTechnique = $observationDirectionTechnique;

        return $this;
    }


    public function getObservationDevis()
    {
        return $this->observationDevis;
    }


    public function setObservationDevis($observationDevis): self
    {
        $this->observationDevis = $observationDevis;

        return $this;
    }


    public function getNumeroDevisRattache()
    {
        return $this->numeroDevisRattache;
    }


    public function setNumeroDevisRattache($numeroDevisRattache): self
    {
        $this->numeroDevisRattache = $numeroDevisRattache;

        return $this;
    }


    public function getDateSoumissionDevis()
    {
        return $this->dateSoumissionDevis;
    }


    public function setDateSoumissionDevis($dateSoumissionDevis): self
    {
        $this->dateSoumissionDevis = $dateSoumissionDevis;

        return $this;
    }


    public function getDevisValide()
    {
        return $this->devisValide;
    }


    public function setDevisValide($devisValide): self
    {
        $this->devisValide = $devisValide;

        return $this;
    }

 
    public function getDateValidationDevis()
    {
        return $this->dateValidationDevis;
    }

    
    public function setDateValidationDevis($dateValidationDevis): self
    {
        $this->dateValidationDevis = $dateValidationDevis;

        return $this;
    }


    public function getIdServiceIntervenant()
    {
        return $this->idServiceIntervenant;
    }

   
    public function setIdServiceIntervenant($idServiceIntervenant): self
    {
        $this->idServiceIntervenant = $idServiceIntervenant;

        return $this;
    }


    public function getDateDevisFinProbable()
    {
        return $this->dateDevisFinProbable;
    }


    public function setDateDevisFinProbable($dateDevisFinProbable): self
    {
        $this->dateDevisFinProbable = $dateDevisFinProbable;

        return $this;
    }


    public function getDateFinEstimationTravaux()
    {
        return $this->dateFinEstimationTravaux;
    }


    public function setDateFinEstimationTravaux($dateFinEstimationTravaux): self
    {
        $this->dateFinEstimationTravaux = $dateFinEstimationTravaux;

        return $this;
    }


    public function getCodeSection()
    {
        return $this->codeSection;
    }


    public function setCodeSection($codeSection): self
    {
        $this->codeSection = $codeSection;

        return $this;
    }


    public function getMasAte()
    {
        return $this->masAte;
    }


    public function setMasAte($masAte): self
    {
        $this->masAte = $masAte;

        return $this;
    }


    public function getCodeAte()
    {
        return $this->codeAte;
    }


    public function setCodeAte($codeAte): self
    {
        $this->codeAte = $codeAte;

        return $this;
    }


    public function getSecteur()
    {
        return $this->secteur;
    }

    public function setSecteur($secteur): self
    {
        $this->secteur = $secteur;

        return $this;
    }


    public function getUtilisateurIntervenant()
    {
        return $this->utilisateurIntervenant;
    }


    public function setUtilisateurIntervenant($utilisateurIntervenant): self
    {
        $this->utilisateurIntervenant = $utilisateurIntervenant;

        return $this;
    }
}
