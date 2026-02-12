<?php

namespace App\Entity\admin\utilisateur;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Repository\admin\utilisateur\ApplicationProfilPageRepository;

/**
 * @ORM\Entity(repositoryClass=ApplicationProfilPageRepository::class)
 * @ORM\Table(name="application_profil_page")
 */
class ApplicationProfilPage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=ApplicationProfil::class, inversedBy="liaisonsPage")
     * @ORM\JoinColumn(name="application_profil_id", referencedColumnName="id", nullable=false)
     */
    private ?ApplicationProfil $applicationProfil;

    /**
     * @ORM\ManyToOne(targetEntity=PageHff::class, inversedBy="applicationProfilPages")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=false)
     */
    private ?PageHff $page;

    // -------------------------------------------------------------------------
    //  Permissions
    // -------------------------------------------------------------------------

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $peutVoir = true;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $peutAjouter = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $peutModifier = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $peutSupprimer = false;

    // -------------------------------------------------------------------------
    //  Getters / Setters
    // -------------------------------------------------------------------------

    public function getId(): int
    {
        return $this->id;
    }

    public function getApplicationProfil(): ?ApplicationProfil
    {
        return $this->applicationProfil;
    }

    public function setApplicationProfil(?ApplicationProfil $applicationProfil): self
    {
        $this->applicationProfil = $applicationProfil;
        return $this;
    }

    public function getPage(): ?PageHff
    {
        return $this->page;
    }

    public function setPage(?PageHff $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function isPeutVoir(): bool
    {
        return $this->peutVoir;
    }

    public function setPeutVoir(bool $peutVoir): self
    {
        $this->peutVoir = $peutVoir;
        return $this;
    }

    public function isPeutAjouter(): bool
    {
        return $this->peutAjouter;
    }

    public function setPeutAjouter(bool $peutAjouter): self
    {
        $this->peutAjouter = $peutAjouter;
        return $this;
    }

    public function isPeutModifier(): bool
    {
        return $this->peutModifier;
    }

    public function setPeutModifier(bool $peutModifier): self
    {
        $this->peutModifier = $peutModifier;
        return $this;
    }

    public function isPeutSupprimer(): bool
    {
        return $this->peutSupprimer;
    }

    public function setPeutSupprimer(bool $peutSupprimer): self
    {
        $this->peutSupprimer = $peutSupprimer;
        return $this;
    }

    // -------------------------------------------------------------------------
    //  Helper : retourne toutes les permissions sous forme de tableau
    // -------------------------------------------------------------------------

    public function toArray(): array
    {
        return [
            'peutVoir'      => $this->peutVoir,
            'peutAjouter'   => $this->peutAjouter,
            'peutModifier'  => $this->peutModifier,
            'peutSupprimer' => $this->peutSupprimer
        ];
    }
}
