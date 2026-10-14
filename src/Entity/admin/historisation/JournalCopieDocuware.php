<?php

namespace App\Entity\admin\historisation;

use App\Repository\admin\historisation\JournalCopieDocuwareRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une ligne par tentative de copie d'un fichier vers DocuWare.
 * Alimentée par JournalCopieDocuwareService (insertion DBAL), lue par la page admin.
 *
 * @ORM\Entity(repositoryClass=JournalCopieDocuwareRepository::class)
 * @ORM\Table(name="journal_copie_docuware")
 */
class JournalCopieDocuware
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", name="date_copie")
     */
    private ?\DateTimeInterface $dateCopie = null;

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur", nullable=true)
     */
    private ?string $utilisateur = null;

    /**
     * @ORM\Column(type="string", length=500, name="url", nullable=true)
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="string", length=500, name="fichier_source")
     */
    private string $fichierSource = '';

    /**
     * @ORM\Column(type="boolean", name="source_existe")
     */
    private bool $sourceExiste = false;

    /**
     * @ORM\Column(type="bigint", name="taille_source", nullable=true)
     */
    private $tailleSource = null;

    /**
     * @ORM\Column(type="string", length=500, name="destination")
     */
    private string $destination = '';

    /**
     * @ORM\Column(type="string", length=150, name="dossier_docuware", nullable=true)
     */
    private ?string $dossierDocuware = null;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fichier", nullable=true)
     */
    private ?string $nomFichier = null;

    /**
     * @ORM\Column(type="boolean", name="succes")
     */
    private bool $succes = false;

    /**
     * @ORM\Column(type="integer", name="nb_tentatives")
     */
    private int $nbTentatives = 1;

    /**
     * @ORM\Column(type="string", length=1000, name="message_erreur", nullable=true)
     */
    private ?string $messageErreur = null;

    /**
     * @ORM\Column(type="boolean", name="est_vu")
     */
    private bool $estVu = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCopie(): ?\DateTimeInterface
    {
        return $this->dateCopie;
    }

    public function getUtilisateur(): ?string
    {
        return $this->utilisateur;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getFichierSource(): string
    {
        return $this->fichierSource;
    }

    public function getSourceExiste(): bool
    {
        return $this->sourceExiste;
    }

    public function getTailleSource(): ?int
    {
        return $this->tailleSource !== null ? (int) $this->tailleSource : null;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function getDossierDocuware(): ?string
    {
        return $this->dossierDocuware;
    }

    public function getNomFichier(): ?string
    {
        return $this->nomFichier;
    }

    public function getSucces(): bool
    {
        return $this->succes;
    }

    public function getNbTentatives(): int
    {
        return $this->nbTentatives;
    }

    public function getMessageErreur(): ?string
    {
        return $this->messageErreur;
    }

    public function getEstVu(): bool
    {
        return $this->estVu;
    }

    public function setEstVu(bool $estVu): self
    {
        $this->estVu = $estVu;
        return $this;
    }
}
