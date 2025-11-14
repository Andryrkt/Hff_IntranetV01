<?php


namespace App\Controller\magasin\ors\Livrer;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');


use App\Model\dit\DitModel;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\MagasinListeOrALivrerSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrALivrerTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;

/**
 * @Route("/magasin/or")
 */
class OrLivrerController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrALivrerTrait;
    use AutorisationTrait;

    /**
     * @Route("/liste-or-livrer", name="magasinListe_or_Livrer")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $codeAgence = $this->getUser()->getAgenceAutoriserCode();
        $serviceAgence = $this->getUser()->getServiceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrALivrerSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
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
