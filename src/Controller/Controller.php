<?php

namespace App\Controller;




use Twig\Environment;


use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Service\FusionPdf;
use App\Model\dom\DomModel;
use App\Service\GenererPdf;
use App\Model\OdbcCrudModel;
use App\Model\badm\BadmModel;
use App\Model\badm\CasierModel;
use App\Model\dom\DomListModel;
use App\Model\dom\DomDetailModel;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use App\Model\badm\BadmDetailModel;
use App\Model\badm\CasierListModel;
use Symfony\Component\Asset\Package;
use App\Service\ExcelExporterService;
use App\Model\badm\BadmRechercheModel;
use App\Model\dom\DomDuplicationModel;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\badm\CasierListTemporaireModel;
use App\Service\FlashManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
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
    protected $twig;
    protected $loader;
    private $package;
    private $strategy;

    protected $request;
    protected $response;

    protected $excelExport;
    protected $flashManager;

    protected static $validator;

    public function __construct()
    {

        $this->fusionPdf = new FusionPdf();
        $this->genererPdf = new GenererPdf();

        $this->loader = new FilesystemLoader(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Views/templates');
        //$this->twig = new Environment($this->loader);
        $this->twig = new Environment($this->loader, ['debug' => true]);
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addExtension(new RoutingExtension(self::$generator));
        // $this->strategy = new JsonManifestVersionStrategy('/path/to/manifest.json');
        // $this->package = new Package($this->strategy);
        // $this->twig->addExtension(new AssetExtension($this->package));

        $this->ldap = new LdapModel();

        $this->profilModel = new ProfilModel();

        $this->odbcCrud = new OdbcCrudModel();

        $this->casier = new CasierModel();
        $this->casierList = new CasierListModel();
        $this->caiserListTemporaire = new CasierListTemporaireModel();

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
    }




    public static function setTwig($generator)
    {
        self::$generator = $generator;
    }

    public static function setValidator($validator)
    {
        self::$validator = $validator;
    }


    protected function SessionStart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
    }

    protected function SessionDestroy()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user']);
        session_destroy();
        session_unset();
        header("Location:/Hffintranet/");
        exit();
        session_write_close();
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


    function CompleteChaineCaractere($ChaineComplet, $LongerVoulu, $Caracterecomplet, $PositionComplet)
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
}
