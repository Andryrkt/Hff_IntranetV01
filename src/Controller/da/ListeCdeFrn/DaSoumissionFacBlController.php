<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Constants\da\ddp\BonApayerConstants;
use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\da\DemandeAppro;
use App\Entity\dw\DwBcAppro;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlFactory;
use App\Form\da\DaSoumissionFacBlType;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Model\da\DaModel;
use App\Model\da\DaSoumissionFacBlModel;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\dw\DwBcApproRepository;
use App\Service\da\CdeFrn\FacBl\TraitementSoumissionBAPService;
use App\Service\da\CdeFrn\FacBl\TraitementSoumissionDDPLService;
use App\Service\da\CdeFrn\FacBl\TraitementSoumissionfacBlService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdf;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;
use App\Service\historiqueOperation\HistoriqueOperationService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlController extends Controller
{
    use PdfConversionTrait;

    const STATUT_SOUMISSION = 'Soumis à validation';

    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private GeneratePdf $generatePdf;
    private DemandeApproRepository $demandeApproRepository;
    private DwBcApproRepository $dwBcApproRepository;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionFacBlModel $daSoumissionFacBlModel;
    private DaModel $daModel;
    private DaSoumissionFacBlFactory $daSoumissionFacBlFactory;
    private DaSoumissionFacBlMapper $daSoumissionfacBlMapper;

    public function __construct()
    {
        parent::__construct();

        $this->generatePdf                 = new GeneratePdf();
        $this->traitementDeFichier         = new TraitementDeFichier();
        $this->cheminDeBase                = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation         = new HistoriqueOperationDaBcService($this->getEntityManager());
        $this->daSoumissionFacBlRepository = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class);
        $this->demandeApproRepository      = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->dwBcApproRepository         = $this->getEntityManager()->getRepository(DwBcAppro::class);
        $this->daAfficherRepository        = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->daSoumissionFacBlModel      = new DaSoumissionFacBlModel();
        $this->daModel                     = new DaModel();
        $this->daSoumissionFacBlFactory    = new DaSoumissionFacBlFactory($this->getEntityManager());
        $this->daSoumissionfacBlMapper     = new DaSoumissionFacBlMapper();
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dto = $this->daSoumissionFacBlFactory->initialisation($numCde, $numDa, $numOr, $this->getUser());

        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlType::class, $dto, [
            'method'  => 'POST'
        ])->getForm();

        $this->traitementFormulaire($request, $form);

        return $this->render('da/soumissionFacBl.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto,
        ]);
    }

    /**
     * @Route("/check-num-liv-exists/{numLiv}", name="da_check_num_liv_exists")
     */
    public function checkNumLivExists(string $numLiv): JsonResponse
    {
        $this->verifierSessionUtilisateur();

        $exists = $this->daSoumissionFacBlRepository->findOneBy([
            'numLiv' => $numLiv,
            'statutBap' => BonApayerConstants::STATUT_A_TRANSMETTERE
        ]);

        return new JsonResponse(['exists' => ($exists !== null)]);
    }


    /**
     * permet de faire le rtraitement du formulaire
     *
     * @param Request $request
     * @param FormInterface $form
     * @param array $infosLivraison
     * 
     * @return void
     */
    private function traitementFormulaire(Request $request, FormInterface $form): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBlDto $dto */
            $dto = $form->getData();

            if ($dto->montantAregulariser === 0 || $dto->typeDdp === 'ddpl') {
                $traitementSoumissionDDPLService = new TraitementSoumissionDDPLService($this->getEntityManager());
                $sucess = $traitementSoumissionDDPLService->traitementSoumissionDDPL($form, $dto);
            } elseif ($dto->typeDdp === 'bap') {
                $traitementSoumissionBAPService = new TraitementSoumissionBAPService($this->getEntityManager());
                $sucess = $traitementSoumissionBAPService->traitementSoumissionBAP($form, $dto, $this->getUserMail());
            } else {
                $traitementSoumissionfacBlService = new TraitementSoumissionfacBlService($this->getEntityManager());
                $sucess = $traitementSoumissionfacBlService->traitementSoumissionFacBl($form, $dto);
            }

            if ($sucess) {
                /** HISTORISATION */
                $message = 'Le document est soumis pour validation';
                $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
                $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
                $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
                $this->historiqueOperation->sendNotificationSoumission($message, $dto->numeroCde, $nomDeRoute, true, $criteria, $nomInputSearch);
            }
        }
    }
}
