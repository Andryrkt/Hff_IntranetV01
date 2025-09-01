<?php

namespace App\Controller\da\ListeCdeFrn;
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



use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class ExportExcelCdefrnController extends BaseController
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

    private DaAfficherRepository $daAfficherRepository;

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
     * @Route("/export-excel/list-cde-frn", name="da_export_excel_list_cde_frn")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->sessionManagerService->get('criteria_for_excel_Da_Cde_frn');

        // recupération des données de la DA
        $dasFiltered = $this->donnerAfficher($criteria);

        $data = [];
        // En-tête du tableau d'excel
        $data[] = [
            "N° DA",
            "Achat direct",
            "N° DIT",
            "Niv. Urgence",
            "N° OR",
            "Date planning OR",
            "N° Fournisseur",
            "Fournisseur",
            "N° Commande",
            "Statut BC",
            "Date Fin souhaité",
            "Réference",
            "Désignation",
            "Qté dem",
            "Qté en attente",
            "Qté Dispo (Qté à livrer)",
            "Qté livrée",
            "Date livraison prévue",
            "Nbr Jour(s) dispo",
            "Demandeur"
        ];

        // Convertir les entités en tableau de données
        $data = $this->convertirObjetEnTableau($dasFiltered, $data);

        // Crée le fichier Excel
        $this->excelServiceService->createSpreadsheet($data, "donnees_" . date('YmdHis'));
    }


    /** 
     * Convertis les données d'objet en tableau
     * 
     * @param array $dasFiltered tableau d'objets à convertir
     * @param array $data tableau de retour
     * 
     * @return array
     */
    private function convertirObjetEnTableau(array $dasFiltered, array $data): array
    {
        /** @var DaAfficher */
        foreach ($dasFiltered as $da) {
            $data[] = [
                $da->getNumeroDemandeAppro(),
                $da->getAchatDirect() ? 'OUI' : 'NON',
                $da->getNumeroDemandeDit(),
                $da->getNiveauUrgence(),
                $da->getNumeroOR() ?? '-',
                $da->getDatePlannigOr(),
                $da->getNumeroFournisseur(),
                $da->getNomFournisseur(),
                $da->getNumeroCde(),
                $da->getStatutCde(),
                $da->getDateFinSouhaite()->format('d/m/Y'),
                $da->getArtRefp(),
                $da->getArtDesi(),
                $da->getQteDem(),
                $da->getQteEnAttent() == 0 ? '-' : $da->getQteEnAttent(),
                $da->getQteDispo() == 0 ? '-' : $da->getQteDispo(),
                $da->getQteLivrer() == 0 ? '-' : $da->getQteLivrer(),
                $da->getDateLivraisonPrevue() == null ? '' : $da->getDateLivraisonPrevue()->format('d/m/Y'),
                $da->getJoursDispo(),
                $da->getDemandeur()
            ];
        }

        return $data;
    }

    private function donnerAfficher(?array $criteria): array
    {
        /** @var array récupération des lignes de daValider avec version max et or valider */
        $daAfficherValiders =  $this->daAfficherRepository->getDaOrValider($criteria);

        return $daAfficherValiders;
    }
}
