<?php

namespace App\Controller;


use Twig\Environment;

use App\Model\DomModel;
use App\Service\FusionPdf;
use App\Service\GenererPdf;
use Twig\Loader\FilesystemLoader;


include dirname(__DIR__) . '/Service/GenererPdf.php';

class Controller
{
    protected $DomModel;
    private $PersonnelModel;
    protected $fusionPdf;
    protected $genererPdf;
    protected $twig;
    protected $loader;


    public function __construct()
    {
        $this->DomModel = new DomModel();
        $this->fusionPdf = new FusionPdf();
        $this->genererPdf = new GenererPdf();

        $this->loader = new FilesystemLoader(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Views/templates');
        $this->twig = new Environment($this->loader);
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
}
