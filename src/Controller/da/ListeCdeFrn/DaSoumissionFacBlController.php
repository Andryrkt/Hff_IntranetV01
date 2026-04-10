<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Constants\da\ddp\BonApayerConstants;
use App\Controller\Controller;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
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

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $daSoumissionFacBlFactory = new DaSoumissionFacBlFactory($this->getEntityManager());
        $dto = $daSoumissionFacBlFactory->initialisation($numCde, $numDa, $numOr, $this->getUser());

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

            if ($dto->typeDdp === 'regul') {
                $traitementSoumissionDDPLService = new TraitementSoumissionDDPLService($this->getEntityManager());
                $sucess = $traitementSoumissionDDPLService->traitementSoumissionDDPL($form, $dto);
            } elseif ($dto->typeDdp === 'ddpl') {
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
                $historiqueOperation = new HistoriqueOperationDaBcService($this->getEntityManager());
                $historiqueOperation->sendNotificationSoumission($message, $dto->numeroCde, $nomDeRoute, true, $criteria, $nomInputSearch);
            }
        }
    }
}
