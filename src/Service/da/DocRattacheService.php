<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;

class DocRattacheService
{
    private DaDocumentService $daDocumentService;
    private DwDataService $dwDataService;
    private array $docTypes;
    private array $daDocumentMapping;

    public function __construct(DaDocumentService $daDocumentService, DwDataService $dwDataService)
    {
        $this->daDocumentService = $daDocumentService;
        $this->dwDataService     = $dwDataService;
        $this->docTypes          = $this->getDocTypesConfig();
        $this->daDocumentMapping = $this->getDaDocumentMappingConfig();
    }

    /**
     * Fonction pour obtenir tous les fichiers attachés à une DA
     *
     * @param DemandeAppro $demandeAppro
     *
     * @return array
     */
    public function getAllAttachedFiles(DemandeAppro $demandeAppro): array
    {
        $result = [];

        $daType = $demandeAppro->getDaTypeId(); // type de la DA

        // Récupérer les types de documents pour ce type de DA
        $documentTypesForDA = $this->daDocumentMapping[$daType] ?? []; // Ex: ['BAI', 'BAD', 'DEV_PJ_DA', 'DEV_PJ_OBS', 'BC', 'FACBL']

        foreach ($documentTypesForDA as $docTypeKey) {
            // Vérifier si le type de document existe dans la configuration
            if (!isset($this->docTypes[$docTypeKey])) continue;

            $config = $this->docTypes[$docTypeKey];

            // Récupérer les données puis les normaliser
            $data = $config['fetch']($demandeAppro);

            // Construire le résultat
            $result[] = [
                'labelType'  => $docTypeKey,
                'type'       => $config['type'],
                'icon'       => $config['icon'],
                'colorClass' => $config['colorClass'],
                'fichiers'   => $config['normalize']($data),
            ];
        }

        return $result;
    }

    /*************************
     * Fonctions utilitaires *
     *************************/
    /** Types de documents attachés selon le type de DA */
    private function getDaDocumentMappingConfig(): array
    {
        return [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['BAI', 'OR', 'DEV_PJ_DA', 'DEV_PJ_OBS', 'BC', 'FACBL', 'DDP'],
            DemandeAppro::TYPE_DA_DIRECT           => ['BAI', 'BAD', 'DEV_PJ_DA', 'DEV_PJ_OBS', 'BC', 'FACBL', 'DDP'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['BAI', 'BAD', 'DEV_PJ_OBS'],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => ['BAI', 'BAD', 'DEV_PJ_OBS'],
        ];
    }

    /**
     * Configuration des types de documents
     * - fetch     : récupère les données brutes d'une DA
     * - normalize : transforme ces données en liste de fichiers
     */
    private function getDocTypesConfig(): array
    {
        return [
            'BAI' => [
                'type'       => "Bon d'achat (Intranet)",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-bai',
                'fetch'      => fn(DemandeAppro $da) => $this->daDocumentService->getBaIntranetPath($da),
                'normalize'  => fn($docs) => $this->normalizePathSingleFile($docs, 'nom'),
            ],
            'OR' => [
                'type'       => 'Ordre de réparation',
                'icon'       => 'fa-solid fa-wrench',
                'colorClass' => 'border-left-or',
                'fetch'      => fn(DemandeAppro $da) => $this->daDocumentService->getOrPath($da),
                'normalize'  => fn($docs) => $this->normalizePathSingleFile($docs, 'numeroOr'),
            ],
            'BAD' => [
                'type'       => "Bon d'achat (DocuWare)",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-bad',
                'fetch'      => fn(DemandeAppro $da) => $this->dwDataService->getBaDocuWarePath($da),
                'normalize'  => fn($docs) => $this->normalizePathsMultipleFiles($docs, 'num'),
            ],
            'DEV_PJ_DA' => [
                'type'       => 'Devis / PJ (émis dans la demande / proposition)',
                'icon'       => 'fa-solid fa-money-bill-wave',
                'colorClass' => 'border-left-devpj',
                'fetch'      => fn(DemandeAppro $da) => $this->daDocumentService->getDevisPjPathDaLine($da),
                'normalize'  => fn($docs) => $this->normalizePathsMultipleFiles($docs, 'nomPj'),
            ],
            'DEV_PJ_OBS' => [
                'type'       => "Devis / PJ (émis dans l'observation)",
                'icon'       => 'fa-solid fa-money-bill-wave',
                'colorClass' => 'border-left-devpj',
                'fetch'      => fn(DemandeAppro $da) => $this->daDocumentService->getDevisPjPathObservation($da),
                'normalize'  => fn($docs) => $this->normalizePathsMultipleFiles($docs, 'nomPj'),
            ],
            'BC' => [
                'type'       => 'Bon de commande',
                'icon'       => 'fa-solid fa-file-circle-check',
                'colorClass' => 'border-left-bc',
                'fetch'      => fn(DemandeAppro $da) => $this->dwDataService->getBcPath($da),
                'normalize'  => fn($docs) => $this->normalizePathsMultipleFiles($docs, 'numeroBc'),
            ],
            'FACBL' => [
                'type'       => 'Facture / Bon de livraison',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fetch'      => fn(DemandeAppro $da) => $this->dwDataService->getFacBlPath($da),
                'normalize'  => fn($docs) => $this->normalizePathsFacBl($docs),
            ],
            'DDP' => [
                'type'       => 'Demande de paiement / Bon à payer',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fetch'      => fn(DemandeAppro $da) => $this->daDocumentService->getAllDdpPath($da),
                'normalize'  => fn($docs) => $this->normalizePathsDdp($docs),
            ],
        ];
    }

    /**
     * Normaliser les chemins pour un seul fichier
     *
     * @param array  $doc
     * @param string $numKey
     *
     * @return array
     */
    private function normalizePathSingleFile(array $doc, string $numKey): array
    {
        if (empty($doc)) return [];

        return $this->normalizePathsMultipleFiles([$doc], $numKey);
    }

    /**
     * Normaliser les chemins pour plusieurs fichiers
     *
     * @param array  $allDocs
     * @param string $numKey
     *
     * @return array
     */
    private function normalizePathsMultipleFiles(array $allDocs, string $numKey): array
    {
        return array_map(fn($doc) => [
            'nom'  => $doc[$numKey],
            'path' => $doc['path']
        ], $allDocs);
    }

    /**
     * Normaliser les chemins pour plusieurs fichiers de facture / Bon de Livraison
     *
     * @param array $allDocs
     *
     * @return array
     */
    private function normalizePathsFacBl(array $allDocs): array
    {
        return array_map(fn($doc) => [
            'nom'   => $doc['nomFichierScannee'] ?? $doc['idFacBl'],
            'numBC' => $doc['numeroBc'],
            'path'  => $doc['path']
        ], $allDocs);
    }

    /**
     * Normaliser les chemins des DDP (avec le BC lié via `numero_commande`)
     *
     * @param array $allDocs
     *
     * @return array
     */
    private function normalizePathsDdp(array $allDocs): array
    {
        return array_map(fn($doc) => [
            'nom'   => $doc['numeroDdp'],
            'numBC' => $doc['numeroCommande'] ?? null,
            'path'  => $doc['path']
        ], $allDocs);
    }
}
