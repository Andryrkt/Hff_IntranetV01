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

    public function getFileInfo(string $numeroDdp, string $codeTypeDemande): array
    {
        $table = self::TABLE_MAP[$codeTypeDemande] ?? 'DW_demande_de_paiement';
        $column = self::COLUMN_MAP[$codeTypeDemande] ?? 'numero_ddp';

        $relativePath = $this->demandePaiementModel->getFilePathDdp($numeroDdp, $table, $column);

        $fullPath = $relativePath ? $_ENV['BASE_PATH_FICHIER'] . '/' . $relativePath : '';

        $exists = $fullPath && $this->fileCheckerService->checkFileExists($fullPath);

        return [
            'exists' => $exists,
            'path' => $fullPath,
        ];
    }
}
