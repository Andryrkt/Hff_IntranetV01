<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Controller\Controller;
use App\Model\badm\BadmDetailModel;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmDetailController extends Controller
{

    /**
     * @Route("/detail/{id}", name="BadmDetail_detailBadm")
     */
    public function detailBadm($id)
    {
        $badm = $this->getEntityManager()->getRepository(Badm::class)->findOneBy(['id' => $id]);

        $badmDetailModel = new BadmDetailModel();
        $data = $badmDetailModel->findAll($badm->getIdMateriel());

        $this->logUserVisit('BadmDetail_detailBadm', [
            'id' => $id
        ]); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/detail.html.twig',
            [
                'badm' => $badm,
                'data' => $data
            ]
        );
    }
}
