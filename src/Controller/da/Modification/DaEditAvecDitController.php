<?php

namespace App\Controller\da\Modification;

use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Repository\da\DemandeApproRepository;
use App\Service\da\EmailDaService;
use App\Entity\da\DaObservation;
use App\Service\da\DaEditionService;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\Admin\UrlIdCipher;
use App\Form\da\DemandeApproFormType;
use App\Service\da\DaService;
use App\Service\da\DaAfficherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/demande-appro")
 */
class DaEditAvecDitController extends Controller
{
    private DemandeApproRepository $demandeApproRepository;
    private EmailDaService $emailDaService;

    private UrlIdCipher $urlIdCipher;
    private DaService $daService;
    private DaAfficherService $daAfficherService;
    private DaEditionService $daEditionService;

    public function __construct(DaService $daService, DaAfficherService $daAfficherService, DaEditionService $daEditionService)
    {
        $em = $this->getEntityManager();
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->emailDaService = new EmailDaService($this->getTwig(), $this->getUrlGenerator());
        $this->urlIdCipher = new UrlIdCipher;
        $this->daService = $daService;
        $this->daAfficherService = $daAfficherService;
        $this->daEditionService = $daEditionService;
    }

    /**
     * @Route("/edit-avec-dit/{token}", name="da_edit_avec_dit")
     */
    public function edit(string $token, Request $request)
    {
        $id = $this->urlIdCipher->decryptInt($token);

        if (empty($id) && $id !== 0) throw new ResourceNotFoundException();

        /** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
        $demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $ancienDals = $this->daEditionService->getAncienDAL($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $ancienDals);

        $observations = $this->getEntityManager()->getRepository(DaObservation::class)->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        return $this->render('da/edit-avec-dit.html.twig', [
            'form'         => $form->createView(),
            'observations' => $observations,
            'peutModifier' => $this->daEditionService->peutModifier($demandeAppro->getStatutDal(), $this->estAtelier()),
            'numDa'        => $numDa,
        ]);
    }

    /** 
     * @Route("/delete-line-avec-dit/{numDa}/{ligne}",name="da_delete_line_avec_dit")
     */
    public function deleteLineDa(string $numDa, string $ligne)
    {
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
            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé

            $notifType = "success";
            $notifMessage = "Réussite de l'opération: la ligne de DA a été supprimée avec succès.";
        } else {
            $notifType = "danger";
            $notifMessage = "Echec de la suppression de la ligne: la ligne de DA n'existe pas.";
        }
        $this->getSessionService()->set('notification', ['type' => $notifType, 'message' => $notifMessage]);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }

    private function traitementForm($form, Request $request, iterable $ancienDals): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro = $form->getData();
            $numDa = $demandeAppro->getNumeroDemandeAppro();

            $this->daEditionService->modificationDa($demandeAppro, $this->lignesFormulaire($form->get('DAL')), StatutDaConstant::STATUT_SOUMIS_APPRO);
            if ($demandeAppro->getObservation() !== null) {
                $this->daService->insertionObservation($numDa, $demandeAppro->getObservation(), $this->getUserName());
            }

            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé

            $this->emailDaService->envoyerMailModificationDa($demandeAppro, $this->getUser(), $ancienDals);

            //notification
            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
        }
    }

    /** Valeurs du formulaire des lignes DAL, passées au service (qui ne connaît pas les formulaires) */
    private function lignesFormulaire($formDAL): array
    {
        $lignes = [];
        foreach ($formDAL as $subFormDAL) {
            $lignes[] = [
                'dal'               => $subFormDAL->getData(),
                'filesToDelete'     => $subFormDAL->get('filesToDelete')->getData(),
                'existingFileNames' => $subFormDAL->get('existingFileNames')->getData(),
                'newFiles'          => $subFormDAL->get('fileNames')->getData(),
            ];
        }
        return $lignes;
    }
}
