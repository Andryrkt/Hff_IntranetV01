<?php

namespace App\Entity\admin\dom;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\dom\Indemnite;
use App\Repository\admin\dom\RmqRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


  /**
 *   @ORM\Table(name="rmq")
 * @ORM\Entity(repositoryClass=RmqRepository::class)
 * @ORM\HasLifecycleCallbacks
 */

class Rmq
{
    use DateTrait;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private string $description;

     /**
     * @ORM\OneToMany(targetEntity=Indemnite::class, mappedBy="rmqs")
     */
    private $indemnites;

     public function __construct()
    {
        $this->indemnites = new ArrayCollection();
    }
    
    public function getId(): int
    {
        return $this->id;
    }

   
    public function getDescription(): string
    {
        return $this->description;
    }

   
    public function setDescription(string $description): self
    {
        $this->description = $description;

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
            $indemnite->setRmq($this);
        }
        return $this;
    }

    public function removeIndemnite(Indemnite $indemnite): self
    {
        if ($this->indemnites->contains($indemnite)) {
            $this->indemnites->removeElement($indemnite);
            if ($indemnite->getRmq() === $this) {
                $indemnite->setRmq(null);
            }
        }

        return $this;
    }
}