<?php

namespace App\Entity\dit;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Secteur;
use App\Entity\admin\Service;
use App\Entity\admin\Societte;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\StatutDemande;
use App\Repository\dit\DitRepository;
use App\Entity\Traits\QuantiteDitTrait;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\Traits\AgenceServiceTrait;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\Traits\AgenceServiceEmetteurTrait;
use App\Entity\Traits\BilanFinancierMaterielTrait;
use App\Entity\Traits\CaracteristiqueMaterielTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=DitRepository::class)
 * @ORM\Table(name="demande_intervention")
 * @ORM\HasLifecycleCallbacks
 */

class DemandeIntervention
{
    use AgenceServiceEmetteurTrait;
    use AgenceServiceTrait;
    use CaracteristiqueMaterielTrait;
    use BilanFinancierMaterielTrait;
    use QuantiteDitTrait;

    public const CODE_APP = 'DIT';
    public const STATUT_A_AFFECTER = 50;
    public const STATUT_AFFECTEE_SECTION = 51;
    public const STATUT_CLOTUREE_ANNULEE = 52;
    public const STATUT_CLOTUREE_VALIDER = 53;
    public const STATUT_CLOTUREE_HORS_DELAI = 54;
    public const STATUT_TERMINER = 57;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit",nullable=true)
     */
    private ?string $numeroDemandeIntervention = null;

    /**
     * @ORM\ManyToOne(targetEntity=WorTypeDocument::class, inversedBy="demandeInterventions")
     * @ORM\JoinColumn(name="type_document", referencedColumnName="id")
     */
    private  $typeDocument = null; //relation avec la table wor_type_document

    /**
     * @ORM\Column(type="string", length=3, name="code_societe",nullable=true)
     */
    private  $codeSociete = null;

    /**
     * @ORM\ManyToOne(targetEntity=Societte::class, inversedBy="demandeInterventions")
     * @ORM\JoinColumn(name="societe", referencedColumnName="id")
     */
    private  $societe = null; // relation avec la table societe

    /**
     * @ORM\Column(type="string", length=30, name="type_reparation",nullable=true)
     */
    private  $typeReparation = null;

