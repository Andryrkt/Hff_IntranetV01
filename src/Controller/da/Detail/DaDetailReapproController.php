<?php

namespace App\Controller\da\Detail;

use App\Service\da\DaService;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Model\da\DaAfficherModel;
use App\Form\da\DaObservationType;
use App\Service\Admin\UrlIdCipher;
use App\Service\da\DaDetailService;
use App\Service\da\DaTimelineService;
use App\Service\da\DocRattacheService;
use App\Service\da\DaObservationService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/demande-appro")
 */
class DaDetailReapproController extends Controller
{
	private DaDetailService $daDetailService;

	private DaService $daService;

	private DaObservationService $daObservationService;
	private UrlIdCipher $urlIdCipher;
	private DaTimelineService $daTimelineService;
	private DocRattacheService $docRattacheService;

	public function __construct(
		DaService $daService,
		DaObservationService $daObservationService,
		DocRattacheService $docRattacheService,
		DaTimelineService $daTimelineService,
		DaDetailService $daDetailService
	) {
		$this->daDetailService    = $daDetailService;
		$this->daService          = $daService;
		$this->daObservationService = $daObservationService;
		$this->urlIdCipher        = new UrlIdCipher;
		$this->daTimelineService  = $daTimelineService;
		$this->docRattacheService = $docRattacheService;
	}

	/**
	 * @Route("/detail-reappro/{token}", name="da_detail_reappro")
	 */
	public function detail(string $token, Request $request)
	{
		$id = $this->urlIdCipher->decryptInt($token);

		if (empty($id) && $id !== 0) throw new ResourceNotFoundException();

		$demandeAppro = $this->daService->getDemandeAppro($id); // recupération de la DA
		$observations = $this->daObservationService->getObservations($demandeAppro->getNumeroDemandeAppro());

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);
		$timeLineData = $this->daTimelineService->getTimelineData($demandeAppro);
		$statutEtAction = (new DaAfficherModel)->getStatutEtActionAffichage($demandeAppro->getNumeroDemandeAppro(), "{$demandeAppro->getAgenceServiceEmetteur()} — {$demandeAppro->getDemandeur()}");
		$resolvedSlug = $this->urlIdCipher->resolveSlugDemandeAppro($request->query->get('redirect'), $this->getUrlGenerator());

		return $this->render('da/detail.html.twig', [
			'detailTemplate'    => 'detail-reappro',
			'urlRetour'         => $resolvedSlug['url'],
			'titreBoutonRetour' => $resolvedSlug['title'],
			'formObservation'	=> $formObservation->createView(),
			'demandeAppro'      => $demandeAppro,
			'isMensuel'         => $demandeAppro->getDaTypeId() == DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
			'codeCentrale'      => $this->estAdmin() || $this->estEnergie(),
			'observations'      => $observations,
			'fichiers'          => $fichiers,
			'timelineData'      => $timeLineData,
			'connectedUser'     => $this->getUser(),
			'statutDa'          => $statutEtAction['statutDa'],
			'classStatutDa'    	=> $statutEtAction['classStatutDa'],
			'action'      		=> $statutEtAction['action'],
		]);
	}

	/** 
	 * Traitement du formulaire
	 */
	private function traitementFormulaire($form, Request $request, DemandeAppro $demandeAppro)
	{
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$notification = $this->daDetailService->envoyerObservation($demandeAppro, $form->getData(), $this->getUserName(), $this->getUser(), $this->estAppro());

			$this->getSessionService()->set('notification', $notification);
			return $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
		}
	}
}
