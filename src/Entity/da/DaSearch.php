<?php

namespace App\Entity\da;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\dit\WorNiveauUrgence;

class DaSearch
{
    private ?string $numDit = null;
    private ?string $numDa = null;
    private ?string $demandeur = null;
    private ?string $statutDA = null;
    private ?string $statutOR = null;
    private ?string $statutBC = null;
    private ?string $sortNbJours = null;
    private ?string $idMateriel = null;
    private ?string $typeAchat = null;

    private ?WorNiveauUrgence $niveauUrgence = null;

    private ?DateTime $dateDebutCreation = null;
    private ?DateTime $dateFinCreation = null;
    private ?DateTime $dateDebutfinSouhaite = null;
    private ?DateTime $dateFinFinSouhaite = null;

    private ?Agence $agenceEmetteur = null;
    private ?Service $serviceEmetteur = null;

    private ?Agence $agenceDebiteur = null;
    private ?Service $serviceDebiteur = null;

    /**
     * Get the value of numDit
     */
    public function getNumDit()
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     *
     * @return  self
     */
    public function setNumDit($numDit)
    {
        $this->numDit = $numDit;

        return $this;
    }

    /**
     * Get the value of numDa
     */
    public function getNumDa()
    {
        return $this->numDa;
    }

    /**
     * Set the value of numDa
     *
     * @return  self
     */
    public function setNumDa($numDa)
    {
        $this->numDa = $numDa;

        return $this;
    }

    /**
     * Get the value of demandeur
     */
    public function getDemandeur()
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     *
     * @return  self
     */
    public function setDemandeur($demandeur)
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    /**
     * Get the value of statutDA
     */
    public function getStatutDA()
    {
        return $this->statutDA;
    }

    /**
     * Set the value of statutDA
     *
     * @return  self
     */
    public function setStatutDA($statutDA)
    {
        $this->statutDA = $statutDA;

        return $this;
    }

    /**
     * Get the value of statutOR
     */
    public function getStatutOR()
    {
        return $this->statutOR;
    }

    /**
     * Set the value of statutOR
     *
     * @return  self
     */
    public function setStatutOR($statutOR)
    {
        $this->statutOR = $statutOR;

        return $this;
    }

    /**
     * Get the value of statutBC
     */
    public function getStatutBC()
    {
        return $this->statutBC;
    }

    /**
     * Set the value of statutBC
     *
     * @return  self
     */
    public function setStatutBC($statutBC)
    {
        $this->statutBC = $statutBC;

        return $this;
    }

    /**
     * Get the value of sortNbJours
     */
    public function getSortNbJours()
    {
        return $this->sortNbJours;
    }

    /**
     * Set the value of sortNbJours
     *
     * @return  self
     */
    public function setSortNbJours($sortNbJours)
    {
        $this->sortNbJours = $sortNbJours;

        return $this;
    }

    /**
     * Get the value of idMateriel
     */
    public function getIdMateriel()
    {
        return $this->idMateriel;
    }

    /**
     * Set the value of idMateriel
     *
     * @return  self
     */
    public function setIdMateriel($idMateriel)
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    /**
     * Get the value of typeAchat
     */
    public function getTypeAchat()
    {
        return $this->typeAchat;
    }

    /**
     * Set the value of typeAchat
     *
     * @return  self
     */
    public function setTypeAchat($typeAchat)
    {
        $this->typeAchat = $typeAchat;

        return $this;
    }

    /**
     * Get the value of niveauUrgence
     */
    public function getNiveauUrgence()
    {
        return $this->niveauUrgence;
    }

    /**
     * Set the value of niveauUrgence
     *
     * @return  self
     */
    public function setNiveauUrgence($niveauUrgence)
    {
        $this->niveauUrgence = $niveauUrgence;

        return $this;
    }

    /**
     * Get the value of dateDebutCreation
     */
    public function getDateDebutCreation()
    {
        return $this->dateDebutCreation;
    }

    /**
     * Set the value of dateDebutCreation
     *
     * @return  self
     */
    public function setDateDebutCreation($dateDebutCreation)
    {
        $this->dateDebutCreation = $dateDebutCreation;

        return $this;
    }

    /**
     * Get the value of dateFinCreation
     */
    public function getDateFinCreation()
    {
        return $this->dateFinCreation;
    }

