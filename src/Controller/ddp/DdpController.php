<?php

namespace App\Controller\ddp;


use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ddp")
 */
class DdpController extends Controller
{
    /**
     * @Route("/new/avance", name="new_ddp_avance")
     */
    public function newAvance()
    {


        return $this->render('ddp/avance.html.twig', [
            'controller_name' => 'DdpController',
        ]);
    }
}
