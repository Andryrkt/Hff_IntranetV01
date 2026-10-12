<?php

namespace App\Controller\da\Detail;

use App\Model\dit\DitModel;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Model\da\DaAfficherModel;
use App\Form\da\DaObservationType;
use App\Service\Admin\UrlIdCipher;
use App\Service\da\DaDetailService;
use App\Service\da\DaTimelineService;
use App\Service\da\DocRattacheService;
use App\Constants\da\StatutDaConstant;
use App\Entity\dit\DemandeIntervention;
use App\Service\da\DaObservationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/demande-appro")
 */
class DaDetailAvecDitController extends Controller
{
	private DemandeApproRepository $demandeApproRepository;

	private DocRattacheService $docRattacheService;
	private DaTimelineService $daTimelineService;
	private DaObservationService $daObservationService;
	private DaDetailService $daDetailService;
	private UrlIdCipher $urlIdCipher;

	public function __construct(
		EntityManagerInterface $em,
		DocRattacheService $docRattacheService,
		DaTimelineService $daTimelineService,
		DaObservationService $daObservationService,
		DaDetailService $daDetailService
	) {
		$this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
		$this->daDetailService = $daDetailService;
		$this->docRattacheService = $docRattacheService;
		$this->daTimelineService = $daTimelineService;
		$this->daObservationService = $daObservationService;
		$this->urlIdCipher = new UrlIdCipher;
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
		$ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
		$dit = $ditRepository->findOneBy(["numeroDemandeIntervention" => $demandeAppro->getNumeroDemandeDit()]);
		$demandeAppro->setDit($dit);
		$dataModel = $ditModel->recupNumSerieParcPourDa($demandeAppro->getDit()->getIdMateriel());
		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationService->getObservations($demandeAppro->getNumeroDemandeAppro());

		$fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);

		$statutEtAction = (new DaAfficherModel)->getStatutEtActionAffichage($demandeAppro->getNumeroDemandeAppro(), "{$demandeAppro->getAgenceServiceEmetteur()} — {$demandeAppro->getDemandeur()}");
		$statutDa = $statutEtAction['statutDa'] ?? "";

		$demandeApproLPrepared = $this->daDetailService->prepareDataForDisplayDetail($demandeAppro->getDAL(), $statutDa, 'da_delete_line_avec_dit');
		$timeLineData = $this->daTimelineService->getTimelineData($demandeAppro);
		$resolvedSlug = $this->urlIdCipher->resolveSlugDemandeAppro($request->query->get('redirect'), $this->getUrlGenerator());

		return $this->render('da/detail.html.twig', [
			'detailTemplate'      		=> 'detail-avec-dit',
			'urlRetour'           		=> $resolvedSlug['url'],
			'titreBoutonRetour'   		=> $resolvedSlug['title'],
			'urlModifierDa'      		=> $this->getUrlGenerator()->generate('da_edit_avec_dit', ['token' => $token]),
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
			$notification = $this->daDetailService->envoyerObservation($demandeAppro, $form->getData(), $this->getUserName(), $this->getUser(), $this->estAppro());

			$this->getSessionService()->set('notification', $notification);
			return $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
		}
	}
}
