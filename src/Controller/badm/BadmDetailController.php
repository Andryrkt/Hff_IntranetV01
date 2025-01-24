<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Controller\Controller;
use App\Model\badm\BadmDetailModel;
use Symfony\Component\Routing\Annotation\Route;


class BadmDetailController extends Controller
{

    /**
     * @Route("/detailBadm/{id}", name="BadmDetail_detailBadm")
     */
    public function detailBadm($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $badm = self::$em->getRepository(Badm::class)->findOneBy(['id' => $id]);

        $badmDetailModel = new BadmDetailModel();
        $data = $badmDetailModel->findAll($badm->getIdMateriel());

        $this->logUserVisit('BadmDetail_detailBadm', [
            'id' => $id
        ]); // historisation du page visité par l'utilisateur

        self::$twig->display(
            'badm/detail.html.twig',
            [
                'badm' => $badm,
                'data' => $data
            ]
        );
    }
}
