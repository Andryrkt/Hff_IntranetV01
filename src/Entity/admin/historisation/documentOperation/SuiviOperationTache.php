<?php

namespace App\Entity\admin\historisation\documentOperation;

use App\Repository\admin\historisation\documentOperation\SuiviOperationTacheRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tâche de correction regroupant les opérations au résultat autre que OK pour la même raison.
 * Créée / mise à jour par TacheSuiviOperation (insertion DBAL), triée et suivie depuis la page admin "Suivi des opérations".
 *
 * @ORM\Entity(repositoryClass=SuiviOperationTacheRepository::class)
 * @ORM\Table(name="suivi_operation_tache")
 */
class SuiviOperationTache
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", length=40, name="signature") */
    private string $signature = '';

    /** @ORM\Column(type="string", length=50, name="type_operation") */
    private string $typeOperation = '';

    /** @ORM\Column(type="string", length=100, name="etape") */
    private string $etape = '';

    /** @ORM\Column(type="string", length=20, name="statut_operation") */
    private string $statutOperation = '';

    /** @ORM\Column(type="string", length=500, name="message_type", nullable=true) */
    private ?string $messageType = null;

    /** @ORM\Column(type="string", length=1000, name="dernier_message", nullable=true) */
    private ?string $dernierMessage = null;

    /** @ORM\Column(type="integer", name="nb_occurrences") */
    private int $nbOccurrences = 1;

    /** @ORM\Column(type="datetime", name="premiere_occurrence") */
    private ?\DateTimeInterface $premiereOccurrence = null;

    /** @ORM\Column(type="datetime", name="derniere_occurrence") */
    private ?\DateTimeInterface $derniereOccurrence = null;

    /** @ORM\Column(type="string", length=36, name="dernier_id_suivi") */
    private string $dernierIdSuivi = '';

    /** @ORM\Column(type="string", length=50, name="dernier_numero_document", nullable=true) */
    private ?string $dernierNumeroDocument = null;

    /** @ORM\Column(type="string", length=20, name="statut") */
    private string $statut = 'A_TRIER';

    /** @ORM\Column(type="string", length=10, name="effort", nullable=true) */
    private ?string $effort = null;

    /** @ORM\Column(type="string", length=10, name="priorite") */
    private string $priorite = 'NORMALE';

    /** @ORM\Column(type="boolean", name="est_regression") */
    private bool $estRegression = false;

    /** @ORM\Column(type="string", length=100, name="assigne_a", nullable=true) */
    private ?string $assigneA = null;

    /** @ORM\Column(type="date", name="echeance", nullable=true) */
    private ?\DateTimeInterface $echeance = null;

    /** @ORM\Column(type="text", name="cause", nullable=true) */
    private ?string $cause = null;

    /** @ORM\Column(type="string", length=500, name="correction", nullable=true) */
    private ?string $correction = null;

    /** @ORM\Column(type="datetime", name="date_resolution", nullable=true) */
    private ?\DateTimeInterface $dateResolution = null;

    /** @ORM\Column(type="string", length=100, name="resolu_par", nullable=true) */
    private ?string $resoluPar = null;

    /** @ORM\Column(type="datetime", name="date_modification", nullable=true) */
    private ?\DateTimeInterface $dateModification = null;

    /** @ORM\Column(type="string", length=100, name="modifie_par", nullable=true) */
    private ?string $modifiePar = null;

    public function getId() { return $this->id; }
    public function getSignature(): string { return $this->signature; }
    public function getTypeOperation(): string { return $this->typeOperation; }
    public function getEtape(): string { return $this->etape; }
    public function getStatutOperation(): string { return $this->statutOperation; }
    public function getMessageType(): ?string { return $this->messageType; }
    public function getDernierMessage(): ?string { return $this->dernierMessage; }
    public function getNbOccurrences(): int { return $this->nbOccurrences; }
    public function getPremiereOccurrence(): ?\DateTimeInterface { return $this->premiereOccurrence; }
    public function getDerniereOccurrence(): ?\DateTimeInterface { return $this->derniereOccurrence; }
    public function getDernierIdSuivi(): string { return $this->dernierIdSuivi; }
    public function getDernierNumeroDocument(): ?string { return $this->dernierNumeroDocument; }
    public function getStatut(): string { return $this->statut; }
    public function getEffort(): ?string { return $this->effort; }
    public function getPriorite(): string { return $this->priorite; }
    public function getEstRegression(): bool { return $this->estRegression; }
    public function getAssigneA(): ?string { return $this->assigneA; }
    public function getEcheance(): ?\DateTimeInterface { return $this->echeance; }
    public function getCause(): ?string { return $this->cause; }
    public function getCorrection(): ?string { return $this->correction; }
    public function getDateResolution(): ?\DateTimeInterface { return $this->dateResolution; }
    public function getResoluPar(): ?string { return $this->resoluPar; }
    public function getDateModification(): ?\DateTimeInterface { return $this->dateModification; }
    public function getModifiePar(): ?string { return $this->modifiePar; }

    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }
    public function setEffort(?string $effort): self { $this->effort = $effort; return $this; }
    public function setPriorite(string $priorite): self { $this->priorite = $priorite; return $this; }
    public function setEstRegression(bool $estRegression): self { $this->estRegression = $estRegression; return $this; }
    public function setAssigneA(?string $assigneA): self { $this->assigneA = $assigneA; return $this; }
    public function setEcheance(?\DateTimeInterface $echeance): self { $this->echeance = $echeance; return $this; }
    public function setCause(?string $cause): self { $this->cause = $cause; return $this; }
    public function setCorrection(?string $correction): self { $this->correction = $correction; return $this; }
    public function setDateResolution(?\DateTimeInterface $dateResolution): self { $this->dateResolution = $dateResolution; return $this; }
    public function setResoluPar(?string $resoluPar): self { $this->resoluPar = $resoluPar; return $this; }
    public function setDateModification(?\DateTimeInterface $dateModification): self { $this->dateModification = $dateModification; return $this; }
    public function setModifiePar(?string $modifiePar): self { $this->modifiePar = $modifiePar; return $this; }
}
