<?php

namespace App\Entity\admin;

use App\Entity\admin\Personnel;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\utilisateur\AgenceServiceIriumRepository;

/**
 * @ORM\Table(name="Agence_Service_Irium")
 * @ORM\Entity(repositoryClass=AgenceServiceIriumRepository::class)
 */
class AgenceServiceIrium
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length="6", name="id_irium",  nullable=true)
     *
     * @var string
     */
    private ?string $idIrium = null;

    /**
     * @ORM\Column(type="string", length="10", nullable=true)
     *
     * @var string
     */
    private ?string $agence_i100 = null;

    /**
     * @ORM\Column(type="string", length="50")
     *
     * @var string
     */
    private ?string $nom_agence_i100;

    /**
     * @ORM\Column(type="string", length="10", nullable=true)
     *
     * @var string
     */
    private ?string $service_i100 = null;

    /**
     * @ORM\Column(type="string", length="50", nullable=true)
     *
     * @var string
     */
    private ?string $nom_service_i100;

    /**
     * @ORM\Column(type="string", length="10")
     *
     * @var string
     */
    private ?string $agence_ips;

    /**
     * @ORM\Column(type="string", length="50")
     *
     * @var string
     */
    private ?string $service_ips;

    /**
     * @ORM\Column(type="string", length="50")
     *
     * @var string
     */
    private ?string $libelle_service_ips;

    /**
     * @ORM\Column(type="string", length="4")
     *
     * @var string
     */
    private ?string $societe_ios;

    /**
     * @ORM\Column(type="string", length="6", nullable=true)
     *
     * @var string
     */
    private ?string $service_sage_paie = null;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="agenceServiceIrium")
     */
    private $userAgenceService;

    /**
     * @ORM\OneToMany(targetEntity=Personnel::class, mappedBy="agenceServiceIriumId")
     */
    private $personnelId;

    //=============================================================================================

    public function __construct()
    {
        $this->userAgenceService = new ArrayCollection();
        $this->personnelId = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdirium(): ?string
    {
        return $this->idIrium;
    }

    public function setIdirium(?string $id_irium): self
    {
        $this->idIrium = $id_irium;

        return $this;
    }

    public function getAgencei100(): ?string
    {
        return $this->agence_i100;
    }

    public function setAgencei100(?string $agence_i100): self
    {
        $this->agence_i100 = $agence_i100;

        return $this;
    }

    public function getNomagencei100(): ?string
    {
        return $this->nom_agence_i100;
    }

    
    public function setNomagencei100(?string $nom_agence_i100): self
    {
        $this->nom_agence_i100 = $nom_agence_i100;

        return $this;
    }

    public function getServicei100(): ?string
    {
        return $this->service_i100;
    }

    public function setServicei100(?string $service_i100): self
    {
        $this->service_i100 = $service_i100;

        return $this;
    }

    public function getNomservicei100(): ?string
    {
        return $this->nom_service_i100;
    }
 
    public function setNomservicei100(?string $nom_service_i100): self
    {
        $this->nom_service_i100 = $nom_service_i100;

        return $this;
    }

    public function getAgenceips(): ?string
    {
        return $this->agence_ips;
    }

 
    public function setAgenceips(?string $agence_ips): self
    {
        $this->agence_ips = $agence_ips;

        return $this;
    }

    public function getServiceips(): ?string
    {
        return $this->service_ips;
    }

    public function setServiceips(?string $service_ips): self
    {
        $this->service_ips = $service_ips;

        return $this;
    }


    public function getLibelleserviceips(): ?string
    {
        return $this->libelle_service_ips;
    }

 
    public function setLibelleserviceips(?string $libelle_service_ips): self
    {
        $this->libelle_service_ips = $libelle_service_ips;

        return $this;
    }


    public function getSocieteios(): ?string
    {
        return $this->societe_ios;
    }

    public function setSocieteios(?string $societe_ios): self
    {
        $this->societe_ios = $societe_ios;

        return $this;
    }

    public function getServicesagepaie(): ?string
    {
        return $this->service_sage_paie;
    }

  
    public function setService_sage_paie(?string $service_sage_paie): self
    {
        $this->service_sage_paie = $service_sage_paie;

        return $this;
    }

      /**
     * Get the value of demandeInterventions
     */ 
    public function getUserAgenceService()
    {
        return $this->userAgenceService;
    }

    public function addUserAgenceService(User $userAgenceService): self
    {
        if (!$this->userAgenceService->contains($userAgenceService)) {
            $this->userAgenceService[] = $userAgenceService;
            $userAgenceService->setAgenceServiceIrium($this);
        }

        return $this;
    }

    public function removeUserAgenceService(User $userAgenceService): self
    {
        if ($this->userAgenceService->contains($userAgenceService)) {
            $this->userAgenceService->removeElement($userAgenceService);
            if ($userAgenceService->getAgenceServiceIrium() === $this) {
                $userAgenceService->setAgenceServiceIrium(null);
            }
        }
        
        return $this;
    }

    public function setUserAgenceService($userAgenceService)
    {
        $this->userAgenceService = $userAgenceService;

        return $this;
    }


    public function getPersonnelId(): Collection
    {
        return $this->personnelId;
    }

    public function addPersonnelId(Personnel $personnelId): self
    {
        if (!$this->personnelId->contains($personnelId)) {
            $this->personnelId[] = $personnelId;
            $personnelId->setAgenceServiceIriumId($this);
        }

        return $this;
    }

    public function removePersonnelId(Personnel $personnelId): self
    {
        if ($this->personnelId->contains($personnelId)) {
            $this->personnelId->removeElement($personnelId);
            if ($personnelId->getAgenceServiceIriumId() === $this) {
                $personnelId->setAgenceServiceIriumId(null);
            }
        }
        
        return $this;
    }

    public function setPersonnelId($personnelId): self
    {
        $this->personnelId = $personnelId;

        return $this;
    }
}