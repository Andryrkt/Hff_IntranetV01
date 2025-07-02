<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Form\da\DaObservationType;
use App\Model\dit\DitModel;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDetailController extends Controller
{
	use lienGenerique;

	private DemandeApproRepository $daRepository;
	private DitRepository $ditRepository;
	private DaObservationRepository $daObservationRepository;
	private DemandeApproLRepository $daLRepository;

	public function __construct()
	{
		parent::__construct();
		$this->daRepository = self::$em->getRepository(DemandeAppro::class);
		$this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
		$this->daObservationRepository = self::$em->getRepository(DaObservation::class);
		$this->daLRepository = self::$em->getRepository(DemandeApproL::class);
	}

	/**
	 * @Route("/detail/{id}", name="da_detail")
	 */
	public function deatil(int $id, Request $request)
	{
		//verification si user connecter
		$this->verifierSessionUtilisateur();
		$dit = $this->ditRepository->find($id); // recupération du DIT
		$demandeAppro = $this->daRepository->findOneBy(['numeroDemandeDit' => $dit->getNumeroDemandeIntervention()]); // recupération de la DA associée au DIT
		$ditModel = new DitModel;
		$dataModel = $ditModel->recupNumSerieParcPourDa($dit->getIdMateriel());

		$numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
		$demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$form = self::$validator->createBuilder(DaObservationType::class, $daObservation)->getForm();

		$this->traitementFormulaire($form, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

		self::$twig->display('da/detail.html.twig', [
			'form'				=> $form->createView(),
			'demandeAppro'      => $demandeAppro,
			'observations'      => $observations,
			'numSerie'          => $dataModel[0]['num_serie'],
			'numParc'           => $dataModel[0]['num_parc'],
			'dit'               => $dit,
			// 'idDit'             => $id,
			// 'numeroVersionMax'  => $numeroVersionMax,
			// 'numDa'             => $numDa,
			'nomFichierRefZst'  => $demandeAppro->getNonFichierRefZst(),
			'estAte'            => $this->estUserDansServiceAtelier(),
		]);
	}

	/**  
	 * 
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
			$this->insertionObservation($daObservation, $demandeAppro);

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			/** ENVOIE D'EMAIL à l'APPRO pour l'observation */
			$service = $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : '');
			$this->envoyerMailObservation([
				'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
				'observation'   => $daObservation->getObservation(),
				'service'       => $service,
				'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
			]);

			$this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			$this->redirectToRoute("da_list");
		}
	}

	/** 
	 * Insertion de l'observation dans la Base de donnée
	 */
	private function insertionObservation(DaObservation $daObservation, DemandeAppro $demandeAppro)
	{
		$text = str_replace(["\r\n", "\n", "\r"], "<br>", $daObservation->getObservation());

		$daObservation
			->setObservation($text)
			->setNumDa($demandeAppro->getNumeroDemandeAppro())
			->setUtilisateur($this->getUser()->getNomUtilisateur())
		;

		self::$em->persist($daObservation);
		self::$em->flush();
	}

	/** 
	 * Fonctions pour envoyer un mail sur l'observation à la service Appro 
	 */
	private function envoyerMailObservation(array $tab)
	{
		$email       = new EmailService;

		$to = $tab['service'] == 'atelier' ? DemandeAppro::MAIL_APPRO : DemandeAppro::MAIL_ATELIER;

		$content = [
			'to'        => $to,
			// 'cc'        => array_slice($emailValidateurs, 1),
			'template'  => 'da/email/emailDa.html.twig',
			'variables' => [
				'statut'     => "commente",
				'subject'    => "{$tab['numDa']} - Observation ajoutée par l'" . strtoupper($tab['service']),
				'tab'        => $tab,
				'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list"),
			]
		];
		$email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
		// $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
		$email->sendEmail($content['to'], [], $content['template'], $content['variables']);
	}
}