    /**
     * Set the value of dateFinCreation
     *
     * @return  self
     */
    public function setDateFinCreation($dateFinCreation)
    {
        $this->dateFinCreation = $dateFinCreation;

        return $this;
    }

    /**
     * Get the value of dateDebutfinSouhaite
     */
    public function getDateDebutfinSouhaite()
    {
        return $this->dateDebutfinSouhaite;
    }

    /**
     * Set the value of dateDebutfinSouhaite
     *
     * @return  self
     */
    public function setDateDebutfinSouhaite($dateDebutfinSouhaite)
    {
        $this->dateDebutfinSouhaite = $dateDebutfinSouhaite;

        return $this;
    }

    /**
     * Get the value of dateFinFinSouhaite
     */
    public function getDateFinFinSouhaite()
    {
        return $this->dateFinFinSouhaite;
    }

    /**
     * Set the value of dateFinFinSouhaite
     *
     * @return  self
     */
    public function setDateFinFinSouhaite($dateFinFinSouhaite)
    {
        $this->dateFinFinSouhaite = $dateFinFinSouhaite;

        return $this;
    }

    /**
     * Get the value of agenceEmetteur
     */
    public function getAgenceEmetteur()
    {
        return $this->agenceEmetteur;
    }

    /**
     * Set the value of agenceEmetteur
     *
     * @return  self
     */
    public function setAgenceEmetteur($agenceEmetteur)
    {
        $this->agenceEmetteur = $agenceEmetteur;

        return $this;
    }

    /**
     * Get the value of serviceEmetteur
     */
    public function getServiceEmetteur()
    {
        return $this->serviceEmetteur;
    }

    /**
     * Set the value of serviceEmetteur
     *
     * @return  self
     */
    public function setServiceEmetteur($serviceEmetteur)
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceDebiteur
     */
    public function getAgenceDebiteur()
    {
        return $this->agenceDebiteur;
    }

    /**
     * Set the value of agenceDebiteur
     *
     * @return  self
     */
    public function setAgenceDebiteur($agenceDebiteur)
    {
        $this->agenceDebiteur = $agenceDebiteur;

        return $this;
    }

    /**
     * Get the value of serviceDebiteur
     */
    public function getServiceDebiteur()
    {
        return $this->serviceDebiteur;
    }

    /**
     * Set the value of serviceDebiteur
     *
     * @return  self
     */
    public function setServiceDebiteur($serviceDebiteur)
    {
        $this->serviceDebiteur = $serviceDebiteur;

        return $this;
    }

    /**
     * Convertit l'objet en tableau associatif
     */
    public function toArray(): array
    {
        return [
            'numDit'               => $this->numDit,
            'numDa'                => $this->numDa,
            'demandeur'            => $this->demandeur,
            'statutDA'             => $this->statutDA,
            'statutOR'             => $this->statutOR,
            'statutBC'             => $this->statutBC,
            'sortNbJours'          => $this->sortNbJours,
            'idMateriel'           => $this->idMateriel,
            'typeAchat'            => $this->typeAchat,
            'niveauUrgence'        => $this->niveauUrgence        ? $this->niveauUrgence->getId()                : null,
            'dateDebutCreation'    => $this->dateDebutCreation    ? $this->dateDebutCreation->format('Y-m-d')    : null,
            'dateFinCreation'      => $this->dateFinCreation      ? $this->dateFinCreation->format('Y-m-d')      : null,
            'dateDebutfinSouhaite' => $this->dateDebutfinSouhaite ? $this->dateDebutfinSouhaite->format('Y-m-d') : null,
            'dateFinFinSouhaite'   => $this->dateFinFinSouhaite   ? $this->dateFinFinSouhaite->format('Y-m-d')   : null,
            'agenceEmetteur'       => $this->agenceEmetteur       ? $this->agenceEmetteur->getId()               : null,
            'serviceEmetteur'      => $this->serviceEmetteur      ? $this->serviceEmetteur->getId()              : null,
            'agenceDebiteur'       => $this->agenceDebiteur       ? $this->agenceDebiteur->getId()               : null,
            'serviceDebiteur'      => $this->serviceDebiteur      ? $this->serviceDebiteur->getId()              : null,
        ];
    }

    /**
     * Hydrate l'objet à partir d'un tableau
     */
    public function toObject(array $data): self
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
}
