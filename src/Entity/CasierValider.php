<?php



namespace App\Entity;

use DateTime;
use App\Entity\User;
use App\Entity\Agence;
use App\Entity\StatutDemande;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\AgenceServiceTrait;
use App\Repository\CasierRepository;
use App\Traits\AgenceServiceEmetteurTrait;


/**
 * @ORM\Entity(repositoryClass=CasierRepository::class)
 * @ORM\Table(name="Casier_Materiels")
 * @ORM\HasLifecycleCallbacks
 */
class CasierValider
{
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=20, name="Casier")
     *
     * @var string
     */
    private string $casier;


    /**
     * @ORM\Column(type="date", name="Date_Creation")
     */
    private DateTime $dateCreation;

    /**
     * @ORM\Column(type="string", length=15, name="Numero_CAS")
     *
     * @var string
     */
    private string $numeroCas;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="casiers")
     * @ORM\JoinColumn(name="Nom_Session_Utilisateur", referencedColumnName="id")
     */
    private  $nomSessionUtilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="casiers")
     * @ORM\JoinColumn(name="Agence_Rattacher", referencedColumnName="id")
     */
    private $agenceRattacher;

    /**
     * @ORM\ManyToOne(targetEntity=StatutDemande::class, inversedBy="casiers")
     * @ORM\JoinColumn(name="id_statut_demande", referencedColumnName="ID_Statut_Demande")
     */
    private $idStatutDemande = null;

    
 

    public function getId()
    {
        return $this->id;
    }

    
    public function getCasier()
    {
        return $this->casier;
    }

    public function setCasier(string $casier): self
    {
        $this->casier = $casier;

        return $this;
    }


    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    
    public function setDateCreation($dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    
    public function getNumeroCas()
    {
        return $this->numeroCas;
    }

   
    public function setNumeroCas(string $numeroCas): self
    {
        $this->numeroCas = $numeroCas;

        return $this;
    }

   
    public function getNomSessionUtilisateur()
    {
        return $this->nomSessionUtilisateur;
    }

   
    public function setNomSessionUtilisateur($nomSessionUtilisateur): self
    {
        $this->nomSessionUtilisateur = $nomSessionUtilisateur;

        return $this;
    }

   
    public function getAgenceRattacher(): ?Agence
    {
        return $this->agenceRattacher;
    }

   
    public function setAgenceRattacher(?Agence $agence): self
    {
        $this->agenceRattacher = $agence;

        return $this;
    }
 
    public function getIdStatutDemande()
    {
        return $this->idStatutDemande;
    }

    
    public function setIdStatutDemande($idStatutDemande): self
    {
        $this->idStatutDemande = $idStatutDemande;

        return $this;
    }


}
