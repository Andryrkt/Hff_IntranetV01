<?php

namespace App\Entity\admin\utilisateur;


use Doctrine\ORM\Mapping as ORM;
use App\Repository\admin\utilisateur\AgenceServiceAutoriserRepository;

/**
 * @ORM\Table(name="Agence_service_autorise")
 * @ORM\Entity(repositoryClass=AgenceServiceAutoriserRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class AgenceServiceAutoriser 
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=50)
    
     * @var string
     */
    private string $Session_Utilisateur;

    /**
     * @ORM\Column(type="string", length=5)
     *
     * @var ?string
     */
    private ?string $Code_AgenceService_IRIUM;

    /**
     * @ORM\Column(type="date")
     *
     */
    private $Date_creation;




    public function getId()
    {
        return $this->id;
    }

    public function getSessionUtilisateur(): ?string
    {
        return $this->Session_Utilisateur;
    }

    public function setSessionUtilisateur(string $Session_Utilisateur): self
    {
        $this->Session_Utilisateur = $Session_Utilisateur;

        return $this;
    }


    public function getCodeAgenceServiceIrium(): ?string
    {
        return $this->Code_AgenceService_IRIUM;
    }

    public function setCodeAgenceServiceIrium(string $Code_AgenceService_IRIUM): self
    {
        $this->Code_AgenceService_IRIUM = $Code_AgenceService_IRIUM;

        return $this;
    }

    public function getDate_creation()
    {
        return $this->Date_creation;
    }

    public function setDate_creation($Date_creation): self
    {
        $this->Date_creation = $Date_creation;

        return $this;
    }

   /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->Date_creation = new \DateTime();
    }

}