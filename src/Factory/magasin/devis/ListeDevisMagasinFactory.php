<?php

namespace App\Factory\magasin\devis;

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

    public function transformationEnObjet(array $data): listeDevisMagasinFactory
    {
        $this->setStatutDw($data['statut_dw'] ?? '');
        $this->setNumeroDevis($data['numero_devis'] ?? '');
        $this->setDateCreation($this->convertToDateTime($data['date_creation']) ? $this->convertToDateTime($data['date_creation'])->format('d/m/Y') : null);
        $this->setSuccursaleServiceEmetteur($data['emmeteur'] ?? '');
        $this->setCodeClientLibelleClient($data['client'] ?? '');
        $this->setReferenceCLient($data['reference_client'] ?? '');
        $this->setMontant($data['montant'] ?? 0.00);
        $this->setOperateur($data['operateur'] ?? ''); //utilisateur qui a soumis le devis
        $this->setDateDenvoiDevisAuClient($this->convertToDateTime($data['date_envoi_devis_au_client']) ? $this->convertToDateTime($data['date_envoi_devis_au_client'])->format('d/m/Y') : null);
        $this->setStatutIps($data['statut_ips'] ?? '');

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
}
