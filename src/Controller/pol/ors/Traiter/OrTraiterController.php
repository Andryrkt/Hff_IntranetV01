<?php


namespace App\Controller\pol\ors\Traiter;

// ini_set('max_execution_time', 10000);

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Role;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use Symfony\Component\Form\FormInterface;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;

/**
 * @Route("/pol/or-pol")
 */
class OrTraiterController extends Controller
{
    use Transformation;
    use MagasinOrATraiterTrait;
    use AutorisationTrait;

    /**
     * @Route("/listes-or-a-traiter", name="pol_or_liste_a_traiter")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $codeAgence = $this->getUser()->getAgenceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->hasRoles(Role::ROLE_ADMINISTRATEUR, Role::ROLE_MULTI_SUCURSALES);
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
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
