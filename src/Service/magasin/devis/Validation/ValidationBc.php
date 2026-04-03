<?php


namespace App\Service\magasin\devis\Validation;

use App\Constants\Magasin\Devis\StatutBcNegConstant;
use App\Constants\Magasin\Devis\StatutDevisNegContant;
use App\Dto\Magasin\Devis\Soumission\BcDto;
use App\Model\magasin\devis\Soumission\BcModel;
use App\Traits\Validator\ValidatorNotificationTrait;

class ValidationBc
{
    use ValidatorNotificationTrait;

    public function ValidateBcAvantAffichage(BcDto $bcDto): bool
    {
        $bcModel = new BcModel();
        $infoDevis = $bcModel->getInfoDevisForValidateBc($bcDto->numeroDevis, $bcDto->codeSociete);

        // Bloqué si:

        // le numéro de devis n'existe pas ou est vide
        if (empty($bcDto->numeroDevis)) {
            $message = "Le numero de devis est obligatoire pour la soumission.";
            $this->sendNotificationDevisMagasin($message, '-', false);
            return true; // Validation failed
        }

        // le statut de devis "A traiter"
        if (empty($infoDevis)) {
            $message = 'on ne peut pas soumettre un BC à validation que si la statut de la devis est encore "A traiter".';
            $this->sendNotificationDevisMagasin($message, $bcDto->numeroDevis, false);
            return true; // Validation failed
        }

        // le statut BC est en cours de validation
        if ($infoDevis && $infoDevis['statut_bc'] === StatutBcNegConstant::SOUMIS_VALIDATION) {
            $message = 'Le BC est en cours de validation.';
            $this->sendNotificationDevisMagasin($message, $bcDto->numeroDevis, false);
            return true; // Validation failed
        }
        // le statut de devis "Validé - à envoyer client" et le statut BC "en attent BC"
        if ($infoDevis && $infoDevis['statut'] === StatutDevisNegContant::VALIDE_AGENCE && $infoDevis['statut_bc'] === StatutBcNegConstant::EN_ATTENTE_BC) {
            $message = 'on ne peut pas soumettre un BC à validation que si le devis est envoyé au client et la reception du bc est en attente.';
            $this->sendNotificationDevisMagasin($message, $bcDto->numeroDevis, false);
            return true; // Validation failed
        }


        return false; // Validation passed
    }
}
