<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\da\DaArticleReapproRepository;

/**
 * @ORM\Entity(repositoryClass=DaArticleReapproRepository::class)
 * @ORM\Table(name="da_article_reappro")
 * @ORM\HasLifecycleCallbacks
 */
class DaArticleReappro
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, name="art_constp")
     */
    private ?string $artConstp = '';

    /**
     * @ORM\Column(type="string", length=50, name="art_refp")
     */
    private ?string $artRefp = '';

    /**
     * @ORM\Column(type="string", length=3, name="art_desi")
     */
    private ?string $artDesi = '';

    /**
     * @ORM\Column(type="string", length=100, name="qte_validee_appro")
     */
    private ?string $qteValide = '';

    /**
     * @ORM\Column(type="string", length=100, name="art_pu")
     */
    private ?string $artPU = '';

    /**
     * @ORM\Column(type="string", length=3, name="code_agence")
     */
    private ?string $codeAgence = '';

    /**
     * @ORM\Column(type="string", length=3, name="code_service")
     */
    private ?string $codeService = '';

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of artConstp
     */
    public function getArtConstp()
    {
        return $this->artConstp;
    }

    /**
     * Set the value of artConstp
     *
     * @return  self
     */
    public function setArtConstp($artConstp)
    {
        $this->artConstp = $artConstp;

        return $this;
    }

    /**
     * Get the value of artRefp
     */
    public function getArtRefp()
    {
        return $this->artRefp;
    }

    /**
     * Set the value of artRefp
     *
     * @return  self
     */
    public function setArtRefp($artRefp)
    {
        $this->artRefp = $artRefp;

        return $this;
    }

    /**
     * Get the value of artDesi
     */
    public function getArtDesi()
    {
        return $this->artDesi;
    }

    /**
     * Set the value of artDesi
     *
     * @return  self
     */
    public function setArtDesi($artDesi)
    {
        $this->artDesi = $artDesi;

        return $this;
    }

    /**
     * Get the value of qteValide
     */
    public function getQteValide()
    {
        return $this->qteValide;
    }

    /**
     * Set the value of qteValide
     *
     * @return  self
     */
    public function setQteValide($qteValide)
    {
        $this->qteValide = $qteValide;

        return $this;
    }

    /**
     * Get the value of artPU
     */
    public function getArtPU()
    {
        return $this->artPU;
    }

    /**
     * Set the value of artPU
     *
     * @return  self
     */
    public function setArtPU($artPU)
    {
        $this->artPU = $artPU;

        return $this;
    }

    /**
     * Get the value of codeAgence
     */
    public function getCodeAgence()
    {
        return $this->codeAgence;
    }

    /**
     * Set the value of codeAgence
     *
     * @return  self
     */
    public function setCodeAgence($codeAgence)
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    /**
     * Get the value of codeService
     */
    public function getCodeService()
    {
        return $this->codeService;
    }

    /**
     * Set the value of codeService
     *
     * @return  self
     */
    public function setCodeService($codeService)
    {
        $this->codeService = $codeService;

        return $this;
    }
}
