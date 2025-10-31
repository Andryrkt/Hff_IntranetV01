<?php

namespace App\Controller\da\Detail;


use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Form\da\DaObservationType;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\detail\DaDetailAvecDitTrait;
use App\Model\dit\DitModel;

/**
 * @Route("/demande-appro")
 */
class DaDetailAvecDitController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailAvecDitTrait;
	use AutorisationTrait;

	public function __construct()
	{
		parent::__construct();

		$this->initDaDetailAvecDitTrait();
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
		$demandeAppro = $this->getEntityManager()->getRepository(DemandeAppro::class)->find($id); // recupération de la DA
		$dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $demandeAppro->getNumeroDemandeDit()]); // recupération du DIT associée à la DA
		$ditModel = new DitModel();
		$dataModel = $ditModel->recupNumSerieParcPourDa($dit->getIdMateriel());

		$numeroVersionMax = $this->getEntityManager()->getRepository(DemandeApproL::class)->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
		$demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->getEntityManager()->getRepository(DaObservation::class)->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()]);

		$fichiers = $this->getAllDAFile([
			'baPath'    => $this->getBaPath($demandeAppro),
			'orPath'    => $this->getOrPath($demandeAppro),
			'bcPath'    => $this->getBcPath($demandeAppro),
			'facblPath' => $this->getFacBlPath($demandeAppro),
			'devPjPath' => $this->getDevisPjPath($demandeAppro),
		]);

		$demandeApproLPrepared = $this->prepareDataForDisplayDetail($demandeAppro->getDAL());

		return $this->render('da/detail.html.twig', [
			'detailTemplate'      		=> 'detail-avec-dit',
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
