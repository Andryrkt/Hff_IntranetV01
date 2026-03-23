<?php


namespace App\Controller\magasin\ors\Traiter;

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Controller\Traits\Transformation;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Service\TableauEnStringService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/or")
 */
class OrTraiterController extends Controller
{
    use Transformation;
    use MagasinOrATraiterTrait;
    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_MAGASIN);

        $codeAgence = array_column($agenceServiceAutorises, 'agence_code');

        $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('magasin_liste_or_traiter_search_criteria', $criteria);

        $data = $this->recupData($criteria);

        $this->logUserVisit('magasinListe_index'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/ors/listOrATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }
}
