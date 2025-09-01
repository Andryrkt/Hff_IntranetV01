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


use DateTime;
use DateTimeZone;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfnp\listeCdeFrnNonPlacerModel;
use App\Form\magasin\lcfnp\ListeCdeFrnNonPlaceSearchType;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @Route("/magasin")
 */
class ListeCdeFrnNonPlaceController extends  Controller
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

    use AutorisationTrait;

    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;
    private listeCdeFrnNonPlacerModel $listeCdeFrnNonPlacerModel;
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
     * @Route("/liste-commande-fournisseur-non-placer", name="liste_Cde_Frn_Non_Placer")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_LCF);
        /** FIN AUtorisation acées */

        $form = $this->getFormFactory()->createBuilder(ListeCdeFrnNonPlaceSearchType::class, [], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            'orValide' => true
        ];
        $data = [];
        $today = new DateTime('now', new DateTimeZone('Indian/Antananarivo'));
        $vheure = $today->format("H:i:s");
        $vinstant = str_replace(":", "", $vheure);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            // dd($criteria);
            $this->sessionManagerService->set('lcfnp_liste_cde_frs_non_placer', $criteria);

            $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());
            $this->listeCdeFrnNonPlacerModel->viewHffCtrmarqVinstant($criteria, $vinstant);
            $data = $this->listeCdeFrnNonPlacerModel->requetteBase($criteria, $vinstant, $numOrValides);
            $this->listeCdeFrnNonPlacerModel->dropView($vinstant);
        }
        $this->getTwig()->render('magasin/lcfnp/listCdeFnrNonPlacer.html.twig', [
            'form' => $form->createView(),
            'data' => $data,
        ]);
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
