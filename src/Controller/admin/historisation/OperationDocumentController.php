<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\historisation\documentOperation\TypeDocument;
use App\Entity\admin\historisation\documentOperation\TypeOperation;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocument;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocumentSearch;
use App\Form\admin\historisation\documentOperation\HistoriqueOperationDocumentSearchType;

class OperationDocumentController extends Controller
{
    /**
     * @Route("/admin/operation-document", name="operation_document_index")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $historiqueOperationDocumentSearch = new HistoriqueOperationDocumentSearch;

        $this->initialisationFormRecherche($historiqueOperationDocumentSearch);

        //création et initialisation du formulaire de la recherche
        $form = $this->getFormFactory()->createBuilder(HistoriqueOperationDocumentSearchType::class, $historiqueOperationDocumentSearch, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $historiqueOperationDocumentSearch = $form->getData();
        }

        $criteria = [];
        // transformer l'objet historiqueOperationDocumentSearch en tableau
        $criteria = $historiqueOperationDocumentSearch->toArray();
        //recupères les données du criteria dans une session nommé historique_operation_document_search_criteria
        $this->getSessionService()->set('historique_operation_document_search_criteria', $criteria);

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 50;

        $paginationData = $this->isObjectEmpty($historiqueOperationDocumentSearch) ? [] : $this->getEntityManager()->getRepository(HistoriqueOperationDocument::class)->findPaginatedAndFiltered($page, $limit, $historiqueOperationDocumentSearch);

        return $this->render('admin/historisation/operation-document/index.html.twig', [
            'form'        => $form->createView(),
            'data'        => $paginationData['data'] ?? null,
            'currentPage' => $paginationData['currentPage'] ?? null,
            'totalPages'  => $paginationData['lastPage'] ?? 0,
            'resultat'    => $paginationData['totalItems'] ?? 0,
            'criteria'    => $criteria,
        ]);
    }

    /**
     * @Route("/admin/operation-document/dashboard", name="operation_document_dashboard")
     */
    public function dashboard()
    {
        return $this->render('admin/historisation/operation-document/dashboard.html.twig');
    }

    /**
     * @Route("/admin/operation-document/detail", name="operation_document_detail")
     */
    public function detail()
    {
        return $this->render('admin/historisation/operation-document/detail.html.twig');
    }

    /** 
     * Méthode pour vérifier si l'objet est vide
     * 
     * @return bool
     */
    private function isObjectEmpty(HistoriqueOperationDocumentSearch $historiqueOperationDocumentSearch): bool
    {
        return
            $historiqueOperationDocumentSearch->getUtilisateur() === "" &&
            $historiqueOperationDocumentSearch->getUtilisateur() === "" &&
            $historiqueOperationDocumentSearch->getStatutOperation() === "" &&
            $historiqueOperationDocumentSearch->getTypeDocument() === null &&
            $historiqueOperationDocumentSearch->getTypeDocument() === null &&
            $historiqueOperationDocumentSearch->getDateOperationDebut() === null &&
            $historiqueOperationDocumentSearch->getDateOperationFin() === null;
    }

    /** 
     * Méthode pour initialiser le recherche
     */
    private function initialisationFormRecherche(HistoriqueOperationDocumentSearch $historiqueOperationDocumentSearch)
    {
        // Initialisation des critères depuis la session
        $criteria = $this->getSessionService()->get('historique_operation_document_search_criteria', []) ?? [];

        // Si des critères existent, les utiliser pour définir les entités associées
        if (!empty($criteria)) {
            $typeOperation = isset($criteria['typeOperation']) && $criteria['typeOperation'] !== null ? $this->getEntityManager()->getRepository(TypeOperation::class)->find($criteria['typeOperation']) : null;
            $typeDocument = isset($criteria['typeDocument']) && $criteria['typeDocument'] !== null ? $this->getEntityManager()->getRepository(TypeDocument::class)->find($criteria['typeDocument']) : null;

            $historiqueOperationDocumentSearch
                ->setNumeroDocument($criteria['numeroDocument'])
                ->setUtilisateur($criteria['utilisateur'])
                ->setStatutOperation($criteria['statutOperation'])
                ->setTypeOperation($typeOperation)
                ->setTypeDocument($typeDocument)
                ->setDateOperationDebut($criteria['dateOperationDebut'] ?? null)
                ->setDateOperationFin($criteria['dateOperationFin'] ?? null)
            ;
        }
    }
}
