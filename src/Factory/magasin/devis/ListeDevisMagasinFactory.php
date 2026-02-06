<?php

namespace App\Factory\magasin\devis;

use App\Constants\Magasin\Devis\PointageRelanceStatutConstant;

class listeDevisMagasinFactory
{
    private $statutDw = '';
    private $numeroDevis;
    private $dateCreation;
    private $succursaleServiceEmetteur;
    private $codeClientLibelleClient;
    private $referenceCLient;
    private $montant = 0.00;
    private $operateur;
    private $dateDenvoiDevisAuClient;
    private $statutIps;
    private $statutBc;
    private $creePar;
    private $numeroPO;
    private $urlPO;
    private $dateDerniereRelance;
    private $nombreDeRelance;
    private ?string $statutRelance = null;
    private array $relances = [];

    /**
     * Get the value of statutDw
     */
    public function getStatutDw()
    {
        return $this->statutDw;
    }

    /**
     * Set the value of statutDw
     *
     * @return  self
     */
    public function setStatutDw($statutDw)
    {
        $this->statutDw = $statutDw;

        return $this;
    }

    /**
     * Get the value of numeroDevis
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     *
     * @return  self
     */
    public function setNumeroDevis($numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;

        return $this;
    }

    /**
     * Get the value of dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set the value of dateCreation
     *
     * @return  self
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get the value of succursaleServiceEmetteur
     */
    public function getSuccursaleServiceEmetteur()
    {
        return $this->succursaleServiceEmetteur;
    }

    /**
     * Set the value of succursaleServiceEmetteur
     *
     * @return  self
     */
    public function setSuccursaleServiceEmetteur($succursaleServiceEmetteur)
    {
        $this->succursaleServiceEmetteur = $succursaleServiceEmetteur;

        return $this;
    }

    /**
     * Get the value of codeClientLibelleClient
     */
    public function getCodeClientLibelleClient()
    {
        return $this->codeClientLibelleClient;
    }

    /**
     * Set the value of codeClientLibelleClient
     *
     * @return  self
     */
    public function setCodeClientLibelleClient($codeClientLibelleClient)
    {
        $this->codeClientLibelleClient = $codeClientLibelleClient;

        return $this;
    }

    /**
     * Get the value of referenceCLient
     */
    public function getReferenceCLient()
    {
        return $this->referenceCLient;
    }

    /**
     * Set the value of referenceCLient
     *
     * @return  self
     */
    public function setReferenceCLient($referenceCLient)
    {
        $this->referenceCLient = $referenceCLient;

        return $this;
    }

    /**
     * Get the value of montant
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set the value of montant
     *
     * @return  self
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get the value of operateur
     */
    public function getOperateur()
    {
        return $this->operateur;
    }

    /**
     * Set the value of operateur
     *
     * @return  self
     */
    public function setOperateur($operateur)
    {
        $this->operateur = $operateur;

        return $this;
    }

    /**
     * Get the value of dateDenvoiDevisAuClient
     */
    public function getDateDenvoiDevisAuClient()
    {
        return $this->dateDenvoiDevisAuClient;
    }

    /**
     * Set the value of dateDenvoiDevisAuClient
     *
     * @return  self
     */
    public function setDateDenvoiDevisAuClient($dateDenvoiDevisAuClient)
    {
        $this->dateDenvoiDevisAuClient = $dateDenvoiDevisAuClient;

        return $this;
    }

    /**
     * Get the value of statutIps
     */
    public function getStatutIps()
    {
        return $this->statutIps;
    }

    /**
     * Set the value of statutIps
     *
     * @return  self
     */
    public function setStatutIps($statutIps)
    {
        $this->statutIps = $statutIps;

        return $this;
    }

    /**
     * Get the value of statutBc
     */
    public function getStatutBc()
    {
        return $this->statutBc;
    }

    /**
     * Set the value of statutBc
     */
    public function setStatutBc($statutBc): self
    {
        $this->statutBc = $statutBc;

        return $this;
    }

