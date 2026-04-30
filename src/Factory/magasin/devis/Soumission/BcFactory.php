<?php

namespace App\Factory\magasin\devis\Soumission;

use App\Constants\Magasin\Devis\StatutBcNegConstant;
use App\Dto\Magasin\Devis\Soumission\BcDto;
use App\Model\magasin\devis\Soumission\BcModel;
use App\Model\magasin\devis\Soumission\SoumissionModel;
use App\Service\autres\VersionService;

class BcFactory
{
    public function create(?string $numeroDevis, ?string $codeSociete): BcDto
    {
        $bcDto = new BcDto();
        $bcDto->numeroDevis = $numeroDevis;
        $bcDto->codeSociete = $codeSociete;


        return $bcDto;
    }

    public function createApresSoumission(BcDto $bcDto, string $userName, string $userMail): BcDto
    {
        $bcModel = new BcModel();

        $bcDto->dateCreation = new \DateTime();
        $bcDto->dateModification = new \DateTime();
        $bcDto->numeroVersion = VersionService::autoIncrement($bcModel->getNumeroVersion($bcDto->numeroDevis, $bcDto->codeSociete));
        $bcDto->statutBc = StatutBcNegConstant::SOUMIS_VALIDATION;
        $bcDto->utilisateur = $userName;
        $bcDto->userMail = $userMail;
        $bcDto->montantDevis = (float) $bcModel->getMontantDevis($bcDto->numeroDevis, $bcDto->codeSociete)[0];
        $bcDto->montantBc = (float) str_replace(',', '.', str_replace(' ', '', $bcDto->montantBc));

        $infoClient = $bcModel->getClientAndModePaiement($bcDto->numeroDevis, $bcDto->codeSociete);
        $bcDto->codeClient = $infoClient[0]['code_client'] ?? '';
        $bcDto->nomClient = $infoClient[0]['nom_client'] ?? '';
        $bcDto->modePayement = $infoClient[0]['mode_paiement'] ?? '';


        $devisNegModel = new SoumissionModel();
        $bcDto->numeroVersionDevis = $devisNegModel->getNumeroVersion($bcDto->numeroDevis);

        return $bcDto;
    }
}
