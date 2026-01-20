<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\da\DemandeApproParent;
use App\Form\da\DemandeApproAchatFormType;
use App\Traits\JoursOuvrablesTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/demande-appro") */
class DaNewAchatController extends Controller
{
    use AutorisationTrait, JoursOuvrablesTrait;

    /**
     * @Route("/new-da-achat/{id<\d+>}", name="da_new_achat")
     */
    public function newDaAchat(int $id, Request $request)
    {
        // verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin() || $this->estCreateurDeDADirecte());
        /** FIN AUtorisation accès */

        $demandeApproParentRepository = $this->getEntityManager()->getRepository(DemandeApproParent::class);

        $demandeApproParent = $id === 0 ? $this->initialisationDemandeApproAchat() : $demandeApproParentRepository->find($id);

        $form = $this->getFormFactory()->createBuilder(DemandeApproAchatFormType::class, $demandeApproParent)->getForm();
        $this->traitementFormAchat($form, $request, $demandeApproParent);

        return $this->render('da/new-da-achat.html.twig', [
            'form'        => $form->createView(),
            'codeCentrale' => $this->estAdmin() || in_array($demandeApproParent->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
        ]);
    }

    private function initialisationDemandeApproAchat(): DemandeApproParent
    {
        $demandeApproParent = new DemandeApproParent();

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $demandeApproParent
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setAgenceServiceEmetteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setUser($this->getUser())
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaite($this->ajouterJoursOuvrables(5)) // Définit la date de fin souhaitée automatiquement à 5 jours après la date actuelle
        ;

        return $demandeApproParent;
    }

    private function traitementFormAchat(FormInterface $form, Request $request, DemandeApproParent $demandeApproParent): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeApproParent = $form->getData();
        }
    }
}
