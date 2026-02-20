<?php

namespace App\Api\dw;


use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Traits\FileUtilityTrait;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\dossierInterventionAtelierModel;

class DwApi extends Controller
{
    use FileUtilityTrait;

    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;


    public function __construct()
    {
        parent::__construct();
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = $this->getEntityManager()->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $this->getEntityManager()->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/api/dw-fetch/{numDit}", name="api_fetch_dw")
     * 
     * Cette fonction permet d'envoier les donners Ordre de réparation, facture, rapport d'intervention, commande
     * qui correspond à un demande d'intervention
     */
    public function dwfetch($numDit)
    {
        $dwModel = new dossierInterventionAtelierModel();

        // Récupération initiale : Demande d'intervention
        $dwDit = $this->fetchAndLabel($dwModel, 'findDwDit', $numDit, "Demande d'intervention");

        // Ordre de réparation et documents liés
        $dwOr = $this->fetchAndLabel($dwModel, 'findDwOr', $numDit, "Ordre de réparation");
        $dwFac = $dwRi = $dwCde = $dwBca = $dwFacBl = [];

        // Si un ordre de réparation est trouvé, récupérer les autres données liées
        if (!empty($dwOr)) {
            $numeroDocOr = $dwOr[0]['numero_doc'];
            $dwFac   = $this->fetchAndLabel($dwModel, 'findDwFac',   $numeroDocOr, "Facture");
            $dwRi    = $this->fetchAndLabel($dwModel, 'findDwRi',    $numeroDocOr, "Rapport d'intervention");
            $dwCde   = $this->fetchAndLabel($dwModel, 'findDwCde',   $numeroDocOr, "Commande");
            $dwBca   = $this->fetchAndLabel($dwModel, 'findDwBca',   $numeroDocOr, "Bon de commande APPRO");
            $dwFacBl = $this->fetchAndLabel($dwModel, 'findDwFacBl', $numeroDocOr, "Facture / Bon de livraison");
        }

        // Documents liés à la demande d'intervention
        $dwBc  = !empty($dwDit) ? $this->fetchAndLabel($dwModel, 'findDwBc',  $dwDit[0]['numero_doc'], "Bon de Commande Client") : [];
        $dwDev = !empty($dwDit) ? $this->fetchAndLabel($dwModel, 'findDwDev', $dwDit[0]['numero_doc'], "Devis") : [];
        $daValide = !empty($dwDit) ? $this->getAllBaValide($numDit) : [];
        $daDevisPj = !empty($dwDit) ? $this->getAllDevisPjDA($numDit) : [];

        // Fusionner toutes les données
        $data = array_merge($dwDit, $dwOr, $dwFac, $dwRi, $dwCde, $dwBc, $dwDev, $dwBca, $dwFacBl, $daValide, $daDevisPj);

        header("Content-type:application/json");

        echo json_encode($data);
    }

    /**
     *@Route("/api/dw-chemin-fetch/{numDoc}/{nomDoc}/{numVersion}", name="api_fetch_dw_chemin")
     */
    public function dwCheminFichier($numDoc, $nomDoc, $numVersion)
    {
        $dwModel = new dossierInterventionAtelierModel();

        switch ($nomDoc) {
            case 'Demande d\'intervention':
                $dw = $dwModel->findCheminDit($numDoc) ?? [];
                break;
            case 'Ordre de réparation':
                $dw = $dwModel->findCheminOr($numDoc, $numVersion) ?? [];
                break;
            case 'Facture':
                $dw = $dwModel->findCheminFac($numDoc) ?? [];
                break;
            case 'Rapport d\'intervention':
                $dw = $dwModel->findCheminRi($numDoc) ?? [];
                break;
            default:
                $dw = $dwModel->findCheminCde($numDoc) ?? [];
                break;
        }

        header("Content-type:application/json");

        echo json_encode(['chemin' => $dw[0]]);
    }

    /**
     * Méthode utilitaire pour récupérer et étiqueter des documents
     */
    private function fetchAndLabel($model, string $method, $param, string $label): array
    {
        $items = $model->$method($param) ?? [];
        foreach ($items as &$item) {
            $item['nomDoc'] = $label;
        }
        return $items;
    }

    private function getAllBaValide(string $numeroDit)
    {
        $items = [];

        $allNumDaValide = $this->demandeApproRepository->findAllNumDaValide($numeroDit);

        foreach ($allNumDaValide as $numDaValide) {
            $chemin = "da/$numDaValide/$numDaValide.pdf";
            $items[] = [
                'nomDoc'            => "Bon d’achat validé",
                'numero_doc'        => $numDaValide,
                'chemin'            => $chemin,
                'taille_fichier'    => $this->getFileSize($_ENV['BASE_PATH_FICHIER_COURT'] . "/$chemin"),
                'extension_fichier' => '.pdf',
            ];
        }

        return $items;
    }

    private function getAllDevisPjDA(string $numeroDit)
    {
        $items = [];

        $pjDals = $this->demandeApproLRepository->findAttachmentsByNumeroDit($numeroDit);
        $pjDalrs = $this->demandeApproLRRepository->findAttachmentsByNumeroDit($numeroDit);

        /** 
         * Fusionner les résultats des deux tables
         * @var array<int, array{numeroDemandeAppro: string, fileNames: array}>
         **/
        $allRows = array_merge($pjDals, $pjDalrs);

        $allFileNames = [];
        foreach ($allRows as $row) {
            $files = $row['fileNames'];
            foreach ($files as $fileName) {
                $key = "{$row['numeroDemandeAppro']}_{$this->getFileNameWithoutExtension($fileName)}";
                if (!isset($allFileNames[$key])) {
                    $allFileNames[$key] = true;

                    $items[] = [
                        'nomDoc'            => "Devis / Pièce jointe",
                        'numero_doc'        => $key,
                        'chemin'            => "da/{$row['numeroDemandeAppro']}/$fileName",
                        'taille_fichier'    => $this->getFileSize($_ENV['BASE_PATH_FICHIER_COURT'] . "/da/{$row['numeroDemandeAppro']}/$fileName"),
                        'extension_fichier' => $this->getFileExtension($fileName),
                    ];
                }
            }
        }

        return $items;
    }
}
