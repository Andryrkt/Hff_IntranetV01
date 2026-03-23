<?php


namespace App\Controller\pol\ors\Traiter;

// ini_set('max_execution_time', 10000);

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Controller\Traits\Transformation;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pol/or-pol")
 */
class OrTraiterController extends Controller
{
    use Transformation;
    use MagasinOrATraiterTrait;
    /**
     * @Route("/listes-or-a-traiter", name="pol_or_liste_a_traiter")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_POL);

        $codeAgence = array_column($agenceServiceAutorises, 'agence_code');

        $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET',
            'est_pneumatique' => true
        ])->getForm();

        //traitement du formulaire et recupération des data
        $data = $this->traitementFormualire($form, $request, $agenceUser);

        $this->logUserVisit('magasinListe_index'); // historisation du page visité par l'utilisateur

        return $this->render('pol/ors/listOrATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }

    private function traitementFormualire(FormInterface $form, Request $request, string $agenceUser): array
    {
        $form->handleRequest($request);

        $criteria = [
            "agenceUser" => $agenceUser
        ];
        if ($form->isSubmitted() && $form->isValid()) {

            // recupération des données du formulaire
            $criteria = $form->getData();
        }
        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_liste_or_traiter_search_criteria', $criteria);

        //recupération des données
        return $this->recupData($criteria, new MagasinListeOrATraiterModel());
    }
}
