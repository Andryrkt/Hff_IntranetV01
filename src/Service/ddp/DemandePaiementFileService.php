<?php

namespace App\Service\ddp;

use App\Model\ddp\DemandePaiementModel;
use App\Service\da\FileCheckerService;

class DemandePaiementFileService
{
    private const TABLE_MAP = [
        'DPR' => 'DW_regularisation_ddp',
        'BAP' => 'DW_bon_a_payer',
    ];

    private const COLUMN_MAP = [
        'DPR' => 'numero_ddr',
        'BAP' => 'numero_bap',
    ];

    private DemandePaiementModel $demandePaiementModel;
    private FileCheckerService $fileCheckerService;

    public function __construct()
    {
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->fileCheckerService = new FileCheckerService();
    }

    /**
     * Retourne les informations du fichier associé à une demande.
     *
     * @param string $numeroDdp
     * @param string $codeTypeDemande
     * 
     * @return array{exists:bool,path:string}
     */
    public function getFileInfo(string $numeroDdp, string $codeTypeDemande): array
    {
        $table = self::TABLE_MAP[$codeTypeDemande] ?? 'DW_demande_de_paiement';
        $column = self::COLUMN_MAP[$codeTypeDemande] ?? 'numero_ddp';

        $relativePath = $this->demandePaiementModel->getFilePathDdp($numeroDdp, $table, $column);
        $baseBathFichier = rtrim($_ENV['BASE_PATH_FICHIER'], '/');

        // Si le fichier n'est pas trouvé dans la base de données, on cherche dans le dossier
        $fullPath = $relativePath ? "$baseBathFichier/$relativePath" : "$baseBathFichier/ddp/$numeroDdp/$numeroDdp.pdf";

        $exists = $fullPath && $this->fileCheckerService->checkFileExists($fullPath);

        return [
            'exists' => $exists,
            'path'   => $fullPath,
        ];
    }
}
