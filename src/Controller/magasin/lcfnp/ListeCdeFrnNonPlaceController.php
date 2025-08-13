<?php

namespace App\Controller\magasin\lcfnp;

use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\lcfnp\ListeCdeFrnNonPlaceSearchType;
use App\Model\magasin\lcfnp\listeCdeFrnNonPlacerModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use DateTime;
use DateTimeZone;

/**
 * @Route("/magasin")
 */
class ListeCdeFrnNonPlaceController extends  Controller
{
    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;
    private listeCdeFrnNonPlacerModel $listeCdeFrnNonPlacerModel;
    public function __construct()
    {
        parent::__construct();

        $this->listeCdeFrnNonPlacerModel = new listeCdeFrnNonPlacerModel();
        $this->ditOrsSoumisRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
    }
    /**
     * @Route("/lcfnp/liste_cde_frs_non_placer", name="liste_Cde_Frn_Non_Placer")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $form = self::$validator->createBuilder(ListeCdeFrnNonPlaceSearchType::class, [], [
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
            $this->sessionService->set('lcfnp_liste_cde_frs_non_placer', $criteria);

            $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());
            $this->listeCdeFrnNonPlacerModel->viewHffCtrmarqVinstant($criteria, $vinstant);
            $data = $this->listeCdeFrnNonPlacerModel->requetteBase($criteria, $vinstant, $numOrValides);
            $this->listeCdeFrnNonPlacerModel->dropView($vinstant);
        }
        self::$twig->display('magasin/lcfnp/listCdeFnrNonPlacer.html.twig', [
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
