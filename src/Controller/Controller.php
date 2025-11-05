<?php

namespace App\Controller;

use Parsedown;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use App\Entity\admin\utilisateur\Role;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Service\SessionManagerService;

/**
 * Classe Controller avec injection de dépendances
 * Cette classe remplace l'ancienne classe Controller statique
 */
class Controller
{
    protected $parsedown;

    // Services injectés (accessibles via getters)
    protected $entityManager;
    protected $urlGenerator;
    protected $twig;
    protected $formFactory;
    protected $session;
    protected $tokenStorage;
    protected $authorizationChecker;
    protected $sessionService;

    // Propriétés publiques avec getters lazy pour les modèles et services
    public $request;
    public $response;

    public function __construct()
    {
        // Créer la requête et la réponse
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->parsedown = new Parsedown();
    }

    protected function getSessionService(): SessionManagerService
    {
        if ($this->sessionService === null) {
            try {
                $container = $this->getContainer();
                if ($container && $container->has('App\\Service\\SessionManagerService')) {
                    $this->sessionService = $container->get('App\\Service\\SessionManagerService');
                } else {
                    $this->sessionService = new \App\Service\SessionManagerService();
                }
            } catch (\Throwable $e) {
                $this->sessionService = new \App\Service\SessionManagerService();
            }
        }
        return $this->sessionService;
    }

