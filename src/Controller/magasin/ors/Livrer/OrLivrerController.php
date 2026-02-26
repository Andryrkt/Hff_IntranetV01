<?php


namespace App\Controller\magasin\ors\Livrer;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\magasin\MagasinListeOrALivrerSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrALivrerTrait;
/**
 * @Route("/magasin/or")
 */
class OrLivrerController extends Controller
{
    use Transformation;
    use MagasinOrALivrerTrait;
    /**
     * @Route("/liste-or-livrer", name="magasinListe_or_Livrer")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
    {
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_MAGASIN);

        $codeAgence = array_column($agenceServiceAutorises, 'agence_code');

        $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrALivrerSearchType::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orCompletNon" => "ORs COMPLET",
            "pieces" => "PIECES MAGASIN"
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('magasin_liste_or_livrer_search_criteria', $criteria);

        $data = $this->recupData($criteria);

        $this->logUserVisit('magasinListe_or_Livrer'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/ors/listOrLivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }
}
