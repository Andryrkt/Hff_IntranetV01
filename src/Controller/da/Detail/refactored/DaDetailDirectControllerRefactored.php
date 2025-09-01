<?php

namespace App\Controller\da\Detail;
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
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Form\da\DaObservationType;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\detail\DaDetailDirectTrait;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class DaDetailDirectController extends BaseController
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

	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailDirectTrait;
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
	 * @Route("/detail-direct/{id}", name="da_detail_direct")
	 */
	public function detail(int $id, Request $request)
	{
		//verification si user connecter
		$this->verifierSessionUtilisateur();

		$this->checkPageAccess($this->estAdmin()); // todo: à changer plus tard

		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA

		$numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());

		$demandeAppro = $this->filtreDal($demandeAppro, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, [
			'achatDirect' => $demandeAppro->getAchatDirect()
		])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()]);

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL());

		$fichiers = $this->getAllDAFile([
			'baPath'    => $this->getBaPath($demandeAppro),
			'bcPath'    => $this->getBcPath($demandeAppro),
			'facblPath' => $this->getFacBlPath($demandeAppro),
		]);

		return $this->render('da/detail.html.twig', [
			'formObservation'			=> $formObservation->createView(),
			'demandeAppro'      		=> $demandeAppro,
			'demandeApproLines'   		=> $demandeApproLPrepared,
			'observations'      		=> $observations,
			'fichiers'            		=> $fichiers,
			'connectedUser'     		=> $this->getUser(),
			'statutAutoriserModifAte' 	=> $demandeAppro->getStatutDal() === DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
			'estAte'            		=> $this->estUserDansServiceAtelier(),
			'estAppro'          		=> $this->estUserDansServiceAppro(),
		]);
	}

	/**  
	 * Filtre les lignes de la DA (Demande Appro) pour ne garder que celles qui correspondent au numero de version max
	 */
	private function filtreDal($demandeAppro, int $numeroVersionMax): DemandeAppro
	{
		// filtre une collection de versions selon le numero de version max
		$dernieresVersions = $demandeAppro->getDAL()->filter(function ($item) use ($numeroVersionMax) {
			return $item->getNumeroVersion() == $numeroVersionMax && $item->getDeleted() == 0;
		});
		$demandeAppro->setDAL($dernieresVersions); // on remplace la collection de versions par la collection filtrée

		return $demandeAppro;
	}

	/** 
	 * Traitement du formulaire
	 */
	private function traitementFormulaire($form, Request $request, DemandeAppro $demandeAppro)
	{
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			/** @var DaObservation $daObservation daObservation correspondant au donnée du form */
			$daObservation = $form->getData();

			$this->insertionObservation($daObservation->getObservation(), $demandeAppro);

			if ($this->estUserDansServiceAppro() && $daObservation->getStatutChange()) {
				$this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
				$this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);

				$this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
			}

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			/** ENVOIE D'EMAIL pour l'observation */
			$service = $this->estUserDansServiceAppro() ? 'appro' : $demandeAppro->getServiceEmetteur()->getLibelleService();
			$this->emailDaService->envoyerMailObservationDaDirect($demandeAppro, [
				'service' 		=> $service,
				'observation'   => $daObservation->getObservation(),
				'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
			]);

			$this->sessionManagerService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da");
		}
	}

	private function modificationStatutDal(string $numDa, string $statut): void
	{
		$numeroVersionMax = $this->getEntityManager()->getRepository(DemandeApproL::class)->getNumeroVersionMax($numDa);
		$dals = $this->getEntityManager()->getRepository(DemandeApproL::class)->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

		foreach ($dals as  $dal) {
			$dal->setStatutDal($statut);
			$dal->setEdit(3);
			$this->getEntityManager()->persist($dal);
		}

		$this->getEntityManager()->flush();
	}

	private function modificationStatutDa(string $numDa, string $statut): void
	{
		$da = $this->getEntityManager()->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $numDa]);
		$da->setStatutDal($statut);

		$this->getEntityManager()->persist($da);
		$this->getEntityManager()->flush();
	}
}
