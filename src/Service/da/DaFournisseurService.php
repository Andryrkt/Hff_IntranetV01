<?php

namespace App\Service\da;

use App\Model\da\DaModel;
use App\Service\UserData\UserDataService;

class DaFournisseurService
{
    private DaModel $daModel;
    private UserDataService $userDataService;
    private ?array $fournisseurs = null;

    public function __construct(DaModel $daModel, UserDataService $userDataService)
    {
        $this->daModel = $daModel;
        $this->userDataService = $userDataService;
    }

    /**
     * Numéro du fournisseur d'après son nom (0 si inconnu).
     * La liste n'est chargée qu'au premier appel.
     */
    public function getNumeroFournisseur(?string $nomFournisseur): int
    {
        if ($this->fournisseurs === null) {
            $fournisseurs = $this->daModel->getAllFournisseur($this->userDataService->getCodeSociete());
            $this->fournisseurs = array_column($fournisseurs, 'numerofournisseur', 'nomfournisseur');
        }

        return $this->fournisseurs[$nomFournisseur] ?? 0;
    }
}