    /**
     * @ORM\Column(type="string", length=30, name="reparation_realise",nullable=true)
     * @Groups("intervention")
     */
    private ?string $reparationRealise = null;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieATEAPP::class, inversedBy="DemandeIntervention")
     * @ORM\JoinColumn(name="categorie_demande", referencedColumnName="id")
     * @Groups("intervention")
     */
    private ?CategorieAteApp $categorieDemande = null; //relation avec la table categorie_ate_app

    /**
     * @ORM\Column(type="string", length=140, name="internet_externe",nullable=true)
     * @Groups("intervention")
     */
    private ?string $internetExterne = null;

    /**
     * @ORM\Column(type="string", length=5, name="agence_service_debiteur",nullable=true)
     * @Groups("intervention")
     */
    private ?string $agenceServiceDebiteur = null;

    /**
     * @ORM\Column(type="string", length=5, name="agence_service_emmeteur",nullable=true)
     * @Groups("intervention")
     */
    private ?string $agenceServiceEmetteur = null;

    /**
     * @ORM\Column(type="string", length=100, name="nom_client",nullable=true)
     * @Groups("intervention")
     */
    private ?string $nomClient = null;

    /**
     * @ORM\Column(type="string", length=10, name="numero_telephone",nullable=true)
     * @Groups("intervention")
     */
    private ?string $numeroTel = null;

    /**
     * @ORM\Column(type="string", length=100, name="mail_client",nullable=true)
     * @Groups("intervention")
     */
    private ?string $mailClient = null;

    /**
     * @ORM\Column(type="datetime",  name="date_or",nullable=true)
     * @Groups("intervention")
     */
    private ?DateTime $dateOr = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_or",nullable=true)
     * @Groups("intervention")
     */
    private ?string $heureOR = null;

    /**
     * @ORM\Column(type="datetime",  name="date_prevue_travaux",nullable=true)
     * @Groups("intervention")
     */
    private ?DateTime $datePrevueTravaux = null;

    /**
     * @ORM\Column(type="string", length=3, name="demande_devis",nullable=true)
     * @Groups("intervention")
     */
    private ?string $demandeDevis = null;

    /**
     * @ORM\ManyToOne(targetEntity=WorNiveauUrgence::class, inversedBy="DemandeInterventions")
     * @ORM\JoinColumn(name="id_niveau_urgence", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $idNiveauUrgence = null;

    /**
     * @ORM\Column(type="string", length=3, name="avis_recouvrement",nullable=true)
     * @Groups("intervention")
     */
    private ?string $avisRecouvrement = null;

    /**
     * @ORM\Column(type="string", length=3, name="client_sous_contrat",nullable=true)
     * @Groups("intervention")
     */
    private ?string $clientSousContrat = null;

    /**
     * @ORM\Column(type="string", length=100, name="objet_demande",nullable=true)
     * @Groups("intervention")
     */
    private ?string $objetDemande = null;

    /**
     * @ORM\Column(type="string", length=5000, name="detail_demande",nullable=true)
     * 
     * @Groups("intervention")
     */
    private ?string $detailDemande = null;

    /**
     * @ORM\Column(type="string", length=3, name="livraison_partiel",nullable=true)
     * @Groups("intervention")
     */
    private ?string $livraisonPartiel = null;

    /**
     * @ORM\Column(type="integer", name="ID_Materiel", nullable=true)
     * @Groups("intervention")
     */
    private ?int $idMateriel = null;

    /**
     * @ORM\Column(type="string", length=100, name="mail_demandeur",nullable=true)
     * @Groups("intervention")
     */
    private ?string $mailDemandeur = null;

    /**
     * @ORM\Column(type="datetime",  name="date_demande", nullable=true)
     * @Groups("intervention")
     */
    private ?datetime $dateDemande = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_demande", nullable=true)
     * @Groups("intervention")
     *
     * @var string|null
     */
    private ?string $heureDemande = null;

    /**
     * @ORM\Column(type="datetime", name="date_cloture")
     * @Groups("intervention")
     *
     * @var DateTime|null
     */
    private ?DateTime $dateCloture = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_cloture",nullable=true)
     * @Groups("intervention")
     */
    private ?string $heureCloture = null;

    /**
     * @ORM\Column(type="string", length=200, name="piece_joint",nullable=true)
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "image/jpeg", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
     *     mimeTypesMessage="Please upload a valid PDF, JPEG, XLSX, or DOCX file."
     * )
     * @Groups("intervention")
     */
    private ?string $pieceJoint03 = null;

    /**
     * @ORM\Column(type="string", length=200, name="piece_joint1",nullable=true)
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "image/jpeg", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
     *     mimeTypesMessage="Please upload a valid PDF, JPEG, XLSX, or DOCX file."
     * )
     * @Groups("intervention")
     */
    private ?string $pieceJoint01 = null;

    /**
     * @ORM\Column(type="string", length=200, name="piece_joint2", nullable=true)
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"application/pdf", "image/jpeg", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
     *     mimeTypesMessage="Please upload a valid PDF, JPEG, XLSX, or DOCX file."
     * )
     * @Groups("intervention")
     */
    private ?string $pieceJoint02 = null;

    /**
     * @ORM\Column(type="string", length=50, name="utilisateur_demandeur", nullable=true)
     * @Groups("intervention")
     */
    private ?string $utilisateurDemandeur = null;

    /**
     * @ORM\Column(type="string", length=3000, name="observations", nullable=true)
     * @Groups("intervention")
     */
    private ?string $observations = null;

    /**
     * @ORM\ManyToOne(targetEntity=StatutDemande::class, inversedBy="DemandeIntervention")
     * @ORM\JoinColumn(name="id_statut_demande", referencedColumnName="ID_Statut_Demande")
     * @Groups("intervention")
     */
    private $idStatutDemande = null;

    /**
     * @ORM\Column(type="datetime",  name="date_validation",nullable=true)
     * @Groups("intervention")
     */
    private ?datetime $dateValidation = null;

    /**
     * @ORM\Column(type="string", length=5, name="heure_validation",nullable=true)
     * @Groups("intervention")
     */
    private ?string $heureValidation = null;

    /**
     * @ORM\Column(type="string", length=15, name="numero_client",nullable=true)
     * @Groups("intervention")
     */
    private ?string $numeroClient = null;

    /**
     * @ORM\Column(type="string", length=50, name="libelle_client",nullable=true)
     * @Groups("intervention")
     */
    private ?string $libelleClient = null;

    /**
     * @ORM\Column(type="datetime",  name="date_fin_souhaite",nullable=true)
     * @Groups("intervention")
     */
    private ?datetime $dateFinSouhaite = null;

    /**
     * @ORM\Column(type="string", length=15, name="numero_or",nullable=true)
     * @Groups("intervention")
     */
    private ?string $numeroOR = null;

    /**
     * @ORM\Column(type="string", length=3000, name="observation_direction_technique",nullable=true)
     * @Groups("intervention")
     */
    private ?string $observationDirectionTechnique = null;

    /**
     * @ORM\Column(type="string", length=3000, name="observation_devis",nullable=true)
     * @Groups("intervention")
     */
    private ?string $observationDevis = null;

    /**
     * @ORM\Column(type="string", length=200, name="numero_devis_rattache",nullable=true)
     * @Groups("intervention")
     */
    private ?string $numeroDevisRattache = null;

    /**
     * @ORM\Column(type="datetime",  name="date_soumission_devis",nullable=true)
     * @Groups("intervention")
     */
    private ?datetime $dateSoumissionDevis = null;

    /**
     * @ORM\Column(type="datetime", name="date_devis_rattache", nullable=true)
     * @Groups("intervention")
     *
     * @var datetime|null
     */
    private ?datetime $dateDevisRattache = null;

    /**
     * @ORM\Column(type="string", length=3, name="statut_devis",nullable=true)
     * @Groups("intervention")
     */
    private ?string $statutDevis = null;

    /**
     * @ORM\Column(type="datetime", name="date_validation_devis", nullable=true)
     * @Groups("intervention")
     *
     * @var datetime|null
     */
    private ?datetime $dateValidationDevis = null;

    /**
     * @ORM\Column(type="string", length=3, name="id_service_intervenant", nullable=true)
     * @Groups("intervention")
     *
     * @var string|null
     */
    private ?string $idServiceIntervenant = null;

    /**
     * @ORM\Column(type="datetime",  name="date_devis_fin_probable",nullable=true)
     * @Groups("intervention")
     */
    private ?DateTime $dateDevisFinProbable = null;

    /**
     * @ORM\Column(type="datetime", name="date_fin_estimation_travaux",nullable=true)
     * @Groups("intervention")
     */
    private ?datetime $dateFinEstimationTravaux = null;

    /**
     * @ORM\Column(type="string", length=3, name="code_section",nullable=true)
     * @Groups("intervention")
     */
    private ?string $codeSection = null;

    /**
     * @ORM\Column(type="string", length=3, name="mas_ate",nullable=true)
     * @Groups("intervention")
     */
    private ?string $masAte = null;

    /**
     * @ORM\Column(type="string", length=6, name="code_ate",nullable=true)
     */
    private ?string $codeAte = null;

    /**
     * @ORM\ManyToOne(targetEntity=Secteur::class, inversedBy="demandeInterventions")
     * @ORM\JoinColumn(name="secteur", referencedColumnName="id")
     * @Groups("intervention")
     */
    private $secteur = null;

    /**
     * @ORM\Column(type="string", length=50, name="utilisateur_intervenant",nullable=true)
     * @Groups("intervention")
     */
    private ?string $utilisateurIntervenant = null;

    /**
     * @ORM\Column(type="string", length=255, name="section_affectee")
     *
     * @var string|null
     */
    private ?string $sectionAffectee = null;

    /**
     * @ORM\Column(type="string", length=255, name="statut_or")
     *
     * @var string|null
     */
    private ?string $statutOr = null;


    /**
     * @ORM\Column(type="string", length=255, name="statut_commande")
     *
     * @var string|null
     */
    private ?string $statutCommande = null;

    /**
     * @ORM\Column(type="date", name="date_validation_or", nullable=true)
     *
     * @var \DateTime|null
     */
    private ?\DateTime $dateValidationOr = null;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="ditAgenceEmetteur")
     * @ORM\JoinColumn(name="agence_emetteur_id", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $agenceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="ditServiceEmetteur")
     * @ORM\JoinColumn(name="service_emetteur_id", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $serviceEmetteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="ditAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $agenceDebiteurId;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="ditServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     * @Groups("intervention")
     */
    private  $serviceDebiteurId;

    /**
     * @ORM\Column(type="string", length=255, name="section_support_1")
     */
    private $sectionSupport1;

    /**
     * @ORM\Column(type="string", length=255, name="section_support_2")
     */
    private $sectionSupport2;

    /**
     * @ORM\Column(type="string", length=255, name="section_support_3")
     */
    private $sectionSupport3;

    /**
     * @ORM\Column(type="string", length=255, name="etat_facturation")
     */
    private $etatFacturation;

    /**
     * @ORM\Column(type="string", length=255, name="ri")
     */
    private $ri;

    /**
     * @ORM\Column(type="integer")
     */
    private $migration;

    private $nbrPj;

    private $quatreStatutOr;

    private $estOrEqDit;

    private bool $estOrASoumi = false;

    /**
     * @ORM\Column(type="integer", name="num_migr")
     *
     * @var integer
     */
    private int $numMigration;

    /**
     * @ORM\Column(type="boolean", name="a_annuler")
     */
    private $aAnnuler = false;

    /**
     * @ORM\Column(type="datetime", name="date_annulation")
     */
    private $dateAnnulation;

    private $dateSoumissionOR;

    private $montantTotalOR;

    private bool $estAnnulable = false;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit_avoir")
     */
    private ?string $numeroDemandeDitAvoit = null;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit_refacturation")
     */
    private ?string $numeroDemandeDitRefacturation = null;

    /**
     * @ORM\Column(type="boolean", name="dit_avoir")
     */
    private bool $estDitAvoir = false;

    /**
     * @ORM\Column(type="boolean", name="dit_refacturation")
     */
    private bool $estDitRefacturation = false;

    /**
     * @ORM\Column(type="boolean", name="ate_pol_tana")
     */
    private bool $estAtePolTana = false;

    /** ===================================================================================================================
     * 
     * GETTER and SETTER
     * 
     *===============================================================================================================*/

    public function getId()
    {
        return $this->id;
    }


    public function getNumeroDemandeIntervention(): ?string
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

    public function getSociete()
    {
        return $this->societe;
    }


    public function setSociete($societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    public function getTypeReparation(): ?string
    {
        return $this->typeReparation;
    }

    public function setTypeReparation(string $typeReparation): self
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

    /**
     * Get the value of mailClient
     */
    public function getMailClient()
    {
        return $this->mailClient;
    }

    /**
     * Set the value of mailClient
     *
     * @return  self
     */
    public function setMailClient($mailClient)
    {
        $this->mailClient = $mailClient;

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


    public function getStatutDevis()
    {
        return $this->statutDevis;
    }


    public function setStatutDevis($statutDevis): self
    {
        $this->statutDevis = $statutDevis;

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

    public function getSectionAffectee()
    {
        return $this->sectionAffectee;
    }

    public function setSectionAffectee($sectionAffectee): self
    {
        $this->sectionAffectee = $sectionAffectee;
        return $this;
    }

    public function getStatutOr()
    {
        return $this->statutOr;
    }

    public function setStatutOr($statutOr): self
    {
        $this->statutOr = $statutOr;
        return $this;
    }

    public function getStatutCommande()
    {
        return $this->statutCommande;
    }

    public function setStatutCommande($statutCommande): self
    {
        $this->statutCommande = $statutCommande;
        return $this;
    }


    public function getDateValidationOr()
    {
        return $this->dateValidationOr;
    }


    public function setDateValidationOr(?\DateTime $dateValidationOr): self
    {
        $this->dateValidationOr = $dateValidationOr;

        return $this;
    }


    public function getAgenceEmetteurId()
    {
        return $this->agenceEmetteurId;
    }


    public function setAgenceEmetteurId($agenceEmetteurId): self
    {
        $this->agenceEmetteurId = $agenceEmetteurId;

        return $this;
    }


    public function getServiceEmetteurId()
    {
        return $this->serviceEmetteurId;
    }


    public function setServiceEmetteurId($serviceEmetteurId): self
    {
        $this->serviceEmetteurId = $serviceEmetteurId;

        return $this;
    }


    public function getAgenceDebiteurId()
    {
        return $this->agenceDebiteurId;
    }


    public function setAgenceDebiteurId($agenceDebiteurId): self
    {
        $this->agenceDebiteurId = $agenceDebiteurId;

        return $this;
    }


    public function getServiceDebiteurId()
    {
        return $this->serviceDebiteurId;
    }


    public function setServiceDebiteurId($serviceDebiteurId): self
    {
        $this->serviceDebiteurId = $serviceDebiteurId;

        return $this;
    }



    /**
     * Get the value of sectionSupport1
     */
    public function getSectionSupport1()
    {
        return $this->sectionSupport1;
    }

    /**
     * Set the value of sectionSupport1
     *
     * @return  self
     */
    public function setSectionSupport1($sectionSupport1)
    {
        $this->sectionSupport1 = $sectionSupport1;

        return $this;
    }

    /**
     * Get the value of sectionSupport2
     */
    public function getSectionSupport2()
    {
        return $this->sectionSupport2;
    }

    /**
     * Set the value of sectionSupport2
     *
     * @return  self
     */
    public function setSectionSupport2($sectionSupport2)
    {
        $this->sectionSupport2 = $sectionSupport2;

        return $this;
    }

    /**
     * Get the value of sectionSupport3
     */
    public function getSectionSupport3()
    {
        return $this->sectionSupport3;
    }

    /**
     * Set the value of sectionSupport3
     *
     * @return  self
     */
    public function setSectionSupport3($sectionSupport3)
    {
        $this->sectionSupport3 = $sectionSupport3;

        return $this;
    }

    public function getEtatFacturation()
    {
        return $this->etatFacturation;
    }

    public function setEtatFacturation($etatFacturation)
    {
        $this->etatFacturation = $etatFacturation;
        return $this;
    }

    public function getRi()
    {
        return $this->ri;
    }

    public function setRi($ri)
    {
        $this->ri = $ri;
        return $this;
    }

    /**
     * Get the value of migration
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * Set the value of migration
     *
     * @return  self
     */
    public function setMigration($migration)
    {
        $this->migration = $migration;

        return $this;
    }

    /**
     * Get the value of nbrPj
     */
    public function getNbrPj()
    {
        return $this->nbrPj;
    }

    /**
     * Set the value of nbrPj
     *
     * @return  self
     */
    public function setNbrPj($nbrPj)
    {
        $this->nbrPj = $nbrPj;

        return $this;
    }

    /**
     * Get the value of quatreStatutOr
     */
    public function getQuatreStatutOr()
    {
        return $this->quatreStatutOr;
    }

    /**
     * Set the value of quatreStatutOr
     *
     * @return  self
     */
    public function setQuatreStatutOr($quatreStatutOr)
    {
        $this->quatreStatutOr = $quatreStatutOr;

        return $this;
    }

    public function getEstOrEqDit()
    {
        return $this->estOrEqDit;
    }

    public function setEstOrEqDit($estOrEqDit)
    {
        $this->estOrEqDit = $estOrEqDit;
        return $this;
    }



    /**
     * Get the value of estOrASoumi
     */
    public function getEstOrASoumi()
    {
        return $this->estOrASoumi;
    }

    /**
     * Set the value of estOrASoumi
     *
     * @return  self
     */
    public function setEstOrASoumi($estOrASoumi)
    {
        $this->estOrASoumi = $estOrASoumi;

        return $this;
    }

    /**
     * Get the value of numMigration
     *
     * @return  integer
     */
    public function getNumMigration()
    {
        return $this->numMigration;
    }

    /**
     * Set the value of numMigration
     *
     * @param  integer  $numMigration
     *
     * @return  self
     */
    public function setNumMigration($numMigration)
    {
        $this->numMigration = $numMigration;

        return $this;
    }

    /**
     * Get the value of aAnnuler
     */
    public function getAAnnuler()
    {
        return $this->aAnnuler;
    }

    /**
     * Set the value of aAnnuler
     *
     * @return  self
     */
    public function setAAnnuler($aAnnuler)
    {
        $this->aAnnuler = $aAnnuler;

        return $this;
    }

    /**
     * Get the value of dateAnnulation
     */
    public function getDateAnnulation()
    {
        return $this->dateAnnulation;
    }

    /**
     * Set the value of dateAnnulation
     *
     * @return  self
     */
    public function setDateAnnulation($dateAnnulation)
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }

    /**
     * Get the value of dateSoumissionOR
     */
    public function getDateSoumissionOR()
    {
        return $this->dateSoumissionOR;
    }

    /**
     * Set the value of dateSoumissionOR
     *
     * @return  self
     */
    public function setDateSoumissionOR($dateSoumissionOR)
    {
        $this->dateSoumissionOR = $dateSoumissionOR;

        return $this;
    }

    /**
     * Get the value of montantTotalOR
     */
    public function getMontantTotalOR()
    {
        return $this->montantTotalOR;
    }

    /**
     * Set the value of montantTotalOR
     *
     * @return  self
     */
    public function setMontantTotalOR($montantTotalOR)
    {
        $this->montantTotalOR = $montantTotalOR;

        return $this;
    }

    /**
     * Get the value of estAnnulable
     */
    public function getEstAnnulable()
    {
        return $this->estAnnulable;
    }

    /**
     * Set the value of estAnnulable
     *
     * @return  self
     */
    public function setEstAnnulable($estAnnulable)
    {
        $this->estAnnulable = $estAnnulable;

        return $this;
    }

    /**
     * Get the value of numeroDemandeDitAvoit
     */
    public function getNumeroDemandeDitAvoit()
    {
        return $this->numeroDemandeDitAvoit;
    }

    /**
     * Set the value of numeroDemandeDitAvoit
     *
     * @return  self
     */
    public function setNumeroDemandeDitAvoit($numeroDemandeDitAvoit)
    {
        $this->numeroDemandeDitAvoit = $numeroDemandeDitAvoit;

        return $this;
    }

    /**
     * Get the value of numeroDemandeDitRefacturation
     */
    public function getNumeroDemandeDitRefacturation()
    {
        return $this->numeroDemandeDitRefacturation;
    }

    /**
     * Set the value of numeroDemandeDitRefacturation
     *
     * @return  self
     */
    public function setNumeroDemandeDitRefacturation($numeroDemandeDitRefacturation)
    {
        $this->numeroDemandeDitRefacturation = $numeroDemandeDitRefacturation;

        return $this;
    }

    /**
     * Get the value of estDitAvoir
     */
    public function getEstDitAvoir()
    {
        return $this->estDitAvoir;
    }

    /**
     * Set the value of estDitAvoir
     *
     * @return  self
     */
    public function setEstDitAvoir($estDitAvoir)
    {
        $this->estDitAvoir = $estDitAvoir;

        return $this;
    }

    /**
     * Get the value of estDitRefacturation
     */
    public function getEstDitRefacturation()
    {
        return $this->estDitRefacturation;
    }

    /**
     * Set the value of estDitRefacturation
     *
     * @return  self
     */
    public function setEstDitRefacturation($estDitRefacturation)
    {
        $this->estDitRefacturation = $estDitRefacturation;

        return $this;
    }

    /**
     * Get the value of estAtePolTana
     */
    public function getEstAtePolTana()
    {
        return $this->estAtePolTana;
    }

    /**
     * Set the value of estAtePolTana
     *
     * @return  self
     */
    public function setEstAtePolTana($estAtePolTana)
    {
        $this->estAtePolTana = $estAtePolTana;

        return $this;
    }
}