    /**
     * Récupérer l'EntityManager
     */
    public function getEntityManager(): EntityManagerInterface
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Récupérer le générateur d'URL
     */
    public function getUrlGenerator(): UrlGeneratorInterface
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('router');
    }

    /**
     * Récupérer Twig
     */
    public function getTwig(): Environment
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('twig');
    }

    /**
     * Récupérer la factory de formulaires
     */
    public function getFormFactory(): FormFactoryInterface
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('form.factory');
    }

    /**
     * Récupérer la session
     */
    public function getSession(): SessionInterface
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('session');
    }

    /**
     * Récupérer le stockage de tokens
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('security.token_storage');
    }

    /**
     * Récupérer le vérificateur d'autorisation
     */
    public function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get('security.authorization_checker');
    }

    /**
     * Récupérer le conteneur de services
     */
    protected function getContainer()
    {
        global $container;
        return $container;
    }

    /**
     * Récupérer les services depuis le conteneur
     */
    protected function getService(string $serviceId)
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }
        return $container->get($serviceId);
    }

    /**
     * Récupérer le service Excel
     */
    protected function getExcelService(): \App\Service\ExcelService
    {
        $container = $this->getContainer();
        if (!$container) {
            throw new \RuntimeException('Le conteneur de services n\'est pas disponible');
        }

        if ($container->has('App\\Service\\ExcelService')) {
            return $container->get('App\\Service\\ExcelService');
        }

        // Fallback : créer une nouvelle instance si le service n'est pas enregistré
        return new \App\Service\ExcelService();
    }



    /**
     * Getter magique pour charger les services à la demande
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'request':
                return $this->request;
            case 'response':
                return $this->response;
            default:
                throw new \InvalidArgumentException("Propriété '$name' non trouvée");
        }
    }

    /**
     * Détruire la session utilisateur
     */
    protected function SessionDestroy()
    {
        // Commence la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Supprime l'utilisateur de la session
        $this->getSessionService()->remove('user');

        // Détruit la session
        session_destroy();

        // Réinitialise toutes les variables de session
        session_unset();

        // Redirige vers la page d'accueil
        $this->redirectToRoute('security_signin');

        // Ferme l'écriture de la session pour éviter les problèmes de verrouillage
        session_write_close();

        // Arrête l'exécution du script pour s'assurer que rien d'autre ne se passe après la redirection
        exit();
    }

    /**
     * Récupérer l'heure actuelle
     */
    protected function getTime()
    {
        date_default_timezone_set('Indian/Antananarivo');
        return date("H:i");
    }

    /**
     * Récupérer la date système actuelle
     */
    protected function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }

    /**
     * Conversion de caractères Windows-1252 vers UTF-8
     */
    protected function conversionCaratere(string $chaine): string
    {
        return iconv('Windows-1252', 'UTF-8', $chaine);
    }

    /**
     * Conversion de tableau de caractères Windows-1252 vers UTF-8
     */
    protected function conversionTabCaractere(array $tab): array
    {
        $array = [];
        foreach ($tab as $key => $values) {
            foreach ($values as $key => $value) {
                $array[$key] = iconv('Windows-1252', 'UTF-8', $value);
            }
        }
        return $array;
    }

    /**
     * Rediriger vers une URL
     */
    protected function redirectTo($url)
    {
        $response = new RedirectResponse($url);
        $response->send();
    }

    /**
     * Rediriger vers une route
     */
    protected function redirectToRoute(string $routeName, array $params = [])
    {
        $url = $this->getUrlGenerator()->generate($routeName, $params);
        header("Location: $url");
        exit();
    }

    /**
     * Tester la validité d'un JSON
     */
    protected function testJson($jsonData)
    {
        if ($jsonData === false) {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    echo 'Aucune erreur';
                    break;
                case JSON_ERROR_DEPTH:
                    echo 'Profondeur maximale atteinte';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo 'Inadéquation des états ou mode invalide';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo 'Caractère de contrôle inattendu trouvé';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo 'Erreur de syntaxe, JSON malformé';
                    break;
                case JSON_ERROR_UTF8:
                    echo 'Caractères UTF-8 malformés, possiblement mal encodés';
                    break;
                default:
                    echo 'Erreur inconnue';
                    break;
            }
        } else {
            echo $jsonData;
        }
    }

    /**
     * Compléter une chaîne de caractères
     */
    private function CompleteChaineCaractere($ChaineComplet, $LongerVoulu, $Caracterecomplet, $PositionComplet)
    {
        for ($i = 1; $i < $LongerVoulu; $i++) {
            if (strlen($ChaineComplet) < $LongerVoulu) {
                if ($PositionComplet = "G") {
                    $ChaineComplet = $Caracterecomplet . $ChaineComplet;
                } else {
                    $ChaineComplet = $Caracterecomplet . $Caracterecomplet;
                }
            }
        }
        return $ChaineComplet;
    }

    /**
     * Incrémentation automatique des numéros d'applications
     */
    protected function autoINcriment(string $nomDemande)
    {
        $YearsOfcours = date('y');
        $MonthOfcours = date('m');
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours;

        $Max_Num = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => $nomDemande])->getDerniereId();

        $vNumSequential = substr($Max_Num, -4);
        $DateAnneemoisnum = substr($Max_Num, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);

        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential = $vNumSequential + 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 1;
            }
        }

        $Result_Num = $nomDemande . $AnneMoisOfcours . $this->CompleteChaineCaractere($vNumSequential, 4, "0", "G");
        return $Result_Num;
    }

    /**
     * Décrémentation automatique des numéros DIT
     */
    protected function autoDecrementDIT(string $nomDemande): string
    {
        $YearsOfcours = date('y');
        $MonthOfcours = date('m');
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours;

        if ($nomDemande === 'DIT') {
            $Max_Num = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT'])->getDerniereId();
        } else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '9999';
        }

        $vNumSequential = substr($Max_Num, -4);
        $DateAnneemoisnum = substr($Max_Num, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);

        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential = $vNumSequential - 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 9999;
            }
        }

        $Result_Num = $nomDemande . $AnneMoisOfcours . $vNumSequential;
        return $Result_Num;
    }

    /**
     * Décrémentation automatique des numéros d'applications
     */
    protected function autoDecrement(string $nomDemande): string
    {
        $YearsOfcours = date('y');
        $MonthOfcours = date('m');
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours;

        $Max_Num = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => $nomDemande])->getDerniereId();

        $vNumSequential = substr($Max_Num, -4);
        $DateAnneemoisnum = substr($Max_Num, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);

        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential = $vNumSequential - 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 9999;
            }
        }

        return $nomDemande . $AnneMoisOfcours . $vNumSequential;
    }

    /**
     * Récupérer l'agence et le service de l'utilisateur connecté (objets)
     */
    protected function agenceServiceIpsObjet(): array
    {
        try {
            $userId = $this->getSessionService()->get('user_id');

            if (!$userId) {
                throw new \Exception("User ID not found in session");
            }

            $user = $this->getEntityManager()->getRepository(User::class)->find($userId);

            if (!$user) {
                throw new \Exception("User not found with ID $userId");
            }

            $codeAgence = $user->getAgenceServiceIrium()->getAgenceIps();
            $agenceIps = $this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);

            if (!$agenceIps) {
                throw new \Exception("Agence not found with code $codeAgence");
            }

            $codeService = $user->getAgenceServiceIrium()->getServiceIps();
            $serviceIps = $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
            if (!$serviceIps) {
                throw new \Exception("Service not found with code $codeService");
            }

            return [
                'agenceIps' => $agenceIps,
                'serviceIps' => $serviceIps
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [
                'agenceIps' => null,
                'serviceIps' => null
            ];
        }
    }

    /**
     * Récupérer l'agence et le service de l'utilisateur connecté (chaînes)
     */
    protected function agenceServiceIpsString(): array
    {
        try {
            $userId = $this->getSessionService()->get('user_id');
            if (!$userId) {
                throw new \Exception("User ID not found in session");
            }

            $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
            if (!$user) {
                throw new \Exception("User not found with ID $userId");
            }

            $codeAgence = $user->getAgenceServiceIrium()->getAgenceips();
            $agenceIps = $this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
            if (!$agenceIps) {
                throw new \Exception("Agence not found with code $codeAgence");
            }

            $codeService = $user->getAgenceServiceIrium()->getServiceips();
            $serviceIps = $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
            if (!$serviceIps) {
                throw new \Exception("Service not found with code $codeService");
            }

            return [
                'agenceIps' => $agenceIps->getCodeAgence() . ' ' . $agenceIps->getLibelleAgence(),
                'serviceIps' => $serviceIps->getCodeService() . ' ' . $serviceIps->getLibelleService()
            ];
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return [
                'agenceIps' => '',
                'serviceIps' => ''
            ];
        }
    }

    /**
     * Logger la visite d'un utilisateur
     */
    protected function logUserVisit(string $nomRoute, ?array $params = null)
    {
        $idUtilisateur = $this->getSessionService()->get('user_id');
        $utilisateur = ($idUtilisateur && $idUtilisateur !== '-') ? $this->getEntityManager()->getRepository(User::class)->find($idUtilisateur) : null;
        $utilisateurNom = $utilisateur ? $utilisateur->getNomUtilisateur() : null;
        $page = $this->getEntityManager()->getRepository(PageHff::class)->findPageByRouteName($nomRoute);
        $machine = gethostbyaddr($_SERVER['REMOTE_ADDR']) ?? $_SERVER['REMOTE_ADDR'];

        $log = new UserLogger();

        $log->setUtilisateur($utilisateurNom ?: '-');
        $log->setNom_page($page->getNom());
        $log->setParams($params ?: null);
        $log->setUser($utilisateur);
        $log->setMachineUser($machine);

        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }

    /**
     * Vérifier la session utilisateur
     */
    protected function verifierSessionUtilisateur()
    {
        if (!$this->isUserConnected()) {
            $this->redirectToRoute("security_signin");
        }
    }

    /**
     * Récupérer l'ID de l'utilisateur
     */
    protected function getUserId(): ?int
    {
        return $this->getSessionService()->get('user_id');
    }

    /**
     * Récupérer l'utilisateur
     */
    protected function getUser(): ?User
    {
        $userId = $this->getUserId();
        return $userId ? $this->getEntityManager()->getRepository(User::class)->find($userId) : null;
    }

    /**
     * Récupérer l'email de l'utilisateur
     */
    protected function getUserMail(): ?string
    {
        $user = $this->getUser();
        return $user ? $user->getMail() : null;
    }

    /**
     * Récupérer le nom de l'utilisateur
     */
    protected function getUserName(): string
    {
        $user = $this->getUser();
        return $user ? $user->getNomUtilisateur() : 'unknown';
    }

    /**
     * Vérifier si l'utilisateur est dans le service atelier
     */
    protected function estUserDansServiceAtelier(): bool
    {
        $user = $this->getUser();
        if (!$user) return false;
        $serviceIds = $user->getServiceAutoriserIds();
        return in_array(DemandeAppro::ID_ATELIER, $serviceIds);
    }

    /**
     * Vérifier si l'utilisateur est dans le service appro
     */
    protected function estUserDansServiceAppro(): bool
    {
        $user = $this->getUser();
        if (!$user) return false;
        $serviceIds = $user->getServiceAutoriserIds();
        return in_array(DemandeAppro::ID_APPRO, $serviceIds);
    }

    /**
     * Vérifier si l'utilisateur est un créateur de DA directe
     */
    protected function estCreateurDeDADirecte(): bool
    {
        $user = $this->getUser();
        if (!$user) return false;
        $roleIds = $user->getRoleIds();
        return in_array(Role::ROLE_DA_DIRECTE, $roleIds);
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    protected function estAdmin(): bool
    {
        $user = $this->getUser();
        if (!$user) return false;
        $roleIds = $user->getRoleIds();
        return in_array(Role::ROLE_ADMINISTRATEUR, $roleIds);
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    protected function estSuperAdmin(): bool
    {
        $user = $this->getUser();
        if (!$user) return false;
        $roleIds = $user->getRoleIds();
        return in_array(Role::ROLE_SUPER_ADMINISTRATEUR, $roleIds);
    }

    // =====================================
    // MÉTHODES STATIQUES DE COMPATIBILITÉ
    // (Temporaires - à supprimer après refactorisation complète)
    // =====================================

    /**
     * @deprecated Utiliser l'injection de dépendances à la place
     */
    public static function getEntity()
    {
        global $container;
        return $container ? $container->get('doctrine.orm.default_entity_manager') : null;
    }

    /**
     * @deprecated Utiliser l'injection de dépendances à la place
     */
    public static function getTwigStatic()
    {
        global $container;
        return $container ? $container->get('twig') : null;
    }

    /**
     * @deprecated Utiliser l'injection de dépendances à la place
     */
    public static function getGeneratorStatic()
    {
        global $container;
        return $container ? $container->get('router') : null;
    }

    /**
     * @deprecated Utiliser l'injection de dépendances à la place
     */
    public static function getValidatorStatic()
    {
        global $container;
        return $container ? $container->get('form.factory') : null;
    }



    /**
     * @deprecated Ne pas utiliser - méthode obsolète
     */
    public static function setTwig($twig) {}

    /**
     * @deprecated Ne pas utiliser - méthode obsolète
     */
    public static function setValidator($validator) {}

    /**
     * @deprecated Ne pas utiliser - méthode obsolète
     */
    public static function setGenerator($generator) {}

    /**
     * @deprecated Ne pas utiliser - méthode obsolète
     */
    public static function setEntity($entity) {}

    /**
     * @deprecated Ne pas utiliser - méthode obsolète
     */
    public static function setPaginator($paginator) {}

    /**
     * Rendre un template Twig
     */
    protected function render(string $template, array $parameters = []): Response
    {
        $content = $this->getTwig()->render($template, $parameters);
        return new Response($content);
    }

    // =====================================
    // MÉTHODES HELPER DE BASECONTROLLER
    // =====================================

    /**
     * Méthode helper pour la redirection vers une route avec Response
     */
    protected function redirectToRouteResponse(string $routeName, array $params = []): RedirectResponse
    {
        $url = $this->getUrlGenerator()->generate($routeName, $params);
        return new RedirectResponse($url);
    }

    /**
     * Méthode helper pour la redirection vers une URL avec Response
     */
    protected function redirectToResponse(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }

    /**
     * Méthode helper pour créer une réponse JSON
     */
    protected function jsonResponse($data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Méthode helper pour vérifier si l'utilisateur est connecté
     */
    public function isUserConnected(): bool
    {
        return $this->getSessionService()->has('user_id');
    }

    /**
     * Méthode helper pour obtenir l'ID de l'utilisateur connecté
     */
    protected function getCurrentUserId()
    {
        return $this->getSessionService()->get('user_id');
    }

    /**
     * Méthode helper pour obtenir le nom de l'utilisateur connecté
     */
    protected function getCurrentUsername()
    {
        return $this->getSessionService()->get('user');
    }
}
