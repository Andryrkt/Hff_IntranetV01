<?php

namespace App\Service\UserData;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\utilisateur\ApplicationProfilAgenceService;
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
    public const SUFFIX_PAGES        = 'pages';
    public const SUFFIX_PERMISSIONS  = 'permissions';
    public const SUFFIX_AG_SERV_ID   = 'agence_service_id';
    public const SUFFIX_AG_SERV_CODE = 'agence_service_code';
    public const SUFFIX_VERSION      = 'version';
    public const CACHE_KEY_PREFIX    = 'security.profil_';

    private EntityManagerInterface $em;
    private ?SessionInterface $session = null;
    private TagAwareCacheInterface $cache;
    private ?User $user = null;
    private ?Profil $cacheProfil = null;

    /** @var array<string, array|null> */
    private array $cachePermissions = [];
    private ?array $cachePagesProfilDonnees = null;
    private ?array $cacheAgServDonneesId = null;
    private ?array $cacheAgServDonneesCode = null;
    private ?array $cacheRoutesIndex = null;
    private ?int $profilId = null;

    /** Cache mémoire des versions (évite N lectures de pool par requête) */
    private array $cacheVersions = [];

    public function __construct(EntityManagerInterface $em, TagAwareCacheInterface $cache, ?SessionInterface $session = null)
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
     * Récupérer le code société
     */
    public function getCodeSociete(): string
    {
        $userInfo = $this->getUserInfo();
        return $userInfo['societe_code'] ?? "";
    }

    /**
     * Récupérer le profil id enregistré
     */
    public function getProfilId(): ?int
    {
        if ($this->profilId === null) {
            $userInfo = $this->getUserInfo();
            $this->profilId = $userInfo['profil_id'] ?? null;
        }
        return $this->profilId;
    }

    /**
     * Set the value of profilId.
     * Réinitialise automatiquement tous les caches mémoire — indispensable
     * en CLI lors de la boucle multi-profils du warmup pour éviter que les
     * données du profil précédent contaminent le suivant.
     */
    public function setProfilId(?int $profilId): self
    {
        $this->profilId = $profilId;
        $this->resetCacheMemoireIntraRequete();

        return $this;
    }

    /**
     * Vide tous les caches mémoire intra-requête de ce service.
     * Appelé par setProfilId() et par invaliderCacheProfil().
     */
    public function resetCacheMemoireIntraRequete(): void
    {
        $this->cacheProfil             = null;
        $this->cachePermissions        = [];
        $this->cachePagesProfilDonnees = null;
        $this->cacheAgServDonneesId    = null;
        $this->cacheAgServDonneesCode  = null;
        $this->cacheRoutesIndex        = null;
        $this->cacheVersions           = [];
    }

    // =========================================================================
    //  VERSIONING MANUEL — remplace les tags Symfony
    //
    //  Principe : chaque profil possède une "version" stockée dans le pool.
    //  Les clés de données incluent cette version. Invalider = changer la version.
    //  Les anciennes clés deviennent orphelines (jamais relues) puis expirées
    //  naturellement par le pool (LRU, TTL du pool, etc.).
    //
    //  Avantage vs tags Symfony : la version est une simple valeur scalaire lue
    //  dans le même pool par CLI et web → zéro désynchronisation.
    // =========================================================================

    /**
     * Retourne la version courante du profil.
     * Créée automatiquement si elle n'existe pas encore.
     * Mise en cache mémoire pour n'interroger le pool qu'une seule fois par requête.
     */
    private function getVersion(int $profilId): string
    {
        if (isset($this->cacheVersions[$profilId])) {
            return $this->cacheVersions[$profilId];
        }

        $cleVersion = self::CACHE_KEY_PREFIX . $profilId . '.' . self::SUFFIX_VERSION;

        return $this->cacheVersions[$profilId] = $this->cache->get($cleVersion, function (ItemInterface $item): string {
            $item->expiresAfter(null);
            return uniqid('v', true);
        });
    }

    /**
     * Change la version du profil → toutes les clés de données précédentes
     * ne seront plus jamais lues (leurs clés contiennent l'ancienne version).
     */
    private function invaliderVersion(int $profilId): void
    {
        $cleVersion = self::CACHE_KEY_PREFIX . $profilId . '.' . self::SUFFIX_VERSION;
        $this->cache->delete($cleVersion);
        unset($this->cacheVersions[$profilId]);
    }

    /**
     * Construit une clé de cache versionnée : security.profil_{id}.{version}.{suffix}[.{extra}]
     */
    private function buildKey(int $profilId, string $suffix, string $extra = ''): string
    {
        $version = $this->getVersion($profilId);
        $base    = sprintf('%s%d.%s.%s', self::CACHE_KEY_PREFIX, $profilId, $version, $suffix);
        return $extra !== '' ? $base . '.' . $extra : $base;
    }

    // =========================================================================
    //  PERMISSIONS
    // =========================================================================

    /**
     * Écrase et reconstruit les permissions d'une route pour un profil donné.
     * Appelé par CacheWarmupSecurityCommand après invaliderVersion().
     */
    public function ecraserPermissions(string $nomRoute, Profil $profil): void
    {
        $profilId = $profil->getId();
        $cle      = $this->buildKey($profilId, self::SUFFIX_PERMISSIONS, md5($nomRoute));

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item) use ($nomRoute, $profil): ?array {
            $item->expiresAfter(null);
            return $this->calculerPermissions($nomRoute, $profil);
        });
    }

    /**
     * Retourne les permissions d'une route pour le profil connecté.
     */
    public function getPermissions(string $nomRoute): ?array
    {
        // 1. Cache mémoire (même requête)
        if (array_key_exists($nomRoute, $this->cachePermissions)) {
            return $this->cachePermissions[$nomRoute];
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return $this->cachePermissions[$nomRoute] = [];
        }

        // 2. Cache applicatif (entre requêtes, partagé par profil)
        $cle = $this->buildKey($profilId, self::SUFFIX_PERMISSIONS, md5($nomRoute));

        return $this->cachePermissions[$nomRoute] = $this->cache->get($cle, function (ItemInterface $item) use ($nomRoute): ?array {
            $item->expiresAfter(null);
            return $this->calculerPermissions($nomRoute, $this->getProfil());
        });
    }
    
    // =========================================================================
    //  PAGES DU PROFIL
    // =========================================================================

    /**
     * Écrase et reconstruit les pages visibles pour un profil donné.
     */
    public function ecraserPagesProfil(Profil $profil): void
    {
        $profilId = $profil->getId();
        $cle      = $this->buildKey($profilId, self::SUFFIX_PAGES);

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item) use ($profil): array {
            $item->expiresAfter(null);
            return $this->calculerPagesProfil($profil);
        });
    }

    /**
     * Retourne toutes les pages visibles du profil, groupées par application.
     */
    public function getPagesProfil(): ?array
    {
        // 1. Cache mémoire
        if ($this->cachePagesProfilDonnees !== null) {
            return $this->cachePagesProfilDonnees;
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return $this->cachePagesProfilDonnees = [];
        }

        // 2. Cache applicatif
        $cle = $this->buildKey($profilId, self::SUFFIX_PAGES);

        return $this->cachePagesProfilDonnees = $this->cache->get($cle, function (ItemInterface $item): array {
            $item->expiresAfter(null);
            return $this->calculerPagesProfil($this->getProfil());
        });
    }

    // =========================================================================
    //  AGENCES & SERVICES
    // =========================================================================

    /**
     * Écrase et reconstruit les agences et services groupés par ID.
     */
    public function ecraserAgenceServiceGroupById(string $codeApp, Profil $profil): void
    {
        $profilId = $profil->getId();
        $cle      = $this->buildKey($profilId, self::SUFFIX_AG_SERV_ID, md5($codeApp));

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item) use ($codeApp, $profil): array {
            $item->expiresAfter(null);
            return $this->calculerAgenceService($codeApp, $profil, true);
        });
    }

    /**
     * Retourne toutes les agences et services du profil, groupées par ID.
     */
    public function getAgenceServiceGroupById(string $codeApp): ?array
    {
        // 1. Cache mémoire
        if ($this->cacheAgServDonneesId !== null) {
            return $this->cacheAgServDonneesId;
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return $this->cacheAgServDonneesId = [];
        }

        // 2. Cache applicatif
        $cle = $this->buildKey($profilId, self::SUFFIX_AG_SERV_ID, md5($codeApp));

        return $this->cacheAgServDonneesId = $this->cache->get($cle, function (ItemInterface $item) use ($codeApp): array {
            $item->expiresAfter(null);
            return $this->calculerAgenceService($codeApp, $this->getProfil(), true);
        });
    }

    /**
     * Écrase et reconstruit les agences et services groupés par CODE.
     */
    public function ecraserAgenceServiceGroupByCode(string $codeApp, Profil $profil): void
    {
        $profilId = $profil->getId();
        $cle      = $this->buildKey($profilId, self::SUFFIX_AG_SERV_CODE, md5($codeApp));

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item) use ($codeApp, $profil): array {
            $item->expiresAfter(null);
            return $this->calculerAgenceService($codeApp, $profil, false);
        });
    }

    /**
     * Retourne toutes les agences et services du profil, groupées par CODE.
     */
    public function getAgenceServiceGroupByCode(string $codeApp): ?array
    {
        if ($this->cacheAgServDonneesCode !== null) {
            return $this->cacheAgServDonneesCode;
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return $this->cacheAgServDonneesCode = [];
        }

        $cle = $this->buildKey($profilId, self::SUFFIX_AG_SERV_CODE, md5($codeApp));

        return $this->cacheAgServDonneesCode = $this->cache->get($cle, function (ItemInterface $item) use ($codeApp): array {
            $item->expiresAfter(null);
            return $this->calculerAgenceService($codeApp, $this->getProfil(), false);
        });
    }

    // =========================================================================
    //  ENTITÉS LIÉES AU PROFIL
    // =========================================================================

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
    //  INVALIDATION
    // =========================================================================

    /**
     * Invalide tout le cache applicatif d'un profil.
     *
     * Mécanisme : on change la "version" du profil dans le pool.
     * Toutes les clés de données qui contenaient l'ancienne version
     * ne seront plus jamais construites → elles mourront naturellement (LRU/TTL du pool).
     *
     * À appeler après modification des permissions ou des pages d'un profil.
     */
    public function invaliderCacheProfil(int $profilId): void
    {
        $this->invaliderVersion($profilId);
        $this->resetCacheMemoireIntraRequete();
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
    public function calculerPermissions(string $nomRoute, ?Profil $profil = null): ?array
    {
        if ($profil === null) {
            return null;
        }

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {

                if ($applicationProfilPage->getPage()->getNomRoute() !== $nomRoute) continue;

                return [
                    SecurityService::PERMISSION_VOIR      => $applicationProfilPage->isPeutVoir(),
                    SecurityService::PERMISSION_AJOUTER   => $applicationProfilPage->isPeutAjouter(),
                    SecurityService::PERMISSION_MODIFIER  => $applicationProfilPage->isPeutModifier(),
                    SecurityService::PERMISSION_SUPPRIMER => $applicationProfilPage->isPeutSupprimer(),
                    SecurityService::PERMISSION_EXPORTER  => $applicationProfilPage->isPeutExporter(),
                ];
            }
        }

        return null; // Route non configurée = accès refusé
    }

    /**
     * Calcule les pages visibles du profil depuis la BDD.
     * Retourne des tableaux de scalaires (sérialisables en cache).
     */
    public function calculerPagesProfil(?Profil $profil = null): array
    {
        if ($profil === null) {
            return [];
        }

        $pages = [];

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            $codeApp = $applicationProfil->getApplication()->getCodeApp();

            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                if (!$applicationProfilPage->isPeutVoir()) continue;

                $page = $applicationProfilPage->getPage();

                // On stocke uniquement des scalaires (pas d'entité Doctrine)
                $pages[$codeApp][] = [
                    'nom'    => $page->getNom(),
                    'route'  => $page->getNomRoute(), // nom de la route dans le controleur
                    'lien'   => $page->getLien(), // lien de la page
                ];
            }
        }

        return $pages;
    }

    /**
     * Calcule les agences et services autorisés pour le profil depuis la BDD.
     * Retourne des tableaux de scalaires (sérialisables en cache).
     */
    public function calculerAgenceService(string $codeApp, ?Profil $profil = null, bool $groupById = true): array
    {
        if ($profil === null) {
            return [];
        }

        $agenceServices = [];

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            $codeApplication = $applicationProfil->getApplication()->getCodeApp();

            if ($codeApplication !== $codeApp) continue;

            /** @var ApplicationProfilAgenceService $applicationProfilAgenceService */
            foreach ($applicationProfil->getLiaisonsAgenceService() as $applicationProfilAgenceService) {
                $agenceService = $applicationProfilAgenceService->getAgenceService();
                $agence        = $agenceService->getAgence();
                $service       = $agenceService->getService();

                $key = $groupById
                    ? $agenceService->getId()
                    : $agence->getCodeAgence() . '-' . $service->getCodeService();

                $agenceServices[$key] = [
                    'id'              => $agenceService->getId(),
                    'agence_id'       => $agence->getId(),
                    'service_id'      => $service->getId(),
                    'agence_code'     => $agence->getCodeAgence(),
                    'agence_libelle'  => $agence->getLibelleAgence(),
                    'service_code'    => $service->getCodeService(),
                    'service_libelle' => $service->getLibelleService(),
                ];
            }
        }

        return $agenceServices;
    }

    // =========================================================================
    //  HELPERS DE NAVIGATION
    // =========================================================================
    /**
     * Retourne un index plat de toutes les routes visibles du profil.
     * Accès O(1) par nom de route.
     *
     * Structure : ['nom_route' => ['nom' => ..., 'route' => ..., 'lien' => ...], ...]
     */
    public function getRoutesVisiblesIndex(): array
    {
        if ($this->cacheRoutesIndex !== null) {
            return $this->cacheRoutesIndex;
        }

        $this->cacheRoutesIndex = [];

        foreach ($this->getPagesProfil() as $pages) {
            foreach ($pages as $page) {
                $this->cacheRoutesIndex[$page['route']] = $page;
            }
        }

        return $this->cacheRoutesIndex;
    }

    /**
     * Vérifie si une route est visible — O(1), cache persistant.
     */
    public function peutVoir(string $route): bool
    {
        return isset($this->getRoutesVisiblesIndex()[$route]);
    }

    /**
     * True si au moins une des routes est visible.
     */
    public function peutVoirModule(string ...$routes): bool
    {
        $index = $this->getRoutesVisiblesIndex();
        foreach ($routes as $route) {
            if (isset($index[$route])) {
                return true;
            }
        }
        return false;
    }
}
