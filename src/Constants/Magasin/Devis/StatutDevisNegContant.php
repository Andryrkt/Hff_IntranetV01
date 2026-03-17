<?php

namespace App\Constants\Magasin\Devis;


class StatutDevisNegContant
{
    public const PRIX_A_CONFIRMER = 'Prix à confirmer';
    public const PRIX_VALIDER_TANA = 'Prix validé - devis à envoyer au client';
    public const PRIX_VALIDER_AGENCE = 'Prix validé - devis à soumettre';
    public const PRIX_MODIFIER_TANA = 'Prix modifié - devis à envoyer au client';
    public const PRIX_MODIFIER_AGENCE = 'Prix modifié - devis à soumettre';
    public const DEMANDE_REFUSE_PAR_PM = 'Demande refusée par le PM';
    public const A_VALIDER_CHEF_AGENCE = "A valider chef d'agence";
    public const VALIDE_AGENCE = 'Validé - à envoyer au client';
    public const ENVOYER_CLIENT = 'Envoyé au client';
    public const CLOTURER_A_MODIFIER = 'Cloturé - A modifier';
    public const A_TRAITER = 'A traiter';
}
