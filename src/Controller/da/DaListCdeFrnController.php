<?php

namespace App\Controller\da;


use App\Entity\da\DaValider;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Model\da\DaListeCdeFrnModel;
use App\Service\TableauEnStringService;
use App\Form\da\daCdeFrn\CdeFrnListType;
use Symfony\Component\Form\FormInterface;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Repository\da\DaValiderRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrLivrerModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends Controller
{
    private DaValiderRepository $daValiderRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaListeCdeFrnModel $daListeCdeFrnModel;

    public function __construct()
    {
        parent::__construct();
        $this->daValiderRepository = self::$em->getRepository(DaValider::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
    }

    /**
     * @Route("/da-list-cde-frn", name ="da_list_cde_frn" )
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();

        /** ===  Formulaire pour la recherche === */
        $form = self::$validator->createBuilder(CdeFrnListType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $criteria = $this->traitementFormulaireRecherche($request, $form);

        /** ==== récupération des données à afficher ==== */
        $daValides = $this->donnerAfficher($criteria);

        /** === Formulaire pour l'envoie de BC et FAC + Bl === */
        $formSoumission = self::$validator->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        self::$twig->display('da/daListCdeFrn.html.twig', [
            'daValides' => $daValides,
            'formSoumission' => $formSoumission->createView(),
            'form' => $form->createView(),
        ]);
    }

    private function donnerAfficher(?array $criteria): array
    {
        /** récupération des ors Zst validé sous forme de tableau */
        $numOrValide = $this->ditOrsSoumisAValidationRepository->findNumOrValide();
        $numOrString = TableauEnStringService::TableauEnString(',', $numOrValide);
        $numOrValideZst = $this->daListeCdeFrnModel->getNumOrValideZst($numOrString);

        /** @var array récupération des lignes de daValider avec version max et or valider */
        $daValiders =  $this->daValiderRepository->getDaOrValider($numOrValideZst, $criteria);

        return $daValiders;
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
