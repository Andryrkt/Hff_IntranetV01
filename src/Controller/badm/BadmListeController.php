<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Model\dit\DitModel;
use App\Service\ExcelService;
use App\Controller\Controller;
use App\Entity\badm\BadmSearch;
use App\Form\badm\BadmSearchType;
use App\Model\badm\BadmRechercheModel;
use App\Repository\badm\BadmRepository;
use App\Controller\Traits\BadmListTrait;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmListeController extends Controller
{
    use BadmListTrait;
    /**
     * @Route("/liste", name="badmListe_AffichageListeBadm")
     */
    public function AffichageListeBadm(Request $request)
    {
        $badmSearch = new BadmSearch();

        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        $this->initialisation($badmSearch, $this->getEntityManager());

        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_BADM);
        $form = $this->getFormFactory()->createBuilder(BadmSearchType::class, $badmSearch, [
            'method' => 'GET',
            'agenceServiceAutorises' => $agenceServiceAutorises
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rechercherSurNumSerieParc($form, $badmSearch);
        }

        $this->gererAgenceService($badmSearch, $agenceServiceAutorises);

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $badmSearch->toArray();
        //enregistre le critère dans la session
        $this->getSessionService()->set('badm_search_criteria', $criteria);

        /** @var BadmRepository $repository */
        $repository = $this->getEntityManager()->getRepository(Badm::class);
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $criteria, $agenceServiceAutorises);

        $this->ajoutNumSerieNumParc($paginationData);

        $this->logUserVisit('badmListe_AffichageListeBadm'); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/listBadm.html.twig',
            [
                'form'        => $form->createView(),
                'data'        => $paginationData['data'],
                'empty'       => $empty,
                'criteria'    => $criteria,
                'currentPage' => $paginationData['currentPage'],
                'lastPage'    => $paginationData['lastPage'],
                'resultat'    => $paginationData['totalItems'],
            ]
        );
    }

    /**
     * @Route("/export-badm-excel", name="export_badm_excel")
     */
    public function exportExcel()
    {
        // Récupère les critères dans la session
        $criteria = $this->getSessionService()->get('badm_search_criteria', []);
        $option = $this->getSessionService()->get('badm_search_option', []);

        // Récupère les entités filtrées
        $entities = $this->getEntityManager()->getRepository(Badm::class)->findAndFilteredExcel($criteria, $option);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "N°BADM",
            "Date demande",
            "Mouvement",
            "Id matériel",
            "Ag/Serv émetteur",
            "N° Parc",
            "Casier émetteur",
            "Casier destinataire"
        ];

        foreach ($entities as $entity) {
            if ($entity->getCasierDestinataire() === null) {
                $casierDestinataire = '';
            } elseif ($entity->getCasierDestinataire()->getId() == 0 ||  $entity->getCasierDestinataire()->getId() == '' || $entity->getCasierDestinataire()->getId() == null) {
                $casierDestinataire = '';
            } else {
                $casierDestinataire = $entity->getCasierDestinataire()->getCasier();
            }
            $data[] = [
                $entity->getStatutDemande() ? $entity->getStatutDemande()->getDescription() : '',
                $entity->getNumBadm(),
                $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
                $entity->getTypeMouvement() ? $entity->getTypeMouvement()->getDescription() : '',
                $entity->getIdMateriel(),
                $entity->getAgenceServiceEmetteur(),
                $entity->getNumParc(),
                $entity->getCasierEmetteur(),
                $casierDestinataire
            ];
        }

        // Crée le fichier Excel
        (new ExcelService())->createSpreadsheet($data);
    }

    /**
     * @Route("/badm-list-annuler", name="badm_list_annuler")
     *
     * @param Request $request
     * @return void
     */
    public function listAnnuler(Request $request)
    {
        $badmSearch = new BadmSearch();

        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la nag=vigation pagiantion */
        $this->initialisation($badmSearch, $this->getEntityManager());

        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_BADM);
        $form = $this->getFormFactory()->createBuilder(BadmSearchType::class, $badmSearch, [
            'method' => 'GET',
            'agenceServiceAutorises' => $agenceServiceAutorises
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rechercherSurNumSerieParc($form, $badmSearch);
        }

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $badmSearch->toArray();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        //enregistre le critère dans la session
        $this->getSessionService()->set('badm_search_criteria', $criteria);

        /** @var BadmRepository $repository */
        $repository = $this->getEntityManager()->getRepository(Badm::class);
        $paginationData = $repository->findPaginatedAndFilteredListAnnuler($page, $limit, $criteria, $agenceServiceAutorises);

        for ($i = 0; $i < count($paginationData['data']); $i++) {
            $badmRechercheModel = new BadmRechercheModel();
            $badms = $badmRechercheModel->findDesiSerieParc($paginationData['data'][$i]->getIdMateriel());

            $paginationData['data'][$i]->setDesignation($badms[0]['designation']);
            $paginationData['data'][$i]->setNumSerie($badms[0]['num_serie']);
            $paginationData['data'][$i]->setNumParc($badms[0]['num_parc']);
        }

        $this->logUserVisit('badm_list_annuler'); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/listBadm.html.twig',
            [
                'form'        => $form->createView(),
                'data'        => $paginationData['data'],
                'empty'       => $empty,
                'criteria'    => $criteria,
                'currentPage' => $paginationData['currentPage'],
                'lastPage'    => $paginationData['lastPage'],
                'resultat'    => $paginationData['totalItems']
            ]
        );
    }


    public function rechercherSurNumSerieParc($form, $badmSearch)
    {
        $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData();
        $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();

        if (!empty($numParc) || !empty($numSerie)) {
            $ditModel = new DitModel();
            $idMateriel = $ditModel->recuperationIdMateriel($numParc, $numSerie);

            if (!empty($idMateriel)) {
                $this->recuperationCriterie($badmSearch, $form);
                $badmSearch->setIdMateriel($idMateriel[0]['num_matricule']);
            } else {
                $this->recuperationCriterie($badmSearch, $form);
                $badmSearch->setIdMateriel('0');
            }
        } else {
            $this->recuperationCriterie($badmSearch, $form);
            $badmSearch->setIdMateriel($form->get('idMateriel')->getData());
        }
    }

    private function ajoutNumSerieNumParc($paginationData)
    {
        for ($i = 0; $i < count($paginationData['data']); $i++) {
            $badmRechercheModel = new BadmRechercheModel();
            $badms = $badmRechercheModel->findDesiSerieParc($paginationData['data'][$i]->getIdMateriel());
            if (!empty($badms)) {
                $paginationData['data'][$i]->setDesignation($badms[0]['designation']);
                $paginationData['data'][$i]->setNumSerie($badms[0]['num_serie']);
                if ($badms[0]['num_parc'] == null) {
                    $paginationData['data'][$i]->setNumParc($paginationData['data'][$i]->getNumParc());
                } else {
                    $paginationData['data'][$i]->setNumParc($badms[0]['num_parc']);
                }
            }
        }
    }

    private function gererAgenceService(BadmSearch $badmSearch, array $agenceServiceAutorises): void
    {
        // Changer le serviceEmetteur
        if ($badmSearch->getServiceEmetteur()) {
            $ligneId = $badmSearch->getServiceEmetteur();
            if ($ligneId && isset($agenceServiceAutorises[$ligneId])) {
                $badmSearch->setServiceEmetteur($agenceServiceAutorises[$ligneId]['service_id']);
            }
        }

        // Changer le serviceDebiteur
        if ($badmSearch->getServiceDebiteur()) {
            $ligneId = $badmSearch->getServiceDebiteur();
            if ($ligneId && isset($agenceServiceAutorises[$ligneId])) {
                $badmSearch->setServiceDebiteur($agenceServiceAutorises[$ligneId]['service_id']);
            }
        }
    }
}
