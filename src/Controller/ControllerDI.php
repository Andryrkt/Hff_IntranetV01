<?php

namespace App\Controller;

use Parsedown;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Service\FusionPdf;
use App\Model\dit\DitModel;
use App\Model\dom\DomModel;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Model\badm\BadmModel;
use App\Service\ExcelService;
use App\Model\dom\DomListModel;
use App\Entity\admin\Application;
use App\Model\dom\DomDetailModel;
use App\Model\TransferDonnerModel;
use App\Entity\admin\utilisateur\User;
use App\Model\dom\DomDuplicationModel;
use App\Service\SessionManagerService;
use App\Model\admin\personnel\PersonnelModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use App\Entity\admin\utilisateur\Role;
use App\Entity\da\DemandeAppro;
use App\Model\da\DaModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Classe Controller avec injection de dépendances
 * Cette classe remplace l'ancienne classe Controller statique
 */
class ControllerDI
{
    protected $fusionPdf;
    protected $ldap;
    protected $profilModel;
    protected $casier;
    protected $badm;
    protected $Person;
    protected $DomModel;
    protected $DaModel;
    protected $detailModel;
    protected $duplicata;
    protected $domList;
    protected $ProfilModel;
    protected $loader;
    protected $request;
    protected $response;
    protected $parsedown;
    protected $profilUser;
    protected $ditModel;
    protected $transfer04;
    protected $sessionService;
    protected $excelService;

    // Services injectés
    protected $entityManager;
    protected $urlGenerator;
    protected $twig;
    protected $formFactory;
    protected $session;
    protected $tokenStorage;
    protected $authorizationChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        FusionPdf $fusionPdf,
        LdapModel $ldap,
        ProfilModel $profilModel,
        BadmModel $badm,
        PersonnelModel $personnel,
        DomModel $domModel,
        DaModel $daModel,
        DomDetailModel $domDetailModel,
        DomDuplicationModel $domDuplicationModel,
        DomListModel $domListModel,
        DitModel $ditModel,
        TransferDonnerModel $transferDonnerModel,
        SessionManagerService $sessionManagerService,
        ExcelService $excelService
    ) {
        // Services injectés
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;

        // Services instanciés
        $this->fusionPdf = $fusionPdf;
        $this->ldap = $ldap;
        $this->profilModel = $profilModel;
        $this->badm = $badm;
        $this->Person = $personnel;
        $this->DomModel = $domModel;
        $this->DaModel = $daModel;
        $this->detailModel = $domDetailModel;
        $this->duplicata = $domDuplicationModel;
        $this->domList = $domListModel;
        $this->ProfilModel = $profilModel;
        $this->ditModel = $ditModel;
        $this->transfer04 = $transferDonnerModel;
        $this->sessionService = $sessionManagerService;
        $this->excelService = $excelService;

        // Créer la requête et la réponse
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->parsedown = new Parsedown();
    }

    /**
     * Récupérer l'EntityManager
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Récupérer le générateur d'URL
     */
    public function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->urlGenerator;
    }

    /**
     * Récupérer Twig
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Récupérer la factory de formulaires
     */
    public function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    /**
     * Récupérer la session
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * Récupérer le stockage de tokens
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->tokenStorage;
    }

    /**
     * Récupérer le vérificateur d'autorisation
     */
    public function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->authorizationChecker;
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
        unset($_SESSION['user']);

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
        $url = $this->urlGenerator->generate($routeName, $params);
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

        $Max_Num = $this->entityManager->getRepository(Application::class)->findOneBy(['codeApp' => $nomDemande])->getDerniereId();

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
            $Max_Num = $this->entityManager->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT'])->getDerniereId();
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

        $Max_Num = $this->entityManager->getRepository(Application::class)->findOneBy(['codeApp' => $nomDemande])->getDerniereId();

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
            $userId = $this->sessionService->get('user_id');

            if (!$userId) {
                throw new \Exception("User ID not found in session");
            }

            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                throw new \Exception("User not found with ID $userId");
            }

            $codeAgence = $user->getAgenceServiceIrium()->getAgenceIps();
            $agenceIps = $this->entityManager->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);

            if (!$agenceIps) {
                throw new \Exception("Agence not found with code $codeAgence");
            }

            $codeService = $user->getAgenceServiceIrium()->getServiceIps();
            $serviceIps = $this->entityManager->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
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
            $userId = $this->sessionService->get('user_id');
            if (!$userId) {
                throw new \Exception("User ID not found in session");
            }

            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                throw new \Exception("User not found with ID $userId");
            }

            $codeAgence = $user->getAgenceServiceIrium()->getAgenceips();
            $agenceIps = $this->entityManager->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
            if (!$agenceIps) {
                throw new \Exception("Agence not found with code $codeAgence");
            }

            $codeService = $user->getAgenceServiceIrium()->getServiceips();
            $serviceIps = $this->entityManager->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
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
        $idUtilisateur = $this->sessionService->get('user_id');
        $utilisateur = $idUtilisateur !== '-' ? $this->entityManager->getRepository(User::class)->find($idUtilisateur) : null;
        $utilisateurNom = $utilisateur ? $utilisateur->getNomUtilisateur() : null;
        $page = $this->entityManager->getRepository(PageHff::class)->findPageByRouteName($nomRoute);
        $machine = gethostbyaddr($_SERVER['REMOTE_ADDR']) ?? $_SERVER['REMOTE_ADDR'];

        $log = new UserLogger();

        $log->setUtilisateur($utilisateurNom ?: '-');
        $log->setNom_page($page->getNom());
        $log->setParams($params ?: null);
        $log->setUser($utilisateur);
        $log->setMachineUser($machine);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Vérifier la session utilisateur
     */
    protected function verifierSessionUtilisateur()
    {
        if (!$this->sessionService->has('user_id')) {
            $this->redirectToRoute("security_signin");
        }
    }

    /**
     * Récupérer l'ID de l'utilisateur
     */
    protected function getUserId(): int
    {
        return $this->sessionService->get('user_id');
    }

    /**
     * Récupérer l'utilisateur
     */
    protected function getUser(): ?User
    {
        $userId = $this->getUserId();
        return $userId ? $this->entityManager->getRepository(User::class)->find($userId) : null;
    }

    /**
     * Récupérer l'email de l'utilisateur
     */
    protected function getUserMail(): string
    {
        return $this->getUser()->getMail();
    }

    /**
     * Récupérer le nom de l'utilisateur
     */
    protected function getUserName(): string
    {
        return $this->getUser()->getNomUtilisateur();
    }

    /**
     * Vérifier si l'utilisateur est dans le service atelier
     */
    protected function estUserDansServiceAtelier(): bool
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(DemandeAppro::ID_ATELIER, $serviceIds);
    }

    /**
     * Vérifier si l'utilisateur est dans le service appro
     */
    protected function estUserDansServiceAppro(): bool
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(DemandeAppro::ID_APPRO, $serviceIds);
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    protected function estAdmin(): bool
    {
        $roleIds = $this->getUser()->getRoleIds();
        return in_array(Role::ROLE_ADMINISTRATEUR, $roleIds);
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    protected function estSuperAdmin(): bool
    {
        $roleIds = $this->getUser()->getRoleIds();
        return in_array(Role::ROLE_SUPER_ADMINISTRATEUR, $roleIds);
    }
}
