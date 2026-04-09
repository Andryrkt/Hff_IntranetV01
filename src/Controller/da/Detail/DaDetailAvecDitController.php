<?php

namespace App\Controller\da\Detail;


use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Form\da\DaObservationType;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Controller\Traits\da\detail\DaDetailAvecDitTrait;
use App\Model\dit\DitModel;
use App\Service\da\DaTimelineService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDetailAvecDitController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailAvecDitTrait;
	private DaTimelineService $daTimelineService;

	public function __construct(DaTimelineService $daTimelineService)
	{
		parent::__construct();

		$this->initDaDetailAvecDitTrait();
		$this->daTimelineService = $daTimelineService;
	}

	/**
	 * @Route("/detail-avec-dit/{id}", name="da_detail_avec_dit")
	 */
	public function detail(int $id, Request $request)
	{
		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
		$ditModel = new DitModel();
		$dataModel = $ditModel->recupNumSerieParcPourDa($demandeAppro->getDit()->getIdMateriel());

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'ASC']);

		$fichiers = $this->getAllDAFile([
			'baiPath'      => $this->getBaIntranetPath($demandeAppro),
			'orPath'       => $this->getOrPath($demandeAppro),
			'bcPath'       => $this->getBcPath($demandeAppro),
			'facblPath'    => $this->getFacBlPath($demandeAppro),
			'devPjPathDal' => $this->getDevisPjPathDal($demandeAppro),
			'devPjPathObs' => $this->getDevisPjPathObservation($demandeAppro),
		]);

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL(), $demandeAppro->getStatutDal());
		$timeLineData = $this->daTimelineService->getTimelineData($demandeAppro->getNumeroDemandeAppro());

		return $this->render('da/detail.html.twig', [
			'detailTemplate'      		=> 'detail-avec-dit',
			'formObservation'			=> $formObservation->createView(),
			'demandeAppro'      		=> $demandeAppro,
			'demandeApproLines'   		=> $demandeApproLPrepared,
			'observations'      		=> $observations,
			'numSerie'          		=> $dataModel[0]['num_serie'],
			'numParc'           		=> $dataModel[0]['num_parc'],
			'fichiers'            		=> $fichiers,
			'connectedUser'     		=> $this->getUser(),
			'statutAutoriserModifAte' 	=> $demandeAppro->getStatutDal() === StatutDaConstant::STATUT_AUTORISER_EMETTEUR,
			'estAte'            		=> false, // TODO: booléen pour savoir si Atelier
			'estAppro'          		=> false, // TODO: booléen pour savoir si Appro
			'timelineData'      		=> $timeLineData,
		]);
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

			$this->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $daObservation->getObservation(), $daObservation->getFileNames());

			// TODO: booléen pour savoir si Appro
			if (false && $daObservation->getStatutChange()) {
				$this->appliquerChangementStatut($demandeAppro, StatutDaConstant::STATUT_AUTORISER_EMETTEUR);

				$this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
			}

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			$this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), false);  // TODO: booléen pour savoir si Appro

			$this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 1, 'page' => 1]);
		}
	}
}
