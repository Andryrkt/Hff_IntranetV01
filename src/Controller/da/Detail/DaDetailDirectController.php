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

/**
 * @Route("/demande-appro")
 */
class DaDetailDirectController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailDirectTrait;
	use AutorisationTrait;

	public function __construct()
	{
		parent::__construct();

		$this->initDaDetailDirectTrait();
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

		$numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());

		$demandeAppro = $this->filtreDal($demandeAppro, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()]);

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL());

		$fichiers = $this->getAllDAFile([
			'baiPath'   => $this->getBaIntranetPath($demandeAppro),
			'bcPath'    => $this->getBcPath($demandeAppro),
			'facblPath' => $this->getFacBlPath($demandeAppro),
			'devPjPath' => $this->getDevisPjPath($demandeAppro),
		]);

		return $this->render('da/detail.html.twig', [
			'detailTemplate'      		=> 'detail-direct',
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

			$this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estUserDansServiceAppro());

			$this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
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
