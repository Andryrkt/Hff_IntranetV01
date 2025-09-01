<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Controller\Controller;
use App\Model\badm\BadmDetailModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmDetailController extends BaseController
{

    /**
     * @Route("/detail/{id}", name="BadmDetail_detailBadm")
     */
    public function detailBadm($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $badm = $this->getEntityManager()->getRepository(Badm::class)->findOneBy(['id' => $id]);

        $badmDetailModel = new BadmDetailModel();
        $data = $badmDetailModel->findAll($badm->getIdMateriel());

        $this->logUserVisit('BadmDetail_detailBadm', [
            'id' => $id
        ]); // historisation du page visité par l'utilisateur

        return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render(
            'badm/detail.html.twig',
            [
                'badm' => $badm,
                'data' => $data
            ]
        ));
    }
}
