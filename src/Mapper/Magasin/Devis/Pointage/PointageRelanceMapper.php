<?php

namespace App\Mapper\Magasin\Devis\Pointage;

class PointageRelanceMapper
{
    public static function toArrayPointageRelance($pointageRelanceEntity)
    {
        return [
            'numero_devis' => $pointageRelanceEntity->getNumeroDevis(),
            'date_de_relance' => $pointageRelanceEntity->getDateDeRelance()->format('Y-m-d H:i:s'),
            'utilisateur' => $pointageRelanceEntity->getUtilisateur(),
            'societe' => $pointageRelanceEntity->getCodeSociete(),
            'agence' => $pointageRelanceEntity->getAgence(),
            'date_creation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'numero_relance' => $pointageRelanceEntity->getNumeroRelance(),
            'numero_version' => $pointageRelanceEntity->getNumeroVersion(),
        ];
    }

    public static function toArrayUpdatePointageRelance()
    {
        return [
            'statut_relance' => 'Relancé',
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }
}
