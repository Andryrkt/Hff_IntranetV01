<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\tik\DemandeSupportInformatique;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;

class DetailTikController extends Controller
{
    /**  
     * @Route("/tik-detail/{id<\d+>}", name="detail_tik")
     */
    public function detail($id)
    {
        $tik = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);
        
        dump($tik);
        if (!$tik) {
            self::$twig->display('404.html.twig');
        } else {
            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik' => $tik
            ]);
        }
        
    }
}

