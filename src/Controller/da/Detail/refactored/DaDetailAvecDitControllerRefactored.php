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
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Form\da\DaObservationType;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\detail\DaDetailAvecDitTrait;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class DaDetailAvecDitController extends BaseController
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
	use DaDetailAvecDitTrait;
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
	 * @Route("/detail-avec-dit/{id}", name="da_detail_avec_dit")
	 */
	public function detail(int $id, Request $request)
	{
		//verification si user connecter
		$this->verifierSessionUtilisateur();

		/** Autorisation accès */
		$this->autorisationAcces($this->getUser(), Application::ID_DAP);
		/** FIN AUtorisation accès */

		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
		$dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $demandeAppro->getNumeroDemandeDit()]); // recupération du DIT associée à la DA
		$ditModel = new DitModel;
		$dataModel = $ditModel->recupNumSerieParcPourDa($dit->getIdMateriel());

		$numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
		$demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, [
			'achatDirect' => $demandeAppro->getAchatDirect()
		])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()]);

		$fichiers = $this->getAllDAFile([
			'baPath'    => $this->getBaPath($demandeAppro),
			'orPath'    => $this->getOrPath($demandeAppro),
			'bcPath'    => $this->getBcPath($demandeAppro),
			'facblPath' => $this->getFacBlPath($demandeAppro),
		]);

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL());

		return $this->render('da/detail.html.twig', [
			'formObservation'			=> $formObservation->createView(),
			'demandeAppro'      		=> $demandeAppro,
			'demandeApproLines'   		=> $demandeApproLPrepared,
			'observations'      		=> $observations,
			'numSerie'          		=> $dataModel[0]['num_serie'],
			'numParc'           		=> $dataModel[0]['num_parc'],
			'dit'               		=> $dit,
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
	private function filtreDal($demandeAppro, $dit, int $numeroVersionMax): DemandeAppro
	{
		$demandeAppro->setDit($dit); // association de la DA avec le DIT

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
			$service = $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : '');
			$this->emailDaService->envoyerMailObservationDaAvecDit($demandeAppro, [
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
