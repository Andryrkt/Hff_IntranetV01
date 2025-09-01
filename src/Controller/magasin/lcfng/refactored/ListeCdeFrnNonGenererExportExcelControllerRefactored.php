<?php

namespace App\Controller\magasin\lcfng;
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
use App\Model\magasin\lcfng\ListeCdeFrnNonGenererModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Controller\BaseController;

/**
 * @Route("/magasin")
 */
class ListeCdeFrnNonGenererExportExcelController extends BaseController
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

    private ListeCdeFrnNonGenererModel $listeCdeFrnNonGenererModel;
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
     * @Route("/lcfng/liste_cde_frs_non_generer_export_excel", name="liste_Cde_Frn_Non_Generer_Export_Excel")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->sessionManagerService->get('lcfng_liste_cde_frs_non_generer');

        // récupération des OR valide dans Ors_soumis_a_validation
        $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());

        $data = $this->listeCdeFrnNonGenererModel->getListeCdeFrnNonGenerer($criteria, $numOrValides);

        // Convertir les entités en tableau de données
        $entities = $this->transformationEnTableauAvecEntiter($data);

        //creation du fichier excel
        $this->excelServiceService->createSpreadsheet($entities);
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
