<?php

namespace App\Controller\da\Direct;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Form\da\DaObservationType;
use App\Controller\Traits\da\detail\DaDetailDirectTrait;
use App\Controller\Traits\lienGenerique;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDetailDirectController extends Controller
{
	use lienGenerique;
	use DaAfficherTrait;
	use DaDetailDirectTrait;

	public function __construct()
	{
		parent::__construct();
		$this->setEntityManager(self::$em);
		$this->initDaDetailDirectTrait();
	}

	/**
	 * @Route("/detail-direct/{id}", name="da_detail_direct")
	 */
	public function detail(int $id, Request $request)
	{
		//verification si user connecter
		$this->verifierSessionUtilisateur();
		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
		$numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());

		$demandeAppro = $this->filtreDal($demandeAppro, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$formObservation = self::$validator->createBuilder(DaObservationType::class, $daObservation)->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()]);

		$fichiers = $this->getAllDAFile([
			'baPath'    => $this->getBaPath($demandeAppro),
			'bcPath'    => $this->getBcPath($demandeAppro),
			'facblPath' => $this->getFacBlPath($demandeAppro),
		]);

		self::$twig->display('da/detail.html.twig', [
			'formObservation'			=> $formObservation->createView(),
			'demandeAppro'      		=> $demandeAppro,
			'observations'      		=> $observations,
			'fichiers'            		=> $fichiers,
			'connectedUser'     		=> $this->getUser(),
			'statutAutoriserModifAte' 	=> $demandeAppro->getStatutDal() === DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
			'estAte'            		=> Controller::estUserDansServiceAtelier(),
			'estAppro'          		=> Controller::estUserDansServiceAppro(),
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

			if (Controller::estUserDansServiceAppro() && $daObservation->getStatutChange()) {
				$this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
				$this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);

				$this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
			}

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			/** ENVOIE D'EMAIL à l'APPRO pour l'observation */
			$service = Controller::estUserDansServiceAtelier() ? 'atelier' : (Controller::estUserDansServiceAppro() ? 'appro' : '');
			$this->envoyerMailObservation([
				'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
				'idDa'          => $demandeAppro->getId(),
				'mailDemandeur' => $demandeAppro->getUser()->getMail(),
				'observation'   => $daObservation->getObservation(),
				'service'       => $service,
				'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
			]);

			$this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("list_da");
		}
	}

	/** 
	 * Fonctions pour envoyer un mail sur l'observation à la service Appro 
	 */
	private function envoyerMailObservation(array $tab)
	{
		$email       = new EmailService;

		$to = $tab['service'] == 'atelier' ? DemandeAppro::MAIL_APPRO : $tab['mailDemandeur'];

		$content = [
			'to'        => $to,
			// 'cc'        => array_slice($emailValidateurs, 1),
			'template'  => 'da/email/emailDa.html.twig',
			'variables' => [
				'statut'     => "commente",
				'subject'    => "{$tab['numDa']} - Observation ajoutée par l'" . strtoupper($tab['service']),
				'tab'        => $tab,
				'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail/" . $tab['idDa']),
			]
		];
		$email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
		// $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
		$email->sendEmail($content['to'], [], $content['template'], $content['variables']);
	}

	private function modificationStatutDal(string $numDa, string $statut): void
	{
		$numeroVersionMax = self::$em->getRepository(DemandeApproL::class)->getNumeroVersionMax($numDa);
		$dals = self::$em->getRepository(DemandeApproL::class)->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

		foreach ($dals as  $dal) {
			$dal->setStatutDal($statut);
			$dal->setEdit(3);
			self::$em->persist($dal);
		}

		self::$em->flush();
	}

	private function modificationStatutDa(string $numDa, string $statut): void
	{
		$da = self::$em->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $numDa]);
		$da->setStatutDal($statut);

		self::$em->persist($da);
		self::$em->flush();
	}
}
