<?php

namespace App\Controller\Pol\cis\Traiter;


use App\Controller\Controller;
use App\Entity\admin\Application;
use Symfony\Component\Form\FormInterface;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;

/**
 * @Route("/pol/cis-pol")
 */
class PolCisATraiterController extends Controller
{
    use AtraiterTrait;
    use AutorisationTrait;

    /**
     * @Route("/cis-liste-a-traiter", name="pol_cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET',
            'est_pneumatique' => true
        ])->getForm();

        //traitement du formulaire et recupération des data
        $data = $this->traitementFormulaire($form, $request, $agenceUser);

        $this->logUserVisit('cis_liste_a_traiter'); // historisation du page visité par l'utilisateur

        return $this->render('pol/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request, string $agenceUser): array
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
        $this->getSessionService()->set('pol_cis_a_traiter_search_criteria', $criteria);

        //recupération des données
        return $this->recupData($criteria);
    }

}
