<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Model\da\DaModel;;

use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Model\da\DaListeCdeFrnModel;
use App\Service\TableauEnStringService;
use App\Form\da\daCdeFrn\CdeFrnListType;
use Symfony\Component\Form\FormInterface;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Controller\Traits\da\StatutBcTrait;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;


/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends Controller
{
    use StatutBcTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DemandeApproRepository $daRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;


    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = self::$em->getRepository(DaAfficher::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
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
        $daAfficherValides = $this->donnerAfficher($criteria);

        /** === Formulaire pour l'envoie de BC et FAC + Bl === */
        $formSoumission = self::$validator->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        /** mise à jour des donners daAfficher */
        foreach ($daAfficherValides as $davalide) {
            $statutBC = $this->statutBc($davalide->getArtRefp(), $davalide->getNumeroDemandeDit(), $davalide->getNumeroDemandeAppro(), $davalide->getArtDesi(), $davalide->getNumeroOr());
            $davalide->setStatutCde($statutBC);
        }


        self::$twig->display('da/daListCdeFrn.html.twig', [
            'daAfficherValides' => $daAfficherValides,
            'formSoumission' => $formSoumission->createView(),
            'form' => $form->createView(),
        ]);
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
