<?php

namespace App\Entity\cas;

use DateTime;
use App\Entity\admin\Agence;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Repository\cas\CasierRepository;
use App\Entity\Traits\AgenceServiceTrait;
use App\Entity\Traits\AgenceServiceEmetteurTrait;



/**
 * @ORM\Entity(repositoryClass=CasierRepository::class)
 * @ORM\Table(name="Casier_Materiels_Temporaire")
 * @ORM\HasLifecycleCallbacks
 */
class Casier
{
    use AgenceServiceEmetteurTrait;
    use AgenceServiceTrait;
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
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="Nom_Session_Utilisateur", referencedColumnName="id")
     */
    private $nomSessionUtilisateur;

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


    private $idMateriel;

    private $numParc;

    private $numSerie;

    private $constructeur = "";

    private $designation = "";

    private $modele = "";

    private $groupe;

    private $anneeDuModele;

    private $affectation;

    private $dateAchat;

    private $chantier;

    private $client;

    private $motif;

    public function getConstructeur()
    {
        return $this->constructeur;
    }

    public function setConstructeur($constructeur): self
    {
        $this->constructeur = $constructeur;

        return $this;
    }


    public function getDesignation()
    {
        return $this->designation;
    }

    public function setDesignation($designation): self
    {
        $this->designation = $designation;

        return $this;
    }


    public function getModele()
    {
        return $this->modele;
    }


    public function setModele($modele): self
    {
        $this->modele = $modele;

        return $this;
    }


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


    public function getIdMateriel()
    {
        return $this->idMateriel;
    }


    public function setIdMateriel($idMateriel): self
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }


    public function getNumParc()
    {
        return $this->numParc;
    }


    public function setNumParc($numParc): self
    {
        $this->numParc = $numParc;

        return $this;
    }


    public function getNumSerie()
    {
        return $this->numSerie;
    }


    public function setNumSerie($numSerie): self
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    /**
     * Get the value of groupe
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set the value of groupe
     *
     * @return  self
     */
    public function setGroupe($groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }



    /**
     * Get the value of anneeDuModele
     */
    public function getAnneeDuModele()
    {
        return $this->anneeDuModele;
    }

    /**
     * Set the value of anneeDuModele
     *
     * @return  self
     */
    public function setAnneeDuModele($anneeDuModele)
    {
        $this->anneeDuModele = $anneeDuModele;

        return $this;
    }

    /**
     * Get the value of affectation
     */
    public function getAffectation()
    {
        return $this->affectation;
    }

    /**
     * Set the value of affectation
     *
     * @return  self
     */
    public function setAffectation($affectation)
    {
        $this->affectation = $affectation;

        return $this;
    }

    /**
     * Get the value of dateAchat
     */
    public function getDateAchat()
    {
        return $this->dateAchat;
    }

    /**
     * Set the value of dateAchat
     *
     * @return  self
     */
    public function setDateAchat($dateAchat)
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    /**
     * Get the value of chantier
     */
    public function getChantier()
    {
        return $this->chantier;
    }

    /**
     * Set the value of chantier
     *
     * @return  self
     */
    public function setChantier($chantier)
    {
        $this->chantier = $chantier;

        return $this;
    }

    /**
     * Get the value of client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return  self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of motif
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set the value of motif
     *
     * @return  self
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;

        return $this;
    }
}
