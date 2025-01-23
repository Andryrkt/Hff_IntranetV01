<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\pageConsultation\PageConsultationSearch;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use App\Form\admin\historisation\pageConsultation\PageConsultationSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PageConsultationController extends Controller
{
    /**
     * @Route("/admin/consultation-page", name="consultation_page_index")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $pageConsultationSearch = new PageConsultationSearch;

        $this->initialisationFormRecherche($pageConsultationSearch);

        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(PageConsultationSearchType::class, $pageConsultationSearch, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pageConsultationSearch = $form->getData();
        }

        $criteria = [];
        // transformer l'objet pageConsultationSearch en tableau
        $criteria = $pageConsultationSearch->toArray();
        //recupères les données du criteria dans une session nommé page_consultation_search_criteria
        $this->sessionService->set('page_consultation_search_criteria', $criteria);

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        $paginationData = $this->isObjectEmpty($pageConsultationSearch) ? [] : self::$em->getRepository(UserLogger::class)->findPaginatedAndFiltered($page, $limit, $pageConsultationSearch);

        self::$twig->display('admin/historisation/consultation-page/index.html.twig', [
            'form'        => $form->createView(),
            'data'        => $paginationData['data'] ?? null,
            'currentPage' => $paginationData['currentPage'] ?? null,
            'totalPages'  => $paginationData['lastPage'] ?? 0,
            'resultat'    => $paginationData['totalItems'] ?? 0,
            'criteria'    => $criteria,
        ]);
    }

    /** 
     * Méthode pour vérifier si l'objet est vide
     * 
     * @return bool
     */
    private function isObjectEmpty(PageConsultationSearch $pageConsultationSearch): bool
    {
        return
            $pageConsultationSearch->getUtilisateur() === "" &&
            $pageConsultationSearch->getNomPage() === "" &&
            $pageConsultationSearch->getMachineUser() === "" &&
            $pageConsultationSearch->getDateDebut() === null &&
            $pageConsultationSearch->getDateFin() === null;
    }

    /** 
     * Méthode pour initialiser le recherche
     */
    private function initialisationFormRecherche(PageConsultationSearch $pageConsultationSearch)
    {
        // Initialisation des critères depuis la session
        $criteria = $this->sessionService->get('page_consultation_search_criteria', []) ?? [];

        // Si des critères existent, les utiliser pour définir les entités associées
        if (!empty($criteria)) {
            $pageConsultationSearch
                ->setUtilisateur($criteria['utilisateur'])
                ->setNomPage($criteria['nom_page'])
                ->setMachineUser($criteria['machineUser'])
                ->setDateDebut($criteria['dateDebut'] ?? null)
                ->setDateFin($criteria['dateFin'] ?? null)
            ;
        }
    }

    /**
     * @Route("/admin/consultation-page/dashboard", name="consultation_page_dashboard")
     */
    public function dashboard()
    {
        self::$twig->display(
            'admin/historisation/consultation-page/dashboard.html.twig'
        );
    }

    /**
     * @Route("/admin/consultation-page/detail", name="consultation_page_detail")
     */
    public function detail()
    {
        self::$twig->display(
            'admin/historisation/consultation-page/detail.html.twig'
        );
    }
}
