<?php

namespace App\Controller\magasin\cis\Traiter;

use App\Controller\Controller;
use App\Service\TableauEnStringService;
use App\Constants\admin\ApplicationConstant;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Traits\magasin\cis\AtraiterTrait;

/**
 * @Route("/magasin/cis")
 */
class CisATraiterController extends Controller
{
    use AtraiterTrait;

    /**
     * @Route("/cis-liste-a-traiter", name="cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_MAGASIN);

        $codeAgence = array_column($agenceServiceAutorises, 'agence_code');

        $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);

        $form = $this->getFormFactory()->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            'orValide' => true
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $data = $this->recupData($criteria);

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('cis_a_traiter_search_criteria', $criteria);

        $this->logUserVisit('cis_liste_a_traiter'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }
}
