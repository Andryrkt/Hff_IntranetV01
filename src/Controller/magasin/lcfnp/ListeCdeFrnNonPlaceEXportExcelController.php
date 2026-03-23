<?php

namespace App\Controller\magasin\lcfnp;

use DateTime;
use DateTimeZone;
use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfnp\ListeCdeFrnNonplacerModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\ExcelService;

/**
 * @Route("/magasin")
 */
class ListeCdeFrnNonPlaceEXportExcelController extends Controller
{

    private ListeCdeFrnNonPlacerModel $listeCdeFrnNonPlacerModel;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;
    public function __construct()
    {
        parent::__construct();
        $this->listeCdeFrnNonPlacerModel = new ListeCdeFrnNonplacerModel();
        $this->ditOrsSoumisRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
    }

    /**
     * @Route("/lcfng/liste_cde_frs_non_placer_export_excel", name="liste_Cde_Frn_Non_placer_Export_Excel")
     *
     * @return void
     */
    public function exportExcel()
    {

        $today = new DateTime('now', new DateTimeZone('Indian/Antananarivo'));
        $vheure = $today->format("H:i:s");
        $vinstant = str_replace(":", "", $vheure);
        $criteria = $this->getSessionService()->get('lcfnp_liste_cde_frs_non_placer');
        $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());
        $this->listeCdeFrnNonPlacerModel->viewHffCtrmarqVinstant($criteria, $vinstant);
        $data = $this->listeCdeFrnNonPlacerModel->requetteBase($criteria, $vinstant, $numOrValides);
        $this->listeCdeFrnNonPlacerModel->dropView($vinstant);
        // Convertir les entités en tableau de données

        $entities = $this->transformationEnTableauAvecEntiter($data);
        //creation du fichier excel
        (new ExcelService())->createSpreadsheet($entities);
    }

    private function transformationEnTableauAvecEntiter(array $data): array
    {
        $tab = [];
        $tab[] = [
            'N° Commande Fournisseur',
            'Date Commande Fournisseu',
            'N° Fournisseur',
            'Nom Fournisseur',
            'Montant Commande',
            'Devis',
            'N° OR'
        ];

        foreach ($data as $value) {
            $tab[] = [
                $value['n_commande'],
                $value['date_cmd'],
                $value['n_frs'],
                $value['nom_frs'],
                $value['mont_ttc'],
                $value['devis'],
                $value['n_or']
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
