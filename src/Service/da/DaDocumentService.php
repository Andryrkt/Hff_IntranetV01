<?php

namespace App\Service\da;

use App\Entity\da\DaObservation;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Model\ddp\DemandePaiementModel;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Chemins des documents rattachés à une DA (BA, devis/PJ, OR, DDP).
 * Appelé dynamiquement par DocRattacheService (clé `service` => `daDocumentService`).
 */
class DaDocumentService
{
    private DaObservationRepository $daObservationRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private FileCheckerService $fileCheckerService;

    public function __construct(EntityManagerInterface $em, FileCheckerService $fileCheckerService)
    {
        $this->fileCheckerService       = $fileCheckerService;
        $this->daObservationRepository  = $em->getRepository(DaObservation::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * Obtenir l'url du bon d'achat
     */
    public function getBaIntranetPath(DemandeAppro $demandeAppro): array
    {
        $item = [];
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $filePath = $_ENV['BASE_PATH_FICHIER_COURT'] . "/da/$numDa/$numDa.pdf";
        $absolutePath = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa/$numDa.pdf";

        if (file_exists($absolutePath)) {
            $item = [
                'nom'  => pathinfo($filePath, PATHINFO_FILENAME),
                'path' => $filePath,
            ];
        }
        return $item;
    }

    /**
     * Obtenir l'url des devis et pièces jointes émis dans les lignes de la DA
     */
    public function getDevisPjPathDaLine(DemandeAppro $demandeAppro): array
    {
        $items = [];

        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $pjDals = $this->demandeApproLRepository->findAttachmentsByNumeroDA($numDa);
        $pjDalrs = $this->demandeApproLRRepository->findAttachmentsByNumeroDA($numDa);

        /**
         * Fusionner les résultats des deux tables
         * @var array<int, array{numeroDemandeAppro: string, fileNames: array}>
         **/
        $allRows = array_merge($pjDals, $pjDalrs);

        foreach ($allRows as $row) {
            $files = $row['fileNames'];
            foreach ($files as $fileName) {
                $items[] = [
                    'nomPj' => $fileName,
                    'path'  => "{$_ENV['BASE_PATH_FICHIER_COURT']}/da/$numDa/$fileName",
                ];
            }
        }
        return $items;
    }

    /**
     * Obtenir l'url des devis et pièces jointes émis dans l'observation
     */
    public function getDevisPjPathObservation(DemandeAppro $demandeAppro): array
    {
        $items = [];
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $pjs = $this->daObservationRepository->findAttachmentsByNumeroDA($numDa);

        foreach ($pjs as $row) {
            $files = $row['fileNames'];
            foreach ($files as $fileName) {
                $items[] = [
                    'nomPj' => $fileName,
                    'path'  => "{$_ENV['BASE_PATH_FICHIER_COURT']}/da/$numDa/$fileName",
                ];
            }
        }
        return $items;
    }

    /**
     * Obtenir l'url de la dernière ordre de réparation validé liée à la DA
     */
    public function getOrPath(DemandeAppro $demandeAppro): array
    {
        $result = [];
        $dataOR = (new DossierInterventionAtelierModel)->findCheminOrDernierValide($demandeAppro->getNumeroDemandeDit(), $demandeAppro->getNumeroDemandeAppro());

        if ($dataOR) {
            $result = [
                'numeroOr' => $dataOR['numero'],
                'path'     => $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $dataOR['chemin']
            ];
        }

        return $result;
    }

    public function getAllDdpPath(DemandeAppro $demandeAppro): array
    {
        $items = [];
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $ddps = (new DemandePaiementModel())->getAllDdpByDa($numDa);
        $basePathFichier = rtrim($_ENV['BASE_PATH_FICHIER'], '/');
        $shortBasePathFichier = rtrim($_ENV['BASE_PATH_FICHIER_COURT'], '/');

        foreach ($ddps as $ddp) {
            $numeroDdp = $ddp['numero_ddp'];
            $path = $ddp['path'] ?? "ddp/$numeroDdp/$numeroDdp.pdf";

            if ($this->fileCheckerService->checkFileExists("$basePathFichier/$path")) {
                $items[] = [
                    'numeroDdp' => $numeroDdp,
                    'path'      => "{$shortBasePathFichier}/{$path}",
                ];
            }
        }
        return $items;
    }
}
