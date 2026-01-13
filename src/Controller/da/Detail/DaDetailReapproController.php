<?php

namespace App\Controller\da\Detail;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\admin\Application;
use App\Form\da\DaObservationType;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\detail\DaDetailReapproTrait;

/**
 * @Route("/demande-appro")
 */
class DaDetailReapproController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailReapproTrait;
	use AutorisationTrait;

	public function __construct()
	{
		parent::__construct();

		$this->initDaDetailReapproTrait();
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

		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'ASC']);

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$fichiers = $this->getAllDAFile([
			'baiPath'   => $this->getBaIntranetPath($demandeAppro),
			'badPath'   => $this->getBaDocuWarePath($demandeAppro),
		]);

		return $this->render('da/detail.html.twig', [
			'detailTemplate'    => 'detail-reappro',
			'formObservation'	=> $formObservation->createView(),
			'demandeAppro'      => $demandeAppro,
			'codeCentrale'      => $this->estAdmin() || in_array($demandeAppro->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
			'observations'      => $observations,
			'fichiers'          => $fichiers,
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

			$this->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $daObservation->getObservation(), $daObservation->getFileNames());

			$notification = [
				'type'    => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			$this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estUserDansServiceAppro());

			$this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da");
		}
	}
}
