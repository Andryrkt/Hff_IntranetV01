<?php

namespace App\Entity;

use App\Entity\Rmq;
use App\Entity\Catg;
use App\Entity\Site;
use App\Traits\DateTrait;
use App\Entity\SousTypeDocument;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\IdemniteRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="indemnites")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private $sites;

    /**
     * @ORM\ManyToOne(targetEntity=Rmq::class, inversedBy="indemnites")
     * @ORM\JoinColumn(name="rmq_id", referencedColumnName="id")
     */
    private $rmqs;

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

    
    public function getCatg()
    {
        return $this->categories;
    }

    public function setCatg($categorie): self
    {
        $this->categories = $categorie;
        return $this;
    }

    

   
    public function getSites()
    {
        return $this->sites;
    }

    public function setSites($site)
    {
        $this->sites = $site;

        return $this;
    }
    

    
    public function getRmqs()
    {
        return $this->rmqs;
    }

    public function setRmqs($rmq): self
    {
        $this->rmqs = $rmq;

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
