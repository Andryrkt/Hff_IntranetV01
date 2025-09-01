<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Entity\dom\Dom;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomsDetailController extends BaseController
{

    /**
     * @Route("/detail/{id}", name="Dom_detail")
     */
    public function detailDom($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dom = $this->getEntityManager()->getRepository(Dom::class)->findOneBy(['id' => $id]);
        $dom->setIdemnityDepl((int)str_replace('.', '', $dom->getIdemnityDepl()));
        $matricule = $dom->getMatricule();
        if (strlen($matricule) === 4 && ctype_digit($matricule)) {
            $is_temporaire = 'PERMANENT';
        } else {
            $is_temporaire = 'TEMPORAIRE';
        }

        $this->logUserVisit('Dom_detail', [
            'id' => $id,
        ]); // historisation du page visité par l'utilisateur

        return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render(
            'doms/detail.html.twig',
            [
                'dom' => $dom,
                'is_temporaire' => $is_temporaire
            ]
        ));
    }
}
