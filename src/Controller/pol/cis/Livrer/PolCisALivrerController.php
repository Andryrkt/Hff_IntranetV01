<?php

namespace App\Controller\pol\cis\Livrer;


use App\Controller\Controller;
use App\Entity\admin\Application;
use Symfony\Component\Form\FormInterface;
use App\Form\magasin\cis\ALivrerSearchtype;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;
use App\Entity\admin\utilisateur\Role;

/**
 * @Route("/pol/cis-pol")
 */
class PolCisALivrerController extends Controller
{
    use ALivrerTrait;
    use AutorisationTrait;

    /**
     * @Route("/cis-liste-a-livrer", name="pol_cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        /** CREATION D'AUTORISATION */
        $autoriser = $this->hasRoles(Role::ROLE_ADMINISTRATEUR, Role::ROLE_MULTI_SUCURSALES);
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET',
            'est_pneumatique' => true
        ])->getForm();

        //traitement du formulaire et recupération des data
        $data = $this->traitementFormualire($form, $request, $agenceUser);

        $this->logUserVisit('cis_liste_a_livrer'); // historisation du page visité par l'utilisateur

        return $this->render('pol/cis/listALivrer.html.twig', [
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
            "orValide" => true,
        ];
        if ($form->isSubmitted() && $form->isValid()) {

            // recupération des données du formulaire
            $criteria = $form->getData();
        }
        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_cis_a_Livrer_search_criteria', $criteria);

        //recupération des données
        return $this->recupData($criteria);
    }
}
