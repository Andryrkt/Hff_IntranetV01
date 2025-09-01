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



use App\Model\da\DaModel;;

use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Model\da\DaListeCdeFrnModel;
use App\Service\TableauEnStringService;
use App\Form\da\daCdeFrn\CdeFrnListType;
use Symfony\Component\Form\FormInterface;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Controller\Traits\da\StatutBcTrait;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Controller\Traits\AutorisationTrait;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Controller\BaseController;


/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends BaseController
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

    use StatutBcTrait;
    use AutorisationTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DemandeApproRepository $demandeApproRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;


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
     * @Route("/da-list-cde-frn", name ="da_list_cde_frn" )
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP, Service::ID_APPRO);
        /** FIN AUtorisation acées */

        /** ===  Formulaire pour la recherche === */
        $form = $this->getFormFactory()->createBuilder(CdeFrnListType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $criteria = $this->traitementFormulaireRecherche($request, $form);
        $this->sessionManagerService->set('criteria_for_excel_Da_Cde_frn', $criteria);

        /** ==== récupération des données à afficher ==== */
        $daAfficherValides = $this->donnerAfficher($criteria);

        /** mise à jour des donners daAfficher */
        $this->quelqueMiseAjourDaAfficher($daAfficherValides);

        /** === Formulaire pour l'envoie de BC et FAC + Bl === */
        $formSoumission = $this->getFormFactory()->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        return $this->render('da/daListCdeFrn.html.twig', [
            'daAfficherValides' => $daAfficherValides,
            'formSoumission' => $formSoumission->createView(),
            'form' => $form->createView(),
        ]);
    }

    private function quelqueMiseAjourDaAfficher(array $daAfficherValides)
    {
        foreach ($daAfficherValides as $davalide) {
            $this->modificationStatutBC($davalide);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Cette methode permet de modifier le statut du BC
     *
     * @return void
     */
    private function modificationStatutBC(DaAfficher $data)
    {
        $statutBC = $this->statutBc($data->getArtRefp(), $data->getNumeroDemandeDit(), $data->getNumeroDemandeAppro(), $data->getArtDesi(), $data->getNumeroOr());
        $data->setStatutCde($statutBC);
        $this->getEntityManager()->persist($data);
    }

    private function donnerAfficher(?array $criteria): array
    {
        /** @var array récupération des lignes de daValider avec version max et or valider */
        $daAfficherValiders =  $this->daAfficherRepository->getDaOrValider($criteria);

        return $daAfficherValiders;
    }

    private function traitementFormulaireSoumission(Request $request, $formSoumission): void
    {
        $formSoumission->handleRequest($request);

        if ($formSoumission->isSubmitted() && $formSoumission->isValid()) {
            $soumission = $formSoumission->getData();

            if ($soumission['soumission'] === true) {
                $this->redirectToRoute("da_soumission_bc", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']]);
            } else {
                $this->redirectToRoute("da_soumission_facbl", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']]);
            }
        }
    }

    private function traitementFormulaireRecherche(Request $request, FormInterface $form): ?array
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }

        $data = $form->getData();

        // Filtrer les champs vides ou nuls
        $dataFiltrée = array_filter($data, fn($val) => $val !== null && $val !== '');

        return empty($dataFiltrée) ? null : $data;
    }
}
