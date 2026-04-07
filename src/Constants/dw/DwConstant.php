<?php

namespace App\Constants\dw;

class DwConstant
{
    // constante utile pour construire les liens Docuware
    private const DW_LINK_PREFIX = "https://hffc.docuware.cloud/docuware/formsweb/";
    private const DW_LINK_SUFFIX = "?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be";

    // constante pour tous les liens Docuware
    public const LINK = [
        "bon-de-caisse"            => self::DW_LINK_PREFIX . "bon-de-caisse" . self::DW_LINK_SUFFIX,
        "contrat"                  => self::DW_LINK_PREFIX . "enregistrement-contrats" . self::DW_LINK_SUFFIX,
        "annulation-conges-valide" => self::DW_LINK_PREFIX . "annulation-conges" . self::DW_LINK_SUFFIX,
        "annulation-conges-rh"     => self::DW_LINK_PREFIX . "annulation-conges-rh" . self::DW_LINK_SUFFIX,
        "new-conge"                => self::DW_LINK_PREFIX . "demande-de-conges-new" . self::DW_LINK_SUFFIX,
        "new-logistique"           => self::DW_LINK_PREFIX . "transport-logistique" . self::DW_LINK_SUFFIX,
    ];
}
