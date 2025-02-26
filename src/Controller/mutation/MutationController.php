<?php

namespace App\Controller\mutation;

use App\Controller\Controller;
use App\Controller\Traits\MutationTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\utilisateur\User;
use App\Entity\mutation\Mutation;
use App\Form\mutation\MutationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MutationController extends Controller
{
    use MutationTrait;

    /**
     * @Route("/mutation/new", name="mutation_nouvelle_demande")
     */
    public function nouveau(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recuperation de l'utilisateur connecter
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        $mutation = new Mutation;
        $this->initialisationMutation($mutation, self::$em);

        $form = self::$validator->createBuilder(MutationFormType::class, $mutation)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->enregistrementValeurDansMutation($form, self::$em, $user);
            $this->genererEtEnvoyerPdf($form, $user);
            $this->redirectToRoute("mutation_liste");
        }

        self::$twig->display('mutation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mutation/list", name="mutation_liste")
     */
    public function listeDom(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // $autoriser = $this->autorisationRole(self::$em);

        // $domSearch = new DomSearch();

        // $agenceServiceIps = $this->agenceServiceIpsObjet();
        // /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        // $this->initialisation($domSearch, self::$em, $agenceServiceIps, $autoriser);

        // $form = self::$validator->createBuilder(DomSearchType::class, $domSearch, [
        //     'method' => 'GET',
        //     'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId()
        // ])->getForm();

        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $domSearch = $form->getData();
        // }

        // $criteria = [];
        // //transformer l'objet ditSearch en tableau
        // $criteria = $domSearch->toArray();

        // $page = max(1, $request->query->getInt('page', 1));
        // $limit = 10;

        // $option = [
        //     'boolean' => $autoriser,
        //     'idAgence' => $this->agenceIdAutoriser(self::$em)
        // ];

        // $repository = self::$em->getRepository(Dom::class);
        // $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $domSearch, $option);

        // //enregistre le critère dans la session
        // $this->sessionService->set('dom_search_criteria', $criteria);
        // $this->sessionService->set('dom_search_option', $option);

        // $this->logUserVisit('doms_liste'); // historisation du page visité par l'utilisateur

        // self::$twig->display(
        //     'doms/list.html.twig',
        //     [
        //         'form' => $form->createView(),
        //         'data' => $paginationData['data'],
        //         'currentPage' => $paginationData['currentPage'],
        //         'lastPage' => $paginationData['lastPage'],
        //         'resultat' => $paginationData['totalItems'],
        //         'criteria' => $criteria,
        //     ]
        // );
    }
}
