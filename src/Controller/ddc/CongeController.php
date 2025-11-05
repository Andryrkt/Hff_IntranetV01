<?php

namespace App\Controller\ddc;

use App\Controller\Controller;
use App\Entity\ddc\DemandeConge;
use App\Entity\admin\Application;
use App\Form\ddc\DemandeCongeType;
use App\Entity\admin\AgenceServiceIrium;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Traits\ddc\CongeListeTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * Affiche la liste des demandes de congé
     * @Route("/conge-liste", name="conge_liste")
     */
    public function listeConge(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // DEBUT D'AUTORISATION
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        //FIN AUTORISATION

        $congeSearch = new DemandeConge();

        // Vérifier s'il s'agit d'un accès direct à la route (sans paramètres de recherche)
        // Dans ce cas, nous réinitialisons tous les filtres
        $isDirectAccess = empty($request->query->all()) ||
            (count($request->query->all()) == 1 && $request->query->has('page'));

        if ($isDirectAccess) {
            // Réinitialiser tous les filtres - créer un objet vide sans données de session
            $congeSearch = new DemandeConge();

            // Effacer les critères de recherche de la session
            $this->sessionService->remove('conge_search_criteria');
            $this->sessionService->remove('conge_search_option');
        } else {
            // Utiliser les critères de recherche stockés dans la session si disponibles
            $sessionCriteria = $this->sessionService->get('conge_search_criteria', []);

            if (!empty($sessionCriteria)) {
                // Remplir l'objet congeSearch avec les critères de session
                $congeSearch->setTypeDemande($sessionCriteria['typeDemande'] ?? null)
                    ->setNumeroDemande($sessionCriteria['numeroDemande'] ?? null)
                    ->setMatricule($sessionCriteria['matricule'] ?? null)
                    ->setNomPrenoms($sessionCriteria['nomPrenoms'] ?? null)
                    ->setDateDemande($sessionCriteria['dateDemande'] ?? null)
                    ->setAdresseMailDemandeur($sessionCriteria['adresseMailDemandeur'] ?? null)
                    ->setSousTypeDocument($sessionCriteria['sousTypeDocument'] ?? null)
                    ->setDureeConge($sessionCriteria['dureeConge'] ?? null)
                    ->setDateDebut($sessionCriteria['dateDebut'] ?? null)
                    ->setDateFin($sessionCriteria['dateFin'] ?? null)
                    ->setSoldeConge($sessionCriteria['soldeConge'] ?? null)
                    ->setMotifConge($sessionCriteria['motifConge'] ?? null)
                    ->setStatutDemande($sessionCriteria['statutDemande'] ?? null)
                    ->setDateStatut($sessionCriteria['dateStatut'] ?? null)
                    ->setPdfDemande($sessionCriteria['pdfDemande'] ?? null);
            }
        }

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
            'admin' => in_array(1, $this->getUser()->getRoleIds()),
            //'idAgence' => $this->agenceIdAutoriser(self::$em)
        ];

        // Si le formulaire est soumis et valide, mettre à jour les critères
        if ($form->isSubmitted() && $form->isValid()) {
            // Formulaire soumis avec des critères de recherche
            $congeSearch = $form->getData();

            // Récupérer les dates de demande (mappées et non mappées)
            $dateDemande = $form->get('dateDemande')->getData();
            $dateDemandeFin = $form->has('dateDemandeFin') ? $form->get('dateDemandeFin')->getData() : null;
            // Stocker les critères dans la session
            $criteria = $congeSearch->toArray();

            // Stocker les dates dans les options pour le repository
            if ($dateDemande) {
                $options['dateDemande'] = $dateDemande;
            }
            if ($dateDemandeFin) {
                $options['dateDemandeFin'] = $dateDemandeFin;
            }


            // Récupérer l'agence pour le filtre Agence_service
            $agence = $request->query->get('demande_conge')['agence'] ?? null;
            if ($agence) {
                $options['agence'] = $agence;
            }

            // Récupérer le service pour le filtre Agence_service
            $service = $request->query->get('demande_conge')['service'] ?? null;
            if ($service) {
                $options['service'] = $service;
            }

            $agenceCode = isset($options['agence']) ? $options['agence'] : null;
            $serviceCode = isset($options['service']) ? $options['service'] : null;
            $options['agenceService'] = ($agenceCode && $serviceCode)
                ? $this->getAgenceServiceSage($agenceCode, $serviceCode)
                : null;

            // Ajouter les dates aux critères pour persistance
            if ($dateDemande) {
                $criteria['dateDemande'] = $dateDemande;
            }
            if ($dateDemandeFin) {
                $criteria['dateDemandeFin'] = $dateDemandeFin;
            }

            // Ajouter le service sélectionné aux critères pour persistance
            $serviceHidden = $request->query->get('service_hidden');
            if ($serviceHidden) {
                $criteria['selected_service'] = $serviceHidden;
            }

            // Enregistrement des critères dans la session
            $this->sessionService->set('conge_search_criteria', $criteria);
            $this->sessionService->set('conge_search_option', $options);
        } else if (!$isDirectAccess) {
            // Utiliser les options de recherche stockées dans la session si disponibles
            // (seulement si ce n'est pas un accès direct)
            $sessionOptions = $this->sessionService->get('conge_search_option', []);
            $options = $sessionOptions;
        }

        // Déterminer les codes agence/service pour l'affichage même si le formulaire n'a pas été soumis
        $agenceCode = $options['agence'] ?? null;
        $serviceCode = $options['service'] ?? null;

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 50;

        // Récupération des données filtrées
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $congeSearch, $options, $this->getUser());





        // Formatage des critères pour l'affichage
        $criteriaTab = $congeSearch->toArray();
        $criteriaTab['statutDemande'] = $criteriaTab['statutDemande'] ?? null;
        $criteriaTab['dateDebut'] = $criteriaTab['dateDebut'] ? $criteriaTab['dateDebut']->format('d-m-Y') : null;
        $criteriaTab['dateFin'] = $criteriaTab['dateFin'] ? $criteriaTab['dateFin']->format('d-m-Y') : null;
        $criteriaTab['dateDemande'] = $criteriaTab['dateDemande'] ? $criteriaTab['dateDemande']->format('d-m-Y') : null;
        $criteriaTab['dateDemandeFin'] = isset($criteriaTab['dateDemandeFin']) && $criteriaTab['dateDemandeFin'] ? $criteriaTab['dateDemandeFin']->format('d-m-Y') : null;
        $criteriaTab['selected_service'] = $criteriaTab['selected_service'] ?? null;
        $agenceCode = isset($options['agence']) ? $options['agence'] : null;
        $serviceCode = isset($options['service']) ? $options['service'] : null;
        $criteriaTab['agenceService'] = ($agenceCode && $serviceCode)
            ? $this->getAgenceServiceSage($agenceCode, $serviceCode)
            : null;

        // Filtrer les critères pour supprimer les valeurs "falsy"
        $filteredCriteria = array_filter($criteriaTab);

        // ajout de agence service code dans le donnée à afficher
        foreach ($paginationData['data'] as $key => $value) {
            $agenceServiceCode = $value->getAgenceServiceirium() ? $value->getAgenceServiceirium()->getServicesagepaie() : null;
            $codeAgenceService = $agenceServiceCode ? $this->getCodeAgenceService($agenceServiceCode) : null;
            $value->setCodeAgenceService($codeAgenceService);
        }

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

    private function getCodeAgenceService(string $agenceServiceSage)
    {
        $agenceServiceIrium = $this->getEntityManager()
            ->getRepository(AgenceServiceIrium::class)
            ->findOneBy(["service_sage_paie" => $agenceServiceSage]);
        return $agenceServiceIrium ? $agenceServiceIrium->getAgenceips() . '-' . $agenceServiceIrium->getServiceips() : null;
    }

    private function getAgenceServiceSage(string $codeAgence, string $codeService): ?string
    {
        $agenceServiceIrium = $this->getEntityManager()
            ->getRepository(AgenceServiceIrium::class)
            ->findOneBy(["agence_ips" => $codeAgence, "service_ips" => $codeService]);
        return $agenceServiceIrium ? $agenceServiceIrium->getServicesagepaie() : null;
    }

    /**
     * @Route("/export-conge-excel", name="export_conge_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // Récupère les critères dans la session
        $criteria = $this->sessionService->get('conge_search_criteria', []);
        $option = $this->sessionService->get('conge_search_option', []);

        // S'assurer que $option est toujours un tableau
        if (!is_array($option)) {
            $option = [];
        }

        // Convertir les dates du format string au format DateTime si nécessaire
        if (isset($criteria['dateDemande']) && is_string($criteria['dateDemande'])) {
            $criteria['dateDemande'] = \DateTime::createFromFormat('d-m-Y', $criteria['dateDemande']);
        }
        if (isset($criteria['dateDemandeFin']) && is_string($criteria['dateDemandeFin'])) {
            $criteria['dateDemandeFin'] = \DateTime::createFromFormat('d-m-Y', $criteria['dateDemandeFin']);
        }
        if (isset($criteria['dateDebut']) && is_string($criteria['dateDebut'])) {
            $criteria['dateDebut'] = \DateTime::createFromFormat('d-m-Y', $criteria['dateDebut']);
        }
        if (isset($criteria['dateFin']) && is_string($criteria['dateFin'])) {
            $criteria['dateFin'] = \DateTime::createFromFormat('d-m-Y', $criteria['dateFin']);
        }

        $congeSearch = new DemandeConge();
        $congeSearch->setTypeDemande(isset($criteria['typeDemande']) ? $criteria['typeDemande'] : null)
            ->setNumeroDemande(isset($criteria['numeroDemande']) ? $criteria['numeroDemande'] : null)
            ->setMatricule(isset($criteria['matricule']) ? $criteria['matricule'] : null)
            ->setNomPrenoms(isset($criteria['nomPrenoms']) ? $criteria['nomPrenoms'] : null)
            ->setDateDemande(isset($criteria['dateDemande']) ? $criteria['dateDemande'] : null)
            ->setAdresseMailDemandeur(isset($criteria['adresseMailDemandeur']) ? $criteria['adresseMailDemandeur'] : null)
            ->setSousTypeDocument(isset($criteria['sousTypeDocument']) ? $criteria['sousTypeDocument'] : null)
            ->setDureeConge(isset($criteria['dureeConge']) ? $criteria['dureeConge'] : null)
            ->setDateDebut(isset($criteria['dateDebut']) ? $criteria['dateDebut'] : null)
            ->setDateFin(isset($criteria['dateFin']) ? $criteria['dateFin'] : null)
            ->setSoldeConge(isset($criteria['soldeConge']) ? $criteria['soldeConge'] : null)
            ->setMotifConge(isset($criteria['motifConge']) ? $criteria['motifConge'] : null)
            ->setStatutDemande(isset($criteria['statutDemande']) ? $criteria['statutDemande'] : null)
            ->setDateStatut(isset($criteria['dateStatut']) ? $criteria['dateStatut'] : null)
            ->setPdfDemande(isset($criteria['pdfDemande']) ? $criteria['pdfDemande'] : null);

        // Récupère les entités filtrées
        $entities = $this->getEntityManager()->getRepository(DemandeConge::class)->findAndFilteredExcel($congeSearch, $option);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "Sous type",
            "N° Demande",
            "Date demande",
            "Matricule",
            "Nom et Prénoms",
            "Agence/Service",
            "Date de début",
            "Date de fin",
            "Durée congé"
        ];

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getStatutDemande(),
                $entity->getSousTypeDocument(),
                $entity->getNumeroDemande(),
                $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
                $entity->getMatricule(),
                $entity->getNomPrenoms(),
                ($entity->getAgenceServiceirium() ? $entity->getAgenceServiceirium()->getServicesagepaie() : null),
                $entity->getDateDebut() ? $entity->getDateDebut()->format('d/m/Y') : '',
                $entity->getDateFin() ? $entity->getDateFin()->format('d/m/Y') : '',
                $entity->getDureeConge()
            ];
        }

        // Crée le fichier Excel
        $this->getExcelService()->createSpreadsheet($data);
        exit();
    }

    /**
     * @Route("/conge-liste-clear", name="conge_liste_clear")
     */
    public function clearListeConge()
    {
        // Clear the search criteria from session
        $this->sessionService->remove('conge_search_criteria');
        $this->sessionService->remove('conge_search_option');

        // Redirect to the main congé list
        return $this->redirectToRoute("conge_liste");
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

    /**
     * @Route("/api/services-by-agence/{codeAgence}")
     * 
     * recupère les service selon le code d'agence dans le table Agence_service_irium
     */
    public function getServiceSelonAgence(string $codeAgence)
    {
        $agencesServices = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->findBy(["agence_ips" => $codeAgence]);

        $services = [];
        $seen   = [];

        foreach ($agencesServices as $agence) {
            $code = $agence->getServiceIps();
            $nom  = $agence->getLibelleServiceIps();

            // clé unique basée sur code+nom
            $key = $code . '|' . $nom;

            if (!isset($seen[$key])) {
                $services[] = [
                    'code' => $code,
                    'nom'  => $nom,
                ];
                $seen[$key] = true;
            }
        }
        return new JsonResponse($services);
    }


    /**
     * @Route("/api/matricule-nom-prenom")
     */
    public function getMatriculeNomPrenom()
    {
        $matriculeNomPrenom = $this->getEntityManager()->getRepository(DemandeConge::class)->getMatriculeNomPrenom();
        return new JsonResponse($matriculeNomPrenom);
    }
}
