<?php

namespace App\Controller\da\Detail;

use App\Service\da\DaService;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\admin\Application;
use App\Service\da\EmailDaService;
use App\Form\da\DaObservationType;
use App\Controller\Traits\AutorisationTrait;
use App\Service\da\DaTimelineService;
use App\Service\da\DocRattacheService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDetailReapproController extends Controller
{
	use AutorisationTrait;

	private DaService $daService;
	private DocRattacheService $docRattacheService;
	private DaTimelineService $daTimelineService;

	public function __construct(DaService $daService, DocRattacheService $docRattacheService, DaTimelineService $daTimelineService)
	{
		$this->daService = $daService;
		$this->docRattacheService = $docRattacheService;
		$this->daTimelineService = $daTimelineService;
	}

	/**
	 * @Route("/detail-reappro/{id}", name="da_detail_reappro")
	 */
	public function detail(int $id, Request $request)
	{
		//verification si user connecter
		$this->verifierSessionUtilisateur();

		/** Autorisation accès */
		$this->autorisationAcces($this->getUser(), Application::ID_DAP);
		/** FIN AUtorisation accès */

		$demandeAppro = $this->daService->getDemandeAppro($id); // recupération de la DA
		$observations = $this->daService->getObservations($demandeAppro->getNumeroDemandeAppro());

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);
		$timeLineData = $this->estAdmin() ? $this->daTimelineService->getTimelineData($demandeAppro->getNumeroDemandeAppro()) : [];

		return $this->render('da/detail.html.twig', [
			'detailTemplate'    => 'detail-reappro',
			'formObservation'	=> $formObservation->createView(),
			'demandeAppro'      => $demandeAppro,
			'isMensuel'         => $demandeAppro->getDaTypeId() == DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
			'codeCentrale'      => $this->estAdmin() || in_array($demandeAppro->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
			'observations'      => $observations,
			'fichiers'          => $fichiers,
			'timelineAccess'    => $this->estAdmin(),
			'timelineData'      => $timeLineData,
			'connectedUser'     => $this->getUser(),
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

			$this->daService->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $daObservation->getObservation(), $this->getUserName(), $daObservation->getFileNames());

			$notification = [
				'type'    => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			$emailDaService = new EmailDaService($this->getTwig());
			$emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estUserDansServiceAppro());

			$this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da");
		}
	}
}
