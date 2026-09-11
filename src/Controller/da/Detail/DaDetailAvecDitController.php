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
use App\Service\da\DocRattacheService;
use App\Service\Admin\UrlIdCipher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/demande-appro")
 */
class DaDetailAvecDitController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailAvecDitTrait;

	private DocRattacheService $docRattacheService;
	private DaTimelineService $daTimelineService;
	private UrlIdCipher $urlIdCipher;

	public function __construct(DocRattacheService $docRattacheService, DaTimelineService $daTimelineService, UrlIdCipher $urlIdCipher)
	{
		parent::__construct();

		$this->initDaDetailAvecDitTrait();
		$this->docRattacheService = $docRattacheService;
		$this->daTimelineService = $daTimelineService;
		$this->urlIdCipher = $urlIdCipher;
	}

	/**
	 * @Route("/detail-avec-dit/{token}", name="da_detail_avec_dit")
	 */
	public function detail(string $token, Request $request)
	{
		$id = $this->urlIdCipher->decryptInt($token);

		if (empty($id) && $id !== 0) throw new ResourceNotFoundException();

		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
		$ditModel = new DitModel();
		$dataModel = $ditModel->recupNumSerieParcPourDa($demandeAppro->getDit()->getIdMateriel());

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'ASC']);

		$fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);

		$statutEtAction = $this->daAfficherRepository->getStatutEtActionAffichage($demandeAppro);
		$statutDa = $statutEtAction['statutDa'] ?? "";

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL(), $statutDa);
		$timeLineData = $this->daTimelineService->getTimelineData($demandeAppro->getNumeroDemandeAppro(), $demandeAppro->getDaTypeId());

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
			'statutDa'          		=> $statutDa,
			'classStatutDa'    		    => $statutEtAction['classStatutDa'],
			'action'      		        => $statutEtAction['action'],
			'statutAutoriserModifAte' 	=> $statutDa === StatutDaConstant::STATUT_AUTORISER_EMETTEUR,
			'estAte'            		=> $this->estAtelier(),
			'estAppro'          		=> $this->estAppro(),
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

			if ($this->estAppro() && $daObservation->getStatutChange()) {
				$this->appliquerChangementStatut($demandeAppro, StatutDaConstant::STATUT_AUTORISER_EMETTEUR);

				$this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
			}

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			$this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estAppro());

			$this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
		}
	}
}
