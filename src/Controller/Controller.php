<?php

namespace App\Controller;




use Parsedown;


use App\Entity\User;
use Twig\Environment;
use App\Entity\Agence;
use App\Entity\Service;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Service\FusionPdf;
use App\Entity\Application;
use App\Model\dit\DitModel;
use App\Model\dom\DomModel;
use App\Service\GenererPdf;
use App\Model\OdbcCrudModel;
use App\Model\badm\BadmModel;
use App\Service\ExcelService;
use App\Model\badm\CasierModel;
use App\Model\dom\DomListModel;
use App\Service\SessionManager;
use App\Model\dom\DomDetailModel;
use Twig\Loader\FilesystemLoader;
use App\Model\TransferDonnerModel;
use Twig\Extension\DebugExtension;
use App\Model\badm\BadmDetailModel;
use App\Model\badm\CasierListModel;
use App\Service\FlashManagerService;
use Symfony\Component\Asset\Package;
use App\Service\AccessControlService;
use App\Service\ExcelExporterService;
use App\Model\badm\BadmRechercheModel;
use App\Model\dom\DomDuplicationModel;
use App\Service\SessionManagerService;
use App\Model\admin\user\ProfilUserModel;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\badm\CasierListTemporaireModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

include dirname(__DIR__) . '/Service/GenererPdf.php';

class Controller
{


    protected $fusionPdf;
    protected $genererPdf;

    protected $ldap;
    protected $profilModel;
    protected $casier;
    protected $badmRech;
    protected $badm;
    protected $Person;
    protected $DomModel;
    protected $detailModel;
    protected $duplicata;
    protected $domList;
    protected $ProfilModel;
    protected $badmDetail;
    protected $casierList;
    protected $caiserListTemporaire;

    protected $odbcCrud;

    protected static $generator;
    protected static $twig;
    protected $loader;
    private $package;
    private $strategy;

    protected $request;
    protected $response;

    protected $excelExport;
    protected $flashManager;

    protected static $validator;

    protected $parsedown;

    protected $profilUser;

    protected static $em;
    protected static $paginator;

    protected $ditModel;

    protected $transfer04;

    protected $sessionService;

    protected $accessControl;

    protected $excelService;

    public function __construct()
    {

        $this->fusionPdf = new FusionPdf();
        $this->genererPdf = new GenererPdf();

        $this->ldap = new LdapModel();

        $this->profilModel = new ProfilModel();

        $this->odbcCrud = new OdbcCrudModel();


        $this->badmRech = new BadmRechercheModel();
        $this->badm = new BadmModel();

        $this->Person = new PersonnelModel();

        $this->DomModel = new DomModel();
        $this->detailModel = new DomDetailModel();
        $this->duplicata = new DomDuplicationModel();

        $this->domList = new DomListModel();
        $this->ProfilModel = new ProfilModel();

        $this->badmDetail = new BadmDetailModel();


        $this->request = Request::createFromGlobals();

        $this->response = new Response();

        $this->excelExport = new ExcelExporterService();
        $this->flashManager = new FlashManagerService();

        $this->parsedown = new Parsedown();

        $this->profilUser = new ProfilUserModel();

        $this->ditModel = new DitModel();

        $this->transfer04 = new TransferDonnerModel();


        $this->sessionService = new SessionManagerService();

        $this->accessControl = new AccessControlService();

        $this->excelService = new ExcelService();
    }




    public static function setTwig($twig)
    {
        self::$twig = $twig;
    }
    public static function getTwig()
    {
        return self::$twig;
    }

    public static function setValidator($validator)
    {
        self::$validator = $validator;
    }

    public static function setGenerator($generator)
    {
        self::$generator = $generator;
    }

    public static function getGenerator()
    {
        return self::$generator;
    }
    
    public static function setEntity($em)
    {
        self::$em = $em;
    }

    public static function getEntity()
    {
        return self::$em;
    }

    public static function setPaginator($paginator)
    {
        self::$paginator = $paginator;
    }

    protected function SessionStart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        } 
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/");
            session_destroy();
            exit();
        }
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
    header("Location: /Hffintranet/");
    
    // Ferme l'écriture de la session pour éviter les problèmes de verrouillage
    session_write_close();
    
    // Arrête l'exécution du script pour s'assurer que rien d'autre ne se passe après la redirection
    exit();
}

    public function getTime()
    {
        date_default_timezone_set('Indian/Antananarivo');
        return date("H:i");
    }
    /**
     * Date Système
     */
    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }


    public function CompleteChaineCaractere($ChaineComplet, $LongerVoulu, $Caracterecomplet, $PositionComplet)
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



    private function conversionCaratere(string $chaine): string
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

    protected function redirectTo($url) {
        // Créer une réponse de redirection
        $response = new RedirectResponse($url);
        // Envoyer la réponse de redirection au client
        $response->send();
    }

    protected function redirectToRoute($routeName, $params = []) {
        $url = self::$generator->generate($routeName, $params);
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
        if ($nomDemande === 'BDM') {
            //$Max_Num = $this->badm->RecupereNumBDM();
            $Max_Num = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'BDM'])->getDerniereId();
        } elseif ($nomDemande === 'CAS') {
            //$Max_Num = $this->casier->RecupereNumCAS()['numCas'];
            $Max_Num = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'CAS'])->getDerniereId();
        } elseif ($nomDemande === 'DIT') {
            $Max_Num = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT'])->getDerniereId();
        } elseif ($nomDemande === 'DOM') {
            $Max_Num = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DOM'])->getDerniereId();
        } 
        else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '0000';
        }

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre msincrimente
        //var_dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //var_dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //var_dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential + 1;
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
    
    protected function autoDecrementDIT(string $nomDemande)
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
            $Max_Num = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT'])->getDerniereId();
        } else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '9999';
        }

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre msincrimente
        //dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential - 1;
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
    

    protected function arrayToObjet(User $user): User
    {
     
        $superieurs = [];
        foreach ($user->getSuperieurs() as  $value) {
            if (empty($value)) {
                return $user;
            } else {
                $superieurs[] = self::$em->getRepository(user::class)->find($value);
           $user->setSuperieurs($superieurs);
            }
           
       }
   
       return $user;
    } 
    

    protected function agenceServiceIpsObjet(): array
    {
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);
        $codeAgence = $user->getAgenceServiceIrium()->getAgenceips();
        $agenceIps = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
        $codeService = $user->getAgenceServiceIrium()->getServiceips();
        $serviceIps = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
        return [
            'agenceIps' => $agenceIps,
            'serviceIps' => $serviceIps
        ];
    }

    protected function agenceServiceIpsString()
    {
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);
        $codeAgence = $user->getAgenceServiceIrium()->getAgenceips();
        $agenceIps = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
        $codeService = $user->getAgenceServiceIrium()->getServiceips();
        $serviceIps = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);


        return [
            'agenceIps' => $agenceIps->getCodeAgence() . ' ' . $agenceIps->getLibelleAgence(), 
            'serviceIps' => $serviceIps->getCodeService() . ' ' . $serviceIps->getLibelleService()
        ];
    }
}
