<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\tik\DemandeSupportInformatique;
use App\Form\tik\DetailTikType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DetailTikController extends Controller
{
    /**  
     * @Route("/tik-detail/{id<\d+>}", name="detail_tik")
     */
    public function detail($id, Request $request)
    {
        $tik = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);
        
        if (!$tik) {
            self::$twig->display('404.html.twig');
        } else {
            $form = self::$validator->createBuilder(DetailTikType::class, $tik)->getForm();

            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) { 
                dd($form);
            }

            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik'        => $tik,
                'form'       => $form->createView(),
                'autoriser'  => true
            ]);
        }
        
    }
}

