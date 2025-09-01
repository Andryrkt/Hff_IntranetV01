<?php

namespace App\Controller\magasin\lcfnp;
use App\Service\FusionPdf;
use App\Model\ProfilModel;
use App\Model\badm\BadmModel;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\dom\DomModel;
use App\Model\da\DaModel;
use App\Model\dom\DomDetailModel;
use App\Model\dom\DomDuplicationModel;
use App\Model\dom\DomListModel;
use App\Model\dit\DitModel;
use App\Service\SessionManagerService;
use App\Service\ExcelService;


use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfnp\ListeCdeFrnNonplacerModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use DateTime;
use DateTimeZone;
use App\Controller\BaseController;

/**
 * @Route("/magasin")
 */
class ListeCdeFrnNonPlaceEXportExcelController extends BaseController
{
    private FusionPdf $fusionPdfService;
    private ProfilModel $profilModelService;
    private BadmModel $badmModelService;
    private PersonnelModel $personnelModelService;
    private DomModel $domModelService;
    private DaModel $daModelService;
    private DomDetailModel $domDetailModelService;
    private DomDuplicationModel $domDuplicationModelService;
    private DomListModel $domListModelService;
    private DitModel $ditModelService;
    private SessionManagerService $sessionManagerService;
    private ExcelService $excelServiceService;


    private ListeCdeFrnNonPlacerModel $listeCdeFrnNonPlacerModel;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;
    public function __construct(
        FusionPdf $fusionPdfService,
        ProfilModel $profilModelService,
        BadmModel $badmModelService,
        PersonnelModel $personnelModelService,
        DomModel $domModelService,
        DaModel $daModelService,
        DomDetailModel $domDetailModelService,
        DomDuplicationModel $domDuplicationModelService,
        DomListModel $domListModelService,
        DitModel $ditModelService,
        SessionManagerService $sessionManagerService,
        ExcelService $excelServiceService
    ) {
        parent::__construct();
        $this->fusionPdfService = $fusionPdfService;
        $this->profilModelService = $profilModelService;
        $this->badmModelService = $badmModelService;
        $this->personnelModelService = $personnelModelService;
        $this->domModelService = $domModelService;
        $this->daModelService = $daModelService;
        $this->domDetailModelService = $domDetailModelService;
        $this->domDuplicationModelService = $domDuplicationModelService;
        $this->domListModelService = $domListModelService;
        $this->ditModelService = $ditModelService;
        $this->sessionManagerService = $sessionManagerService;
        $this->excelServiceService = $excelServiceService;
    }

    /**
     * @Route("/lcfng/liste_cde_frs_non_placer_export_excel", name="liste_Cde_Frn_Non_placer_Export_Excel")
     *
     * @return void
     */
    public function exportExcel()
    {

        $this->verifierSessionUtilisateur();
        $today = new DateTime('now', new DateTimeZone('Indian/Antananarivo'));
        $vheure = $today->format("H:i:s");
        $vinstant = str_replace(":", "", $vheure);
        $criteria = $this->sessionManagerService->get('lcfnp_liste_cde_frs_non_placer');
        $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());
        $this->listeCdeFrnNonPlacerModel->viewHffCtrmarqVinstant($criteria, $vinstant);
        $data = $this->listeCdeFrnNonPlacerModel->requetteBase($criteria, $vinstant, $numOrValides);
        $this->listeCdeFrnNonPlacerModel->dropView($vinstant);
        // Convertir les entités en tableau de données

        $entities = $this->transformationEnTableauAvecEntiter($data);
        //creation du fichier excel
        $this->excelServiceService->createSpreadsheet($entities);
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
