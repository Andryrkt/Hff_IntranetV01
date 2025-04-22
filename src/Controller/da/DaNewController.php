<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\admin\Application;
use App\Form\da\DaObservationType;
use App\Form\da\DemandeApproFormType;
use App\Entity\dit\DemandeIntervention;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproLRCollectionType;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Traits\da\DemandeApproTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaNewController extends Controller
{
    private DaObservation $daObservation;

    public function __construct()
    {
        parent::__construct();
        $this->daObservation = new DaObservation();
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
        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);

        // $dit->getAgenceDebiteurId();

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

            $demandeAppro
                ->setDemandeur($this->getUser()->getNomUtilisateur())
                ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ;

            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                $DAL
                    ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal('Ouvert')
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

            $this->insertionObservation($demandeAppro);

            self::$em->flush();

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("da_list");
        }
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
