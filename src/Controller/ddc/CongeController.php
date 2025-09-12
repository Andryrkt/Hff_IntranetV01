<?php

namespace App\Controller\ddc;

use App\Controller\Controller;
use App\Entity\ddc\DemandeConge;
use App\Entity\admin\Application;
use App\Form\ddc\DemandeCongeType;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Traits\ddc\CongeListeTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/rh/demande-de-conge")
 */
class CongeController extends Controller
{
    use ConversionTrait;
    use CongeListeTrait;
    use FormatageTrait;
    use AutorisationTrait;

    /**
     * @Route("/nouveau-conge", name="new_conge")
     */
    public function nouveauConge()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        /** FIN AUtorisation accès */

        return $this->render('ddc/conge_new.html.twig');
    }

    /**
     * Affiche la liste des demandes de congé
     * @Route("/conge-liste", name="conge_liste")
     */
    public function listeConge(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $autoriser = $this->autorisationRole($this->getEntityManager());

        $congeSearch = new DemandeConge();

        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la navigation pagination */
        $this->initialisation($congeSearch, $this->getEntityManager());

        // Création du formulaire avec l'EntityManager
        $form = $this->getFormFactory()->createBuilder(DemandeCongeType::class, $congeSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        $form->handleRequest($request);

        // Options pour le repository
        $options = [
            //'boolean' => $autoriser,
            //'idAgence' => $this->agenceIdAutoriser(self::$em)
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $congeSearch = $form->getData();

            // Récupérer la date de demande fin (non mappée)
            if ($form->has('dateDemandeFin')) {
                $dateDemandeFin = $form->get('dateDemandeFin')->getData();
                if ($dateDemandeFin) {
                    $options['dateDemandeFin'] = $dateDemandeFin;
                }
            }

            // Récupérer le service et l'agence
            $serviceHidden = $request->query->get('service_hidden');
            if ($serviceHidden) {
                $options['agenceService'] = $serviceHidden;
            }
        }

        // Transformer l'objet congeSearch en tableau pour la session
        $criteria = $congeSearch->toArray();

        // Ajouter les options non mappées aux critères
        if (isset($options['dateDemandeFin'])) {
            $criteria['dateDemandeFin'] = $options['dateDemandeFin'];
        }
        if (isset($options['service'])) {
            $criteria['service'] = $options['service'];
        }

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Récupération des données filtrées
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $congeSearch, $options);

        // Enregistrement des critères dans la session
        $this->getSessionService()->set('conge_search_criteria', $criteria);
        $this->getSessionService()->set('conge_search_option', $options);

        // Formatage des critères pour l'affichage
        $criteriaTab = $criteria;
        $criteriaTab['statutDemande'] = $criteria['statutDemande'] ?? null;
        $criteriaTab['dateDebut'] = $criteria['dateDebut'] ? $criteria['dateDebut']->format('d-m-Y') : null;
        $criteriaTab['dateFin'] = $criteria['dateFin'] ? $criteria['dateFin']->format('d-m-Y') : null;
        $criteriaTab['dateDemande'] = $criteria['dateDemande'] ? $criteria['dateDemande']->format('d-m-Y') : null;
        $criteriaTab['dateDemandeFin'] = isset($criteria['dateDemandeFin']) && $criteria['dateDemandeFin'] ? $criteria['dateDemandeFin']->format('d-m-Y') : null;

        // Filtrer les critères pour supprimer les valeurs "falsy"
        $filteredCriteria = array_filter($criteriaTab);

        // Affichage du template
        return $this->render(
            'ddc/conge_list.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $filteredCriteria,
            ]
        );
    }

    /**
     * Affiche et gère la demande de congé
     * @Route("/conge-demande", name="conge_demande")
     */
    public function demandeConge(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $conge = new DemandeConge();
        $form = $this->getFormFactory()->createBuilder(DemandeCongeType::class, $conge, [
            'em' => $this->getEntityManager()
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conge = $form->getData();
            $conge->setDateDemande(new \DateTime());
            $conge->setNomPrenoms($this->getSessionService()->get('user_nom'));
            $conge->setAdresseMailDemandeur($this->getSessionService()->get('user_email'));
            $conge->setStatutDemande('EN_ATTENTE');
            $conge->setDateStatut(new \DateTime());

            // Gestion du fichier PDF si présent
            $pdfFile = $form->get('pdfDemande')->getData();
            if ($pdfFile) {
                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pdfFile->guessExtension();

                try {
                    $pdfFile->move(
                        // $this->getParameter('pdf_directory'), TODO: ajouter le chemin du fichier
                        $newFilename
                    );
                    $conge->setPdfDemande($newFilename);
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe pendant le téléchargement
                }
            }

            $this->getEntityManager()->persist($conge);
            $this->getEntityManager()->flush();

            return $this->redirectToRoute('conge_liste');
        }

        return $this->render('doms/conge_demande.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/export-conge-excel", name="export_conge_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // Récupère les critères dans la session
        $criteria = $this->getSessionService()->get('conge_search_criteria', []);
        $option = $this->getSessionService()->get('conge_search_option', []);

        $congeSearch = new DemandeConge();
        $congeSearch->setTypeDemande($criteria['typeDemande'] ?? null)
            ->setNumeroDemande($criteria['numeroDemande'] ?? null)
            ->setMatricule($criteria['matricule'] ?? null)
            ->setNomPrenoms($criteria['nomPrenoms'] ?? null)
            ->setDateDemande($criteria['dateDemande'] ?? null)
            ->setAgenceService($criteria['agenceService'] ?? null)
            ->setAdresseMailDemandeur($criteria['adresseMailDemandeur'] ?? null)
            ->setSousTypeDocument($criteria['sousTypeDocument'] ?? null)
            ->setDureeConge($criteria['dureeConge'] ?? null)
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setSoldeConge($criteria['soldeConge'] ?? null)
            ->setMotifConge($criteria['motifConge'] ?? null)
            ->setStatutDemande($criteria['statutDemande'] ?? null)
            ->setDateStatut($criteria['dateStatut'] ?? null)
            ->setPdfDemande($criteria['pdfDemande'] ?? null);

        // Récupère les entités filtrées
        $entities = $this->getEntityManager()->getRepository(DemandeConge::class)->findAndFilteredExcel($congeSearch, $option);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "Type Demande",
            "N° Demande",
            "Date demande",
            "Matricule",
            "Nom et Prénoms",
            "Agence/Service",
            "Date de début",
            "Date de fin",
            "Durée congé",
            "Solde congé",
            "Motif congé",
            "PDF Demande"
        ];

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getStatutDemande(),
                $entity->getTypeDemande(),
                $entity->getNumeroDemande(),
                $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
                $entity->getMatricule(),
                $entity->getNomPrenoms(),
                $entity->getAgenceService(),
                $entity->getDateDebut() ? $entity->getDateDebut()->format('d/m/Y') : '',
                $entity->getDateFin() ? $entity->getDateFin()->format('d/m/Y') : '',
                $entity->getDureeConge(),
                $entity->getSoldeConge(),
                $entity->getMotifConge(),
                $entity->getPdfDemande()
            ];
        }

        // Crée le fichier Excel
        $this->excelService->createSpreadsheet($data);
    }

    /**
     * @Route("/conge-list-annuler", name="conge_list_annuler")
     *
     * @param Request $request
     * @return void
     */
    public function listAnnuler(Request $request)
    {
        $autoriser = $this->autorisationRole($this->getEntityManager());

        $congeSearch = new DemandeConge();

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la navigation pagination */
        $this->initialisation($congeSearch, $this->getEntityManager());

        // Correction ici aussi
        $form = $this->getFormFactory()->createBuilder(DemandeCongeType::class, $congeSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $congeSearch = $form->getData();
        }

        $criteria = [];
        //transformer l'objet congeSearch en tableau
        $criteria = $congeSearch->toArray();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $option = [
            'boolean' => $autoriser,
            'idAgence' => $this->agenceIdAutoriser($this->getEntityManager())
        ];
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        $paginationData = $repository->findPaginatedAndFilteredAnnuler($page, $limit, $congeSearch, $option);

        //enregistre le critère dans la session
        $this->getSessionService()->set('conge_search_criteria', $criteria);
        $this->getSessionService()->set('conge_search_option', $option);

        return $this->render(
            'doms/conge_list.html.twig',
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
     * @Route("/annuler-conge/{numeroDemande}", name="conge_annulationStatut")
     */
    public function annulationStatutController($numeroDemande)
    {
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        $conge = $repository->findOneBy(['numeroDemande' => $numeroDemande]);

        if ($conge) {
            $conge->setStatutDemande('ANNULEE');
            $conge->setDateStatut(new \DateTime());
            $this->getEntityManager()->flush();
        }

        return $this->redirectToRoute("conge_liste");
    }
}
