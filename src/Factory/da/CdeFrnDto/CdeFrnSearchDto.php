<?php

namespace App\Factory\da\CdeFrnDto;

use DateTime;
use App\Entity\admin\dit\WorNiveauUrgence;

class CdeFrnSearchDto
{
    private ?string $numDa = null;
    private ?string $typeAchat = null;
    private ?string $numDit = null;
    private ?string $numOr = null;
    private ?string $numFrn = null;
    private ?string $frn = null;
    private ?string $numCde = null;
    private ?string $ref = null;
    private ?string $designation = null;
    private ?string $niveauUrgence = null;
    private ?string $statutBC = null;
    private ?DateTime $dateDebutOR = null;
    private ?DateTime $dateFinOR = null;
    private ?DateTime $dateDebutfinSouhaite = null;
    private ?DateTime $dateFinFinSouhaite = null;
    private ?string $sortNbJours = null;

    /** ============================================================
     * getter and setter
     *============================================================*/

    /**
     * Get the value of numDa
     */
    public function getNumDa()
    {
        return $this->numDa;
    }

    /**
     * Set the value of numDa
     */
    public function setNumDa($numDa): self
    {
        $this->numDa = $numDa;

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
     */
    public function setTypeAchat($typeAchat): self
    {
        $this->typeAchat = $typeAchat;

        return $this;
    }

    /**
     * Get the value of numDit
     */
    public function getNumDit()
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     */
    public function setNumDit($numDit): self
    {
        $this->numDit = $numDit;

        return $this;
    }

    /**
     * Get the value of numOr
     */
    public function getNumOr()
    {
        return $this->numOr;
    }

    /**
     * Set the value of numOr
     */
    public function setNumOr($numOr): self
    {
        $this->numOr = $numOr;

        return $this;
    }

    /**
     * Get the value of numFrn
     */
    public function getNumFrn()
    {
        return $this->numFrn;
    }

    /**
     * Set the value of numFrn
     */
    public function setNumFrn($numFrn): self
    {
        $this->numFrn = $numFrn;

        return $this;
    }

    /**
     * Get the value of frn
     */
    public function getFrn()
    {
        return $this->frn;
    }

    /**
     * Set the value of frn
     */
    public function setFrn($frn): self
    {
        $this->frn = $frn;

        return $this;
    }

    /**
     * Get the value of numCde
     */
    public function getNumCde()
    {
        return $this->numCde;
    }

    /**
     * Set the value of numCde
     */
    public function setNumCde($numCde): self
    {
        $this->numCde = $numCde;

        return $this;
    }

    /**
     * Get the value of ref
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set the value of ref
     */
    public function setRef($ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get the value of designation
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Set the value of designation
     */
    public function setDesignation($designation): self
    {
        $this->designation = $designation;

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
     */
    public function setNiveauUrgence($niveauUrgence): self
    {
        $this->niveauUrgence = $niveauUrgence;

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
     */
    public function setStatutBC($statutBC): self
    {
        $this->statutBC = $statutBC;

        return $this;
    }

    /**
     * Get the value of dateDebutOR
     */
    public function getDateDebutOR()
    {
        return $this->dateDebutOR;
    }

    /**
     * Set the value of dateDebutOR
     */
    public function setDateDebutOR($dateDebutOR): self
    {
        $this->dateDebutOR = $dateDebutOR;

        return $this;
    }

    /**
     * Get the value of dateFinOR
     */
    public function getDateFinOR()
    {
        return $this->dateFinOR;
    }

    /**
     * Set the value of dateFinOR
     */
    public function setDateFinOR($dateFinOR): self
    {
        $this->dateFinOR = $dateFinOR;

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
     */
    public function setDateDebutfinSouhaite($dateDebutfinSouhaite): self
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
     */
    public function setDateFinFinSouhaite($dateFinFinSouhaite): self
    {
        $this->dateFinFinSouhaite = $dateFinFinSouhaite;

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
     */
    public function setSortNbJours($sortNbJours): self
    {
        $this->sortNbJours = $sortNbJours;

        return $this;
    }


    /**
     * Hydrate l'objet à partir d'un tableau
     */
    public function toObject(?array $data): self
    {
        // Si les données sont null, on retourne l'objet tel quel
        if ($data === null) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Transforme l'objet en tableau en utilisant la réflexion
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        $result = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            if ($value !== null && $value !== '') {
                $result[$property->getName()] = $value;
            }
        }

        return $result;
    }

    /**
     * Transforme l'objet en tableau en filtrant les propriétés nulles ou vides
     */
    public function toArrayFilter(): array
    {
        return array_filter([
            'numDa' => $this->numDa,
            'typeAchat' => $this->typeAchat,
            'numDit' => $this->numDit,
            'numOr' => $this->numOr,
            'numFrn' => $this->numFrn,
            'frn' => $this->frn,
            'numCde' => $this->numCde,
            'ref' => $this->ref,
            'designation' => $this->designation,
            'niveauUrgence' => $this->niveauUrgence,
            'statutBC' => $this->statutBC,
            'dateDebutOR' => $this->dateDebutOR,
            'dateFinOR' => $this->dateFinOR,
            'dateDebutfinSouhaite' => $this->dateDebutfinSouhaite,
            'dateFinFinSouhaite' => $this->dateFinFinSouhaite,
            'sortNbJours' => $this->sortNbJours,
        ], fn($val) => $val !== null && $val !== '');
    }
}
