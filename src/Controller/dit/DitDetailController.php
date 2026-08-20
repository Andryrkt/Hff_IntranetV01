<?php

namespace App\Controller\dit;

use App\Dto\Dit\DitDetailDto;
use App\Controller\Controller;
use App\Service\Admin\UrlIdCipher;
use App\Entity\dit\DitObservation;
use App\Form\dit\DitObservationType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\FormInterface;
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

        /** @var DemandeIntervention $dit */
        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($realId);

        $form = $this->getFormFactory()->createBuilder(DitObservationType::class, $this->initObservation($dit->getNumeroDemandeIntervention()))->getForm();

        $this->traitementFormulaire($form, $request);

        $observations = $this->getEntityManager()->getRepository(DitObservation::class)->findBy(['numDit' => $dit->getNumeroDemandeIntervention()], ['dateCreation' => 'ASC']);

        $this->logUserVisit('dit_fiche_detail', ['id' => $realId]); // historisation du page visité par l'utilisateur

        return  $this->render('dit/detail.html.twig', [
            'form'         => $form->createView(),
            'dto'          => DitDetailDto::fromEntity($dit),
            'observations' => $observations,
        ]);
    }

    /**
     * Permet d'initialiser un objet DitObservation
     *
     * @param string $numDit
     *
     * @return DitObservation
     */
    private function initObservation(string $numDit): DitObservation
    {
        return (new DitObservation)
            ->setNumDit($numDit)
            ->setUtilisateur($this->getUserName())
        ;
    }

    /**
     * Permet de traiter le formulaire de l'observation
     *
     * @param FormInterface $form
     * @param Request       $request
     * 
     * @return void
     */
    private function traitementFormulaire(FormInterface $form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DitObservation $ditObservation */
            $ditObservation = $form->getData();
            $text = str_replace(["\r\n", "\n", "\r"], "<br>", $ditObservation->getObservation());
            $ditObservation->setObservation($text);

            $this->getEntityManager()->persist($ditObservation);
            $this->getEntityManager()->flush();

            $this->getSessionService()->set('notification', [
                'type'    => 'success',
                'message' => 'Votre observation a été enregistré avec succès.'
            ]);
            $this->redirectToRoute('dit_index');
        }
    }
}
