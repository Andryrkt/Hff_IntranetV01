<?php

namespace App\Controller;




use Twig\Environment;
use App\Model\DomModel;
use App\Model\BadmModel;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Service\FusionPdf;
use App\Service\GenererPdf;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;


include dirname(__DIR__) . '/Service/GenererPdf.php';

class Controller
{
    protected $DomModel;
    private $PersonnelModel;
    protected $fusionPdf;
    protected $genererPdf;
    protected $twig;
    protected $loader;
    protected $ldap;
    protected $profilModel;
    protected $badm;


    public function __construct()
    {
        $this->DomModel = new DomModel();
        $this->fusionPdf = new FusionPdf();
        $this->genererPdf = new GenererPdf();

        $this->loader = new FilesystemLoader(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Views/templates');
        //$this->twig = new Environment($this->loader);
        $this->twig = new Environment($this->loader, ['debug' => true]);
        $this->twig->addExtension(new DebugExtension());

        $this->ldap = new LdapModel();

        $this->profilModel = new ProfilModel;

        $this->badm = new BadmModel();
    }

    protected function SessionStart()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
    }

    protected function SessionDestroy()
    {
        session_start();
        unset($_SESSION['user']);
        session_destroy();
        session_unset();
        header("Location:/Hffintranet/");
        exit();
        session_write_close();
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
    /**
     * Incrimentation de Numero_DOM (DOMAnnéeMoisNuméro)
     */
    public function DOM_autoINcriment(string $nomPic)
    {
        //NumDOM auto
        include('FunctionChaine.php');
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        // dernier NumDOM dans la base
        $Max_Num = $this->badm->RecupereNumDom('Numero_Demande_BADM');
        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre msincrimente
        $DateAnneemoisnum = substr($Max_Num, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential + 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 1;
            }
        }
        strlen($vNumSequential);
        $Result_Num = $nomPic . $AnneMoisOfcours . $this->CompleteChaineCaractere($vNumSequential, 4, "0", "G");
        return $Result_Num;
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
