<?php

namespace App\Controller;

use App\Entity\admin\Agence;
use App\Entity\admin\Application;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use App\Entity\admin\Service;
use App\Entity\admin\utilisateur\User;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\badm\BadmModel;
use App\Model\dit\DitModel;
use App\Model\dom\DomDetailModel;
use App\Model\dom\DomDuplicationModel;
use App\Model\dom\DomListModel;
use App\Model\dom\DomModel;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Model\TransferDonnerModel;
use App\Service\AccessControlService;
use App\Service\ExcelService;
use App\Service\FusionPdf;
use App\Service\session\SessionService;
use Parsedown;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class Controller
{
    protected FusionPdf $fusionPdf;
    protected LdapModel $ldap;
    protected ProfilModel $profilModel;
    protected BadmModel $badm;
    protected PersonnelModel $person;
    protected DomModel $domModel;
    protected DomDetailModel $detailModel;
    protected DomDuplicationModel $duplicata;
    protected DomListModel $domList;
    protected Request $request;
    protected Response $response;
    protected Parsedown $parsedown;
    protected DitModel $ditModel;
    protected TransferDonnerModel $transfer04;
    protected SessionService $sessionService;
    protected AccessControlService $accessControl;
    protected ExcelService $excelService;
    protected UrlGeneratorInterface $generator;
    protected Environment $twig;
    protected ValidatorInterface $validator;
    protected EntityManagerInterface $em;
    protected DoctrinePaginator $paginator;


    public function __construct(
        FusionPdf $fusionPdf,
        LdapModel $ldap,
        ProfilModel $profilModel,
        BadmModel $badm,
        PersonnelModel $person,
        DomModel $domModel,
        DomDetailModel $detailModel,
        DomDuplicationModel $duplicata,
        DomListModel $domList,
        Request $request,
        Response $response,
        Parsedown $parsedown,
        DitModel $ditModel,
        TransferDonnerModel $transfer04,
        SessionManagerService $sessionService,
        AccessControlService $accessControl,
        ExcelService $excelService,
        UrlGeneratorInterface $generator,
        Environment $twig,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        DoctrinePaginator $paginator
    ) {
        $this->fusionPdf = $fusionPdf;
        $this->ldap = $ldap;
        $this->profilModel = $profilModel;
        $this->badm = $badm;
        $this->person = $person;
        $this->domModel = $domModel;
        $this->detailModel = $detailModel;
        $this->duplicata = $duplicata;
        $this->domList = $domList;
        $this->request = $request;
        $this->response = $response;
        $this->parsedown = $parsedown;
        $this->ditModel = $ditModel;
        $this->transfer04 = $transfer04;
        $this->sessionService = $sessionService;
        $this->accessControl = $accessControl;
        $this->excelService = $excelService;
        $this->generator = $generator;
        $this->twig = $twig;
        $this->validator = $validator;
        $this->em = $em;
        $this->paginator = $paginator;
    }

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
        $this->redirectToRoute('security_signin'); //security_signin

        // Ferme l'écriture de la session pour éviter les problèmes de verrouillage
        session_write_close();

        // Arrête l'exécution du script pour s'assurer que rien d'autre ne se passe après la redirection
        exit();
    }

    /**
     * recupère les l'heures
     */
    protected function getTime()
    {
        date_default_timezone_set('Indian/Antananarivo');

        return date("H:i");
    }

    /**
     * recupère la date d'aujourd'hui
     * Date Système
     */
    protected function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);

        return $Date_system;
    }

    protected function conversionCaratere(string $chaine): string
    {
        return iconv('Windows-1252', 'UTF-8', $chaine);
    }

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

    protected function redirectTo($url)
    {
        // Créer une réponse de redirection
        $response = new RedirectResponse($url);
        // Envoyer la réponse de redirection au client
        $response->send();
    }

    /**
     * redirigé l'utilisateur vers la route donnée en paramètre
     *
     * @param string $routeName nom de la route en question
     *      Exemple: $routeName = "profil_acceuil"
     * @param array $params tableau de paramètres à ajouter dans la route
     * @return void
     */
    protected function redirectToRoute(string $routeName, array $params = [])
    {
        $url = $this->generator->generate($routeName, $params);
        header("Location: $url");
        exit();
    }

    protected function testJson($jsonData)
    {
        if ($jsonData === false) {
            // L'encodage a échoué, vérifions pourquoi
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
            // L'encodage a réussi
            echo $jsonData;
        }
    }

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
     * Incrimentation de Numero_Applications (DOMAnnéeMoisNuméro)
     */
    protected function autoINcriment(string $nomDemande)
    {
        //NumDOM auto
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        //var_dump($AnneMoisOfcours);
        // dernier NumDOM dans la base

        $Max_Num = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => $nomDemande])->getDerniereId();

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential = substr($Max_Num, -4); // lay 4chiffre msincrimente
        //var_dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //var_dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //var_dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential = $vNumSequential + 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 1;
            }
        }
        //var_dump($vNumSequential);
        $Result_Num = $nomDemande . $AnneMoisOfcours . $this->CompleteChaineCaractere($vNumSequential, 4, "0", "G");
        //var_dump($Result_Num);

        return $Result_Num;
    }

    /**
     * Decrementation de Numero_Applications (DOMAnnéeMoisNuméro)
     *
     * @param string $nomDemande
     * @return string
     */
    protected function autoDecrementDIT(string $nomDemande): string
    {
        //NumDOM auto
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        //$MonthOfcours = "08"; //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        //var_dump($AnneMoisOfcours);
        // dernier NumDOM dans la base

        //$Max_Num = $this->casier->RecupereNumCAS()['numCas'];

        if ($nomDemande === 'DIT') {
            $Max_Num = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT'])->getDerniereId();
        } else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '9999';
        }

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential = substr($Max_Num, -4); // lay 4chiffre msincrimente
        //dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential = $vNumSequential - 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 9999;
            }
        }

        //dump($vNumSequential);
        //var_dump($vNumSequential);
        $Result_Num = $nomDemande . $AnneMoisOfcours . $vNumSequential;

        //var_dump($Result_Num);
        //dd($Result_Num);
        return $Result_Num;
    }

    /**
     * Decrementation de Numero_Applications (DOMAnnéeMoisNuméro)
     *
     * @param string $nomDemande
     * @return string
     */
    protected function autoDecrement(string $nomDemande): string
    {
        //NumDOM auto
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401

        $Max_Num = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => $nomDemande])->getDerniereId();

        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre mdecremente
        $DateAnneemoisnum = substr($Max_Num, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential - 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 9999;
            }
        }

        return $nomDemande . $AnneMoisOfcours . $vNumSequential;
    }


    protected function arrayToObjet(User $user): User
    {

        $superieurs = [];
        foreach ($user->getSuperieurs() as $value) {
            if (empty($value)) {
                return $user;
            } else {
                $superieurs[] = $this->em->getRepository(user::class)->find($value);
                $user->setSuperieurs($superieurs);
            }
        }

        return $user;
    }

    /**
     * recupère l'agence et service de l'utilisateur connecté dans un tableau où les éléments sont des objets
     *
     * @return array
     */
    protected function agenceServiceIpsObjet(): array
    {
        try {
            $userId = $this->sessionService->get('user_id');

            if (! $userId) {
                throw new \Exception("User ID not found in session");
            }

            $user = $this->em->getRepository(User::class)->find($userId);

            if (! $user) {
                throw new \Exception("User not found with ID $userId");
            }

            $codeAgence = $user->getAgenceServiceIrium()->getAgenceIps();
            $agenceIps = $this->em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);

            if (! $agenceIps) {
                throw new \Exception("Agence not found with code $codeAgence");
            }

            $codeService = $user->getAgenceServiceIrium()->getServiceIps();
            $serviceIps = $this->em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
            if (! $serviceIps) {
                throw new \Exception("Service not found with code $codeService");
            }

            return [
                'agenceIps' => $agenceIps,
                'serviceIps' => $serviceIps,
            ];
        } catch (\Exception $e) {
            // Gérer l'erreur ici, par exemple en loguant l'erreur et en retournant une réponse par défaut ou vide.
            error_log($e->getMessage());

            return [
                'agenceIps' => null,
                'serviceIps' => null,
            ];
        }
    }

    /**
     * recupère l'agence et service de l'utilisateur connecté dans un tableau où les éléments sont des chaines de catactère
     *
     * @return array
     */
    protected function agenceServiceIpsString(): array
    {
        try {
            $userId = $this->sessionService->get('user_id');
            if (! $userId) {
                throw new \Exception("User ID not found in session");
            }

            $user = $this->em->getRepository(User::class)->find($userId);
            if (! $user) {
                throw new \Exception("User not found with ID $userId");
            }

            $codeAgence = $user->getAgenceServiceIrium()->getAgenceips();
            $agenceIps = $this->em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
            if (! $agenceIps) {
                throw new \Exception("Agence not found with code $codeAgence");
            }

            $codeService = $user->getAgenceServiceIrium()->getServiceips();
            $serviceIps = $this->em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
            if (! $serviceIps) {
                throw new \Exception("Service not found with code $codeService");
            }

            return [
                'agenceIps' => $agenceIps->getCodeAgence() . ' ' . $agenceIps->getLibelleAgence(),
                'serviceIps' => $serviceIps->getCodeService() . ' ' . $serviceIps->getLibelleService(),
            ];
        } catch (\Throwable $e) {
            error_log($e->getMessage());

            return [
                'agenceIps' => '',
                'serviceIps' => '',
            ];
        }
    }

    protected function logUserVisit(string $nomRoute, ?array $params = null)
    {
        $idUtilisateur  = $this->sessionService->get('user_id');
        $utilisateur    = $idUtilisateur !== '-' ? $this->em->getRepository(User::class)->find($idUtilisateur) : null;
        $utilisateurNom = $utilisateur ? $utilisateur->getNomUtilisateur() : null;
        $page           = $this->em->getRepository(PageHff::class)->findPageByRouteName($nomRoute);
        $machine        = gethostbyaddr($_SERVER['REMOTE_ADDR']) ?? $_SERVER['REMOTE_ADDR'];

        $log            = new UserLogger();

        $log->setUtilisateur($utilisateurNom ?: '-');
        $log->setNom_page($page->getNom());
        $log->setParams($params ?: null);
        $log->setUser($utilisateur);
        $log->setPage($page);
        $log->setMachineUser($machine);

        $this->em->persist($log);
        $this->em->flush();
    }

    public function verifierSessionUtilisateur()
    {
        if (!$this->sessionService->has('user_id')) {
            $this->redirectToRoute("security_signin");
        }
    }

    protected function getUserId(): int
    {
        return $this->sessionService->get('user_id');
    }

    public static function getUser(): User
    {
        $ctrl = new self();
        //recuperation de l'utilisateur connecter
        $userId = $ctrl->getUserId();
        return  self::$em->getRepository(User::class)->find($userId);
    }

    protected function getEmail(): string
    {
        $userId = $this->getUserId();
        return self::$em->getRepository(User::class)->find($userId)->getMail();
    }

    protected function getUserNameUser(): string
    {
        $userId = $this->getUserId();
        return self::$em->getRepository(User::class)->find($userId)->getNomUtilisateur();
    }

    public  static function getMailUser(): string
    {
        $ctrl = new self();

        $userId = $ctrl->getUserId();
        return self::$em->getRepository(User::class)->find($userId)->getMail();
    }

    public static function estUserDansServiceAtelier(): bool
    {
        $ctrl = new self();
        $serviceIds = $ctrl->getUser()->getServiceAutoriserIds();
        return in_array(DemandeAppro::ID_ATELIER, $serviceIds);
    }

    public static function estUserDansServiceAppro(): bool
    {
        $ctrl = new self();
        $serviceIds = $ctrl->getUser()->getServiceAutoriserIds();
        return in_array(DemandeAppro::ID_APPRO, $serviceIds);
    }
}
