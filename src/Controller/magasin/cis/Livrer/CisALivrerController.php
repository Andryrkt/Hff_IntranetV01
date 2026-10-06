<?php

namespace App\Controller\magasin\cis\Livrer;

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Controller\Traits\magasin\cis\ALivrerTrait;
use App\Form\magasin\cis\ALivrerSearchtype;
use App\Service\security\SecurityService;
use App\Service\TableauEnStringService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/cis")
 */
class CisALivrerController extends Controller
{
    use ALivrerTrait;
    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        $agenceUser = "''";

        // Vérifier la permission de voir tous les données
        $multisuccursale = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);

        if (!$multisuccursale) {
            $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_MAGASIN);

            // Si l'utilisateur n'a pas d'agence et service autorisé, on prend son agence par défaut
            $codeAgence = empty($agenceServiceAutorises) ? [$this->getSecurityService()->getCodeAgenceUser()] : array_column($agenceServiceAutorises, 'agence_code');

            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser], [
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
