<?php

namespace App\Controller\da;

use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    private DaAfficherRepository $daAfficherRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = self::$em->getRepository(DaAfficher::class);
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index()
    {
        dd('okey');
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        // Donnée à envoyer à la vue
        $data = $this->daAfficherRepository->findAll();
        self::$twig->display('da/list_da.html.twig', [
            'data' => $data,
        ]);
    }
}
