<?php

namespace App\Controller\da;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\admin\Application;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaObservationRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaNewController extends Controller
{

    use lienGenerique;

    private DaObservation $daObservation;
    private DaObservationRepository $daObservationRepository;
    private DitRepository $ditRepository;


    public function __construct()
    {
        parent::__construct();
        $this->daObservation = new DaObservation();
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
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

        $observations = $this->daObservationRepository->findBy([], ['dateCreation' => 'DESC']);


        self::$twig->display('da/new.html.twig', [
            'form' => $form->createView(),
            'observations' => $observations
        ]);
    }



    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $demandeAppro
                ->setDemandeur($this->getUser()->getNomUtilisateur())
                ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ;

            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                $DAL
                    ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal('soumis à l’appro')
                ;
                if (null === $DAL->getNumeroFournisseur()) {
                    $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Erreur : Le nom du fournisseur doit correspondre à l’un des choix proposés.']);
                    $this->redirectToRoute("da_list");
                }
                self::$em->persist($DAL);
            }

            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DAP']);
            $application->setDerniereId($demandeAppro->getNumeroDemandeAppro());

            self::$em->persist($application);
            self::$em->persist($demandeAppro);

            if ($demandeAppro->getObservation() == null) {
                $this->insertionObservation($demandeAppro);
            }

            self::$em->flush();

            $this->envoyerMailAuxAppros([
                'id'            => $demandeAppro->getId(),
                'numDa'        => $demandeAppro->getNumeroDemandeAppro(),
                'objet'         => $demandeAppro->getObjetDal(),
                'detail'        => $demandeAppro->getDetailDal(),
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
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
            'to'        => 'hasina.andrianadison@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "newDa",
                'subject'    => "{$tab['numDa']} - Nouvelle demande d'approvisionnement créé",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/demande-appro/list")
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
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
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceDebiteur($dit->getAgenceDebiteurId())
            ->setServiceDebiteur($dit->getServiceDebiteurId())
            ->setAgenceEmetteur($dit->getAgenceEmetteurId())
            ->setServiceEmetteur($dit->getServiceEmetteurId())
            ->setAgenceServiceDebiteur($dit->getAgenceDebiteurId()->getCodeAgence() . '-' . $dit->getServiceDebiteurId()->getCodeService())
            ->setAgenceServiceEmetteur($dit->getAgenceEmetteurId()->getCodeAgence() . '-' . $dit->getServiceEmetteurId()->getCodeService())
            ->setStatutDal('Ouvert')
        ;
    }
}
