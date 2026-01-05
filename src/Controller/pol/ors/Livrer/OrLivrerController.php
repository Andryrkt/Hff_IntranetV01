<?php


namespace App\Controller\pol\ors\Livrer;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Role;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use Symfony\Component\Form\FormInterface;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\MagasinListeOrALivrerSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrALivrerTrait;

/**
 * @Route("/pol/ors-pol")
 */
class OrLivrerController extends Controller
{
    use Transformation;
    use MagasinOrALivrerTrait;
    use AutorisationTrait;

    /**
     * @Route("/liste-or-livrer", name="pol_or_liste_a_livrer")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
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

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrALivrerSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET',
            'est_pneumatique' => true
        ])->getForm();

        //traitement du formulaire et recupération des data
        $data = $this->traitementFormualire($form, $request, $agenceUser);

        $this->logUserVisit('magasinListe_or_Livrer'); // historisation du page visité par l'utilisateur

        return $this->render('pol/ors/listOrLivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }

    private function traitementFormualire(FormInterface $form, Request $request, string $agenceUser): array
    {
        $form->handleRequest($request);

        $criteria = [
            "agenceUser" => $agenceUser,
            "orCompletNon" => "ORs COMPLET",
            "pieces" => "PIECES MAGASIN"
        ];
        if ($form->isSubmitted() && $form->isValid()) {

            // recupération des données du formulaire
            $criteria = $form->getData();
        }
        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_liste_or_livrer_search_criteria', $criteria);

        //recupération des données
        return $this->recupData($criteria);
    }
}
