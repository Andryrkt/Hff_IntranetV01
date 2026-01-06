<?php

namespace App\Security;

use App\Entity\admin\Personnel;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserSessionManager
{
    private SessionInterface $session;
    private EntityManagerInterface $entityManager;
    private ?User $user = null; // Cache en mémoire

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    /**
     * Connecte l'utilisateur et sérialise TOUT en session
     * Une seule fois, à la connexion !
     */
    public function login(User $user): void
    {
        // Sérialiser TOUTES les données de l'utilisateur
        $userData = $this->serializeUser($user);

        $this->session->set('_user_serialized', $userData);
        $this->user = null; // Reset du cache mémoire
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void
    {
        $this->session->remove('_user_serialized');
        $this->session->invalidate();
        $this->user = null;
    }

    /**
     * Vérifie si un utilisateur est connecté
     */
    public function isAuthenticated(): bool
    {
        return $this->session->has('_user_serialized');
    }

    /**
     * Récupère l'utilisateur - ZÉRO requête BDD !
     * L'utilisateur est reconstruit depuis la session
     */
    public function getUser(): ?User
    {
        // Si déjà reconstruit en mémoire, le retourner
        if ($this->user !== null) {
            return $this->user;
        }

        // Vérifier si l'utilisateur est en session
        if (!$this->session->has('_user_serialized')) {
            return null;
        }

        $userData = $this->session->get('_user_serialized');

        // Reconstruire l'utilisateur depuis les données sérialisées
        $this->user = $this->unserializeUser($userData);

        return $this->user;
    }

    /**
     * Récupère uniquement les données brutes (encore plus rapide)
     */
    public function getUserData(): ?array
    {
        return $this->session->get('_user_serialized');
    }

    /**
     * Vérifie l'accès à une route (sans requête BDD)
     */
    public function canAccessRoute(string $routeName): bool
    {
        $userData = $this->getUserData();

        if (!$userData) {
            return false;
        }

        return in_array($routeName, $userData['accessible_routes'] ?? []);
    }

    /**
     * Vérifie l'accès à une application (sans requête BDD)
     */
    public function canAccessApplication(int $applicationId): bool
    {
        $userData = $this->getUserData();

        if (!$userData) {
            return false;
        }

        return in_array($applicationId, $userData['application_ids'] ?? []);
    }

    /**
     * Rafraîchit les données utilisateur
     * À appeler si les permissions changent
     */
    public function refresh(): void
    {
        $userData = $this->getUserData();

        if (!$userData) {
            return;
        }

        // Recharger depuis la BDD
        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($userData['id']);

        if ($user) {
            $this->login($user);
        }
    }

    /**
     * Sérialise l'utilisateur et toutes ses relations
     */
    private function serializeUser(User $user): array
    {
        // Sérialiser la société liée
        $societes = $user->getSociettes();
        $societesData = [];
        if ($societes) {
            $societesData[] = [
                'id'   => $societes->getId(),
                'nom'  => $societes->getNom(),
                'code' => $societes->getCodeSociete(),
            ];
        }

        // Sérialiser le personnel
        /** @var Personnel $personnel */
        $personnel = $user->getPersonnels(); // TODO : à sérialiser plus tard si nécéssaire

        // Sérialiser le profil
        $profil = $user->getProfil();
        $profilData = null;
        if ($profil) {
            $profilData = [
                'id' => $profil->getId(),
                'code' => $profil->getCode(),
                'libelle' => $profil->getLibelle(),
                'description' => $profil->getDescription(),
                // Ajoute d'autres propriétés du profil si nécessaire
            ];
        }

        // Sérialiser les applications
        $applications = [];
        $applicationIds = [];
        $accessibleRoutes = [];

        if ($profil) {
            foreach ($profil->getApplications() as $application) {
                $applicationIds[] = $application->getId();

                $pages = [];
                foreach ($application->getPages() as $page) {
                    $accessibleRoutes[] = $page->getRouteName();
                    $pages[] = [
                        'id' => $page->getId(),
                        'nom' => $page->getNom(),
                        'route_name' => $page->getRouteName(),
                        'description' => $page->getDescription(),
                        // Autres propriétés de Page
                    ];
                }

                $applications[] = [
                    'id' => $application->getId(),
                    'nom' => $application->getNom(),
                    'code' => $application->getCode(),
                    'description' => $application->getDescription(),
                    'icone' => $application->getIcone(),
                    'pages' => $pages,
                    // Autres propriétés d'Application
                ];
            }
        }

        // Données complètes de l'utilisateur
        return [
            // Propriétés de base
            'id'                => $user->getId(),
            'nom_utilisateur'   => $user->getNomUtilisateur(),
            'matricule'         => $user->getMatricule(),
            'mail'              => $user->getMail(),
            'societe'           => $societesData,
            'nom'               => $personnel->getNom(),
            'prenom'            => $personnel->getPrenoms(),

            
            'telephone'         => $user->getTelephone(),
            'departement'       => $user->getDepartement(),
            'poste'             => $user->getPoste(),
            'date_creation'     => $user->getDateCreation()?->format('Y-m-d H:i:s'),
            'actif'             => $user->isActif(),

            // Profil complet
            'profil'            => $profilData,

            // Applications complètes avec leurs pages
            'applications'      => $applications,

            // Listes rapides pour les vérifications
            'application_ids'   => $applicationIds,
            'accessible_routes' => array_unique($accessibleRoutes),

            // Métadonnées
            'serialized_at'     => time(),
        ];
    }

    /**
     * Reconstruit l'utilisateur depuis les données sérialisées
     * Crée des objets "détachés" (pas gérés par Doctrine)
     */
    private function unserializeUser(array $userData): User
    {
        // Créer l'utilisateur
        $user = new User();
        $user->setId($userData['id']);
        $user->setNomUtilisateur($userData['nom_utilisateur']);
        $user->setNom($userData['nom']);
        $user->setPrenom($userData['prenom']);
        $user->setEmail($userData['email']);
        $user->setMatricule($userData['matricule'] ?? null);
        $user->setTelephone($userData['telephone'] ?? null);
        $user->setDepartement($userData['departement'] ?? null);
        $user->setPoste($userData['poste'] ?? null);
        $user->setActif($userData['actif'] ?? true);

        if (isset($userData['date_creation'])) {
            $user->setDateCreation(new \DateTime($userData['date_creation']));
        }

        // Reconstruire le profil
        if ($userData['profil']) {
            $profil = new Profil();
            $profil->setId($userData['profil']['id']);
            $profil->setCode($userData['profil']['code']);
            $profil->setLibelle($userData['profil']['libelle']);
            $profil->setDescription($userData['profil']['description'] ?? null);

            // Reconstruire les applications
            foreach ($userData['applications'] as $appData) {
                $application = new Application();
                $application->setId($appData['id']);
                $application->setNom($appData['nom']);
                $application->setCode($appData['code']);
                $application->setDescription($appData['description'] ?? null);
                $application->setIcone($appData['icone'] ?? null);

                // Reconstruire les pages
                foreach ($appData['pages'] as $pageData) {
                    $page = new Page();
                    $page->setId($pageData['id']);
                    $page->setNom($pageData['nom']);
                    $page->setRouteName($pageData['route_name']);
                    $page->setDescription($pageData['description'] ?? null);
                    $page->setApplication($application);

                    $application->addPage($page);
                }

                $profil->addApplication($application);
            }

            $user->setProfil($profil);
        }

        return $user;
    }
}
