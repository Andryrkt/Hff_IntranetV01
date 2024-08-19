<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Entity\Badm;
use Symfony\Component\Routing\Annotation\Route;


class BadmDetailController extends Controller
{

    /**
     * @Route("/detailBadm/{id}", name="BadmDetail_detailBadm")
     */
    public function detailBadm($id)
    {
        $badm = self::$em->getRepository(Badm::class)->findOneBy(['id' => $id]);
        

        $data = $this->badmDetail->findAll($badm->getIdMateriel());
    
      
        self::$twig->display(
            'badm/detail.html.twig',
            [
                'badm' => $badm,
                'data' => $data
            ]
        );
    }
}
