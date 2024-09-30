<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\ConversionTrait;
use App\Entity\dom\Dom;
use App\Form\dom\DomSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DomsListeController extends Controller
{

    use ConversionTrait;

    /**
     * affichage de l'architecture de la liste du DOM
     * @Route("/dom-liste", name="doms_liste")
     */
    public function listeDom(Request $request)
    {
        $autoriser = $this->autorisationRole(self::$em);

        $form = self::$validator->createBuilder(DomSearchType::class, null , [
            'method' => 'GET',
        ])->getForm();


        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $option = [
            'boolean' => $autoriser,
        ];
        $repository= self::$em->getRepository(Dom::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit,[], $option);



        self::$twig->display(
            'doms/list.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems']
            ]
        );
    }


    private function autorisationRole($em): bool
{
    /** CREATION D'AUTORISATION */
    $userId = $this->sessionService->get('user_id');
    $userConnecter = $em->getRepository(User::class)->find($userId);
    $roleIds = $userConnecter->getRoleIds();
    return in_array(1, $roleIds);
    //FIN AUTORISATION
}


 
}
