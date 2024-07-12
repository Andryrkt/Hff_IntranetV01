<?php

namespace App\Entity;

use App\Traits\DateTrait;
use App\Entity\SousTypeDocument;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CatgRepository;
use Doctrine\Common\Collections\ArrayCollection;


  /**
 *   @ORM\Table(name="Catg")
 * @ORM\Entity(repositoryClass=CatgRepository::class)
 * @ORM\HasLifecycleCallbacks
 */

class Catg
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
     * @ORM\ManyToOne(targetEntity=SousTypeDocument::class, inversedBy="catg")
     * @ORM\JoinColumn(name="sous_type_document_id", referencedColumnName="ID_Sous_Type_Document")
     */
    private  SousTypeDocument $sousTypeDocument;

    

    

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

    public function getCatg(): ?SousTypeDocument
    {
        return $this->sousTypeDocument;
    }

    
    public function setCatg(?SousTypeDocument $sousTypeDocument): self
    {
        $this->sousTypeDocument = $sousTypeDocument;

        return $this;
    }
   
}