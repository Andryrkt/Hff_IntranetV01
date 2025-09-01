<?php

namespace App\Controller\da\Modification;
use App\Service\FusionPdf;
use App\Model\ProfilModel;
use App\Model\badm\BadmModel;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\dom\DomModel;
use App\Model\da\DaModel;
use App\Model\dom\DomDetailModel;
use App\Model\dom\DomDuplicationModel;
use App\Model\dom\DomListModel;
use App\Model\dit\DitModel;
use App\Service\SessionManagerService;
use App\Service\ExcelService;


use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Form\da\DemandeApproDirectFormType;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\modification\DaEditDirectTrait;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class DaEditDirectController extends BaseController
{
    private FusionPdf $fusionPdfService;
    private ProfilModel $profilModelService;
    private BadmModel $badmModelService;
    private PersonnelModel $personnelModelService;
    private DomModel $domModelService;
    private DaModel $daModelService;
    private DomDetailModel $domDetailModelService;
    private DomDuplicationModel $domDuplicationModelService;
    private DomListModel $domListModelService;
    private DitModel $ditModelService;
    private SessionManagerService $sessionManagerService;
    private ExcelService $excelServiceService;

    use DaAfficherTrait;
    use DaEditDirectTrait;
    use AutorisationTrait;

    public function __construct(
        FusionPdf $fusionPdfService,
        ProfilModel $profilModelService,
        BadmModel $badmModelService,
        PersonnelModel $personnelModelService,
        DomModel $domModelService,
        DaModel $daModelService,
        DomDetailModel $domDetailModelService,
        DomDuplicationModel $domDuplicationModelService,
        DomListModel $domListModelService,
        DitModel $ditModelService,
        SessionManagerService $sessionManagerService,
        ExcelService $excelServiceService
    ) {
        parent::__construct();
        $this->fusionPdfService = $fusionPdfService;
        $this->profilModelService = $profilModelService;
        $this->badmModelService = $badmModelService;
        $this->personnelModelService = $personnelModelService;
        $this->domModelService = $domModelService;
        $this->daModelService = $daModelService;
        $this->domDetailModelService = $domDetailModelService;
        $this->domDuplicationModelService = $domDuplicationModelService;
        $this->domListModelService = $domListModelService;
        $this->ditModelService = $ditModelService;
        $this->sessionManagerService = $sessionManagerService;
        $this->excelServiceService = $excelServiceService;
    }

    /**
     * @Route("/edit-direct/{id}", name="da_edit_direct")
     */
    public function edit(int $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->checkPageAccess($this->estAdmin()); // todo: à changer plus tard

        /** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
        $demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $demandeAppro = $this->filtreDal($demandeAppro, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

        $ancienDals = $this->getAncienDAL($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproDirectFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $ancienDals);

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        return $this->render('da/edit-da-direct.html.twig', [
            'form'              => $form->createView(),
            'observations'      => $observations,
            'peutModifier'      => $this->PeutModifier($demandeAppro),
            'numDa'             => $numDa,
        ]);
    }

    /** 
     * @Route("/delete-line-direct/{numDa}/{ligne}",name="da_delete_line_direct")
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
        $this->sessionManagerService->set('notification', ['type' => $notifType, 'message' => $notifMessage]);
        $this->redirectToRoute("list_da");
    }

    private function traitementForm($form, Request $request, iterable $ancienDals): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro = $form->getData();
            $numDa = $demandeAppro->getNumeroDemandeAppro();
            $statutDaModifier = $this->statutDaModifier($demandeAppro);

            $this->modificationDa($demandeAppro, $form->get('DAL'), $statutDaModifier);
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            // ajout des données dans la table DaAfficher
            $this->ajouterDansTableAffichageParNumDa($numDa);

            if ($statutDaModifier === DemandeAppro::STATUT_A_VALIDE_DW) {
                // ajout des données dans la table DaSoumisAValidation
                $this->ajouterDansDaSoumisAValidation($demandeAppro);

                /** création de pdf et envoi dans docuware */
                $this->creationPdfSansDitAvaliderDW($demandeAppro);
            }

            /** ENVOIE MAIL */
            $this->emailDaService->envoyerMailModificationDaDirect($demandeAppro, [
                'ancienDals'    => $ancienDals,
                'nouveauDals'   => $demandeAppro->getDAL(),
                'service'       => $demandeAppro->getServiceEmetteur()->getLibelleService(),
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms()
            ]);

            //notification
            $this->sessionManagerService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }
}
