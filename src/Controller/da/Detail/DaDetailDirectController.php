<?php

namespace App\Controller\da\Detail;

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
use App\Controller\Traits\da\detail\DaDetailDirectTrait;
use App\Service\da\DaTimelineService;

/**
 * @Route("/demande-appro")
 */
class DaDetailDirectController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailDirectTrait;
	use AutorisationTrait;
	private DaTimelineService $daTimelineService;

	public function __construct(DaTimelineService $daTimelineService)
	{
		parent::__construct();

		$this->initDaDetailDirectTrait();
		$this->daTimelineService = $daTimelineService;
	}

	/**
	 * @Route("/detail-direct/{id}", name="da_detail_direct")
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

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'ASC']);

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL(), $demandeAppro->getStatutDal());

		$fichiers = $this->getAllDAFile([
			'baiPath'      => $this->getBaIntranetPath($demandeAppro),
			'badPath'      => $this->getBaDocuWarePath($demandeAppro),
			'bcPath'       => $this->getBcPath($demandeAppro),
			'facblPath'    => $this->getFacBlPath($demandeAppro),
			'devPjPathDal' => $this->getDevisPjPathDal($demandeAppro),
			'devPjPathObs' => $this->getDevisPjPathObservation($demandeAppro),
		]);
		$timeLineData = $this->estAdmin() ? $this->daTimelineService->getTimelineData($demandeAppro->getNumeroDemandeAppro()) : [];

		return $this->render('da/detail.html.twig', [
			'detailTemplate'      		=> 'detail-direct',
			'formObservation'			=> $formObservation->createView(),
			'demandeAppro'      		=> $demandeAppro,
			'demandeApproLines'   		=> $demandeApproLPrepared,
			'observations'      		=> $observations,
			'fichiers'            		=> $fichiers,
			'connectedUser'     		=> $this->getUser(),
			'statutAutoriserModifAte' 	=> $demandeAppro->getStatutDal() === DemandeAppro::STATUT_AUTORISER_EMETTEUR,
			'estCreateurDaDirecte'      => $this->estCreateurDeDADirecte(),
			'estAppro'          		=> $this->estUserDansServiceAppro(),
			'timelineAccess'    		=> $this->estAdmin(),
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

			if ($this->estUserDansServiceAppro() && $daObservation->getStatutChange()) {
				$this->appliquerChangementStatut($demandeAppro, DemandeAppro::STATUT_AUTORISER_EMETTEUR);

				$this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
			}

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			$this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estUserDansServiceAppro());

			$this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da");
		}
	}
}
