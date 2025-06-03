<?php

namespace App\Controller\da;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionBcController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Route("/soumission-bc/{numCde}", name="da_soumission_bc")
     */
    public function index(string $numCde)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        self::$twig->display('da/soumissionBc.html.twig', []);
    }
}
