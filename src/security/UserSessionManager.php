<?php

namespace App\Security;

use App\Entity\admin\Application;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\utilisateur\Profil;
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

        // Sérialiser le profil
        $profil = $user->getProfil();
        $profilData = [];
        if ($profil) {
            $profilData = [
                'id'          => $profil->getId(),
                'reference'   => $profil->getReference(),
                'designation' => $profil->getDesignation(),
            ];
        }

        // Sérialiser les applications
        $applications = [];
        $applicationIds = [];
        $accessibleRoutes = [];

        if ($profil) {
            /** @var Application $application */
            foreach ($profil->getApplications() as $application) {
                $applicationIds[] = $application->getId();

                $pages = [];
                /** @var PageHff $page */
                foreach ($application->getPages() as $page) {
                    $accessibleRoutes[$page->getNomRoute()] = true;
                    $pages[] = [
                        'id'         => $page->getId(),
                        'nom'        => $page->getNom(),
                        'nom_route'  => $page->getNomRoute(),
                        'lien'       => $page->getLien(),
                    ];
                }

                $applications[] = [
                    'id'         => $application->getId(),
                    'nom'        => $application->getNom(),
                    'code'       => $application->getCodeApp(),
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
            'nom'               => $user->getFirstName(),
            'prenom'            => $user->getLastName(),
            'profil'            => $profilData,
            'applications'      => $applications, // Applications complètes avec leurs pages
            'application_ids'   => $applicationIds, // Listes rapides pour les vérifications
            'accessible_routes' => $accessibleRoutes,
            'serialized_at'     => time(), // Métadonnées
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
        $user->setMatricule($userData['matricule']);
        $user->setMail($userData['mail']);

        // Reconstruire le profil
        $userDataProfil = $userData['profil'];
        if ($userDataProfil) {
            $profil = new Profil();
            $profil->setId($userDataProfil['id']);
            $profil->setReference($userDataProfil['reference']);
            $profil->setDesignation($userDataProfil['designation']);

            // Reconstruire les applications
            foreach ($userData['applications'] as $appData) {
                $application = new Application();
                $application->setId($appData['id']);
                $application->setNom($appData['nom']);
                $application->setCodeApp($appData['code']);

                // Reconstruire les pages
                foreach ($appData['pages'] as $pageData) {
                    $page = new PageHff();
                    $page->setId($pageData['id']);
                    $page->setNom($pageData['nom']);
                    $page->setNomRoute($pageData['nom_route']);
                    $page->setLien($pageData['lien']);

                    $application->addPage($page);
                }

                $applicationProfil = new ApplicationProfil($profil, $application);
                $profil->addApplicationProfil($applicationProfil);
            }

            $user->setProfil($profil);
        }

        return $user;
    }
}
