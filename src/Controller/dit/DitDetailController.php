<?php

namespace App\Controller\dit;

use App\Dto\Dit\DitDetailDto;
use App\Controller\Controller;
use App\Service\Admin\UrlIdCipher;
use App\Entity\dit\DitObservation;
use App\Form\dit\DitObservationType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitDetailController extends Controller
{
    /**
     * @Route("/fiche-detail-dit/{token}", name="dit_fiche_detail")
     */
    public function detailDit(string $token, Request $request)
    {
        $realId = (new UrlIdCipher)->decryptInt($token);

        if (empty($realId) && $realId !== 0) throw new ResourceNotFoundException();

        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($realId);

        $form = $this->getFormFactory()->createBuilder(DitObservationType::class, new DitObservation)->getForm();

        $this->logUserVisit('dit_fiche_detail', ['id' => $realId]); // historisation du page visité par l'utilisateur

        return  $this->render('dit/detail.html.twig', [
            'form' => $form->createView(),
            'dto'  => DitDetailDto::fromEntity($dit)
        ]);
    }
}
