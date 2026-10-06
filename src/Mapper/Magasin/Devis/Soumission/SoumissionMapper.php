<?php

namespace App\Mapper\Magasin\Devis\Soumission;

use App\Constants\Magasin\Devis\StatutDevisNegContant;
use App\Dto\Magasin\Devis\Soumission\SoumissionDto;
use App\Model\magasin\devis\Soumission\SoumissionModel;

class SoumissionMapper
{
    public static function toArrayVerificationPrix(SoumissionDto $dto, string $nomFichier, string $nomFichierExcel)
    {
        $soumissionModel = new SoumissionModel();
        $infoDevis = $soumissionModel->getInfoDevis($dto->numeroDevis, $dto->codeSociete);
        if (empty($infoDevis)) {
            throw new \Exception("Aucun devis trouvé pour le numéro de devis : " . $dto->numeroDevis);
        }

        return [
            'numero_devis' => $dto->numeroDevis,
            'numero_version' => $dto->numeroVersion,
            'statut_dw' => StatutDevisNegContant::PRIX_A_CONFIRMER,
            'montant_devis' => $infoDevis['montant_devis'],
            'devise' => $infoDevis['devise'],
            'type_soumission' => $dto->typeSoumission,
            'utilisateur' => $dto->userName,
            'cat' => $dto->suffix === 'C' || $dto->suffix === 'CP' ? true : false,
            'non_cat' => $dto->suffix === 'P' || $dto->suffix === 'CP' ? true : false,
            'nom_fichier' => $nomFichier,
            'date_creation' => $dto->dateCreation,
            'date_modification' => $dto->dateCreation,
            'somme_numero_lignes' => $infoDevis['somme_numero_lignes'],
            'est_validation_pm' => $dto->validationPm,
            'piece_joint_excel' => $nomFichierExcel,
            'tache_validateur' => $dto->tacheValidateur,
            'observation' => $dto->observation,
            'code_societe' => $dto->codeSociete,
        ];
    }

    public static function toArrayValidationDevis(SoumissionDto $dto, string $nomFichier)
    {
        $soumissionModel = new SoumissionModel();
        $infoDevis = $soumissionModel->getInfoDevis($dto->numeroDevis, $dto->codeSociete);
        if (empty($infoDevis)) {
            throw new \Exception("Aucun devis trouvé pour le numéro de devis : " . $dto->numeroDevis);
        }

        return [
            'numero_devis' => $dto->numeroDevis,
            'numero_version' => $dto->numeroVersion,
            'statut_dw' => StatutDevisNegContant::STATUT_A_VALIDER_CHEF_AGENCE,
            'montant_devis' => $infoDevis['montant_devis'],
            'devise' => $infoDevis['devise'],
            'type_soumission' => $dto->typeSoumission,
            'utilisateur' => $dto->userName,
            'cat' => $dto->suffix === 'C' || $dto->suffix === 'CP' ? true : false,
            'non_cat' => $dto->suffix === 'P' || $dto->suffix === 'CP' ? true : false,
            'nom_fichier' => $nomFichier,
            'date_creation' => $dto->dateCreation,
            'date_modification' => $dto->dateCreation,
            'somme_numero_lignes' => $infoDevis['somme_numero_lignes'],
            'code_societe' => $dto->codeSociete,
        ];
    }
}
