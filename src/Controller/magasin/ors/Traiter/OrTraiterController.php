<?php


namespace App\Controller\magasin\ors\Traiter;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;
use App\Model\dit\DitModel;

/**
 * @Route("/magasin/or")
 */
class OrTraiterController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrATraiterTrait;
    use AutorisationTrait;

    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        
        $codeAgence = $this->getUser()->getAgenceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
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
