<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;

class DocRattacheService
{
    private DaService $daService;
    private DwDataService $dwDataService;
    private array $docTypes;
    private array $daDocumentMapping;

    public function __construct(DaService $daService, DwDataService $dwDataService)
    {
        $this->daService         = $daService;
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

            // Récupérer le service et appeler la méthode
            $service = $this->{$config['service']} ?? null;

            // Si le service n'existe pas, on continue à l'itération suivante
            if (!$service) continue;

            $method = $config['method'];
            $data = $service->$method($demandeAppro);

            // Normaliser les données
            $normalizer = $config['normalizer'];
            $normalizerParam = $config['normalizerParam'];
            $params = $normalizerParam ? [$data, $normalizerParam] : [$data];

            $fichiers = $this->$normalizer(...$params);

            // Construire le résultat
            $result[] = [
                'labeltype'  => $config['labelType'],
                'type'       => $config['type'],
                'icon'       => $config['icon'],
                'colorClass' => $config['colorClass'],
                'fichiers'   => $fichiers,
            ];
        }

        return $result;
    }

    /************************* 
     * Fonctions utilitaires *
     *************************/
    /** Get the value of daDocumentMapping */
    public function getDaDocumentMappingConfig(): array
    {
        return [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['BAI', 'OR', 'DEV_PJ_DA', 'DEV_PJ_OBS', 'BC', 'FACBL'],
            DemandeAppro::TYPE_DA_DIRECT           => ['BAI', 'BAD', 'DEV_PJ_DA', 'DEV_PJ_OBS', 'BC', 'FACBL'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['BAI', 'BAD', 'DEV_PJ_OBS'],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => ['BAI', 'BAD', 'DEV_PJ_OBS'],
        ];
    }

    /** Get the value of docTypes */
    public function getDocTypesConfig(): array
    {
        return [
            'BAI' => [
                'labelType'       => 'BAI',
                'type'            => "Bon d'achat (Intranet)",
                'icon'            => 'fa-solid fa-file-signature',
                'colorClass'      => 'border-left-bai',
                'service'         => 'daService',
                'method'          => 'getBaIntranetPath',
                'normalizer'      => 'normalizePathSingleFile',
                'normalizerParam' => 'nom',
            ],
            'OR' => [
                'labelType'       => 'OR',
                'type'            => 'Ordre de réparation',
                'icon'            => 'fa-solid fa-wrench',
                'colorClass'      => 'border-left-or',
                'service'         => 'daService',
                'method'          => 'getOrPath',
                'normalizer'      => 'normalizePathSingleFile',
                'normalizerParam' => 'numeroOr',
            ],
            'BAD' => [
                'labelType'       => 'BAD',
                'type'            => "Bon d'achat (DocuWare)",
                'icon'            => 'fa-solid fa-file-signature',
                'colorClass'      => 'border-left-bad',
                'service'         => 'dwDataService',
                'method'          => 'getBaDocuWarePath',
                'normalizer'      => 'normalizePathsForManyFiles',
                'normalizerParam' => 'num',
            ],
            'DEV_PJ_DA' => [
                'labelType'       => 'DEV_PJ_DA',
                'type'            => 'Devis / PJ (émis dans la demande / proposition)',
                'icon'            => 'fa-solid fa-money-bill-wave',
                'colorClass'      => 'border-left-devpj',
                'service'         => 'daService',
                'method'          => 'getDevisPjPathDaLine',
                'normalizer'      => 'normalizePathsForManyFiles',
                'normalizerParam' => 'nomPj',
            ],
            'DEV_PJ_OBS' => [
                'labelType'       => 'DEV_PJ_OBS',
                'type'            => "Devis / PJ (émis dans l'observation)",
                'icon'            => 'fa-solid fa-money-bill-wave',
                'colorClass'      => 'border-left-devpj',
                'service'         => 'daService',
                'method'          => 'getDevisPjPathObservation',
                'normalizer'      => 'normalizePathsForManyFiles',
                'normalizerParam' => 'nomPj',
            ],
            'BC' => [
                'labelType'       => 'BC',
                'type'            => 'Bon de commande',
                'icon'            => 'fa-solid fa-file-circle-check',
                'colorClass'      => 'border-left-bc',
                'service'         => 'dwDataService',
                'method'          => 'getBcPath',
                'normalizer'      => 'normalizePathsForManyFiles',
                'normalizerParam' => 'numeroBc',
            ],
            'FACBL' => [
                'labelType'       => 'FACBL',
                'type'            => 'Facture / Bon de livraison',
                'icon'            => 'fa-solid fa-file-invoice',
                'colorClass'      => 'border-left-facbl',
                'service'         => 'dwDataService',
                'method'          => 'getFacBlPath',
                'normalizer'      => 'normalizePathsForFacBl',
                'normalizerParam' => NULL,
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
        if (empty($allDocs)) return [];

        return array_map(function ($doc) use ($numKey) {
            return [
                'nom'  => $doc[$numKey],
                'path' => $doc['path']
            ];
        }, $allDocs);
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
        if (empty($allDocs)) return [];

        return array_map(function ($doc) {
            return [
                'nom'   => $doc['nomFichierScannee'] ?? $doc['idFacBl'],
                'numBC' => $doc['numeroBc'],
                'path'  => $doc['path']
            ];
        }, $allDocs);
    }
}
