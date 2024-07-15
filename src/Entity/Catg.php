<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CatgRepository;

/**
 * @ORM\Table(name="Catg")
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
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=SousTypeDocument::class, inversedBy="catg")
     * @ORM\JoinColumn(name="sous_type_document_id", referencedColumnName="ID_Sous_Type_Document")
     */
    private $sousTypeDocument;

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

    public function getSousTypeDocument(): ?SousTypeDocument
    {
        return $this->sousTypeDocument;
    }

    public function setSousTypeDocument(?SousTypeDocument $sousTypeDocument): self
    {
        $this->sousTypeDocument = $sousTypeDocument;
        return $this;
    }
}
