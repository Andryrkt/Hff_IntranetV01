<?php

namespace App\Controller\magasin\cis\Livrer;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Model\magasin\cis\CisALivrerModel;
use App\Form\magasin\cis\ALivrerSearchtype;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;
use App\Entity\admin\utilisateur\Role;

/**
 * @Route("/magasin/cis")
 */
class CisALivrerController extends Controller
{
    use ALivrerTrait;
    use AutorisationTrait;

    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_MAG);
        /** FIN AUtorisation acées */

        /** CREATION D'AUTORISATION */
        $autoriser = $this->hasRoles(Role::ROLE_ADMINISTRATEUR, Role::ROLE_MULTI_SUCURSALES);
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orValide" => true,
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $data = $this->recupData($criteria);

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('cis_a_Livrer_search_criteria', $criteria);

        $this->logUserVisit('cis_liste_a_livrer'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/cis/listALivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }
}
