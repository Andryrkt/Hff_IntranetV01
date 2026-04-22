<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Constants\ddp\StatutConstants;
use App\Controller\Controller;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DaSoumissionFacBl;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlFactory;
use App\Form\da\DaSoumissionFacBlType;
use App\Service\da\CdeFrn\FacBl\TraitementSoumissionBAPService;
use App\Service\da\CdeFrn\FacBl\TraitementSoumissionDDPLService;
use App\Service\da\CdeFrn\FacBl\TraitementSoumissionfacBlService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlController extends Controller
{

    private DaSoumissionFacBlFactory $daSoumissionFacBlFactory;
    private TraitementSoumissionDDPLService $traitementSoumissionDDPLService;
    private TraitementSoumissionBAPService $traitementSoumissionBAPService;
    private TraitementSoumissionfacBlService $traitementSoumissionfacBlService;

    public function __construct(
        DaSoumissionFacBlFactory $daSoumissionFacBlFactory,
        TraitementSoumissionDDPLService $traitementSoumissionDDPLService,
        TraitementSoumissionBAPService $traitementSoumissionBAPService,
        TraitementSoumissionfacBlService $traitementSoumissionfacBlService
    ) {
        $this->daSoumissionFacBlFactory = $daSoumissionFacBlFactory;
        $this->traitementSoumissionDDPLService = $traitementSoumissionDDPLService;
        $this->traitementSoumissionBAPService = $traitementSoumissionBAPService;
        $this->traitementSoumissionfacBlService = $traitementSoumissionfacBlService;
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(
        string $numCde,
        string $numDa,
        string $numOr,
        Request $request
    ) {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

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

        $exists = $this->em->getRepository(DaSoumissionFacBl::class)->findOneBy([
            'numLiv' => $numLiv,
            'statutBap' => StatutConstants::BAP_A_TRANSMETTRE
        ]);

        return new JsonResponse(['exists' => ($exists !== null)]);
    }


    /**
     * permet de faire le rtraitement du formulaire
     */
    private function traitementFormulaire(
        Request $request,
        FormInterface $form
    ): void {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBlDto $dto */
            $dto = $form->getData();

            if ($dto->typeDdp === 'regul' || $dto->typeDdp === 'ddpl') {
                $sucess = $this->traitementSoumissionDDPLService->traitementSoumissionDDPL($form, $dto);
            } elseif ($dto->typeDdp === 'bap') {
                $sucess = $this->traitementSoumissionBAPService->traitementSoumissionBAP($form, $dto, $this->getUserMail());
            } else {
                $sucess = $this->traitementSoumissionfacBlService->traitementSoumissionFacBl($form, $dto);
            }

            if ($sucess) {
                /** HISTORISATION */
                $message = 'Le document est soumis pour validation';
                $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
                $nomDeRoute = $dto->typeDdp === 'aucun' ? 'da_list_cde_frn' : 'da_bon_a_payer';
                $nomInputSearch = 'cde_frn_list';

                // Ici aussi on pourrait injecter HistoriqueOperationDaBcService
                $historiqueOperation = new HistoriqueOperationDaBcService($this->getEntityManager());
                $historiqueOperation->sendNotificationSoumission($message, $dto->numeroCde, $nomDeRoute, true, $criteria, $nomInputSearch);
            }
        }
    }
}
