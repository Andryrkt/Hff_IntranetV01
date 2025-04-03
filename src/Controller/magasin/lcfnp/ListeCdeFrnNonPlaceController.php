<?php

namespace App\Controller\magasin\lcfnp;

use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\lcfnp\ListeCdeFrnNonPlaceSearchType;
use App\Repository\dit\DitOrsSoumisAValidationRepository;


class ListeCdeFrnNonPlaceController extends  Controller{
    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;

    public function __construct()
    {
        parent::__construct();
        $this->ditOrsSoumisRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
    }
     /**
     * @Route("/magasin/lcfnp/liste_cde_frs_non_placer", name="liste_Cde_Frn_Non_Placer")
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
        $this->sessionService->set('lcfng_liste_cde_frs_non_generer', $criteria);
        $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());

        
        self::$twig->display('magasin/lcfnp/listCdeFnrNonPlacer.html.twig', [
            'form' => $form->createView(),
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
            if(is_array($values)){
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