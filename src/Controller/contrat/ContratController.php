<?php

namespace App\Controller\contrat;

use App\Controller\Controller;
use App\Entity\contrat\Contrat;
use App\Form\contrat\ContratType;
use App\Controller\Traits\contrat\ContratListeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/documentation/contrats")
 */
class ContratController extends Controller
{
    use ContratListeTrait;

    /**
     * @Route("/nouveau-contrat", name="new_contrat")
     */
    public function nouveauContrat()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        //$this->checkPageAccess($this->estAdmin());

        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => "https://hffc.docuware.cloud/docuware/formsweb/enregistrement-contrats?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
            'pageTitle' => "Nouveau contrat",
            'bgColor'   => "bg-bleu-hff",
            'height'    => 1300,
        ]);
    }
    
    /**
     * Affiche la liste des contrats
     * @Route("/consultation", name="contrat_liste")
     */
    public function listeContrat(Request $request)
    {
        // Vérification si utilisateur connecté
        $this->verifierSessionUtilisateur();

        $contratSearch = new Contrat();

        // Vérifier s'il s'agit d'un accès direct à la route (sans paramètres de recherche)
        $isDirectAccess = empty($request->query->all()) ||
            (count($request->query->all()) == 1 && $request->query->has('page'));

        if ($isDirectAccess) {
            // Réinitialiser tous les filtres
            $contratSearch = new Contrat();
            $this->sessionService->remove('contrat_search_criteria');
            $this->sessionService->remove('contrat_search_option');
        } else {
            // Utiliser les critères de recherche stockés dans la session
            $sessionCriteria = $this->sessionService->get('contrat_search_criteria', []);

            if (!empty($sessionCriteria)) {
                $this->initialisationContrat($contratSearch, $this->getEntityManager());
            }
        }

        // Création du formulaire avec les agences dynamiques
        // Pour le formulaire : Label = code+libellé, Value = code court (ex: '01')
        $agences = $this->getEntityManager()
            ->getRepository(\App\Entity\admin\Agence::class)
            ->findAll();

        $agenceChoices = [];
        foreach ($agences as $agence) {
            // Format ChoiceType : 'Label' => 'value'
            // Label affiché : code+libellé, Valeur envoyée : code court (ex: '01')
            // Car dans la table contrat, le champ 'agence' contient le code court
            $agenceChoices[$agence->getCodeAgence() . '-' . $agence->getLibelleAgence()] = $agence->getCodeAgence();
        }

        // Trier par ordre alphabétique
        ksort($agenceChoices);

        // Charger tous les services possibles pour le formulaire
        $services = $this->getEntityManager()
            ->getRepository(\App\Entity\admin\Service::class)
            ->findAll();

        $serviceChoices = [];
        foreach ($services as $service) {
            // Format ChoiceType : 'Label' => 'value'
            // Les deux sont le code service (ex: 'ATE')
            $serviceChoices[$service->getCodeService() . ' - ' . $service->getLibelleService()] = $service->getCodeService();
        }

        // Trier par ordre alphabétique
        ksort($serviceChoices);

        $form = $this->getFormFactory()->createBuilder(ContratType::class, $contratSearch, [
            'method' => 'GET',
            'agences' => $agenceChoices,
            'services' => $serviceChoices,
        ])->getForm();

        $form->handleRequest($request);

        // Options pour le repository (toujours initialiser avec une valeur par défaut)
        $options = [
            'admin' => in_array(1, $this->getUser()->getRoleIds())
        ];

        // Si le formulaire est soumis, traiter les données (tous les champs sont optionnels)
        if ($form->isSubmitted()) {
            $contratSearch = $form->getData();

            // Gestion spéciale pour les références multiples
            // On divise la chaîne de références multiples pour la recherche
            $originalReference = $contratSearch->getReferenceSearch();
            if ($originalReference && strpos($originalReference, ',') !== false) {
                // Si plusieurs références sont fournies, on les conserve dans les options
                $references = explode(',', $originalReference);
                $references = array_map('trim', $references);
                $references = array_filter($references, function ($value) {
                    return $value !== '';
                });

                // On sauvegarde les références multiples dans les options pour le filtre spécifique
                if (!empty($references)) {
                    $options['references'] = $references;
                }
            }

            // Stocker les critères dans la session
            $criteria = [
                'reference' => $contratSearch->getReferenceSearch(),
                'date_enregistrement_debut' => $contratSearch->getDateEnregistrementDebut(),
                'date_enregistrement_fin' => $contratSearch->getDateEnregistrementFin(),
                'agence' => $contratSearch->getAgenceSearch(),
                'service' => $contratSearch->getServiceSearch(),
                'nom_partenaire' => $contratSearch->getNomPartenaireSearch(),
                'type_tiers' => $contratSearch->getTypeTiersSearch(),
                'date_debut_contrat' => $contratSearch->getDateDebutContrat(),
                'date_fin_contrat' => $contratSearch->getDateFinContrat(),
                'statut' => $contratSearch->getStatut(),
            ];

            // Si des références multiples sont présentes dans les options, les ajouter aux critères
            if (isset($options['references']) && is_array($options['references'])) {
                $criteria['reference'] = implode(', ', $options['references']);
            }

            $this->sessionService->set('contrat_search_criteria', $criteria);
            $this->sessionService->set('contrat_search_option', $options);
        } else {
            // Utiliser les options de recherche stockées dans la session
            $sessionOptions = $this->sessionService->get('contrat_search_option', []);
            if (!empty($sessionOptions)) {
                $options = $sessionOptions;
            }
            // Sinon, garder les options par défaut (avec 'admin' uniquement)
        }

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 50;

        // Recherche paginée
        $repository = $this->getEntityManager()->getRepository(Contrat::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $contratSearch, $options);

        // Formater les critères pour l'affichage
        $criteriaTab = [
            'reference' => $contratSearch->getReferenceSearch(),
            'statut' => $contratSearch->getStatut(),
            'agence' => $contratSearch->getAgenceSearch(),
            'service' => $contratSearch->getServiceSearch(),
            'nom_partenaire' => $contratSearch->getNomPartenaireSearch(),
            'type_tiers' => $contratSearch->getTypeTiersSearch(),
        ];

        // Filtrer les critères vides
        $filteredCriteria = array_filter($criteriaTab);

        // Transformer les objets Contrat en tableaux pour la vue
        $data = [];
        foreach ($paginationData['data'] as $contrat) {
            // Récupérer le libellé complet de l'agence pour l'affichage
            $agenceLibelleComplet = $contrat->getAgence();
            $agenceEntity = $this->getEntityManager()
                ->getRepository(\App\Entity\admin\Agence::class)
                ->findOneBy(['codeAgence' => $contrat->getAgence()]);

            if ($agenceEntity) {
                $agenceLibelleComplet = $agenceEntity->getCodeAgence() . '-' . $agenceEntity->getLibelleAgence();
            }

            // Récupérer le libellé complet du service pour l'affichage
            $serviceLibelleComplet = $contrat->getService();
            $serviceEntity = $this->getEntityManager()
                ->getRepository(\App\Entity\admin\Service::class)
                ->findOneBy(['codeService' => $contrat->getService()]);

            if ($serviceEntity) {
                $serviceLibelleComplet = $serviceEntity->getCodeService() . '-' . $serviceEntity->getLibelleService();
            }

            $data[] = [
                'id' => $contrat->getId(),
                'reference' => $contrat->getReference(),
                'objet' => $contrat->getObjet(),
                'date_enregistrement' => $contrat->getDateEnregistrement(),
                'statut' => $contrat->getStatut(),
                'agence' => $agenceLibelleComplet, // Affichage avec libellé complet
                'agence_code' => $contrat->getAgence(), // Code court pour les traitements
                'service' => $serviceLibelleComplet, // Affichage avec libellé complet
                'service_code' => $contrat->getService(), // Code court pour les traitements
                'nom_partenaire' => $contrat->getNomPartenaire(),
                'type_tiers' => $contrat->getTypeTiers(),
                'date_debut_contrat' => $contrat->getDateDebutContrat(),
                'date_fin_contrat' => $contrat->getDateFinContrat(),
                'piece_jointe' => $contrat->getPieceJointe(),
            ];
        }

        // Charger les services pour l'agence sélectionnée (pour le remplissage initial du select)
        $servicesForSelectedAgence = [];
        $selectedAgenceCode = $contratSearch->getAgenceSearch();
        if ($selectedAgenceCode) {
            // Le code est déjà court maintenant (ex: '01')
            // Chercher l'agence par son code court
            $agenceEntity = $this->getEntityManager()
                ->getRepository(\App\Entity\admin\Agence::class)
                ->findOneBy(['codeAgence' => $selectedAgenceCode]);

            if ($agenceEntity) {
                foreach ($agenceEntity->getServices() as $service) {
                    $servicesForSelectedAgence[] = [
                        'value' => $service->getCodeService(), // Utiliser code_service comme valeur
                        'text' => $service->getCodeService() . ' - ' . $service->getLibelleService()
                    ];
                }
            }
        }

        return $this->render(
            'contrat/contrat_list_view.html.twig',
            [
                'form' => $form->createView(),
                'contratSearch' => $contratSearch,
                'data' => $data,
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'totalPages' => $paginationData['totalPages'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $filteredCriteria,
                'title' => 'Liste des contrats',
                'servicesForSelectedAgence' => $servicesForSelectedAgence
            ]
        );
    }

    /**
     * Affiche le détail d'un contrat
     * @Route("/{id}/show", name="contrat_show")
     */
    public function showContrat(int $id)
    {
        $this->verifierSessionUtilisateur();

        $contrat = $this->getEntityManager()->getRepository(Contrat::class)->findWithDetails($id);

        if (!$contrat) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        return $this->render('contrat/contrat_show.html.twig', [
            'contrat' => $contrat,
        ]);
    }

    /**
     * Export Excel des contrats
     * @Route("/export-excel", name="contrat_export_excel")
     */
    public function exportExcel(Request $request)
    {
        $this->verifierSessionUtilisateur();

        $contratSearch = new Contrat();

        // Récupérer les paramètres de recherche depuis la requête (comme dans le module congé)
        $formSubmitted = $request->query->has('contrat');

        if ($formSubmitted) {
            // Initialiser avec les critères de la session d'abord
            $this->initialisationContrat($contratSearch, $this->getEntityManager());

            // Puis écraser avec les paramètres de la requête actuelle
            $contratData = $request->query->all()['contrat'] ?? [];

            if (!empty($contratData)) {
                // Définir les critères depuis la requête
                if (isset($contratData['referenceSearch']) && $contratData['referenceSearch'] !== '') {
                    $contratSearch->setReferenceSearch($contratData['referenceSearch']);
                }
                if (isset($contratData['agenceSearch']) && $contratData['agenceSearch'] !== '') {
                    $contratSearch->setAgenceSearch($contratData['agenceSearch']);
                }
                if (isset($contratData['serviceSearch']) && $contratData['serviceSearch'] !== '') {
                    $contratSearch->setServiceSearch($contratData['serviceSearch']);
                }
                if (isset($contratData['nom_partenaireSearch']) && $contratData['nom_partenaireSearch'] !== '') {
                    $contratSearch->setNomPartenaireSearch($contratData['nom_partenaireSearch']);
                }
                if (isset($contratData['type_tiersSearch']) && $contratData['type_tiersSearch'] !== '') {
                    $contratSearch->setTypeTiersSearch($contratData['type_tiersSearch']);
                }
                if (isset($contratData['date_enregistrement_debut']) && $contratData['date_enregistrement_debut'] !== '') {
                    $contratSearch->setDateEnregistrementDebut(new \DateTime($contratData['date_enregistrement_debut']));
                }
                if (isset($contratData['date_enregistrement_fin']) && $contratData['date_enregistrement_fin'] !== '') {
                    $contratSearch->setDateEnregistrementFin(new \DateTime($contratData['date_enregistrement_fin']));
                }
                if (isset($contratData['date_debut_contrat']) && $contratData['date_debut_contrat'] !== '') {
                    $contratSearch->setDateDebutContrat(new \DateTime($contratData['date_debut_contrat']));
                }
                if (isset($contratData['date_fin_contrat']) && $contratData['date_fin_contrat'] !== '') {
                    $contratSearch->setDateFinContrat(new \DateTime($contratData['date_fin_contrat']));
                }
            }
        } else {
            // Utiliser les critères de la session
            $this->initialisationContrat($contratSearch, $this->getEntityManager());
        }

        $options = $this->sessionService->get('contrat_search_option', []);

        // S'assurer que $options n'est pas null
        if ($options === null) {
            $options = [];
        }

        $repository = $this->getEntityManager()->getRepository(Contrat::class);
        $contrats = $repository->findAllForExport($contratSearch, $options);

        // Formater les données pour Excel (format liste)
        $data = $this->formatContratListExport($contrats);

        // Générer le nom du fichier avec la date
        $filename = "contrats_export_" . date('Y-m-d_His');

        // Crée le fichier Excel
        $this->getExcelService()->createSpreadsheet($data, $filename);
        exit();
    }

    /**
     * Formate les données des contrats pour l'export Excel
     */
    private function formatContratListExport($contrats): array
    {
        $data = [];

        // Première ligne : les en-têtes
        $data[] = [
            "Référence",
            "Objet",
            "Date enregistrement",
            "Statut",
            "Agence",
            "Service",
            "Partenaire",
            "Type tiers",
            "Date début contrat",
            "Date fin contrat",
            "Pièce jointe"
        ];

        // Lignes suivantes : les données
        foreach ($contrats as $contrat) {
            // Récupérer le libellé complet de l'agence pour l'export
            $agenceLibelleComplet = $contrat->getAgence();
            $agenceEntity = $this->getEntityManager()
                ->getRepository(\App\Entity\admin\Agence::class)
                ->findOneBy(['codeAgence' => $contrat->getAgence()]);

            if ($agenceEntity) {
                $agenceLibelleComplet = $agenceEntity->getCodeAgence() . '-' . $agenceEntity->getLibelleAgence();
            }

            // Récupérer le libellé complet du service pour l'export
            $serviceLibelleComplet = $contrat->getService();
            $serviceEntity = $this->getEntityManager()
                ->getRepository(\App\Entity\admin\Service::class)
                ->findOneBy(['codeService' => $contrat->getService()]);

            if ($serviceEntity) {
                $serviceLibelleComplet = $serviceEntity->getCodeService() . '-' . $serviceEntity->getLibelleService();
            }

            $data[] = [
                $contrat->getReference(),
                $contrat->getObjet() ?? '-',
                $contrat->getDateEnregistrement() ? $contrat->getDateEnregistrement()->format('d/m/Y') : '-',
                $contrat->getStatut() ?? '-',
                $agenceLibelleComplet, // Affichage avec libellé complet
                $serviceLibelleComplet, // Affichage avec libellé complet
                $contrat->getNomPartenaire() ?? '-',
                $contrat->getTypeTiers() ?? '-',
                $contrat->getDateDebutContrat() ? $contrat->getDateDebutContrat()->format('d/m/Y') : '-',
                $contrat->getDateFinContrat() ? $contrat->getDateFinContrat()->format('d/m/Y') : '-',
                $contrat->getPieceJointe() ?? '-',
            ];
        }
        return $data;
    }

    /**
     * API: Récupère toutes les références de contrats pour l'autocomplete
     * @Route("/api/references", name="contrat_api_references", methods={"GET"})
     */
    public function getReferences()
    {
        $this->verifierSessionUtilisateur();

        $repository = $this->getEntityManager()->getRepository(Contrat::class);
        $contrats = $repository->findAll();

        $references = [];
        foreach ($contrats as $contrat) {
            $references[] = [
                'reference' => $contrat->getReference(),
                'objet' => $contrat->getObjet(),
            ];
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($references);
    }

    /**
     * API: Récupère tous les partenaires uniques pour l'autocomplete
     * @Route("/api/partenaires", name="contrat_api_partenaires", methods={"GET"})
     */
    public function getPartenaires()
    {
        $this->verifierSessionUtilisateur();

        $repository = $this->getEntityManager()->getRepository(Contrat::class);
        $contrats = $repository->findAll();

        // Extraire les partenaires uniques
        $partenaires = [];
        foreach ($contrats as $contrat) {
            $nomPartenaire = $contrat->getNomPartenaire();
            if ($nomPartenaire && !in_array($nomPartenaire, $partenaires)) {
                $partenaires[] = $nomPartenaire;
            }
        }

        // Trier par ordre alphabétique
        sort($partenaires);

        // Formater pour l'autocomplete
        $data = [];
        foreach ($partenaires as $partenaire) {
            $data[] = [
                'nom' => $partenaire,
            ];
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($data);
    }
}
