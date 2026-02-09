<?php

namespace App\Controller\magasin\lcfng;

use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfng\ListeCdeFrnNonGenererModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\ExcelService;

/**
 * @Route("/magasin")
 */
class ListeCdeFrnNonGenererExportExcelController extends Controller
{
    private ListeCdeFrnNonGenererModel $listeCdeFrnNonGenererModel;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;

    public function __construct()
    {
        parent::__construct();

        $this->listeCdeFrnNonGenererModel = new ListeCdeFrnNonGenererModel();
        $this->ditOrsSoumisRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
    }

    /**
     * @Route("/lcfng/liste_cde_frs_non_generer_export_excel", name="liste_Cde_Frn_Non_Generer_Export_Excel")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->getSessionService()->get('lcfng_liste_cde_frs_non_generer');

        // récupération des OR valide dans Ors_soumis_a_validation
        $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());

        $data = $this->listeCdeFrnNonGenererModel->getListeCdeFrnNonGenerer($criteria, $numOrValides);

        // Convertir les entités en tableau de données
        $entities = $this->transformationEnTableauAvecEntiter($data);

        //creation du fichier excel
        (new ExcelService())->createSpreadsheet($entities);
    }

    private function transformationEnTableauAvecEntiter(array $data): array
    {
        $tab = [];
        $tab[] = [
            'Type Document',
            'N° Document',
            'Date Document',
            'Libelle',
            'N° Dit',
            'Ag/Serv Emetteur',
            'Ag/Serv Débiteur/Client',
            'N° ITV',
            'N° Lig',
            'CST',
            'Réf',
            'Désignation',
            'Qte demandée',
            'Qte reliquat'
        ];

        foreach ($data as $value) {
            $tab[] = [
                $value['type_document'],
                $value['numdocument'],
                $value['datedocument'],
                $value['libelle'],
                $value['numdit'],
                $value['agenceservicecrediteur'],
                $value['agenceservicedebiteur'],
                $value['numinterv'],
                $value['numeroligne'],
                $value['constructeur'],
                $value['referencepiece'],
                $value['designations'],
                $value['quantitedemander'],
                $value['quantitereliquat'],
            ];
        }

        return $tab;
    }

    private function orEnString($tab): string
    {
        $numOrValide = $this->transformEnSeulTableau($tab);

        return implode("','", $numOrValide);
    }

    public function transformEnSeulTableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as  $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $tab[] = $value;
                }
            } else {
                $tab[] = $values;
            }
        }

        return $tab;
    }
}
