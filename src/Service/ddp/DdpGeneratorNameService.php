<?php

namespace App\Service\ddp;

use App\Service\fichier\AbstractFileNameGeneratorService;

class DdpGeneratorNameService extends AbstractFileNameGeneratorService
{
    /**
     * Gerer un nom pour la page de garde et le fichier fusionner
     */
    public function generateNamePrincipal(
        string $numDdp
    ) {
        return "$numDdp.pdf";
    }
}
