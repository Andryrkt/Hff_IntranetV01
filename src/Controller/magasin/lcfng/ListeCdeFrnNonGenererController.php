<?php

namespace App\Controller\magasin\lcfng;

use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfng\ListeCdeFrnNonGenererModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Form\magasin\lcfng\ListeCdeFrnNonGenererSearchType;

class ListeCdeFrnNonGenererController extends Controller
{

    private ListeCdeFrnNonGenererModel $listeCdeFrnNonGenererModel;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisRepository;

    public function __construct()
    {
        parent::__construct();
        
        $this->listeCdeFrnNonGenererModel = new ListeCdeFrnNonGenererModel();
        $this->ditOrsSoumisRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
    }

    /**
     * @Route("/magasin/lcfng/liste_cde_frs_non_generer", name="liste_Cde_Frn_Non_Generer")
     *
     * @return void
     */
    public function index(Request $request)
    {

        $form = self::$validator->createBuilder(ListeCdeFrnNonGenererSearchType::class, ['agenceEmetteur' => '01-ANTANANARIVO'], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            'orValide' => true
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        
        $this->sessionService->set('lcfng_liste_cde_frs_non_generer', $criteria);

        $numOrValides = $this->orEnString($this->ditOrsSoumisRepository->findNumOrValide());
        
        $data = $this->listeCdeFrnNonGenererModel->getListeCdeFrnNonGenerer($criteria, $numOrValides);

        self::$twig->display('magasin/lcfng/listCdeFnrNonGenerer.html.twig', [
            'data' => $data,
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