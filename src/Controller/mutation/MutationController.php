<?php

namespace App\Controller\mutation;

use App\Controller\Controller;
use App\Controller\Traits\MutationTrait;
use App\Entity\admin\utilisateur\User;
use App\Entity\mutation\Mutation;
use App\Entity\mutation\MutationSearch;
use App\Form\mutation\MutationFormType;
use App\Form\mutation\MutationSearchType;
use App\Service\genererPdf\GeneratePdfMutation;
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
            $mutation = $this->enregistrementValeurDansMutation($form, self::$em, $user);
            $generatePdf = new GeneratePdfMutation;
            $generatePdf->genererPDF($this->donneePourPdf($form, $user));
            $this->envoyerPieceJointes($form, $this->fusionPdf);
            $generatePdf->copyInterneToDOCUWARE($mutation->getNumeroMutation(), $mutation->getAgenceEmetteur()->getCodeAgence() . $mutation->getServiceEmetteur()->getCodeService());
            $this->redirectToRoute("mutation_liste");
        }

        self::$twig->display('mutation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mutation/list", name="mutation_liste")
     */
    public function listeMutation(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $mutationSearch = new MutationSearch();

        $form = self::$validator->createBuilder(MutationSearchType::class, $mutationSearch, [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mutationSearch = $form->getData();
        }

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $mutationSearch->toArray();

        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository = self::$em->getRepository(Mutation::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $mutationSearch);

        //enregistre le critère dans la session
        $this->sessionService->set('mutation_search_criteria', $criteria);

        self::$twig->display(
            'mutation/list.html.twig',
            [
                'form'        => $form->createView(),
                'data'        => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage'    => $paginationData['lastPage'],
                'resultat'    => $paginationData['totalItems'],
                'criteria'    => $criteria,
            ]
        );
    }
}