    /**
     * Get the value of creePar
     */
    public function getCreePar()
    {
        return $this->creePar;
    }

    /**
     * Set the value of creePar
     */
    public function setCreePar($creePar): self
    {
        $this->creePar = $creePar;

        return $this;
    }

    /**
     * Get the value of numeroPO
     */
    public function getNumeroPO()
    {
        return $this->numeroPO;
    }

    /**
     * Set the value of numeroPO
     */
    public function setNumeroPO($numeroPO): self
    {
        $this->numeroPO = $numeroPO;

        return $this;
    }

    /**
     * Get the value of urlPO
     */
    public function getUrlPO()
    {
        return $this->urlPO;
    }

    /**
     * Set the value of urlPO
     */
    public function setUrlPO($urlPO): self
    {
        $this->urlPO = $urlPO;

        return $this;
    }

    public function transformationEnObjet(array $data): listeDevisMagasinFactory
    {
        $this
            ->setStatutDw($data['statut_dw'] ?? '')
            ->setNumeroDevis($data['numero_devis'] ?? '')
            ->setDateCreation($this->convertToDateTime($data['date_creation']) ? $this->convertToDateTime($data['date_creation'])->format('d/m/Y') : null)
            ->setSuccursaleServiceEmetteur($data['emmeteur'] ?? '')
            ->setCodeClientLibelleClient($data['client'] ?? '')
            ->setReferenceCLient($data['reference_client'] ?? '')
            ->setMontant($data['montant'] ?? 0.00)
            ->setOperateur($data['operateur'] ?? '') //utilisateur qui a soumis le devis
            ->setDateDenvoiDevisAuClient($this->convertToDateTime($data['date_envoi_devis_au_client']) ? $this->convertToDateTime($data['date_envoi_devis_au_client'])->format('d/m/Y') : null)
            ->setStatutIps($data['statut_ips'] ?? '')
            ->setStatutBc($data['statut_bc'] ?? '')
            ->setCreePar($data['utilisateur_createur_devis'] ?? '')
            ->setNumeroPO($data['numero_po'] ?? '')
            ->setUrlPO($data['url_po'] ?? '')
            ->setDateDerniereRelance($data['date_derniere_relance'] ? $this->convertToDateTime($data['date_derniere_relance'])->format('d/m/Y') : null)
            ->setNombreDeRelance($data['numero_relance'] ?? null)
            ->setStatutRelance($data['statut_relance'] ?? null)
            ->setRelances($data['relances'] ?? [])
        ;

        return $this;
    }

    private function convertToDateTime($value): ?\DateTime
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if (empty($value)) {
            return null;
        }

        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the value of dateDerniereRelance
     */
    public function getDateDerniereRelance()
    {
        return $this->dateDerniereRelance;
    }

    /**
     * Set the value of dateDerniereRelance
     */
    public function setDateDerniereRelance($dateDerniereRelance): self
    {
        $this->dateDerniereRelance = $dateDerniereRelance;

        return $this;
    }

    /**
     * Get the value of nombreDeRelance
     */
    public function getNombreDeRelance()
    {
        return $this->nombreDeRelance;
    }

    /**
     * Set the value of nombreDeRelance
     */
    public function setNombreDeRelance($nombreDeRelance): self
    {
        $this->nombreDeRelance = $nombreDeRelance;

        return $this;
    }

    /**
     * Get the value of statutRelance
     */
    public function getStatutRelance()
    {
        return $this->statutRelance;
    }

    /**
     * Set the value of statutRelance
     */
    public function setStatutRelance($statutRelance): self
    {
        $this->statutRelance = $statutRelance;

        return $this;
    }

    /**
     * Get the value of relances
     */
    public function getRelances(): array
    {
        return $this->relances;
    }

    /**
     * Set the value of relances
     */
    public function setRelances(array $relances): self
    {
        $this->relances = $relances;

        return $this;
    }

}
