<?php

namespace App\Entity\admin\historisation\documentOperation;

use App\Repository\admin\historisation\documentOperation\HistoriqueOperationEtapeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une étape d'une opération suivie (fichiers joints, page de garde, conversion, fusion, DocuWare, base de données...).
 * Alimentée par SuiviOperationService (insertion DBAL), lue par la page admin "Suivi des opérations".
 *
 * @ORM\Entity(repositoryClass=HistoriqueOperationEtapeRepository::class)
 * @ORM\Table(name="historique_operation_etape")
 */
class HistoriqueOperationEtape
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=36, name="id_suivi")
     */
    private string $idSuivi = '';

    /**
     * @ORM\Column(type="integer", name="id_historique_operation", nullable=true)
     */
    private ?int $idHistoriqueOperation = null;

    /**
     * @ORM\Column(type="string", length=50, name="type_operation")
     */
    private string $typeOperation = '';

    /**
     * @ORM\Column(type="string", length=50, name="numero_document", nullable=true)
     */
    private ?string $numeroDocument = null;

    /**
     * @ORM\Column(type="integer", name="ordre")
     */
    private int $ordre = 0;

    /**
     * @ORM\Column(type="string", length=100, name="etape")
     */
    private string $etape = '';

    /**
     * @ORM\Column(type="string", length=20, name="statut")
     */
    private string $statut = '';

    /**
     * @ORM\Column(type="string", length=1000, name="message", nullable=true)
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="text", name="detail_erreur", nullable=true)
     */
    private ?string $detailErreur = null;

    /**
     * @ORM\Column(type="datetime", name="date_debut")
     */
    private ?\DateTimeInterface $dateDebut = null;

    /**
     * @ORM\Column(type="datetime", name="date_fin", nullable=true)
     */
    private ?\DateTimeInterface $dateFin = null;

    /**
     * @ORM\Column(type="integer", name="duree_ms", nullable=true)
     */
    private ?int $dureeMs = null;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur", nullable=true)
     */
    private ?string $utilisateur = null;

    /**
     * @ORM\Column(type="string", length=500, name="url", nullable=true)
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="boolean", name="est_lu", options={"default": 0})
     */
    private bool $estLu = false;

    /**
     * @ORM\Column(type="string", length=100, name="lu_par", nullable=true)
     */
    private ?string $luPar = null;

    /**
     * @ORM\Column(type="datetime", name="date_lecture", nullable=true)
     */
    private ?\DateTimeInterface $dateLecture = null;

    public function getId()
    {
        return $this->id;
    }

    public function getIdSuivi(): string
    {
        return $this->idSuivi;
    }

    public function getIdHistoriqueOperation(): ?int
    {
        return $this->idHistoriqueOperation;
    }

    public function getTypeOperation(): string
    {
        return $this->typeOperation;
    }

    public function getNumeroDocument(): ?string
    {
        return $this->numeroDocument;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function getEtape(): string
    {
        return $this->etape;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getDetailErreur(): ?string
    {
        return $this->detailErreur;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function getDureeMs(): ?int
    {
        return $this->dureeMs;
    }

    public function getUtilisateur(): ?string
    {
        return $this->utilisateur;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getEstLu(): bool
    {
        return $this->estLu;
    }

    public function getLuPar(): ?string
    {
        return $this->luPar;
    }

    public function getDateLecture(): ?\DateTimeInterface
    {
        return $this->dateLecture;
    }
}
