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

            // Gestion spéciale pour les matricules multiples
            // On divise la chaîne de matricules multiples pour éviter les problèmes de longueur de champ
            $originalMatricule = $congeSearch->getMatricule();
            if ($originalMatricule && strpos($originalMatricule, ',') !== false) {
                // Si plusieurs matricules sont fournis, on ne les stocke pas dans l'entité
                // pour éviter les problèmes avec la longueur du champ (length=4)
                // mais on les conserve dans les options pour le filtre spécifique
                $matricules = explode(',', $originalMatricule);
                $matricules = array_map('trim', $matricules);
                $matricules = array_filter($matricules, function ($value) {
                    return $value !== '';
                });

                // On ne sauvegarde que le premier matricule dans l'entité pour éviter les troncatures
                // et on utilise les autres dans les options de recherche
                if (!empty($matricules)) {
                    $options['matricules'] = $matricules;
                    // Ne pas modifier le matricule de l'entité pour conserver la structure existante
                }
            }

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

        // Récupérer les congés filtrés pour le calendrier
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        $rawCongesForCalendar = $repository->findAndFilteredExcel($congeSearch, $options, $this->getUser());

        // Transformer les objets DemandeConge en tableaux simples pour la vue
        $conges = [];
        foreach ($rawCongesForCalendar as $conge) {
            $conges[] = [
                'id' => $conge->getId(),
                'typeDemande' => $conge->getTypeDemande(),
                'numeroDemande' => $conge->getNumeroDemande(),
                'matricule' => $conge->getMatricule(),
                'nomPrenoms' => $conge->getNomPrenoms(),
                'dateDemande' => $conge->getDateDemande() ? $conge->getDateDemande()->format('Y-m-d H:i:s') : null,
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut() ? [
                    'date' => $conge->getDateDebut()->format('Y-m-d H:i:s')
                ] : null,
                'dateFin' => $conge->getDateFin() ? [
                    'date' => $conge->getDateFin()->format('Y-m-d H:i:s')
                ] : null,
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut() ? $conge->getDateStatut()->format('Y-m-d H:i:s') : null,
                'pdfDemande' => $conge->getPdfDemande(),
            ];
        }

        // Grouper les congés par nom et prénoms pour le calendrier
        $employees = [];
        foreach ($rawCongesForCalendar as $conge) {
            $nomPrenoms = $conge->getNomPrenoms();


            $codeAgenceService = $conge->getAgenceServiceirium() ? $conge->getAgenceServiceirium()->getAgenceips() . '-' . $conge->getAgenceServiceirium()->getServiceips() : $conge->getCodeAgenceService();

            $key =  $codeAgenceService . '_' . $conge->getMatricule() . '_' . $nomPrenoms;
            if (!isset($employees[$key])) {
                $employees[$key] = [];
            }

            $employees[$key][] = [
                'id' => $conge->getId(),
                'typeDemande' => $conge->getTypeDemande(),
                'numeroDemande' => $conge->getNumeroDemande(),
                'matricule' => $conge->getMatricule(),
                'nomPrenoms' => $conge->getNomPrenoms(),
                'dateDemande' => $conge->getDateDemande() ? $conge->getDateDemande()->format('Y-m-d H:i:s') : null,
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut() ? [
                    'date' => $conge->getDateDebut()->format('Y-m-d H:i:s')
                ] : null,
                'dateFin' => $conge->getDateFin() ? [
                    'date' => $conge->getDateFin()->format('Y-m-d H:i:s')
                ] : null,
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut() ? $conge->getDateStatut()->format('Y-m-d H:i:s') : null,
                'pdfDemande' => $conge->getPdfDemande(),
            ];
        }

        // Affichage du template
        return $this->render(
            'ddc/conge_view.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $filteredCriteria,
                'conges' => $conges,
                'employees' => $employees,
                'viewMode' => 'list', // Ajout du mode d'affichage
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
    public function exportExcel(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // Récupère le paramètre format de la requête
        $format = $request->query->get('format', 'list'); // Valeur par défaut : 'list'

        // Récupère le mois et l'année sélectionnés dans la requête
        $year = $request->query->get('year');
        $month = $request->query->get('month');

        // Si pas de mois/année spécifiés, utiliser le mois en cours
        if (!$year || !$month) {
            $selectedDate = new \DateTime();
        } else {
            $selectedDate = new \DateTime($year . '-' . $month . '-01');
        }

        // Récupère les critères dans la session
        $criteria = $this->sessionService->get('conge_search_criteria', []);
        $option = $this->sessionService->get('conge_search_option', []);

        // S'assurer que $option est toujours un tableau
        if (!is_array($option)) {
            $option = [];
        }

        // Ajouter l'option admin si elle n'existe pas
        if (!isset($option['admin'])) {
            $option['admin'] = in_array(1, $this->getUser()->getRoleIds());
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
        $entities = $this->getEntityManager()->getRepository(DemandeConge::class)->findAndFilteredExcel($congeSearch, $option, $this->getUser());

        if ($format === 'table') {
            // Export au format tableau (calendrier)
            $data = $this->formatCalendarExport($entities, $selectedDate);
        } else {
            // Export au format liste (par défaut)
            $data = $this->formatListExport($entities);
        }

        // Crée le fichier Excel
        $this->getExcelService()->createSpreadsheet($data);
        exit();
    }

    /**
     * Formatte les données pour l'export en mode tableau (calendrier)
     */
    private function formatCalendarExport($entities, $selectedDate = null)
    {
        // Grouper les congés par employé (nom et prénoms) comme dans le template Twig
        $employees = [];
        foreach ($entities as $entity) {
            $nomPrenoms = $entity->getNomPrenoms();
            if (!isset($employees[$nomPrenoms])) {
                $employees[$nomPrenoms] = [];
            }
            $employees[$nomPrenoms][] = [
                'id' => $entity->getId(),
                'typeDemande' => $entity->getTypeDemande(),
                'numeroDemande' => $entity->getNumeroDemande(),
                'matricule' => $entity->getMatricule(),
                'nomPrenoms' => $entity->getNomPrenoms(),
                'dateDemande' => $entity->getDateDemande() ? $entity->getDateDemande()->format('Y-m-d H:i:s') : null,
                'agenceDebiteur' => $entity->getAgenceDebiteur(),
                'adresseMailDemandeur' => $entity->getAdresseMailDemandeur(),
                'sousTypeDocument' => $entity->getSousTypeDocument(),
                'dureeConge' => $entity->getDureeConge(),
                'dateDebut' => $entity->getDateDebut() ? [
                    'date' => $entity->getDateDebut()->format('Y-m-d H:i:s')
                ] : null,
                'dateFin' => $entity->getDateFin() ? [
                    'date' => $entity->getDateFin()->format('Y-m-d H:i:s')
                ] : null,
                'soldeConge' => $entity->getSoldeConge(),
                'motifConge' => $entity->getMotifConge(),
                'statutDemande' => $entity->getStatutDemande(),
                'dateStatut' => $entity->getDateStatut() ? $entity->getDateStatut()->format('Y-m-d H:i:s') : null,
                'pdfDemande' => $entity->getPdfDemande(),
            ];
        }

        // Utiliser le mois sélectionné ou par défaut le mois en cours
        $currentMonth = $selectedDate ? clone $selectedDate : new \DateTime();
        $currentMonth->modify('first day of this month');
        $daysInMonth = (int) $currentMonth->format('t'); // Nombre de jours dans le mois

        // Créer la première ligne d'en-tête avec le mois et l'année
        $monthYearHeader = [$currentMonth->format('F Y')]; // Nom du mois et année (ex: "Août 2025")
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $monthYearHeader[] = ""; // Cellules vides pour aligner avec les jours
        }
        $data[] = $monthYearHeader;

        // Créer la deuxième ligne d'en-tête avec les jours
        $dayHeader = [""];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayHeader[] = $day;
        }
        $data[] = $dayHeader;

        // Remplir les lignes pour chaque employé
        foreach ($employees as $employeeName => $employeeConges) {
            $row = [$employeeName]; // Nom de l'employé dans la première colonne

            // Pour chaque jour du mois
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = $currentMonth->format('Y-m') . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                $dateObj = new \DateTime($dateStr);

                // Chercher un congé qui couvre ce jour
                $congeFound = null;
                foreach ($employeeConges as $conge) {
                    $dateDebut = $conge['dateDebut'] ? new \DateTime($conge['dateDebut']['date']) : null;
                    $dateFin = $conge['dateFin'] ? new \DateTime($conge['dateFin']['date']) : null;

                    if (
                        $dateDebut && $dateFin &&
                        $dateObj >= $dateDebut && $dateObj <= $dateFin
                    ) {

                        $congeFound = $conge;
                        break; // Un congé trouvé pour cette date
                    }
                }

                if ($congeFound) {
                    // Utiliser "x" comme indicateur de congé
                    $row[] = "x";
                } else {
                    $row[] = ""; // Pas de congé ce jour-là
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Formatte les données pour l'export en mode liste (classique)
     */
    private function formatListExport($entities)
    {
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

        return $data;
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
    public function getMatriculeNomPrenom(Request $request)
    {
        $query = $request->query->get('query', '');
        $matriculeNomPrenom = $this->getEntityManager()->getRepository(DemandeConge::class)->getMatriculeNomPrenom($query);
        return new JsonResponse($matriculeNomPrenom);
    }

    /**
     * @Route("/api/tags-by-matricule/{matricule}")
     */
    public function getTagsByMatricule(string $matricule)
    {
        // Retrieve tags associated with the specified matricule
        $tags = $this->getEntityManager()->getRepository(DemandeConge::class)->getTagsByMatricule($matricule);

        return new JsonResponse(['tags' => $tags]);
    }

    /**
     * @Route("/conge-calendrier", name="conge_calendrier")
     */
    public function calendrierConge()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // DEBUT D'AUTORISATION
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        //FIN AUTORISATION

        // Récupérer toutes les demandes de congé pour les afficher dans le calendrier
        // On peut filtrer selon les critères enregistrés dans la session
        $criteria = $this->sessionService->get('conge_search_criteria', []);
        $options = $this->sessionService->get('conge_search_option', []);

        $congeSearch = new DemandeConge();
        $congeSearch->setTypeDemande($criteria['typeDemande'] ?? null)
            ->setNumeroDemande($criteria['numeroDemande'] ?? null)
            ->setMatricule($criteria['matricule'] ?? null)
            ->setNomPrenoms($criteria['nomPrenoms'] ?? null)
            ->setDateDemande($criteria['dateDemande'] ?? null)
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

        // Création du formulaire avec l'EntityManager
        $form = $this->getFormFactory()->createBuilder(DemandeCongeType::class, $congeSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        // S'assurer que $options est un tableau
        if (!is_array($options)) {
            $options = [];
        }

        // Ajouter l'option admin si elle n'existe pas
        if (!isset($options['admin'])) {
            $options['admin'] = in_array(1, $this->getUser()->getRoleIds());
        }

        // Récupérer les congés filtrés pour le calendrier
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        $rawConges = $repository->findAndFilteredExcel($congeSearch, $options, $this->getUser());

        // Transformer les objets DemandeConge en tableaux simples pour la vue
        $conges = [];
        foreach ($rawConges as $conge) {
            $conges[] = [
                'id' => $conge->getId(),
                'typeDemande' => $conge->getTypeDemande(),
                'numeroDemande' => $conge->getNumeroDemande(),
                'matricule' => $conge->getMatricule(),
                'nomPrenoms' => $conge->getNomPrenoms(),
                'dateDemande' => $conge->getDateDemande() ? $conge->getDateDemande()->format('Y-m-d H:i:s') : null,
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut() ? [
                    'date' => $conge->getDateDebut()->format('Y-m-d H:i:s')
                ] : null,
                'dateFin' => $conge->getDateFin() ? [
                    'date' => $conge->getDateFin()->format('Y-m-d H:i:s')
                ] : null,
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut() ? $conge->getDateStatut()->format('Y-m-d H:i:s') : null,
                'pdfDemande' => $conge->getPdfDemande(),
            ];
        }

        // Grouper les congés par nom et prénoms pour le calendrier
        $employees = [];
        foreach ($rawConges as $conge) {
            $nomPrenoms = $conge->getNomPrenoms();
            if (!isset($employees[$nomPrenoms])) {
                $employees[$nomPrenoms] = [];
            }

            $employees[$nomPrenoms][] = [
                'id' => $conge->getId(),
                'typeDemande' => $conge->getTypeDemande(),
                'numeroDemande' => $conge->getNumeroDemande(),
                'matricule' => $conge->getMatricule(),
                'nomPrenoms' => $conge->getNomPrenoms(),
                'dateDemande' => $conge->getDateDemande() ? $conge->getDateDemande()->format('Y-m-d H:i:s') : null,
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut() ? [
                    'date' => $conge->getDateDebut()->format('Y-m-d H:i:s')
                ] : null,
                'dateFin' => $conge->getDateFin() ? [
                    'date' => $conge->getDateFin()->format('Y-m-d H:i:s')
                ] : null,
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut() ? $conge->getDateStatut()->format('Y-m-d H:i:s') : null,
                'pdfDemande' => $conge->getPdfDemande(),
            ];
        }

        // Affichage du template
        return $this->render('ddc/conge_view.html.twig', [
            'conges' => $conges,
            'employees' => $employees,
            'criteria' => $criteria,
            'form' => $form->createView(),
            'viewMode' => 'calendar', // Ajout du mode d'affichage
        ]);
    }
}
