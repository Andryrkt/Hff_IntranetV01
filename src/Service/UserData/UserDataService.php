<?php

namespace App\Service\UserData;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\utilisateur\Profil;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\security\SecurityService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Entity\admin\utilisateur\ApplicationProfilPage;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserDataService
{
    private EntityManagerInterface $em;
    private SessionInterface $session;
    private TagAwareCacheInterface $cache;
    private ?User $user = null;
    private ?Profil $cacheProfil = null;

    /** @var array<string, array|null> */
    private array $cachePermissions = [];
    private ?array $cachePagesProfilDonnees = null;

    public function __construct(EntityManagerInterface $em, SessionInterface $session, TagAwareCacheInterface $cache)
    {
        $this->em      = $em;
        $this->session = $session;
        $this->cache   = $cache;
    }

    //================== Méthode Helper - Données de session ==================

    /**
     * Méthode pour vérifier si l'utilisateur est connecté
     */
    public function isUserConnected(): bool
    {
        return $this->session->has('user_info');
    }

    /** 
     * Méthode pour avoir les données de l'utilisateur connecté
     */
    public function getUserInfo(): ?array
    {
        return $this->session->get('user_info', null);
    }

    /**
     * Récupérer l'ID de l'utilisateur
     */
    public function getUserId(): ?int
    {
        $userInfo = $this->getUserInfo();
        return $userInfo['id'] ?? null;
    }

    /**
     * Récupérer l'utilisateur
     */
    public function getUser(): ?User
    {
        if ($this->user === null) {
            $userId = $this->getUserId();
            $this->user = $userId ? $this->em->getRepository(User::class)->find($userId) : null;
        }
        return $this->user;
    }

    /**
     * Récupérer l'email de l'utilisateur
     */
    public function getUserMail(): string
    {
        $userInfo = $this->getUserInfo();
        return $userInfo['email'] ?? "";
    }

    /**
     * Récupérer le nom de l'utilisateur
     */
    public function getUserName(): string
    {
        $userInfo = $this->getUserInfo();
        return $userInfo['username'] ?? "";
    }

    /**
     * Récupérer le profil id enregistré
     */
    public function getProfilId(): ?int
    {
        $userInfo = $this->getUserInfo();
        return $userInfo['profil_id'] ?? NULL;
    }

// =========================================================================
    //  PERMISSIONS
    // =========================================================================

    /**
     * Retourne les permissions d'une route pour le profil connecté.
     * Résultat mis en cache applicatif par (profilId + route).
     *
     * @return array|null  ['peutVoir' => bool, ...] ou null si non configuré
     */
    public function getPermissions(string $nomRoute): ?array
    {
        // 1. Cache mémoire (même requête)
        if (array_key_exists($nomRoute, $this->cachePermissions)) {
            return $this->cachePermissions[$nomRoute];
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return $this->cachePermissions[$nomRoute] = null;
        }

        // 2. Cache applicatif (entre requêtes, partagé par profil)
        $cacheKey = sprintf('profil_%d_permissions_%s', $profilId, md5($nomRoute));

        $donnees = $this->cache->get($cacheKey, function (ItemInterface $item) use ($profilId, $nomRoute) {
            $item->expiresAfter(3600);
            $item->tag(['profil_' . $profilId]);
            return $this->calculerPermissions($nomRoute);
        });

        return $this->cachePermissions[$nomRoute] = $donnees;
    }

    /**
     * Retourne toutes les pages visibles du profil, groupées par application.
     * Structure : ['NomApplication' => [['nom' => ..., 'route' => ...], ...], ...]
     *
     * Stocké en cache applicatif (tableaux de scalaires, pas d'entités).
     * Reconstruit en entités PageHff à la demande via getPagesProfil().
     */
    public function getPagesProfil(): array
    {
        // 1. Cache mémoire
        if ($this->cachePagesProfilDonnees !== null) {
            return $this->cachePagesProfilDonnees;
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return [];
        }

        // 2. Cache applicatif
        $cacheKey = sprintf('profil_%d_pages', $profilId);

        $donnees = $this->cache->get(
            $cacheKey,
            function (ItemInterface $item) use ($profilId) {
                $item->expiresAfter(3600);
                $item->tag(['profil_' . $profilId]);
                return $this->calculerPagesProfil();
            }
        );

        return $this->cachePagesProfilDonnees = $donnees;
    }

    // =========================================================================
    //  ENTITÉS LIÉES AU PROFIL
    //  Rechargées depuis BDD 1 fois par requête (Doctrine IdentityMap garantit
    //  qu'un find() avec le même ID ne fait qu'1 seule requête SQL).
    // =========================================================================

    /**
     * Retourne l'entité Profil de l'utilisateur connecté.
     * Chargée une seule fois par requête (cache mémoire).
     */
    public function getProfil(): ?Profil
    {
        if ($this->cacheProfil !== null) {
            return $this->cacheProfil;
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return null;
        }

        return $this->cacheProfil = $this->em->getRepository(Profil::class)->find($profilId);
    }

    // =========================================================================
    //  INVALIDATION DU CACHE (à appeler si un profil est modifié en admin)
    // =========================================================================

    /**
     * Invalide tout le cache applicatif d'un profil.
     * À appeler après modification des permissions ou des pages d'un profil.
     */
    public function invaliderCacheProfil(int $profilId): void
    {
        $this->cache->invalidateTags(['profil_' . $profilId]);

        // Vider aussi le cache mémoire de la requête courante
        $this->cacheProfil          = null;
        $this->cachePermissions     = [];
        $this->cachePagesProfilDonnees = null;
    }

    // =========================================================================
    //  CALCULS BDD (appelés uniquement sur cache miss)
    // =========================================================================

    /**
     * Calcule les permissions d'une route depuis la BDD.
     * Retourne un tableau de scalaires (sérialisable en cache).
     *
     * @return array|null
     */
    private function calculerPermissions(string $nomRoute): ?array
    {
        $profil = $this->getProfil();
        if ($profil === null) {
            return null;
        }

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                if ($applicationProfilPage->getPage()->getNomRoute() === $nomRoute) {
                    return [
                        SecurityService::PERMISSION_VOIR      => $applicationProfilPage->isPeutVoir(),
                        SecurityService::PERMISSION_AJOUTER   => $applicationProfilPage->isPeutAjouter(),
                        SecurityService::PERMISSION_MODIFIER  => $applicationProfilPage->isPeutModifier(),
                        SecurityService::PERMISSION_SUPPRIMER => $applicationProfilPage->isPeutSupprimer(),
                        SecurityService::PERMISSION_EXPORTER  => $applicationProfilPage->isPeutExporter(),
                    ];
                }
            }
        }

        return null; // Route non configurée = accès refusé
    }

    /**
     * Calcule les pages visibles du profil depuis la BDD.
     * Retourne des tableaux de scalaires (sérialisables en cache).
     */
    private function calculerPagesProfil(): array
    {
        $profil = $this->getProfil();
        if ($profil === null) {
            return [];
        }

        $pages = [];

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            $nomApplication = $applicationProfil->getApplication()->getNom();

            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                if (!$applicationProfilPage->isPeutVoir()) continue;

                $page = $applicationProfilPage->getPage();

                // On stocke uniquement des scalaires (pas d'entité Doctrine)
                $pages[$nomApplication][] = [
                    'nom'    => $page->getNom(),
                    'route'  => $page->getNomRoute(), // nom de la route dans le controleur
                    'lien'   => $page->getLien(), // lien de la page
                ];
            }
        }

        return $pages;
    }
}
