<?php

namespace App\Entity\admin\ddp;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\admin\ddp\DocDemandePaiementRepository;

/**
 * @ORM\Entity(repositoryClass=DocDemandePaiementRepository::class)
 * @ORM\Table(name="document_demande_paiement")
 * @ORM\HasLifecycleCallbacks
 */
class DocDemandePaiement
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_paiement")
     */
    private ?string $numeroDdp;

    private $typeDocumentId;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fichier")
     *
     * @var string|null
     */
    private ?string $nomFichier;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numero
     */ 
    public function getNumeroDdp()
    {
        return $this->numeroDdp;
    }

    /**
     * Set the value of numero
     *
     * @return  self
     */ 
    public function setNumeroDdp($numeroDdp)
    {
        $this->numeroDdp = $numeroDdp;

        return $this;
    }

    /**
     * Get the value of typeDocumentId
     */ 
    public function getTypeDocumentId()
    {
        return $this->typeDocumentId;
    }

    /**
     * Set the value of typeDocumentId
     *
     * @return  self
     */ 
    public function setTypeDocumentId($typeDocumentId)
    {
        $this->typeDocumentId = $typeDocumentId;

        return $this;
    }

    /**
     * Get the value of nomFichier
     *
     * @return  string|null
     */ 
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    /**
     * Set the value of nomFichier
     *
     * @param  string|null  $nomFichier
     *
     * @return  self
     */ 
    public function setNomFichier($nomFichier)
    {
        $this->nomFichier = $nomFichier;

        return $this;
    }
}