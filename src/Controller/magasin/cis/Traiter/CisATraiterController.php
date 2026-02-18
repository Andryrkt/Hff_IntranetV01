<?php

namespace App\Controller\magasin\cis\Traiter;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;

/**
 * @Route("/magasin/cis")
 */
class CisATraiterController extends Controller
{
    use AtraiterTrait;
    use AutorisationTrait;

    /**
     * @Route("/cis-liste-a-traiter", name="cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_MAG);
        /** FIN AUtorisation acées */

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
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
