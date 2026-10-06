<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\cas\CasierValider;
use App\Form\cas\CasierSearchType;
use App\Controller\Traits\Transformation;
use App\Repository\cas\CasierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel/casier")
 */
class CasierListController extends Controller
{

    use Transformation;
    /**
     * @Route("/liste", name="liste_affichageListeCasier")
     */
    public function AffichageListeCasier(Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $form = $this->getFormFactory()->createBuilder(CasierSearchType::class, null, [
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

        /** @var CasierRepository $repository */
        $repository = $this->getEntityManager()->getRepository(CasierValider::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $criteria, $codeSociete);

        // dd($paginationData['data']);

        if (empty($paginationData['data'])) {
            $empty = true;
        }

        $this->logUserVisit('liste_affichageListeCasier'); // historisation du page visité par l'utilisateur

        return $this->render(
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
