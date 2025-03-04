<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\dom\DomListeTrait;
use App\Controller\Traits\FormatageTrait;
use App\Entity\dom\Dom;
use App\Entity\dom\DomSearch;
use App\Form\dom\DomSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DomsListeController extends Controller
{

    use ConversionTrait;
    use DomListeTrait;
    use FormatageTrait;

    /**
     * affichage de l'architecture de la liste du DOM
     * @Route("/dom-liste", name="doms_liste")
     */
    public function listeDom(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $autoriser = $this->autorisationRole(self::$em);

        $domSearch = new DomSearch();

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        $this->initialisation($domSearch, self::$em, $agenceServiceIps, $autoriser);

        $form = self::$validator->createBuilder(DomSearchType::class, $domSearch, [
            'method' => 'GET',
            'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId()
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $domSearch = $form->getData();
        }

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $domSearch->toArray();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $option = [
            'boolean' => $autoriser,
            'idAgence' => $this->agenceIdAutoriser(self::$em)
        ];

        $repository = self::$em->getRepository(Dom::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $domSearch, $option);

        //enregistre le critère dans la session
        $this->sessionService->set('dom_search_criteria', $criteria);
        $this->sessionService->set('dom_search_option', $option);

        $criteriaTab = $criteria;

        $criteriaTab['sousTypeDocument'] = $criteria['sousTypeDocument'] ? $criteria['sousTypeDocument']->getCodeSousType() : $criteria['sousTypeDocument'];
        $criteriaTab['statut']           = $criteria['statut'] ? $criteria['statut']->getDescription() : $criteria['statut'];
        $criteriaTab['dateDebut']        = $criteria['dateDebut'] ? $criteria['dateDebut']->format('d-m-Y') : $criteria['dateDebut'];
        $criteriaTab['dateFin']          = $criteria['dateFin'] ? $criteria['dateFin']->format('d-m-Y') : $criteria['dateFin'];
        $criteriaTab['dateMissionDebut'] = $criteria['dateMissionDebut'] ? $criteria['dateMissionDebut']->format('d-m-Y') : $criteria['dateMissionDebut'];
        $criteriaTab['dateMissionFin']   = $criteria['dateMissionFin'] ? $criteria['dateMissionFin']->format('d-m-Y') : $criteria['dateMissionFin'];

        // Filtrer les critères pour supprimer les valeurs "falsy"
        $filteredCriteria = array_filter($criteriaTab);

        // Déterminer le type de log
        $logType = empty($filteredCriteria) ? ['doms_liste'] : ['doms_liste_search', $filteredCriteria];

        // Appeler la méthode logUserVisit avec les arguments définis
        $this->logUserVisit(...$logType);

        self::$twig->display(
            'doms/list.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $criteria,
            ]
        );
    }


    /**
     * @Route("/export-dom-excel", name="export_dom_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // Récupère les critères dans la session
        $criteria = $this->sessionService->get('dom_search_criteria', []);
        $option = $this->sessionService->get('dom_search_option', []);

        $domSearch = new DomSearch();
        $domSearch->setSousTypeDocument($criteria['sousTypeDocument'])
            ->setStatut($criteria['statut'])
            ->setDateDebut($criteria['dateDebut'])
            ->setDateFin($criteria['dateFin'])
            ->setMatricule($criteria['matricule'])
            ->setDateMissionDebut($criteria['dateMissionDebut'])
            ->setDateMissionFin($criteria['dateMissionFin'])
            ->setAgenceEmetteur($criteria['agenceEmetteur'])
            ->setServiceEmetteur($criteria['serviceEmetteur'])
            ->setAgenceDebiteur($criteria['agenceDebiteur'])
            ->setServiceDebiteur($criteria['serviceDebiteur'])
            ->setNumDom($criteria['numDom'])
        ;
        // Récupère les entités filtrées
        $entities = self::$em->getRepository(Dom::class)->findAndFilteredExcel($domSearch, $option);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "SousType",
            "N°DOM",
            "Date demande",
            "Motif de déplacement",
            "Matricule",
            "Agence/Service",
            "Date de début",
            "Date de fin",
            "Client",
            "Lieu d'intervention",
            "Total général payer",
            "Devis"
        ];

        foreach ($entities as $entity) {

            $data[] = [
                $entity->getIdStatutDemande() ? $entity->getIdStatutDemande()->getDescription() : '',
                $entity->getSousTypeDocument() ? $entity->getSousTypeDocument()->getCodeSousType() : '',
                $entity->getNumeroOrdreMission(),
                $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
                $entity->getMotifDeplacement(),
                $entity->getMatricule(),
                $entity->getLibelleCodeAgenceService(),
                $entity->getDateDebut(),
                $entity->getDateFin(),
                $entity->getClient(),
                $entity->getLieuIntervention(),
                str_replace('.', '', $entity->getTotalGeneralPayer()),
                $entity->getDevis()
            ];
        }

        // Crée le fichier Excel
        $this->excelService->createSpreadsheet($data);
    }



    /**
     * @Route("/dom-list-annuler", name="dom_list_annuler")
     *
     * @param Request $request
     * @return void
     */
    public function listAnnuler(Request $request)
    {
        $autoriser = $this->autorisationRole(self::$em);

        $domSearch = new DomSearch();

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        $this->initialisation($domSearch, self::$em, $agenceServiceIps, $autoriser);

        $form = self::$validator->createBuilder(DomSearchType::class, $domSearch, [
            'method' => 'GET',
            'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId()
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $domSearch = $form->getData();
        }

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $domSearch->toArray();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $option = [
            'boolean' => $autoriser,
            'idAgence' => $this->agenceIdAutoriser(self::$em)
        ];
        $repository = self::$em->getRepository(Dom::class);
        $paginationData = $repository->findPaginatedAndFilteredAnnuler($page, $limit, $domSearch, $option);


        //enregistre le critère dans la session
        $this->sessionService->set('dom_search_criteria', $criteria);
        $this->sessionService->set('dom_search_option', $option);

        $this->logUserVisit('dom_list_annuler'); // historisation du page visité par l'utilisateur

        self::$twig->display(
            'doms/list.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $criteria,
            ]
        );
    }

    /**
     * @Route("/annuler/{numDom}", name="domList_annulationStatut")
     */
    public function annulationStatutController($numDom)
    {
        $this->domList->annulationCodestatut($numDom);
        $this->redirectToRoute("doms_liste");
    }
}
