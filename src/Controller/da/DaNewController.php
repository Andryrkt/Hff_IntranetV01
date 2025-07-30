<?php

namespace App\Controller\da;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Controller\Traits\da\DemandeApproTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\lienGenerique;
use App\Model\da\DaModel;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaNewController extends Controller
{
    use DaTrait;
    use DemandeApproTrait;
    use lienGenerique;

    private DaObservation $daObservation;
    private DaObservationRepository $daObservationRepository;
    private DitRepository $ditRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DaModel $daModel;


    public function __construct()
    {
        parent::__construct();
        $this->daObservation = new DaObservation();
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->daModel = new DaModel();
    }

    /**
     * @Route("/first-form", name="da_first_form")
     */
    public function firstForm()
    {
        self::$twig->display('da/first-form.html.twig');
    }

    /**
     * @Route("/new/{id}", name="da_new")
     */
    public function new($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // obtenir le dit correspondant à l'id {id} du DIT
        /** 
         * @var DemandeIntervention $dit DIT correspondant à l'id $id
         */
        $dit = $this->ditRepository->find($id);

        $demandeAppro = new DemandeAppro;
        $this->initialisationDemandeAppro($demandeAppro, $dit);

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();
        $this->traitementForm($form, $request, $demandeAppro);

        self::$twig->display('da/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** 
             * @var DemandeAppro $demandeAppro
             */
            $demandeAppro = $form->getData();
            $demandeAppro
                ->setDemandeur(Controller::getUser()->getNomUtilisateur())
                ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
                ->setNiveauUrgence($this->ditRepository->getNiveauUrgence($demandeAppro->getNumeroDemandeDit()))
            ;

            $numDa = $demandeAppro->getNumeroDemandeAppro();
            $numDit = $demandeAppro->getNumeroDemandeDit();
            $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

            $formDAL = $form->get('DAL');
            /** ajout de ligne de demande appro dans la table Demande_Appro_L */
            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                /** 
                 * @var DemandeApproL $DAL
                 */
                $DAL
                    ->setNumeroDemandeAppro($numDa)
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
                    ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                    ->setPrixUnitaire($this->daModel->getPrixUnitaire($DAL->getArtRefp())[0])
                    ->setNumeroDit($numDit)
                    ->setJoursDispo($this->getJoursRestants($DAL))
                ;
                $this->traitementFichiers($DAL, $formDAL[$ligne + 1]->get('fileNames')->getData()); // traitement des fichiers uploadés pour chaque ligne DAL
                if (null === $DAL->getNumeroFournisseur()) {
                    $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Erreur : Le nom du fournisseur doit correspondre à l’un des choix proposés.']);
                    $this->redirectToRoute("da_list");
                }
                self::$em->persist($DAL);
            }

            /** Modifie la colonne dernière_id dans la table applications */
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DAP']);
            $application->setDerniereId($numDa);
            self::$em->persist($application);

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            self::$em->persist($demandeAppro);

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro);
            }

            self::$em->flush();

            /** ENVOIE D'EMAIL */
            $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
            $dal = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

            $this->envoyerMailAuxAppros([
                'id'            => $demandeAppro->getId(),
                'numDa'         => $numDa,
                'objet'         => $demandeAppro->getObjetDal(),
                'detail'        => $demandeAppro->getDetailDal(),
                'dal'           => $dal,
                'service'       => 'atelier',
                'observation'   => $demandeAppro->getObservation() !== null ? $demandeAppro->getObservation() : '-',
                'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
            ]);

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("da_list");
        }
    }

    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAppros(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => DemandeAppro::MAIL_APPRO,
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "newDa",
                'subject'    => "{$tab['numDa']} - Nouvelle demande d'approvisionnement créé",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }

    private function insertionObservation(DemandeAppro $demandeAppro): void
    {
        $daObservation = $this->recupDonnerDaObservation($demandeAppro);

        self::$em->persist($daObservation);
    }

    private function recupDonnerDaObservation(DemandeAppro $demandeAppro): DaObservation
    {
        return $this->daObservation
            ->setNumDa($demandeAppro->getNumeroDemandeAppro())
            ->setUtilisateur($demandeAppro->getDemandeur())
            ->setObservation($demandeAppro->getObservation())
        ;
    }

    private function initialisationDemandeAppro(DemandeAppro $demandeAppro, DemandeIntervention $dit)
    {
        $demandeAppro
            ->setDit($dit)
            ->setObjetDal($dit->getObjetDemande())
            ->setDetailDal($dit->getDetailDemande())
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceDebiteur($dit->getAgenceDebiteurId())
            ->setServiceDebiteur($dit->getServiceDebiteurId())
            ->setAgenceEmetteur($dit->getAgenceEmetteurId())
            ->setServiceEmetteur($dit->getServiceEmetteurId())
            ->setAgenceServiceDebiteur($dit->getAgenceDebiteurId()->getCodeAgence() . '-' . $dit->getServiceDebiteurId()->getCodeService())
            ->setAgenceServiceEmetteur($dit->getAgenceEmetteurId()->getCodeAgence() . '-' . $dit->getServiceEmetteurId()->getCodeService())
            ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
            ->setUser(Controller::getUser())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD pour chaque ligne de la demande appro (DAL)
     */
    private function traitementFichiers(DemandeApproL $dal, $files): void
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
    }
}
