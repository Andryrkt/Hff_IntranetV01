<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;

class ListeTikController extends Controller
{
    /**
     * @Route("/tik-liste", name="liste_tik_index")
     */
    public function index()
    {

        $data = self::$em->getRepository(DemandeSupportInformatique::class)->findAll();
    
        self::$twig->display('tik/demandeSupportInformatique/list.html.twig', [
            'data' => $data
        ]);
    }
}