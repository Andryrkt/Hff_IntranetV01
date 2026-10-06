<?php

namespace App\Controller\dom;

use App\Entity\dom\Dom;
use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomsDetailController extends Controller
{

    /**
     * @Route("/detail/{id}", name="Dom_detail")
     */
    public function detailDom($id)
    {
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

        return $this->render(
            'doms/detail.html.twig',
            [
                'dom' => $dom,
                'is_temporaire' => $is_temporaire
            ]
        );
    }
}
