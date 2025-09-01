<?php

namespace App\Controller\da\Modification;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Entity\da\DemandeApproLR;
use App\Form\da\DemandeApproFormType;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\modification\DaEditAvecDitTrait;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class DaEditAvecDitController extends BaseController
{
    use DaAfficherTrait;
    use DaEditAvecDitTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager($this->getEntityManager());
        $this->initDaEditAvecDitTrait();
    }

    /**
     * @Route("/edit-avec-dit/{id}", name="da_edit_avec_dit")
     */
    public function edit(int $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        /** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
        $demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $demandeAppro->getNumeroDemandeDit()]); // recupération du DIT associée à la DA
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

        $ancienDals = $this->getAncienDAL($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $ancienDals);

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        return $this->render('da/edit-avec-dit.html.twig', [
            'form'         => $form->createView(),
            'observations' => $observations,
            'peutModifier' => $this->PeutModifier($demandeAppro),
            'numDa'        => $numDa,
        ]);
    }

    /** 
     * @Route("/delete-line-avec-dit/{numDa}/{ligne}",name="da_delete_line_avec_dit")
     */
    public function deleteLineDa(string $numDa, string $ligne)
    {
        $this->verifierSessionUtilisateur();

        $demandeApproLs = $this->getEntityManager()->getRepository(DemandeApproL::class)->findBy([
            'numeroDemandeAppro' => $numDa,
            'numeroLigne'        => $ligne
        ]);

        if ($demandeApproLs) {
            $demandeApproLRs = $this->getEntityManager()->getRepository(DemandeApproLR::class)->findBy([
                'numeroDemandeAppro' => $numDa,
                'numeroLigne'        => $ligne
            ]);

            foreach ($demandeApproLs as $demandeApproL) {
                $this->getEntityManager()->remove($demandeApproL);
            }

            foreach ($demandeApproLRs as $demandeApproLR) {
                $this->getEntityManager()->remove($demandeApproLR);
            }

            $this->getEntityManager()->flush(); // enregistrer le modifications avant l'appel à la méthode "ajouterDansTableAffichageParNumDa"
            $this->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé

            $notifType = "success";
            $notifMessage = "Réussite de l'opération: la ligne de DA a été supprimée avec succès.";
        } else {
            $notifType = "danger";
            $notifMessage = "Echec de la suppression de la ligne: la ligne de DA n'existe pas.";
        }
        $this->sessionService->set('notification', ['type' => $notifType, 'message' => $notifMessage]);
        $this->redirectToRoute("list_da");
    }

    private function traitementForm($form, Request $request, iterable $ancienDals): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro = $form->getData();
            $numDa = $demandeAppro->getNumeroDemandeAppro();

            $this->modificationDa($demandeAppro, $form->get('DAL'), DemandeAppro::STATUT_SOUMIS_APPRO);
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            $this->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé

            /** ENVOIE MAIL */
            $this->emailDaService->envoyerMailModificationDaAvecDit($demandeAppro, [
                'ancienDals'    => $ancienDals,
                'nouveauDals'   => $demandeAppro->getDAL(),
                'service'       => 'atelier',
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms()
            ]);

            //notification
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }
}
