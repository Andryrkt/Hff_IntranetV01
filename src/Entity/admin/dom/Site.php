<?php

namespace App\Entity\admin\dom;

use App\Entity\dom\Dom;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\mutation\Mutation;
use App\Entity\admin\dom\Indemnite;
use Doctrine\Common\Collections\Collection;
use App\Repository\admin\dom\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;


/**
 *   @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Site
{
    use DateTrait;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="nom_zone")
     *
     * @var string
     */
    private string $nomZone;

    /**
     * @ORM\ManyToMany(targetEntity=Catg::class, mappedBy="sites")
     */
    private Collection $catgs;

    /**
     * @ORM\OneToMany(targetEntity=Indemnite::class, mappedBy="sites")
     */
    private $indemnites;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="siteId")
     */
    private $domSite;


    public function __construct()
    {
        $this->catgs = new ArrayCollection();
        $this->domSite = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNomZone()
    {
        return $this->nomZone;
    }


    public function setNomZone(string $nomZone): self
    {
        $this->nomZone = $nomZone;

        return $this;
    }



    public function getCatgs(): Collection
    {
        return $this->catgs;
    }

    public function addCatg(Catg $catg): self
    {
        if (!$this->catgs->contains($catg)) {
            $this->catgs[] = $catg;
            $catg->addSite($this);
        }
        return $this;
    }

    public function removeCatg(Catg $catg): self
    {
        if ($this->catgs->contains($catg)) {
            $this->catgs->removeElement($catg);
            $catg->removeSite($this);
        }
        return $this;
    }


    /**
     * @return Collection|Indemnite[]
     */
    public function getIndemnites(): Collection
    {
        return $this->indemnites;
    }

    public function addIndemnite(Indemnite $indemnite): self
    {
        if (!$this->indemnites->contains($indemnite)) {
            $this->indemnites[] = $indemnite;
            $indemnite->setSite($this);
        }
        return $this;
    }

    public function removeIndemnite(Indemnite $indemnite): self
    {
        if ($this->indemnites->contains($indemnite)) {
            $this->indemnites->removeElement($indemnite);
            if ($indemnite->getSite() === $this) {
                $indemnite->setSite(null);
            }
        }

        return $this;
    }

    public function getDomSites()
    {
        return $this->domSite;
    }

    public function addDomSite(Dom $domSite): self
    {
        if (!$this->domSite->contains($domSite)) {
            $this->domSite[] = $domSite;
            $domSite->setAgenceDebiteurId($this);
        }

        return $this;
    }

    public function removeDomSite(Dom $domSite): self
    {
        if ($this->domSite->contains($domSite)) {
            $this->domSite->removeElement($domSite);
            if ($domSite->getAgenceDebiteurId() === $this) {
                $domSite->setAgenceDebiteurId(null);
            }
        }

        return $this;
    }

    public function setDomSites($domSite)
    {
        $this->domSite = $domSite;

        return $this;
    }

    public function __toString()
    {
        return $this->nomZone;
    }
}
