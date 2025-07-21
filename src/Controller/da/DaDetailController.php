<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\da\DaObservationType;
use App\Model\dit\DitModel;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDetailController extends Controller
{
	use lienGenerique;
	use DaTrait;

	private DemandeApproRepository $daRepository;
	private DitRepository $ditRepository;
	private DaObservationRepository $daObservationRepository;
	private DemandeApproLRepository $daLRepository;
	private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
	private DossierInterventionAtelierModel $dossierInterventionAtelierModel;

	public function __construct()
	{
		parent::__construct();
		$this->daRepository = self::$em->getRepository(DemandeAppro::class);
		$this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
		$this->daObservationRepository = self::$em->getRepository(DaObservation::class);
		$this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
		$this->daLRepository = self::$em->getRepository(DemandeApproL::class);
		$this->dossierInterventionAtelierModel = new DossierInterventionAtelierModel;
	}

	/**
	 * @Route("/detail/{id}", name="da_detail")
	 */
	public function detail(int $id, Request $request)
	{
		//verification si user connecter
		$this->verifierSessionUtilisateur();
		/** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
		$demandeAppro = $this->daRepository->find($id); // recupération de la DA
		$dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $demandeAppro->getNumeroDemandeDit()]); // recupération du DIT associée à la DA
		$ditModel = new DitModel;
		$dataModel = $ditModel->recupNumSerieParcPourDa($dit->getIdMateriel());

		$numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
		$demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

		$daObservation = new DaObservation;
		$formObservation = self::$validator->createBuilder(DaObservationType::class, $daObservation)->getForm();

		$this->traitementFormulaire($formObservation, $request, $demandeAppro);

		$observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()]);

		$fichiers = $this->getAllDAFile([
			'baPath' => $this->getBaPath($demandeAppro),
			'orPath' => $this->getOrPath($demandeAppro),
			'bcPath' => $this->getBcPath(),
			'blPath' => $this->getBlPath(),
			'facPath' => $this->getFacPath(),
		]);

		self::$twig->display('da/detail.html.twig', [
			'formObservation'			=> $formObservation->createView(),
			'demandeAppro'      		=> $demandeAppro,
			'observations'      		=> $observations,
			'numSerie'          		=> $dataModel[0]['num_serie'],
			'numParc'           		=> $dataModel[0]['num_parc'],
			'dit'               		=> $dit,
			'fichiers'            		=> $fichiers,
			'connectedUser'     		=> Controller::getUser(),
			'statutAutoriserModifAte' 	=> $demandeAppro->getStatutDal() === DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
			'nomFichierRefZst'  		=> $demandeAppro->getNonFichierRefZst(),
			'estAte'            		=> Controller::estUserDansServiceAtelier(),
			'estAppro'          		=> Controller::estUserDansServiceAppro(),
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

			$this->insertionObservation($daObservation, $demandeAppro);

			if (Controller::estUserDansServiceAppro() && $daObservation->getStatutChange()) {
				$this->duplicationDataDaL($demandeAppro->getNumeroDemandeAppro());
				$this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
				$this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
			}

			$notification = [
				'type' => 'success',
				'message' => 'Votre observation a été enregistré avec succès.',
			];

			/** ENVOIE D'EMAIL à l'APPRO pour l'observation */
			$service = Controller::estUserDansServiceAtelier() ? 'atelier' : (Controller::estUserDansServiceAppro() ? 'appro' : '');
			$this->envoyerMailObservation([
				'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
				'mailDemandeur' => $demandeAppro->getUser()->getMail(),
				'observation'   => $daObservation->getObservation(),
				'service'       => $service,
				'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
			]);

			$this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
			return $this->redirectToRoute("da_list");
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
			->setUtilisateur(Controller::getUser()->getNomUtilisateur())
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

		$to = $tab['service'] == 'atelier' ? DemandeAppro::MAIL_APPRO : $tab['mailDemandeur'];

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

	/**
	 * Dupliquer les lignes de la table demande_appro_L
	 *
	 * @param array $refs
	 * @param [type] $data
	 * @return array
	 */
	private function duplicationDataDaL($numDa): void
	{
		$numeroVersionMax = $this->daLRepository->getNumeroVersionMax($numDa);
		$dals = $this->daLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);

		foreach ($dals as $dal) {
			// On clone l'entité (copie en mémoire)
			$newDal = clone $dal;
			$newDal->setNumeroVersion($this->autoIncrementForDa($dal->getNumeroVersion())); // Incrémenter le numéro de version
			$newDal->setEdit(1); // Indiquer que c'est une version modifiée

			// Doctrine crée un nouvel ID automatiquement (ne pas setter manuellement)
			self::$em->persist($newDal);
		}

		self::$em->flush();
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
