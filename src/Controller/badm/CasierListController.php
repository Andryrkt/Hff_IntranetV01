<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use App\Entity\cas\CasierValider;
use App\Form\cas\CasierSearchType;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel/casier")
 */
class CasierListController extends Controller
{

    use Transformation;
    use AutorisationTrait;

    /**
     * @Route("/liste", name="liste_affichageListeCasier")
     */
    public function AffichageListeCasier(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_CHANGEMENT_CASIER);
        /** FIN AUtorisation acées */

        $form = self::$validator->createBuilder(CasierSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $paginationData = self::$em->getRepository(CasierValider::class)->findPaginatedAndFiltered($page, $limit, $criteria);

        // dd($paginationData['data']);

        if (empty($paginationData['data'])) {
            $empty = true;
        }

        $this->logUserVisit('liste_affichageListeCasier'); // historisation du page visité par l'utilisateur

        self::$twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'casier' => $paginationData['data'],
                'form' => $form->createView(),
                'criteria' => $criteria,
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'empty' => $empty,
            ]
        );
    }
}
