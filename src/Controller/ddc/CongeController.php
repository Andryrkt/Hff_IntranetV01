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
        $this->autorisationAcces(Application::ID_DDC);
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

        // Récupérer l'état du filtre "Groupe Direction" depuis la requête
        $groupeDirection = $request->query->get('groupeDirection');

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

            // Si des matricules multiples sont présents dans les options, les ajouter aux critères
            if (isset($options['matricules']) && is_array($options['matricules'])) {
                $criteria['matricule'] = implode(',', $options['matricules']);
            }

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

            // Enregistrer l'état du filtre "Groupe Direction" dans la session
            $this->sessionService->set('groupe_direction_filter', $groupeDirection);
        } else if (!$isDirectAccess) {
            // Utiliser les options de recherche stockées dans la session si disponibles
            // (seulement si ce n'est pas un accès direct)
            $sessionOptions = $this->sessionService->get('conge_search_option', []);
            $options = $sessionOptions;

            // Récupérer l'état du filtre "Groupe Direction" depuis la session
            $groupeDirection = $this->sessionService->get('groupe_direction_filter', false);
        } else {
            // Pour un accès direct, réinitialiser le filtre "Groupe Direction"
            $this->sessionService->remove('groupe_direction_filter');
        }

        // Déterminer les codes agence/service pour l'affichage même si le formulaire n'a pas été soumis
        $agenceCode = $options['agence'] ?? null;
        $serviceCode = $options['service'] ?? null;

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 50;

        // Vérifier si le filtre "Groupe Direction" est activé
        // Le champ groupeDirection est dans le formulaire imbriqué 'demande_conge'
        $groupeDirection = $request->query->get('demande_conge')['groupeDirection'] ?? null;
        // Si le champ n'est pas dans le tableau imbriqué, vérifier directement
        $groupeDirection = $groupeDirection ?: $request->query->get('groupeDirection');

        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);

        if ($groupeDirection) {
            // Si le filtre "Groupe Direction" est coché, ignorer tous les autres filtres
            $paginationData = $repository->findCongesByGroupeDirection($page, $limit, $this->getUser());
        } else {
            // Sinon, utiliser la logique normale de recherche avec tous les filtres
            $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $congeSearch, $options, $this->getUser());
        }

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
                'dateDemande' => $conge->getDateDemande(),
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut(),
                'dateFin' => $conge->getDateFin(),
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut(),
                'pdfDemande' => $conge->getPdfDemande(),
            ];
        }

        // Grouper les congés par nom et prénoms pour le calendrier
        $employees = [];
        foreach ($rawCongesForCalendar as $conge) {
            // Construire la clé au format agenceService_matricule_nomPrenoms
            $agenceServiceCode = $conge->getAgenceServiceirium() ? $conge->getAgenceServiceirium()->getServicesagepaie() : null;
            $codeAgenceService = $agenceServiceCode ? $this->getCodeAgenceService($agenceServiceCode) : 'N/A';
            $matricule = $conge->getMatricule() ?? 'N/A';
            $nomPrenoms = $conge->getNomPrenoms() ?? 'N/A';

            $employeeKey = $codeAgenceService . '_' . $matricule . '_' . $nomPrenoms;

            if (!isset($employees[$employeeKey])) {
                $employees[$employeeKey] = [];
            }

            $employees[$employeeKey][] = [
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

        // Transformer les objets DemandeConge en tableaux simples pour la vue
        $data = [];
        foreach ($paginationData['data'] as $conge) {
            $data[] = [
                'id' => $conge->getId(),
                'typeDemande' => $conge->getTypeDemande(),
                'numeroDemande' => $conge->getNumeroDemande(),
                'matricule' => $conge->getMatricule(),
                'nomPrenoms' => $conge->getNomPrenoms(),
                'dateDemande' => $conge->getDateDemande(),
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut(),
                'dateFin' => $conge->getDateFin(),
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut(),
                'pdfDemande' => $conge->getPdfDemande(),
                'codeAgenceService' => $conge->getCodeAgenceService(),
            ];
        }

        // Déterminer le mois à afficher dans le calendrier
        $selectedMonth = null;

        // Vérifier si une date de début est spécifiée dans les critères
        // Les dates peuvent être dans différents formats selon leur provenance
        if (isset($criteria['dateDebut']) && $criteria['dateDebut']) {
            if ($criteria['dateDebut'] instanceof \DateTimeInterface) {
                $selectedMonth = $criteria['dateDebut'];
            } else {
                // Essayer différents formats de date
                $selectedMonth = \DateTime::createFromFormat('Y-m-d', $criteria['dateDebut']);
                if (!$selectedMonth) {
                    $selectedMonth = \DateTime::createFromFormat('d-m-Y', $criteria['dateDebut']);
                }
            }
        }

        // Si pas de date de début ou si la conversion a échoué, essayer avec dateFin
        if (!$selectedMonth && isset($criteria['dateFin']) && $criteria['dateFin']) {
            if ($criteria['dateFin'] instanceof \DateTimeInterface) {
                $selectedMonth = $criteria['dateFin'];
            } else {
                // Essayer différents formats de date
                $selectedMonth = \DateTime::createFromFormat('Y-m-d', $criteria['dateFin']);
                if (!$selectedMonth) {
                    $selectedMonth = \DateTime::createFromFormat('d-m-Y', $criteria['dateFin']);
                }
            }
        }

        if (!$selectedMonth) {
            // Sinon, utiliser le mois en cours
            $selectedMonth = new \DateTime();
        }

        // Affichage du template pour la liste
        return $this->render(
            'ddc/conge_list_view.html.twig',
            [
                'form' => $form->createView(),
                'congeSearch' => $congeSearch,
                'data' => $data,
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $filteredCriteria,
                'conges' => $conges,
                'employees' => $employees,
                'viewMode' => 'list',
                'selected_month' => $selectedMonth,
                'title' => 'Liste des demandes de congé'
            ]
        );
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

        $request = Request::createFromGlobals();

        // Récupérer toutes les demandes de congé pour les afficher dans le calendrier
        // On peut filtrer selon les critères enregistrés dans la session
        $criteria = $this->sessionService->get('conge_search_criteria', []);
        $options = $this->sessionService->get('conge_search_option', []);

        // Vérifier s'il s'agit d'un accès direct à la route (sans paramètres de recherche)
        // Dans ce cas, nous réinitialisons tous les filtres
        $isDirectAccess = empty($request->query->all());

        if ($isDirectAccess) {
            // Réinitialiser tous les filtres - créer un objet vide sans données de session
            $congeSearch = new DemandeConge();

            // Effacer les critères de recherche de la session
            $this->sessionService->remove('conge_search_criteria');
            $this->sessionService->remove('conge_search_option');
        } else {
            // Utiliser les critères de recherche stockés dans la session si disponibles
            // ou directement depuis la requête si le formulaire est soumis
            if ($request->query->count() > 0) {
                // Extraire les données du formulaire depuis la requête
                $formData = $request->query->get('demande_conge', []);

                $congeSearch = new DemandeConge();

                // Remplir l'objet congeSearch avec les données de la requête
                $congeSearch->setMatricule($formData['matricule'] ?? null)
                    ->setDateDebut($formData['dateDebut'] ? new \DateTime($formData['dateDebut']) : null)
                    ->setDateFin($formData['dateFin'] ? new \DateTime($formData['dateFin']) : null)
                    ->setDateDemande($formData['dateDemande'] ? new \DateTime($formData['dateDemande']) : null)
                    ->setNumeroDemande($formData['numeroDemande'] ?? null)
                    ->setStatutDemande($formData['statutDemande'] ?? null);

                // Stocker les critères dans la session
                $criteria = $congeSearch->toArray();
                $criteria['dateDebut'] = $formData['dateDebut'] ?? null;
                $criteria['dateFin'] = $formData['dateFin'] ?? null;
                $criteria['dateDemande'] = $formData['dateDemande'] ?? null;
                $criteria['statutDemande'] = $formData['statutDemande'] ?? null;

                $this->sessionService->set('conge_search_criteria', $criteria);
                $this->sessionService->set('conge_search_option', $options);
            } else {
                // Utiliser les critères de la session
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
            }
        }

        // Création du formulaire avec l'EntityManager
        $form = $this->getFormFactory()->createBuilder(DemandeCongeType::class, $congeSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        $form->handleRequest($request);

        // S'assurer que $options est un tableau
        if (!is_array($options)) {
            $options = [];
        }

        // Ajouter l'option admin si elle n'existe pas
        if (!isset($options['admin'])) {
            $options['admin'] = in_array(1, $this->getUser()->getRoleIds());
        }

        // Récupérer l'état du filtre "Groupe Direction" depuis la requête (s'il est soumis)
        // Le champ groupeDirection est dans le formulaire imbriqué 'demande_conge'
        $groupeDirection = $request->query->get('demande_conge')['groupeDirection'] ?? null;
        // Si le champ n'est pas dans le tableau imbriqué, vérifier directement
        $groupeDirection = $groupeDirection ?: $request->query->get('groupeDirection');

        // Si le formulaire est soumis, mettre à jour le filtre dans la session
        if ($form->isSubmitted() && $form->isValid()) {
            $this->sessionService->set('groupe_direction_filter', $groupeDirection);
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
        } else {
            // Sinon, récupérer l'état du filtre "Groupe Direction" depuis la session
            $groupeDirection = $this->sessionService->get('groupe_direction_filter', false);
        }

        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);

        if ($groupeDirection) {
            // Si le filtre "Groupe Direction" est activé, ignorer tous les autres filtres
            $rawConges = $repository->findCongesByGroupeDirectionExcel($this->getUser());
        } else {
            // Sinon, utiliser la logique normale de recherche avec tous les filtres
            $rawConges = $repository->findAndFilteredExcel($congeSearch, $options, $this->getUser());
        }

        // Transformer les objets DemandeConge en tableaux simples pour la vue
        $conges = [];
        foreach ($rawConges as $conge) {
            $conges[] = [
                'id' => $conge->getId(),
                'typeDemande' => $conge->getTypeDemande(),
                'numeroDemande' => $conge->getNumeroDemande(),
                'matricule' => $conge->getMatricule(),
                'nomPrenoms' => $conge->getNomPrenoms(),
                'dateDemande' => $conge->getDateDemande(),
                'agenceDebiteur' => $conge->getAgenceDebiteur(),
                'adresseMailDemandeur' => $conge->getAdresseMailDemandeur(),
                'sousTypeDocument' => $conge->getSousTypeDocument(),
                'dureeConge' => $conge->getDureeConge(),
                'dateDebut' => $conge->getDateDebut(),
                'dateFin' => $conge->getDateFin(),
                'soldeConge' => $conge->getSoldeConge(),
                'motifConge' => $conge->getMotifConge(),
                'statutDemande' => $conge->getStatutDemande(),
                'dateStatut' => $conge->getDateStatut(),
                'pdfDemande' => $conge->getPdfDemande(),
            ];
        }

        // Grouper les congés par nom et prénoms pour le calendrier
        $employees = [];
        foreach ($rawConges as $conge) {
            // Construire la clé au format agenceService_matricule_nomPrenoms
            $agenceServiceCode = $conge->getAgenceServiceirium() ? $conge->getAgenceServiceirium()->getServicesagepaie() : null;
            $codeAgenceService = $agenceServiceCode ? $this->getCodeAgenceService($agenceServiceCode) : 'N/A';
            $matricule = $conge->getMatricule() ?? 'N/A';
            $nomPrenoms = $conge->getNomPrenoms() ?? 'N/A';

            $employeeKey = $codeAgenceService . '_' . $matricule . '_' . $nomPrenoms;

            if (!isset($employees[$employeeKey])) {
                $employees[$employeeKey] = [];
            }

            $employees[$employeeKey][] = [
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

        // Déterminer le mois à afficher dans le calendrier
        $selectedMonth = null;

        // Vérifier si une date de début est spécifiée dans les critères
        // Les dates peuvent être dans différents formats selon leur provenance
        if (isset($criteria['dateDebut']) && $criteria['dateDebut']) {
            if ($criteria['dateDebut'] instanceof \DateTimeInterface) {
                $selectedMonth = $criteria['dateDebut'];
            } else {
                // Essayer différents formats de date
                $selectedMonth = \DateTime::createFromFormat('Y-m-d', $criteria['dateDebut']);
                if (!$selectedMonth) {
                    $selectedMonth = \DateTime::createFromFormat('d-m-Y', $criteria['dateDebut']);
                }
            }
        }

        // Si pas de date de début ou si la conversion a échoué, essayer avec dateFin
        if (!$selectedMonth && isset($criteria['dateFin']) && $criteria['dateFin']) {
            if ($criteria['dateFin'] instanceof \DateTimeInterface) {
                $selectedMonth = $criteria['dateFin'];
            } else {
                // Essayer différents formats de date
                $selectedMonth = \DateTime::createFromFormat('Y-m-d', $criteria['dateFin']);
                if (!$selectedMonth) {
                    $selectedMonth = \DateTime::createFromFormat('d-m-Y', $criteria['dateFin']);
                }
            }
        }

        if (!$selectedMonth) {
            // Sinon, utiliser le mois en cours
            $selectedMonth = new \DateTime();
        }

        // Affichage du template
        return $this->render('ddc/conge_calendar_view.html.twig', [
            'conges' => $conges,
            'employees' => $employees,
            'criteria' => $criteria,
            'form' => $form->createView(),
            'viewMode' => 'calendar',
            'selected_month' => $selectedMonth,
            'title' => 'Liste des demandes de congé',
        ]);
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

        // Récupère les critères dans la session
        $criteria = $this->sessionService->get('conge_search_criteria', []);
        $option = $this->sessionService->get('conge_search_option', []);

        // Extraire les filtres de la requête pour surcharger ceux de la session
        $formData = $request->query->get('demande_conge', []);

        // Mettre à jour les critères avec les valeurs de la requête si elles existent
        if (!empty($formData)) {
            $criteria['matricule'] = $formData['matricule'] ?? $criteria['matricule'] ?? null;
            $criteria['dateDebut'] = $formData['dateDebut'] ?? $criteria['dateDebut'] ?? null;
            $criteria['dateFin'] = $formData['dateFin'] ?? $criteria['dateFin'] ?? null;
            $criteria['dateDemande'] = $formData['dateDemande'] ?? $criteria['dateDemande'] ?? null;
            $criteria['numeroDemande'] = $formData['numeroDemande'] ?? $criteria['numeroDemande'] ?? null;
            $criteria['service'] = $formData['service'] ?? $criteria['service'] ?? null;
            $criteria['statutDemande'] = $formData['statutDemande'] ?? $criteria['statutDemande'] ?? null;

            // Mettre à jour les options avec les valeurs de la requête
            $option['agence'] = $formData['agence'] ?? $option['agence'] ?? null;
            $option['service'] = $formData['service'] ?? $option['service'] ?? null;
        }

        // Récupère le mois et l'année sélectionnés dans la requête
        $year = $request->query->get('year');
        $month = $request->query->get('month');

        // Si pas de mois/année spécifiés dans la requête, essayer d'utiliser les dates de filtre
        if (!$year || !$month) {
            // Utiliser la date de début du filtre si disponible
            if (!empty($criteria['dateDebut']) && $criteria['dateDebut'] instanceof \DateTimeInterface) {
                $selectedDate = $criteria['dateDebut'];
            } elseif (!empty($criteria['dateDebut']) && is_string($criteria['dateDebut'])) {
                $selectedDate = new \DateTime($criteria['dateDebut']);
            } elseif (!empty($criteria['dateFin']) && $criteria['dateFin'] instanceof \DateTimeInterface) {
                $selectedDate = $criteria['dateFin'];
            } elseif (!empty($criteria['dateFin']) && is_string($criteria['dateFin'])) {
                $selectedDate = new \DateTime($criteria['dateFin']);
            } else {
                // Si aucune date de filtre n'est disponible, utiliser le mois en cours
                $selectedDate = new \DateTime();
            }
        } else {
            $selectedDate = new \DateTime($year . '-' . $month . '-01');
        }


        // S'assurer que $option est toujours un tableau
        if (!is_array($option)) {
            $option = [];
        }

        // Ajouter l'option admin si elle n'existe pas
        if (!isset($option['admin'])) {
            $option['admin'] = in_array(1, $this->getUser()->getRoleIds());
        }

        // Convertir les dates du format string au format DateTime si nécessaire
        // Gérer les différents formats de date possibles
        if (isset($criteria['dateDemande']) && is_string($criteria['dateDemande'])) {
            // Essayer différents formats de date
            $date = \DateTime::createFromFormat('Y-m-d', $criteria['dateDemande']);
            if ($date === false) {
                $date = \DateTime::createFromFormat('d-m-Y', $criteria['dateDemande']);
            }
            $criteria['dateDemande'] = $date ?: null;
        } elseif (isset($criteria['dateDemande']) && !($criteria['dateDemande'] instanceof \DateTimeInterface)) {
            // Si ce n'est pas une chaîne ni un objet DateTime, le définir à null
            $criteria['dateDemande'] = null;
        }
        if (isset($criteria['dateDemandeFin']) && is_string($criteria['dateDemandeFin'])) {
            // Essayer différents formats de date
            $date = \DateTime::createFromFormat('Y-m-d', $criteria['dateDemandeFin']);
            if ($date === false) {
                $date = \DateTime::createFromFormat('d-m-Y', $criteria['dateDemandeFin']);
            }
            $criteria['dateDemandeFin'] = $date ?: null;
        } elseif (isset($criteria['dateDemandeFin']) && !($criteria['dateDemandeFin'] instanceof \DateTimeInterface)) {
            // Si ce n'est pas une chaîne ni un objet DateTime, le définir à null
            $criteria['dateDemandeFin'] = null;
        }
        if (isset($criteria['dateDebut']) && is_string($criteria['dateDebut'])) {
            // Essayer différents formats de date
            $date = \DateTime::createFromFormat('Y-m-d', $criteria['dateDebut']);
            if ($date === false) {
                $date = \DateTime::createFromFormat('d-m-Y', $criteria['dateDebut']);
            }
            $criteria['dateDebut'] = $date ?: null;
        } elseif (isset($criteria['dateDebut']) && !($criteria['dateDebut'] instanceof \DateTimeInterface)) {
            // Si ce n'est pas une chaîne ni un objet DateTime, le définir à null
            $criteria['dateDebut'] = null;
        }
        if (isset($criteria['dateFin']) && is_string($criteria['dateFin'])) {
            // Essayer différents formats de date
            $date = \DateTime::createFromFormat('Y-m-d', $criteria['dateFin']);
            if ($date === false) {
                $date = \DateTime::createFromFormat('d-m-Y', $criteria['dateFin']);
            }
            $criteria['dateFin'] = $date ?: null;
        } elseif (isset($criteria['dateFin']) && !($criteria['dateFin'] instanceof \DateTimeInterface)) {
            // Si ce n'est pas une chaîne ni un objet DateTime, le définir à null
            $criteria['dateFin'] = null;
        }

        $congeSearch = new DemandeConge();

        // Extraction et validation des dates
        $dateDebutValue = $this->validateDateCriteriaValue($criteria['dateDebut'] ?? null);
        $dateFinValue = $this->validateDateCriteriaValue($criteria['dateFin'] ?? null);

        $congeSearch->setTypeDemande($this->validateCriteriaValue($criteria['typeDemande'] ?? null))
            ->setNumeroDemande($this->validateCriteriaValue($criteria['numeroDemande'] ?? null))
            ->setMatricule($this->validateCriteriaValue($criteria['matricule'] ?? null))
            ->setNomPrenoms($this->validateCriteriaValue($criteria['nomPrenoms'] ?? null))
            ->setDateDemande($this->validateDateCriteriaValue($criteria['dateDemande'] ?? null))
            ->setAdresseMailDemandeur($this->validateCriteriaValue($criteria['adresseMailDemandeur'] ?? null))
            ->setSousTypeDocument($this->validateCriteriaValue($criteria['sousTypeDocument'] ?? null))
            ->setDureeConge($this->validateCriteriaValue($criteria['dureeConge'] ?? null))
            ->setDateDebut($dateDebutValue)
            ->setDateFin($dateFinValue)
            ->setSoldeConge($this->validateCriteriaValue($criteria['soldeConge'] ?? null))
            ->setMotifConge($this->validateCriteriaValue($criteria['motifConge'] ?? null))
            ->setStatutDemande($this->validateCriteriaValue($criteria['statutDemande'] ?? null))
            ->setDateStatut($this->validateDateCriteriaValue($criteria['dateStatut'] ?? null))
            ->setPdfDemande($this->validateCriteriaValue($criteria['pdfDemande'] ?? null));

        // Vérification que les dates sont correctement affectées
        if ($dateDebutValue !== null) {
            $congeSearch->setDateDebut($dateDebutValue);
        }
        if ($dateFinValue !== null) {
            $congeSearch->setDateFin($dateFinValue);
        }

        // Vérifier si le filtre "Groupe Direction" est activé
        // Suivre la même logique que dans la méthode listeConge
        // Le champ groupeDirection est dans le formulaire imbriqué 'demande_conge'
        $groupeDirection = $request->query->get('demande_conge')['groupeDirection'] ?? null;
        // Si le champ n'est pas dans le tableau imbriqué, vérifier directement
        $groupeDirection = $groupeDirection ?: $request->query->get('groupeDirection');

        // Si le filtre est présent dans la requête, le mettre à jour dans la session
        if ($groupeDirection !== null) {
            $this->sessionService->set('groupe_direction_filter', $groupeDirection);
        } else {
            // Sinon, utiliser la valeur de la session
            $groupeDirection = $this->sessionService->get('groupe_direction_filter', false);
        }


        // Récupère les entités filtrées
        $repository = $this->getEntityManager()->getRepository(DemandeConge::class);
        if ($groupeDirection) {
            // Si le filtre "Groupe Direction" est activé, ignorer tous les autres filtres
            $entities = $repository->findCongesByGroupeDirectionExcel($this->getUser());
        } else {
            // Sinon, utiliser la logique normale de recherche avec tous les filtres
            $entities = $repository->findAndFilteredExcel($congeSearch, $option, $this->getUser());
        }


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

        // Récupérer le mode d'affichage actuel pour le conserver après réinitialisation
        $currentViewMode = $this->sessionService->get('conge_view_mode', 'list');

        // Redirect to the main congé list with the current view mode
        if ($currentViewMode === 'calendar') {
            return $this->redirectToRoute("conge_calendrier");
        } else {
            return $this->redirectToRoute("conge_liste");
        }
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

        // Récupérer le mode d'affichage actuel pour le conserver après annulation
        $currentViewMode = $this->sessionService->get('conge_view_mode', 'list');

        if ($currentViewMode === 'calendar') {
            return $this->redirectToRoute("conge_calendrier");
        } else {
            return $this->redirectToRoute("conge_liste");
        }
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
     * Valide la valeur d'un critère de recherche
     * Empêche les erreurs de type en retournant null si la valeur n'est pas valide
     */
    private function validateCriteriaValue($value)
    {
        // Si c'est un booléen, on le convertit en valeur appropriée ou null
        if (is_bool($value)) {
            return null;
        }

        // Si c'est un tableau ou un objet, on le convertit en chaîne ou null
        if (is_array($value) || is_object($value)) {
            return null;
        }

        // Pour les chaînes vides, on retourne null
        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Valide la valeur d'un critère de date
     * Empêche les erreurs de type en retournant null si la valeur n'est pas un DateTime
     */
    private function validateDateCriteriaValue($value)
    {
        // Si c'est un booléen ou un tableau, on retourne null
        if (is_bool($value) || is_array($value)) {
            return null;
        }

        // Si c'est une chaîne vide, on retourne null
        if ($value === '') {
            return null;
        }

        // Si c'est déjà un objet DateTime, on le retourne tel quel
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        // Si c'est une chaîne de caractères, on essaie de la convertir
        if (is_string($value)) {
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
                // Si la conversion échoue, on retourne null
                return null;
            }
        }

        // Pour tout autre type, on retourne null
        return null;
    }
}
