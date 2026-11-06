<?php

namespace App\Constants\ddp;

class TypeDemandePaiementConstants
{
    const ID_DEMANDE_PAIEMENT_A_L_AVANCE = 1;
    const ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE = 2;
    const ID_DEMANDE_PAIEMENT_REGULE = 3;
    const ID_BON_A_PAYER = 4;

    const PLAFOND_MONTANT_PROFORMA = 20000; // montant facture proforma max pour la demande de paiement à l'avance (magasin)
}
