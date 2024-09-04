<?php

namespace App\Entity\admin\dom;

use App\Entity\admin\dom\Rmq;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\dom\SousTypeDocument;
use App\Repository\admin\dom\IdemniteRepository;


/**
 * @ORM\Table(name="idemnite")
 * @ORM\Entity(repositoryClass=IdemniteRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Indemnite
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity=Catg::class, inversedBy="indemnites")
     * @ORM\JoinColumn(name="catg_id", referencedColumnName="id")
     */
    private $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="indemnites")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity=Rmq::class, inversedBy="indemnites")
     * @ORM\JoinColumn(name="rmq_id", referencedColumnName="id")
     */
    private $rmq;

    /**
     * @ORM\ManyToOne(targetEntity=SousTypeDocument::class, inversedBy="indemnites")
     * @ORM\JoinColumn(name="sousTypeDoc_id", referencedColumnName="ID_Sous_Type_Document")
     */
    private $sousTypeDoc;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMontant(): int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getCategorie(): ?Catg
    {
        return $this->categorie;
    }

    public function setCategorie(?Catg $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;
        return $this;
    }

    public function getRmq(): ?Rmq
    {
        return $this->rmq;
    }

    public function setRmq(?Rmq $rmq): self
    {
        $this->rmq = $rmq;
        return $this;
    }

    public function getSousTypeDoc(): ?SousTypeDocument
    {
        return $this->sousTypeDoc;
    }

    public function setSousTypeDoc(?SousTypeDocument $sousTypeDoc): self
    {
        $this->sousTypeDoc = $sousTypeDoc;
        return $this;
    }
}
